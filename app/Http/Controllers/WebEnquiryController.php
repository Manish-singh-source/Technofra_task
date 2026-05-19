<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class WebEnquiryController extends Controller
{
    public function contact()
    {
        $contactEnquiries = DB::table('contactform')
            ->orderByDesc('created_at')
            ->get();

        return view('web-enquiry.contact', compact('contactEnquiries'));
    }

    public function career()
    {
        $careerEnquiries = DB::table('jobapplication')
            ->orderByDesc('created_at')
            ->get();

        return view('web-enquiry.career', compact('careerEnquiries'));
    }

    public function careerShow(int $id)
    {
        $careerEnquiry = DB::table('jobapplication')->where('id', $id)->first();

        abort_if(! $careerEnquiry, 404);

        return view('web-enquiry.career-show', compact('careerEnquiry'));
    }

    public function careerDestroy(int $id): RedirectResponse
    {
        $deleted = DB::table('jobapplication')->where('id', $id)->delete();

        if (! $deleted) {
            return redirect()
                ->route('web-enquiry.career')
                ->with('error', 'Career enquiry not found or already deleted.');
        }

        return redirect()
            ->route('web-enquiry.career')
            ->with('success', 'Career enquiry deleted successfully.');
    }
}
