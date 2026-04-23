<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Mail\TestMail;
use App\Models\Department;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = [
            'links' => [
                'show' => url('/api/v1/settings'),
                'search_tags' => url('/api/v1/settings/search-tags'),
            ],
        ];

        if ($user && $user->can('view_general_settings')) {
            $data['general'] = $this->formatGeneralSettings();
            $data['teams'] = $this->formatTeams();
            $data['departments'] = $this->formatDepartments();
            $data['links']['general'] = url('/api/v1/settings/general');
            $data['links']['teams'] = url('/api/v1/settings/teams');
            $data['links']['departments'] = url('/api/v1/settings/departments');
        }

        if ($user && $user->can('view_company_information')) {
            $data['company'] = $this->formatCompanySettings();
            $data['links']['company'] = url('/api/v1/settings/company');
        }

        if ($user && $user->can('view_email_settings')) {
            $data['email'] = $this->formatEmailSettings();
            $data['renewal'] = $this->formatRenewalSettings();
            $data['links']['email'] = url('/api/v1/settings/email');
            $data['links']['renewal'] = url('/api/v1/settings/renewal');
            $data['links']['test_email'] = url('/api/v1/settings/test-email');
        }

        return ApiResponse::success($data, 'Settings fetched successfully.');
    }

    public function general(): JsonResponse
    {
        return ApiResponse::success($this->formatGeneralSettings(), 'General settings fetched successfully.');
    }

    public function updateGeneral(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'sometimes|required|string|max:255',
            'crm_logo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'favicon' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,ico|dimensions:max_width=32,max_height=32',
            'app_logo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'login_logo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_crm_logo' => 'sometimes|boolean',
            'remove_favicon' => 'sometimes|boolean',
            'remove_app_logo' => 'sometimes|boolean',
            'remove_login_logo' => 'sometimes|boolean',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (
                ! $request->filled('company_name')
                && ! $request->hasFile('crm_logo')
                && ! $request->hasFile('favicon')
                && ! $request->hasFile('app_logo')
                && ! $request->hasFile('login_logo')
                && ! $request->boolean('remove_crm_logo')
                && ! $request->boolean('remove_favicon')
                && ! $request->boolean('remove_app_logo')
                && ! $request->boolean('remove_login_logo')
            ) {
                $validator->errors()->add('settings', 'Please provide at least one general setting to update.');
            }
        });

        if ($validator->fails()) {
            return ApiResponse::error('Validation error.', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            if ($request->filled('company_name')) {
                Setting::set('company_name', $request->input('company_name'), 'text');
            }

            if ($request->boolean('remove_crm_logo')) {
                $this->deleteGeneralAsset(Setting::get('crm_logo'));
                Setting::set('crm_logo', null, 'image');
            }

            if ($request->boolean('remove_favicon')) {
                $this->deleteGeneralAsset(Setting::get('favicon'));
                Setting::set('favicon', null, 'image');
            }

            if ($request->boolean('remove_app_logo')) {
                $this->deleteGeneralAsset(Setting::get('app_logo'));
                Setting::set('app_logo', null, 'image');
            }

            if ($request->boolean('remove_login_logo')) {
                $this->deleteGeneralAsset(Setting::get('login_logo'));
                Setting::set('login_logo', null, 'image');
            }

            if ($request->hasFile('crm_logo')) {
                $this->deleteGeneralAsset(Setting::get('crm_logo'));
                Setting::set('crm_logo', $this->storeGeneralAsset($request->file('crm_logo'), 'logo'), 'image');
            }

            if ($request->hasFile('favicon')) {
                $this->deleteGeneralAsset(Setting::get('favicon'));
                Setting::set('favicon', $this->storeGeneralAsset($request->file('favicon'), 'favicon'), 'image');
            }

            if ($request->hasFile('app_logo')) {
                $this->deleteGeneralAsset(Setting::get('app_logo'));
                Setting::set('app_logo', $this->storeGeneralAsset($request->file('app_logo'), 'app_logo'), 'image');
            }

            if ($request->hasFile('login_logo')) {
                $this->deleteGeneralAsset(Setting::get('login_logo'));
                Setting::set('login_logo', $this->storeGeneralAsset($request->file('login_logo'), 'login_logo'), 'image');
            }

            DB::commit();

            return ApiResponse::success($this->formatGeneralSettings(), 'General settings updated successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return ApiResponse::error('Failed to update general settings.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }

    public function company(): JsonResponse
    {
        return ApiResponse::success($this->formatCompanySettings(), 'Company information fetched successfully.');
    }

    public function updateCompany(Request $request): JsonResponse
    {
        $fields = $this->companySettingFields();

        $validator = Validator::make($request->all(), [
            'company_name' => 'sometimes|required|string|max:255',
            'company_email' => 'sometimes|required|email|max:255',
            'company_phone' => 'sometimes|nullable|string|max:50',
            'address' => 'sometimes|nullable|string|max:500',
            'city' => 'sometimes|nullable|string|max:100',
            'state' => 'sometimes|nullable|string|max:100',
            'zip' => 'sometimes|nullable|string|max:20',
            'country' => 'sometimes|nullable|string|max:100',
            'website' => 'sometimes|nullable|string|max:255',
            'gst_number' => 'sometimes|nullable|string|max:50',
            'office_start_time' => 'sometimes|required|date_format:H:i',
            'lunch_start_time' => 'sometimes|required|date_format:H:i',
            'lunch_end_time' => 'sometimes|required|date_format:H:i',
            'office_end_time' => 'sometimes|required|date_format:H:i',
        ]);

        $validator->after(function ($validator) use ($request, $fields) {
            if (! $this->requestHasAny($request, array_keys($fields))) {
                $validator->errors()->add('settings', 'Please provide at least one company information setting to update.');

                return;
            }

            $settings = array_merge($this->companyTimingDefaults(), Setting::getAllSettings(), $request->only(array_keys($fields)));
            $officeStart = strtotime((string) ($settings['office_start_time'] ?? ''));
            $lunchStart = strtotime((string) ($settings['lunch_start_time'] ?? ''));
            $lunchEnd = strtotime((string) ($settings['lunch_end_time'] ?? ''));
            $officeEnd = strtotime((string) ($settings['office_end_time'] ?? ''));

            if ($officeStart === false || $lunchStart === false || $lunchEnd === false || $officeEnd === false) {
                return;
            }

            if (! ($officeStart < $lunchStart && $lunchStart < $lunchEnd && $lunchEnd < $officeEnd)) {
                $validator->errors()->add('office_start_time', 'Office timings must follow: Office Start < Lunch Start < Lunch End < Office End.');
            }
        });

        if ($validator->fails()) {
            return ApiResponse::error('Validation error.', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            foreach ($fields as $field => $type) {
                if (array_key_exists($field, $request->all())) {
                    Setting::set($field, $request->input($field), $type);
                }
            }

            DB::commit();

            return ApiResponse::success($this->formatCompanySettings(), 'Company information updated successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return ApiResponse::error('Failed to update company information.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }

    public function email(): JsonResponse
    {
        return ApiResponse::success($this->formatEmailSettings(), 'Email settings fetched successfully.');
    }

    public function updateEmail(Request $request): JsonResponse
    {
        $fields = $this->emailSettingFields();

        $validator = Validator::make($request->all(), [
            'mail_engine' => 'sometimes|required|in:phpmailer,codeigniter',
            'email_protocol' => 'sometimes|required|in:smtp,microsoft_oauth,gmail_oauth,sendmail,mail',
            'email_encryption' => 'sometimes|nullable|in:tls,ssl,none',
            'smtp_host' => 'required_if:email_protocol,smtp|nullable|string|max:255',
            'smtp_port' => 'required_if:email_protocol,smtp|nullable|integer|max:65535',
            'email' => 'sometimes|required|email|max:255',
            'smtp_username' => 'required_if:email_protocol,smtp|nullable|string|max:255',
            'smtp_password' => 'required_if:email_protocol,smtp|nullable|string|max:255',
            'mail_from_name' => 'sometimes|nullable|string|max:255',
            'email_charset' => 'sometimes|nullable|string|max:50',
            'bcc_all' => 'sometimes|nullable|email',
            'email_signature' => 'sometimes|nullable|string',
            'predefined_header' => 'sometimes|nullable|string',
            'predefined_footer' => 'sometimes|nullable|string',
        ]);

        $validator->after(function ($validator) use ($request, $fields) {
            if (! $this->requestHasAny($request, array_keys($fields))) {
                $validator->errors()->add('settings', 'Please provide at least one email setting to update.');
            }
        });

        if ($validator->fails()) {
            return ApiResponse::error('Validation error.', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            foreach ($fields as $field => $type) {
                if (array_key_exists($field, $request->all())) {
                    Setting::set($field, $request->input($field), $type);
                }
            }

            $settings = array_merge($this->formatEmailSettings(), $request->only(array_keys($fields)));
            $this->updateEnvironmentFile([
                'MAIL_MAILER' => $this->resolveLaravelMailer((string) ($settings['email_protocol'] ?? 'smtp')),
                'MAIL_HOST' => (string) ($settings['smtp_host'] ?? ''),
                'MAIL_PORT' => (string) ($settings['smtp_port'] ?? ''),
                'MAIL_USERNAME' => (string) ($settings['smtp_username'] ?? ''),
                'MAIL_PASSWORD' => (string) ($settings['smtp_password'] ?? ''),
                'MAIL_ENCRYPTION' => ($settings['email_encryption'] ?? null) === 'none' ? 'null' : (string) ($settings['email_encryption'] ?? ''),
                'MAIL_FROM_ADDRESS' => (string) ($settings['email'] ?? ''),
                'MAIL_FROM_NAME' => (string) (($settings['mail_from_name'] ?? '') ?: (Setting::get('company_name', config('app.name')))),
            ]);

            $this->applyRuntimeMailConfiguration(array_merge($settings, [
                'company_name' => Setting::get('company_name', config('app.name')),
            ]));

            DB::commit();

            return ApiResponse::success($this->formatEmailSettings(), 'Email settings updated successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return ApiResponse::error('Failed to update email settings.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }

    public function renewal(): JsonResponse
    {
        return ApiResponse::success($this->formatRenewalSettings(), 'Renewal notification settings fetched successfully.');
    }

    public function updateRenewal(Request $request): JsonResponse
    {
        $fields = $this->renewalSettingFields();

        $validator = Validator::make($request->all(), [
            'renewal_admin_email' => 'sometimes|required|email|max:255',
            'renewal_notification_time' => 'sometimes|required|date_format:H:i',
            'renewal_notice_days' => 'sometimes|required|integer|min:1|max:30',
            'renewal_notifications_enabled' => 'sometimes|nullable|boolean',
        ]);

        $validator->after(function ($validator) use ($request, $fields) {
            if (! $this->requestHasAny($request, array_keys($fields))) {
                $validator->errors()->add('settings', 'Please provide at least one renewal notification setting to update.');
            }
        });

        if ($validator->fails()) {
            return ApiResponse::error('Validation error.', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            foreach ($fields as $field => $type) {
                if (! array_key_exists($field, $request->all())) {
                    continue;
                }

                $value = $field === 'renewal_notifications_enabled'
                    ? ($request->boolean($field) ? '1' : '0')
                    : $request->input($field);

                Setting::set($field, $value, $type);
            }

            DB::commit();

            return ApiResponse::success($this->formatRenewalSettings(), 'Renewal notification settings updated successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return ApiResponse::error('Failed to update renewal notification settings.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }

    public function teams(): JsonResponse
    {
        return ApiResponse::success($this->formatTeams(), 'Teams fetched successfully.');
    }

    public function updateTeams(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'teams' => 'required|array|min:1',
            'teams.*.name' => 'required|string|max:255|distinct',
            'teams.*.description' => 'nullable|string|max:1000',
            'teams.*.icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            'teams.*.existing_icon_path' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation error.', $validator->errors(), 422);
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
                return ApiResponse::error('Validation error.', [
                    'teams' => ['At least one team is required.'],
                ], 422);
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

            $usedIconPaths = collect($teams)->pluck('icon_path')->filter()->values()->all();
            foreach ($oldIconPaths as $oldPath) {
                if (! in_array($oldPath, $usedIconPaths, true)) {
                    $this->deleteTeamIcon($oldPath);
                }
            }

            return ApiResponse::success($this->formatTeams(), 'Teams updated successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return ApiResponse::error('Failed to update teams.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }

    public function departments(): JsonResponse
    {
        return ApiResponse::success($this->formatDepartments(), 'Departments fetched successfully.');
    }

    public function updateDepartments(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'departments' => 'required|array|min:1',
            'departments.*.name' => 'required|string|max:255|distinct',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation error.', $validator->errors(), 422);
        }

        try {
            $departments = [];

            foreach ($request->input('departments', []) as $row) {
                $name = trim((string) ($row['name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $departments[] = [
                    'name' => $name,
                    'is_active' => true,
                ];
            }

            if (count($departments) === 0) {
                return ApiResponse::error('Validation error.', [
                    'departments' => ['At least one department is required.'],
                ], 422);
            }

            DB::beginTransaction();

            Department::query()->delete();
            foreach ($departments as $departmentData) {
                Department::create($departmentData);
            }

            DB::commit();

            return ApiResponse::success($this->formatDepartments(), 'Departments updated successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return ApiResponse::error('Failed to update departments.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }

    public function sendTestEmail(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'test_email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation error.', $validator->errors(), 422);
        }

        try {
            $settings = array_merge($this->getMailSettingsFromEnvironment(), Setting::getAllSettings());
            $this->applyRuntimeMailConfiguration($settings);

            Mail::to($request->input('test_email'))->send(new TestMail($settings));

            return ApiResponse::success([
                'test_email' => $request->input('test_email'),
            ], 'Test email sent successfully.');
        } catch (\Throwable $exception) {
            return ApiResponse::error('Failed to send test email.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }

    public function searchTags(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'q' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation error.', $validator->errors(), 422);
        }

        $tags = Tag::search((string) $request->query('q', ''));

        return ApiResponse::success($tags, 'Tags fetched successfully.');
    }

    /**
     * Get app logo (loading screen logo).
     */
    public function getAppLogo(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->can('view_general_settings')) {
            return ApiResponse::error('Unauthorized.', [], 403);
        }

        $settings = Setting::getAllSettings();
        $appLogoPath = $settings['app_logo'] ?? null;

        return ApiResponse::success([
            'app_logo' => [
                'path' => $appLogoPath,
                'url' => Setting::resolveGeneralAssetUrl($appLogoPath),
            ],
            'links' => [
                'show' => url('/api/v1/settings/app-logo'),
                'update' => url('/api/v1/settings/app-logo'),
            ],
        ], 'App logo fetched successfully.');
    }

    /**
     * Update app logo (loading screen logo).
     */
    public function updateAppLogo(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->can('view_general_settings')) {
            return ApiResponse::error('Unauthorized.', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'app_logo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_app_logo' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation error.', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            if ($request->boolean('remove_app_logo')) {
                $this->deleteGeneralAsset(Setting::get('app_logo'));
                Setting::set('app_logo', null, 'image');
            }

            if ($request->hasFile('app_logo')) {
                $this->deleteGeneralAsset(Setting::get('app_logo'));
                Setting::set('app_logo', $this->storeGeneralAsset($request->file('app_logo'), 'app_logo'), 'image');
            }

            DB::commit();

            $settings = Setting::getAllSettings();
            $appLogoPath = $settings['app_logo'] ?? null;

            return ApiResponse::success([
                'app_logo' => [
                    'path' => $appLogoPath,
                    'url' => Setting::resolveGeneralAssetUrl($appLogoPath),
                ],
            ], 'App logo updated successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return ApiResponse::error('Failed to update app logo.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }

    /**
     * Get login logo.
     */
    public function getLoginLogo(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->can('view_general_settings')) {
            return ApiResponse::error('Unauthorized.', [], 403);
        }

        $settings = Setting::getAllSettings();
        $loginLogoPath = $settings['login_logo'] ?? null;

        return ApiResponse::success([
            'login_logo' => [
                'path' => $loginLogoPath,
                'url' => Setting::resolveGeneralAssetUrl($loginLogoPath),
            ],
            'links' => [
                'show' => url('/api/v1/settings/login-logo'),
                'update' => url('/api/v1/settings/login-logo'),
            ],
        ], 'Login logo fetched successfully.');
    }

    /**
     * Update login logo.
     */
    public function updateLoginLogo(Request $request): JsonResponse
    {
        $user = $request->user();
        if (! $user || ! $user->can('view_general_settings')) {
            return ApiResponse::error('Unauthorized.', [], 403);
        }

        $validator = Validator::make($request->all(), [
            'login_logo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'remove_login_logo' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation error.', $validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            if ($request->boolean('remove_login_logo')) {
                $this->deleteGeneralAsset(Setting::get('login_logo'));
                Setting::set('login_logo', null, 'image');
            }

            if ($request->hasFile('login_logo')) {
                $this->deleteGeneralAsset(Setting::get('login_logo'));
                Setting::set('login_logo', $this->storeGeneralAsset($request->file('login_logo'), 'login_logo'), 'image');
            }

            DB::commit();

            $settings = Setting::getAllSettings();
            $loginLogoPath = $settings['login_logo'] ?? null;

            return ApiResponse::success([
                'login_logo' => [
                    'path' => $loginLogoPath,
                    'url' => Setting::resolveGeneralAssetUrl($loginLogoPath),
                ],
            ], 'Login logo updated successfully.');
        } catch (\Throwable $exception) {
            DB::rollBack();

            return ApiResponse::error('Failed to update login logo.', [
                'server' => [$exception->getMessage()],
            ], 500);
        }
    }

    private function formatGeneralSettings(): array
    {
        $settings = Setting::getAllSettings();
        $crmLogoPath = $settings['crm_logo'] ?? null;
        $faviconPath = $settings['favicon'] ?? null;
        $appLogoPath = $settings['app_logo'] ?? null;
        $loginLogoPath = $settings['login_logo'] ?? null;

        return [
            'company_name' => $settings['company_name'] ?? null,
            'crm_logo' => [
                'path' => $crmLogoPath,
                'url' => Setting::resolveGeneralAssetUrl($crmLogoPath),
            ],
            'favicon' => [
                'path' => $faviconPath,
                'url' => Setting::resolveGeneralAssetUrl($faviconPath),
            ],
            'app_logo' => [
                'path' => $appLogoPath,
                'url' => Setting::resolveGeneralAssetUrl($appLogoPath),
            ],
            'login_logo' => [
                'path' => $loginLogoPath,
                'url' => Setting::resolveGeneralAssetUrl($loginLogoPath),
            ],
            'links' => [
                'show' => url('/api/v1/settings/general'),
                'update' => url('/api/v1/settings/general'),
                'app_logo' => url('/api/v1/settings/app-logo'),
                'login_logo' => url('/api/v1/settings/login-logo'),
            ],
        ];
    }

    private function formatCompanySettings(): array
    {
        $settings = array_merge($this->companyTimingDefaults(), Setting::getAllSettings());
        $data = [];

        foreach (array_keys($this->companySettingFields()) as $field) {
            $data[$field] = $settings[$field] ?? null;
        }

        $data['links'] = [
            'show' => url('/api/v1/settings/company'),
            'update' => url('/api/v1/settings/company'),
        ];

        return $data;
    }

    private function formatEmailSettings(): array
    {
        $settings = array_merge($this->getMailSettingsFromEnvironment(), Setting::getAllSettings());

        if (empty($settings['mail_from_name'])) {
            $settings['mail_from_name'] = $settings['company_name'] ?? config('app.name');
        }

        $data = [];
        foreach (array_keys($this->emailSettingFields()) as $field) {
            $data[$field] = $settings[$field] ?? null;
        }

        $data['links'] = [
            'show' => url('/api/v1/settings/email'),
            'update' => url('/api/v1/settings/email'),
            'test_email' => url('/api/v1/settings/test-email'),
        ];

        return $data;
    }

    private function formatRenewalSettings(): array
    {
        $settings = array_merge([
            'renewal_notice_days' => 7,
            'renewal_notifications_enabled' => '0',
        ], Setting::getAllSettings());

        $data = [];
        foreach (array_keys($this->renewalSettingFields()) as $field) {
            $data[$field] = $settings[$field] ?? null;
        }

        $data['renewal_notifications_enabled'] = filter_var(
            $data['renewal_notifications_enabled'],
            FILTER_VALIDATE_BOOLEAN
        );

        $data['links'] = [
            'show' => url('/api/v1/settings/renewal'),
            'update' => url('/api/v1/settings/renewal'),
        ];

        return $data;
    }

    private function formatTeams(): array
    {
        return Team::query()
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'icon_path', 'is_active'])
            ->map(function (Team $team) {
                $iconPath = $this->normalizeTeamIconPath((string) ($team->icon_path ?? ''));

                return [
                    'id' => $team->id,
                    'name' => (string) $team->name,
                    'description' => (string) ($team->description ?? ''),
                    'icon_path' => $iconPath,
                    'icon_url' => $iconPath !== '' && is_file(public_path($iconPath)) ? asset($iconPath) : null,
                    'is_active' => (bool) $team->is_active,
                ];
            })
            ->values()
            ->all();
    }

    private function formatDepartments(): array
    {
        return Department::query()
            ->orderBy('name')
            ->get(['id', 'name', 'is_active'])
            ->map(function (Department $department) {
                return [
                    'id' => $department->id,
                    'name' => (string) $department->name,
                    'is_active' => (bool) $department->is_active,
                ];
            })
            ->values()
            ->all();
    }

    private function companySettingFields(): array
    {
        return [
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
    }

    private function emailSettingFields(): array
    {
        return [
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
    }

    private function renewalSettingFields(): array
    {
        return [
            'renewal_admin_email' => 'text',
            'renewal_notification_time' => 'text',
            'renewal_notice_days' => 'text',
            'renewal_notifications_enabled' => 'text',
        ];
    }

    private function companyTimingDefaults(): array
    {
        return [
            'office_start_time' => '09:00',
            'lunch_start_time' => '13:00',
            'lunch_end_time' => '14:00',
            'office_end_time' => '18:00',
        ];
    }

    private function requestHasAny(Request $request, array $keys): bool
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $request->all())) {
                return true;
            }
        }

        return false;
    }

    private function storeTeamIcon($iconFile): string
    {
        $destinationPath = public_path('uploads/team-icons');
        if (! is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $fileName = uniqid('team_', true).'.'.strtolower($iconFile->getClientOriginalExtension());
        $iconFile->move($destinationPath, $fileName);

        return 'uploads/team-icons/'.$fileName;
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
            $legacyStoragePath = storage_path('app/public/'.$normalized);
            $destinationPath = public_path('uploads/team-icons');
            if (! is_dir($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            $targetRelativePath = 'uploads/team-icons/'.basename($normalized);
            $targetAbsolutePath = public_path($targetRelativePath);
            if (is_file($legacyStoragePath) && ! is_file($targetAbsolutePath)) {
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

    private function storeGeneralAsset($file, string $prefix): string
    {
        $destinationPath = public_path('uploads/settings');
        if (! is_dir($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        $fileName = uniqid($prefix.'_', true).'.'.strtolower($file->getClientOriginalExtension());
        $file->move($destinationPath, $fileName);

        return 'uploads/settings/'.$fileName;
    }

    private function deleteGeneralAsset(?string $path): void
    {
        $normalized = Setting::normalizeGeneralAssetPath((string) $path);
        if ($normalized !== '') {
            $publicFile = public_path($normalized);
            if (is_file($publicFile)) {
                @unlink($publicFile);
            }
        }

        $raw = trim(str_replace('\\', '/', (string) $path));
        $raw = ltrim($raw, '/');
        if ($raw === '') {
            return;
        }

        if (str_starts_with($raw, 'public/')) {
            $raw = substr($raw, 7);
        }

        $legacyCandidates = [];
        if (basename($raw) !== '') {
            $legacyCandidates[] = 'settings/'.basename($raw);
        }
        if (str_starts_with($raw, 'settings/')) {
            $legacyCandidates[] = $raw;
        }

        foreach (array_unique($legacyCandidates) as $legacyPath) {
            if (Storage::disk('public')->exists($legacyPath)) {
                Storage::disk('public')->delete($legacyPath);
            }
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
        if (! is_file($path)) {
            return [];
        }

        $values = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            $trimmed = trim((string) $line);
            if ($trimmed === '' || str_starts_with($trimmed, '#') || ! str_contains($line, '=')) {
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
        if (! is_file($path)) {
            throw new \RuntimeException('.env file not found.');
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read .env file.');
        }

        foreach ($updates as $key => $value) {
            $formatted = $key.'='.$this->formatEnvironmentValue($value);
            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

            if (preg_match($pattern, $contents)) {
                $contents = preg_replace($pattern, $formatted, $contents, 1) ?? $contents;
            } else {
                $contents .= rtrim($contents) === '' ? $formatted : PHP_EOL.$formatted;
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
            return '"'.addcslashes($value, '"\\').'"';
        }

        return $value;
    }
}
