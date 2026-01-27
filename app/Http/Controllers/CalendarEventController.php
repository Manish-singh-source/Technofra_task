<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Log, Validator};
use Carbon\Carbon;

class CalendarEventController extends Controller
{
    /**
     * Get all calendar events for FullCalendar display.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEvents(Request $request)
    {
        try {
            $events = CalendarEvent::active()
                ->with('creator')
                ->get()
                ->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'start' => $event->event_date->format('Y-m-d') . 'T' . $event->event_time->format('H:i:s'),
                        'description' => $event->description,
                        'email_recipients' => $event->email_recipients,
                        'backgroundColor' => $event->notification_sent ? '#28a745' : '#007bff',
                        'borderColor' => $event->notification_sent ? '#28a745' : '#007bff',
                        'extendedProps' => [
                            'notification_sent' => $event->notification_sent,
                            'created_by' => $event->creator->name ?? 'Unknown',
                        ]
                    ];
                });

            return response()->json($events);
        } catch (\Exception $e) {
            Log::error('Error fetching calendar events: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error fetching events'], 500);
        }
    }

    /**
     * Store a newly created event.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_time' => 'required|date_format:H:i',
            'email_recipients' => 'required|string',
            'whatsapp_recipients' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate email recipients
        $emails = array_filter(array_map('trim', explode(',', $request->email_recipients)));
        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid email address: {$email}"
                ], 422);
            }
        }

        // Validate WhatsApp recipients (phone numbers)
        if ($request->whatsapp_recipients) {
            $phones = array_filter(array_map('trim', explode(',', $request->whatsapp_recipients)));
            foreach ($phones as $phone) {
                // Remove all non-numeric characters except +
                $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
                if (strlen($cleanPhone) < 10) {
                    return response()->json([
                        'success' => false,
                        'message' => "Invalid phone number: {$phone}"
                    ], 422);
                }
            }
        }

        DB::beginTransaction();
        try {
            $eventDateTime = Carbon::parse($request->event_date . ' ' . $request->event_time);

            $event = CalendarEvent::create([
                'title' => $request->title,
                'description' => $request->description,
                'event_date' => $request->event_date,
                'event_time' => $eventDateTime,
                'email_recipients' => $request->email_recipients,
                'whatsapp_recipients' => $request->whatsapp_recipients,
                'created_by' => Auth::id(),
                'status' => 1,
            ]);

            DB::commit();

            activity()
                ->performedOn($event)
                ->causedBy(Auth::user())
                ->log('Calendar event created: ' . $event->title);

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully',
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->event_date->format('Y-m-d') . 'T' . $event->event_time->format('H:i:s'),
                    'description' => $event->description,
                    'backgroundColor' => '#007bff',
                    'borderColor' => '#007bff',
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating calendar event: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified event.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $event = CalendarEvent::with('creator')->findOrFail($id);

            return response()->json([
                'success' => true,
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'event_date' => $event->event_date->format('Y-m-d'),
                    'event_time' => $event->event_time->format('H:i'),
                    'email_recipients' => $event->email_recipients,
                    'whatsapp_recipients' => $event->whatsapp_recipients,
                    'notification_sent' => $event->notification_sent,
                    'reminder_10min_sent' => $event->reminder_10min_sent,
                    'event_time_notification_sent' => $event->event_time_notification_sent,
                    'created_by' => $event->creator->name ?? 'Unknown',
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching event: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Event not found'], 404);
        }
    }

    /**
     * Update the specified event.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date',
            'event_time' => 'required|date_format:H:i',
            'email_recipients' => 'required|string',
            'whatsapp_recipients' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate email recipients
        $emails = array_filter(array_map('trim', explode(',', $request->email_recipients)));
        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid email address: {$email}"
                ], 422);
            }
        }

        // Validate WhatsApp recipients (phone numbers)
        if ($request->whatsapp_recipients) {
            $phones = array_filter(array_map('trim', explode(',', $request->whatsapp_recipients)));
            foreach ($phones as $phone) {
                // Remove all non-numeric characters except +
                $cleanPhone = preg_replace('/[^0-9+]/', '', $phone);
                if (strlen($cleanPhone) < 10) {
                    return response()->json([
                        'success' => false,
                        'message' => "Invalid phone number: {$phone}"
                    ], 422);
                }
            }
        }

        DB::beginTransaction();
        try {
            $event = CalendarEvent::findOrFail($id);
            $eventDateTime = Carbon::parse($request->event_date . ' ' . $request->event_time);

            $event->update([
                'title' => $request->title,
                'description' => $request->description,
                'event_date' => $request->event_date,
                'event_time' => $eventDateTime,
                'email_recipients' => $request->email_recipients,
                'whatsapp_recipients' => $request->whatsapp_recipients,
            ]);

            DB::commit();

            activity()
                ->performedOn($event)
                ->causedBy(Auth::user())
                ->log('Calendar event updated: ' . $event->title);

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully',
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->event_date->format('Y-m-d') . 'T' . $event->event_time->format('H:i:s'),
                    'description' => $event->description,
                    'backgroundColor' => $event->notification_sent ? '#28a745' : '#007bff',
                    'borderColor' => $event->notification_sent ? '#28a745' : '#007bff',
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating calendar event: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified event.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $event = CalendarEvent::findOrFail($id);
            $eventTitle = $event->title;

            $event->delete();

            DB::commit();

            activity()
                ->causedBy(Auth::user())
                ->log('Calendar event deleted: ' . $eventTitle);

            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting calendar event: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle event status.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:calendar_events,id',
            'status' => 'required|in:0,1'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false], 422);
        }

        DB::beginTransaction();
        try {
            $event = CalendarEvent::findOrFail($request->id);
            $event->status = $request->status;
            $event->save();

            DB::commit();

            activity()
                ->performedOn($event)
                ->causedBy(Auth::user())
                ->log('Calendar event status changed: ' . $event->title);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error toggling event status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
