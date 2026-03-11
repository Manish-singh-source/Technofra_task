<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use App\Mail\TestMail;

class SettingController extends Controller
{
    /**
     * Display the settings page with all tabs.
     */
    public function index()
    {
        $settings = Setting::getAllSettings();
        $settings = array_merge($this->getMailSettingsFromEnvironment(), $settings);

        if (empty($settings['mail_from_name'])) {
            $settings['mail_from_name'] = $this->getMailSettingsFromEnvironment()['mail_from_name'] ?? ($settings['company_name'] ?? config('app.name'));
        }
        $teams = Team::query()
            ->orderBy('name')
            ->get(['name', 'description', 'icon_path'])
            ->map(function ($team) {
                $iconPath = $this->normalizeTeamIconPath((string) ($team->icon_path ?? ''));

                return [
                    'name' => (string) $team->name,
                    'description' => (string) ($team->description ?? ''),
                    'icon_path' => $iconPath,
                ];
            })
            ->values()
            ->all();
        return view('settings.index', compact('settings', 'teams'));
    }

    /**
     * Update general settings (logo, favicon, company_name).
     */
    public function updateGeneral(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'crm_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,ico|dimensions:max_width=32,max_height=32',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Update company name
            Setting::set('company_name', $request->company_name, 'text');

            // Handle logo upload
            if ($request->hasFile('crm_logo')) {
                // Delete old logo if exists
                $oldLogo = Setting::get('crm_logo');
                if ($oldLogo && Storage::exists('public/settings/' . $oldLogo)) {
                    Storage::delete('public/settings/' . $oldLogo);
                }

                $logo = $request->file('crm_logo');
                $logoName = 'logo_' . time() . '.' . $logo->getClientOriginalExtension();
                $logo->storeAs('public/settings', $logoName);
                Setting::set('crm_logo', $logoName, 'image');
            }

            // Handle favicon upload
            if ($request->hasFile('favicon')) {
                // Delete old favicon if exists
                $oldFavicon = Setting::get('favicon');
                if ($oldFavicon && Storage::exists('public/settings/' . $oldFavicon)) {
                    Storage::delete('public/settings/' . $oldFavicon);
                }

                $favicon = $request->file('favicon');
                $faviconName = 'favicon_' . time() . '.' . $favicon->getClientOriginalExtension();
                $favicon->storeAs('public/settings', $faviconName);
                Setting::set('favicon', $faviconName, 'image');
            }

            DB::commit();

            return redirect()->route('settings')
                ->with('success', 'General settings updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update general settings: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update company information.
     */
    public function updateCompany(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:50',
            'office_start_time' => 'required|date_format:H:i',
            'lunch_start_time' => 'required|date_format:H:i',
            'lunch_end_time' => 'required|date_format:H:i',
            'office_end_time' => 'required|date_format:H:i',
        ]);

        $validator->after(function ($validator) use ($request) {
            $officeStart = strtotime((string) $request->office_start_time);
            $lunchStart = strtotime((string) $request->lunch_start_time);
            $lunchEnd = strtotime((string) $request->lunch_end_time);
            $officeEnd = strtotime((string) $request->office_end_time);

            if ($officeStart === false || $lunchStart === false || $lunchEnd === false || $officeEnd === false) {
                return;
            }

            if (!($officeStart < $lunchStart && $lunchStart < $lunchEnd && $lunchEnd < $officeEnd)) {
                $validator->errors()->add('office_start_time', 'Office timings must follow: Office Start < Lunch Start < Lunch End < Office End.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('active_settings_tab', 'company');
        }

        try {
            DB::beginTransaction();

            $fields = [
                'company_name' => 'text',
                'company_email' => 'text',
                'company_phone' => 'text',
                'address' => 'text',
                'city' => 'text',
                'state' => 'text',
                'zip' => 'text',
                'country' => 'text',
                'website' => 'text',
                'gst_number' => 'text',
                'office_start_time' => 'text',
                'lunch_start_time' => 'text',
                'lunch_end_time' => 'text',
                'office_end_time' => 'text',
            ];

            foreach ($fields as $field => $type) {
                Setting::set($field, $request->$field, $type);
            }

            DB::commit();

            return redirect()->route('settings')
                ->with('success', 'Company information updated successfully.')
                ->with('active_settings_tab', 'company');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update company information: ' . $e->getMessage())
                ->withInput()
                ->with('active_settings_tab', 'company');
        }
    }

    /**
     * Update email/SMTP settings.
     */
    public function updateEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mail_engine' => 'required|in:phpmailer,codeigniter',
            'email_protocol' => 'required|in:smtp,microsoft_oauth,gmail_oauth,sendmail,mail',
            'email_encryption' => 'nullable|in:tls,ssl,none',
            'smtp_host' => 'required_if:email_protocol,smtp|nullable|string|max:255',
            'smtp_port' => 'required_if:email_protocol,smtp|nullable|integer|max:65535',
            'email' => 'required|email|max:255',
            'smtp_username' => 'required_if:email_protocol,smtp|nullable|string|max:255',
            'smtp_password' => 'required_if:email_protocol,smtp|nullable|string|max:255',
            'mail_from_name' => 'nullable|string|max:255',
            'email_charset' => 'nullable|string|max:50',
            'bcc_all' => 'nullable|email',
            'email_signature' => 'nullable|string',
            'predefined_header' => 'nullable|string',
            'predefined_footer' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $fields = [
                'mail_engine' => 'text',
                'email_protocol' => 'text',
                'email_encryption' => 'text',
                'smtp_host' => 'text',
                'smtp_port' => 'text',
                'email' => 'text',
                'smtp_username' => 'text',
                'smtp_password' => 'text',
                'mail_from_name' => 'text',
                'email_charset' => 'text',
                'bcc_all' => 'text',
                'email_signature' => 'text',
                'predefined_header' => 'text',
                'predefined_footer' => 'text',
            ];

            foreach ($fields as $field => $type) {
                Setting::set($field, $request->$field, $type);
            }

            $this->updateEnvironmentFile([
                'MAIL_MAILER' => $this->resolveLaravelMailer((string) $request->email_protocol),
                'MAIL_HOST' => (string) $request->smtp_host,
                'MAIL_PORT' => (string) $request->smtp_port,
                'MAIL_USERNAME' => (string) $request->smtp_username,
                'MAIL_PASSWORD' => (string) $request->smtp_password,
                'MAIL_ENCRYPTION' => $request->email_encryption === 'none' ? 'null' : (string) $request->email_encryption,
                'MAIL_FROM_ADDRESS' => (string) $request->email,
                'MAIL_FROM_NAME' => (string) ($request->mail_from_name ?: ($request->company_name ?: config('app.name'))),
            ]);

            $this->applyRuntimeMailConfiguration([
                'email_protocol' => $request->email_protocol,
                'email_encryption' => $request->email_encryption,
                'smtp_host' => $request->smtp_host,
                'smtp_port' => $request->smtp_port,
                'smtp_username' => $request->smtp_username,
                'smtp_password' => $request->smtp_password,
                'email' => $request->email,
                'mail_from_name' => $request->mail_from_name,
                'company_name' => Setting::get('company_name', config('app.name')),
            ]);

            DB::commit();

            return redirect()->route('settings')
                ->with('success', 'Email settings updated successfully.')
                ->with('active_settings_tab', 'email');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update email settings: ' . $e->getMessage())
                ->withInput()
                ->with('active_settings_tab', 'email');
        }
    }


    /**
     * Update renewal notification settings.
     */
    public function updateRenewal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'renewal_admin_email' => 'required|email|max:255',
            'renewal_notification_time' => 'required|date_format:H:i',
            'renewal_notice_days' => 'required|integer|min:1|max:30',
            'renewal_notifications_enabled' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('active_settings_tab', 'renewal');
        }

        try {
            DB::beginTransaction();

            Setting::set('renewal_admin_email', $request->renewal_admin_email, 'text');
            Setting::set('renewal_notification_time', $request->renewal_notification_time, 'text');
            Setting::set('renewal_notice_days', (string) $request->renewal_notice_days, 'text');
            Setting::set(
                'renewal_notifications_enabled',
                $request->boolean('renewal_notifications_enabled') ? '1' : '0',
                'text'
            );

            DB::commit();

            return redirect()->route('settings')
                ->with('success', 'Renewal notification settings updated successfully.')
                ->with('active_settings_tab', 'renewal');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update renewal notification settings: ' . $e->getMessage())
                ->withInput()
                ->with('active_settings_tab', 'renewal');
        }
    }
    /**
     * Send test email.
     */
    public function sendTestEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'test_email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Get email settings
            $settings = array_merge($this->getMailSettingsFromEnvironment(), Setting::getAllSettings());

            $this->applyRuntimeMailConfiguration($settings);

            // Send test email
            Mail::to($request->test_email)->send(new TestMail($settings));

            return redirect()->route('settings')
                ->with('success', 'Test email sent successfully to ' . $request->test_email)
                ->with('active_settings_tab', 'email');
        } catch (\Exception $e) {
            return redirect()->route('settings')
                ->with('error', 'Failed to send test email: ' . $e->getMessage())
                ->with('active_settings_tab', 'email');
        }
    }

    /**
     * Update team settings.
     */
    public function updateTeams(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'teams' => 'required|array|min:1',
            'teams.*.name' => 'required|string|max:255|distinct',
            'teams.*.description' => 'nullable|string|max:1000',
            'teams.*.icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'teams.*.existing_icon_path' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput()
                ->with('active_settings_tab', 'teams');
        }

        try {
            $rows = $request->input('teams', []);
            $teams = [];
            foreach ($rows as $index => $row) {
                $name = trim((string) ($row['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $description = trim((string) ($row['description'] ?? ''));
                $iconPath = $this->normalizeTeamIconPath(trim((string) ($row['existing_icon_path'] ?? '')));

                if ($request->hasFile("teams.$index.icon")) {
                    $iconFile = $request->file("teams.$index.icon");
                    if ($iconFile && $iconFile->isValid()) {
                        $iconPath = $this->storeTeamIcon($iconFile);
                    }
                }

                $teams[] = [
                    'name' => $name,
                    'description' => $description !== '' ? $description : null,
                    'icon_path' => $iconPath !== '' ? $iconPath : null,
                    'is_active' => true,
                ];
            }

            if (count($teams) === 0) {
                return redirect()->back()
                    ->with('error', 'At least one team is required.')
                    ->withInput()
                    ->with('active_settings_tab', 'teams');
            }

            DB::beginTransaction();
            $oldIconPaths = Team::query()
                ->whereNotNull('icon_path')
                ->pluck('icon_path')
                ->toArray();

            Team::query()->delete();
            foreach ($teams as $teamData) {
                Team::create($teamData);
            }
            DB::commit();

            $usedIconPaths = collect($teams)
                ->pluck('icon_path')
                ->filter()
                ->values()
                ->all();
            foreach ($oldIconPaths as $oldPath) {
                if (!in_array($oldPath, $usedIconPaths, true)) {
                    $this->deleteTeamIcon($oldPath);
                }
            }

            return redirect()->route('settings')
                ->with('success', 'Teams updated successfully.')
                ->with('active_settings_tab', 'teams');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update teams: ' . $e->getMessage())
                ->withInput()
                ->with('active_settings_tab', 'teams');
        }
    }

    private function storeTeamIcon($iconFile): string
    {
        $destinationPath = public_path('uploads/team-icons');
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $fileName = uniqid('team_', true) . '.' . strtolower($iconFile->getClientOriginalExtension());
        $iconFile->move($destinationPath, $fileName);

        return 'uploads/team-icons/' . $fileName;
    }

    private function normalizeTeamIconPath(string $iconPath): string
    {
        $normalized = trim(str_replace('\\', '/', $iconPath));
        $normalized = ltrim($normalized, '/');

        if ($normalized === '') {
            return '';
        }

        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, 7);
        }

        if (str_starts_with($normalized, 'uploads/team-icons/')) {
            return $normalized;
        }

        if (str_starts_with($normalized, 'team-icons/')) {
            $legacyStoragePath = storage_path('app/public/' . $normalized);
            $destinationPath = public_path('uploads/team-icons');
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $targetRelativePath = 'uploads/team-icons/' . basename($normalized);
            $targetAbsolutePath = public_path($targetRelativePath);
            if (is_file($legacyStoragePath) && !is_file($targetAbsolutePath)) {
                copy($legacyStoragePath, $targetAbsolutePath);
            }

            if (is_file($targetAbsolutePath)) {
                return $targetRelativePath;
            }
        }

        return $normalized;
    }

    private function deleteTeamIcon(?string $iconPath): void
    {
        $raw = trim(str_replace('\\', '/', (string) $iconPath));
        $raw = ltrim($raw, '/');

        if ($raw === '') {
            return;
        }

        if (str_starts_with($raw, 'public/')) {
            $raw = substr($raw, 7);
        }

        $normalized = $this->normalizeTeamIconPath($raw);

        $publicFile = public_path($normalized);
        if (is_file($publicFile)) {
            @unlink($publicFile);
        }

        if (str_starts_with($raw, 'team-icons/') && Storage::disk('public')->exists($raw)) {
            Storage::disk('public')->delete($raw);
        }
    }

    private function getMailSettingsFromEnvironment(): array
    {
        $envValues = $this->readEnvironmentFile();

        return [
            'email_protocol' => $envValues['MAIL_MAILER'] ?? config('mail.default', 'smtp'),
            'email_encryption' => $this->normalizeEnvironmentValue($envValues['MAIL_ENCRYPTION'] ?? config('mail.mailers.smtp.encryption', 'tls')) ?? 'none',
            'smtp_host' => $envValues['MAIL_HOST'] ?? config('mail.mailers.smtp.host', ''),
            'smtp_port' => $envValues['MAIL_PORT'] ?? config('mail.mailers.smtp.port', 587),
            'smtp_username' => $envValues['MAIL_USERNAME'] ?? config('mail.mailers.smtp.username', ''),
            'smtp_password' => $envValues['MAIL_PASSWORD'] ?? config('mail.mailers.smtp.password', ''),
            'email' => $envValues['MAIL_FROM_ADDRESS'] ?? config('mail.from.address', ''),
            'mail_from_name' => $envValues['MAIL_FROM_NAME'] ?? config('mail.from.name', config('app.name')),
        ];
    }

    private function applyRuntimeMailConfiguration(array $settings): void
    {
        $protocol = $this->resolveLaravelMailer((string) ($settings['email_protocol'] ?? 'smtp'));
        $encryption = $settings['email_encryption'] ?? 'tls';
        if ($encryption === 'none' || $encryption === 'null' || $encryption === '') {
            $encryption = null;
        }

        config([
            'mail.mailer' => $protocol,
            'mail.default' => $protocol,
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $settings['smtp_host'] ?? '',
            'mail.mailers.smtp.port' => $settings['smtp_port'] ?? 587,
            'mail.mailers.smtp.username' => $settings['smtp_username'] ?? '',
            'mail.mailers.smtp.password' => $settings['smtp_password'] ?? '',
            'mail.mailers.smtp.encryption' => $encryption,
            'mail.from.address' => $settings['email'] ?? '',
            'mail.from.name' => $settings['mail_from_name'] ?? $settings['company_name'] ?? config('app.name'),
        ]);

        app()->forgetInstance('mailer');
        Mail::purge();
    }

    private function resolveLaravelMailer(string $protocol): string
    {
        return in_array($protocol, ['smtp', 'sendmail', 'mail'], true) ? $protocol : 'smtp';
    }

    private function readEnvironmentFile(): array
    {
        $path = base_path('.env');
        if (!is_file($path)) {
            return [];
        }

        $values = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $trimmed = trim((string) $line);
            if ($trimmed === '' || str_starts_with($trimmed, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $values[trim($key)] = $this->normalizeEnvironmentValue($value);
        }

        return $values;
    }

    private function updateEnvironmentFile(array $updates): void
    {
        $path = base_path('.env');
        if (!is_file($path)) {
            throw new \RuntimeException('.env file not found.');
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read .env file.');
        }

        foreach ($updates as $key => $value) {
            $formatted = $key . '=' . $this->formatEnvironmentValue($value);
            $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

            if (preg_match($pattern, $contents)) {
                $contents = preg_replace($pattern, $formatted, $contents, 1) ?? $contents;
            } else {
                $contents .= rtrim($contents) === '' ? $formatted : PHP_EOL . $formatted;
            }
        }

        if (file_put_contents($path, $contents) === false) {
            throw new \RuntimeException('Unable to write .env file.');
        }
    }

    private function normalizeEnvironmentValue($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        return strtolower($value) === 'null' ? null : $value;
    }

    private function formatEnvironmentValue($value): string
    {
        $value = (string) $value;

        if ($value === '') {
            return '""';
        }

        if (strtolower($value) === 'null') {
            return 'null';
        }

        if (preg_match('/\s|#|=|"|\'/', $value)) {
            return '"' . addcslashes($value, "\"\\") . '"';
        }

        return $value;
    }
    /**
     * API: Search tags.
     */
    public function searchTags(Request $request)
    {
        $query = $request->get('q', '');
        $tags = \App\Models\Tag::search($query);
        
        return response()->json([
            'success' => true,
            'data' => $tags,
        ]);
    }
}

