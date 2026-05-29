<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WebEnquiryController extends Controller
{
    public function contact()
    {
        $contactEnquiries = DB::table('contactform')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->get();

        return view('web-enquiry.contact', compact('contactEnquiries'));
    }

    public function career(Request $request)
    {
        $careerEnquiries = DB::table('jobapplication')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->get();

        return view('web-enquiry.career', compact('careerEnquiries'));
    }

    public function careerShow(int $id)
    {
        $careerEnquiry = DB::table('jobapplication')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        abort_if(! $careerEnquiry, 404);

        return view('web-enquiry.career-show', compact('careerEnquiry'));
    }

    public function careerDestroy(int $id): RedirectResponse
    {
        $deleted = DB::table('jobapplication')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);

        if (! $deleted) {
            return redirect()
                ->route('web-enquiry.career')
                ->with('error', 'Career enquiry not found or already deleted.');
        }

        return redirect()
            ->route('web-enquiry.career')
            ->with('success', 'Career enquiry deleted successfully.');
    }

    public function contactDestroy(int $id): RedirectResponse
    {
        $deleted = DB::table('contactform')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);

        if (! $deleted) {
            return redirect()
                ->route('web-enquiry.contact')
                ->with('error', 'Contact enquiry not found or already deleted.');
        }

        return redirect()
            ->route('web-enquiry.contact')
            ->with('success', 'Contact enquiry deleted successfully.');
    }
}
