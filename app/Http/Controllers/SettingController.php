<?php

namespace App\Http\Controllers;

use App\Models\Setting;
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
        $tags = \App\Models\Tag::orderBy('name')->paginate(20);
        return view('settings.index', compact('settings', 'tags'));
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
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
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
            ];

            foreach ($fields as $field => $type) {
                Setting::set($field, $request->$field, $type);
            }

            DB::commit();

            return redirect()->route('settings')
                ->with('success', 'Company information updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update company information: ' . $e->getMessage())
                ->withInput();
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
                'email_charset' => 'text',
                'bcc_all' => 'text',
                'email_signature' => 'text',
                'predefined_header' => 'text',
                'predefined_footer' => 'text',
            ];

            foreach ($fields as $field => $type) {
                Setting::set($field, $request->$field, $type);
            }

            DB::commit();

            return redirect()->route('settings')
                ->with('success', 'Email settings updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update email settings: ' . $e->getMessage())
                ->withInput();
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
            $settings = Setting::getAllSettings();

            // Configure mail settings dynamically
            $protocol = $settings['email_protocol'] ?? 'smtp';
            $encryption = $settings['email_encryption'] ?? 'tls';
            if ($encryption === 'none') {
                $encryption = null;
            }

            // Set the mailer configuration at runtime
            config([
                'mail.mailer' => $protocol,
                'mail.default' => $protocol,
                'mail.mailers.smtp.host' => $settings['smtp_host'] ?? '',
                'mail.mailers.smtp.port' => $settings['smtp_port'] ?? 587,
                'mail.mailers.smtp.username' => $settings['smtp_username'] ?? '',
                'mail.mailers.smtp.password' => $settings['smtp_password'] ?? '',
                'mail.mailers.smtp.encryption' => $encryption,
                'mail.from.address' => $settings['email'] ?? '',
                'mail.from.name' => $settings['company_name'] ?? 'CRM System',
            ]);

            // Clear the mailer instance to use new config
            app()->forgetInstance('mailer');
            Mail::purge();

            // Send test email
            Mail::to($request->test_email)->send(new TestMail($settings));

            return redirect()->route('settings')
                ->with('success', 'Test email sent successfully to ' . $request->test_email);
        } catch (\Exception $e) {
            return redirect()->route('settings')
                ->with('error', 'Failed to send test email: ' . $e->getMessage());
        }
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
