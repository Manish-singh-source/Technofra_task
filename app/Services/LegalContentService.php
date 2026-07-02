<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\File;

class LegalContentService
{
    public function privacyPolicy(): array
    {
        return $this->readDocument(
            'docs2/privacy-policy.md',
            'privacy_policy_title',
            'Privacy Policy',
            'privacy_policy_content',
            'privacy_policy_updated_at',
            'Privacy policy fetched successfully.'
        );
    }

    public function termsAndConditions(): array
    {
        return $this->readDocument(
            'docs2/terms-and-conditions.md',
            'terms_conditions_title',
            'Terms and Conditions',
            'terms_conditions_content',
            'terms_conditions_updated_at',
            'Terms and conditions fetched successfully.'
        );
    }

    private function readDocument(string $relativePath, string $settingTitleKey, string $defaultTitle, string $settingContentKey, string $settingUpdatedAtKey, string $fallbackMessage): array
    {
        $path = base_path($relativePath);

        if (File::exists($path)) {
            $modifiedAt = File::lastModified($path);

            return [
                'title' => $defaultTitle,
                'format' => 'markdown',
                'content' => File::get($path),
                'updated_at' => $modifiedAt ? date('Y-m-d H:i:s', $modifiedAt) : null,
                'source' => $relativePath,
                'message' => $fallbackMessage,
            ];
        }

        return [
            'title' => Setting::get($settingTitleKey, $defaultTitle),
            'format' => 'markdown',
            'content' => Setting::get($settingContentKey, ''),
            'updated_at' => Setting::get($settingUpdatedAtKey),
            'source' => 'settings',
            'message' => $fallbackMessage,
        ];
    }
}
