<?php

namespace App\Http\Controllers;

use App\Services\LegalContentService;

class LegalController extends Controller
{
    public function __construct(private readonly LegalContentService $legalContentService)
    {
    }

    public function privacyPolicy()
    {
        return view('legal.document', [
            'document' => $this->legalContentService->privacyPolicy(),
        ]);
    }

    public function termsAndConditions()
    {
        return view('legal.document', [
            'document' => $this->legalContentService->termsAndConditions(),
        ]);
    }
}
