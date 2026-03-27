<?php

namespace App\Http\Controllers;

use App\Models\BookCall;

class BookCallController extends Controller
{
    public function index()
    {
        $bookCalls = BookCall::query()
            ->orderByDesc('booking_datetime')
            ->orderByDesc('id')
            ->get();

        return view('book-call.index', compact('bookCalls'));
    }

    public function destroy(BookCall $bookCall)
    {
        $bookCall->delete();

        return redirect()
            ->route('book-call.index')
            ->with('success', 'Book call deleted successfully.');
    }
}
