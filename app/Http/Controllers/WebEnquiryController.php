<?php

namespace App\Http\Controllers;

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
}
