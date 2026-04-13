<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, DB, Log, Validator};
use Carbon\Carbon;

class CalendarEventController extends Controller
{
    private const APPOINTMENT_BUFFER_MINUTES = 30;

    /**
     * API: Get all calendar events for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiIndex(Request $request)
    {
        try {
            $events = $this->userEventsQuery()
                ->when(!$request->boolean('include_inactive'), function ($query) {
                    $query->active();
                })
                ->with('creator')
                ->orderBy('event_date')
                ->orderBy('event_time')
                ->get()
                ->map(fn (CalendarEvent $event) => $this->formatEventResource($event));

            return response()->json([
                'success' => true,
                'data' => $events,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching calendar events for API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching events',
            ], 500);
        }
    }

    /**
     * Get all calendar events for FullCalendar display.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEvents(Request $request)
    {
        try {
            $events = $this->userEventsQuery()
                ->active()
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
            'email_recipients' => 'nullable|string',
            'whatsapp_recipients' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (empty(trim((string) $request->email_recipients)) && empty(trim((string) $request->whatsapp_recipients))) {
            return response()->json([
                'success' => false,
                'message' => 'Please add at least one email or WhatsApp recipient.'
            ], 422);
        }

        $emails = array_filter(array_map('trim', explode(',', (string) $request->email_recipients)));
        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid email address: {$email}"
                ], 422);
            }
        }

        if ($request->whatsapp_recipients) {
            $phones = array_filter(array_map('trim', explode(',', $request->whatsapp_recipients)));
            foreach ($phones as $phone) {
                $cleanPhone = preg_replace('/\D+/', '', $phone);
                if (!preg_match('/^[1-9]\d{7,14}$/', $cleanPhone)) {
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

            if ($this->hasSchedulingConflict($eventDateTime)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Another appointment already exists within 30 minutes of this time. Please choose a different slot.'
                ], 422);
            }

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

            $this->logActivity('Calendar event created: ' . $event->title, $event);

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
            $event = $this->userEventsQuery()
                ->with('creator')
                ->findOrFail($id);

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
     * API: Show a single calendar event for the authenticated user.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiShow($id)
    {
        try {
            $event = $this->userEventsQuery()
                ->with('creator')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $this->formatEventResource($event),
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching calendar event for API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Event not found',
            ], 404);
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
            'email_recipients' => 'nullable|string',
            'whatsapp_recipients' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if (empty(trim((string) $request->email_recipients)) && empty(trim((string) $request->whatsapp_recipients))) {
            return response()->json([
                'success' => false,
                'message' => 'Please add at least one email or WhatsApp recipient.'
            ], 422);
        }

        $emails = array_filter(array_map('trim', explode(',', (string) $request->email_recipients)));
        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid email address: {$email}"
                ], 422);
            }
        }

        if ($request->whatsapp_recipients) {
            $phones = array_filter(array_map('trim', explode(',', $request->whatsapp_recipients)));
            foreach ($phones as $phone) {
                $cleanPhone = preg_replace('/\D+/', '', $phone);
                if (!preg_match('/^[1-9]\d{7,14}$/', $cleanPhone)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Invalid phone number: {$phone}"
                    ], 422);
                }
            }
        }

        DB::beginTransaction();
        try {
            $event = $this->userEventsQuery()->findOrFail($id);
            $eventDateTime = Carbon::parse($request->event_date . ' ' . $request->event_time);

            if ($this->hasSchedulingConflict($eventDateTime, $event->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Another appointment already exists within 30 minutes of this time. Please choose a different slot.'
                ], 422);
            }

            $event->update([
                'title' => $request->title,
                'description' => $request->description,
                'event_date' => $request->event_date,
                'event_time' => $eventDateTime,
                'email_recipients' => $request->email_recipients,
                'whatsapp_recipients' => $request->whatsapp_recipients,
            ]);

            DB::commit();

            $this->logActivity('Calendar event updated: ' . $event->title, $event);

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
     * API: Update an existing calendar event.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiUpdate(Request $request, $id)
    {
        $response = $this->update($request, $id);
        $payload = $response->getData(true);

        if (($payload['success'] ?? false) !== true) {
            return $response;
        }

        $event = $this->userEventsQuery()->with('creator')->find($id);

        return response()->json([
            'success' => true,
            'message' => $payload['message'] ?? 'Event updated successfully',
            'data' => $event ? $this->formatEventResource($event) : ($payload['event'] ?? null),
        ]);
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
            $event = $this->userEventsQuery()->findOrFail($id);
            $eventTitle = $event->title;

            $event->delete();

            DB::commit();

            $this->logActivity('Calendar event deleted: ' . $eventTitle);

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
     * API: Store a new calendar event.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiStore(Request $request)
    {
        $response = $this->store($request);
        $payload = $response->getData(true);

        if (($payload['success'] ?? false) !== true) {
            return $response;
        }

        $eventId = $payload['event']['id'] ?? null;
        $event = $eventId
            ? $this->userEventsQuery()->with('creator')->find($eventId)
            : null;

        return response()->json([
            'success' => true,
            'message' => $payload['message'] ?? 'Event created successfully',
            'data' => $event ? $this->formatEventResource($event) : ($payload['event'] ?? null),
        ], 201);
    }

    /**
     * API: Delete a calendar event.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiDestroy($id)
    {
        return $this->destroy($id);
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
            $event = $this->userEventsQuery()->findOrFail($request->id);
            $event->status = $request->status;
            $event->save();

            DB::commit();

            $this->logActivity('Calendar event status changed: ' . $event->title, $event);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error toggling event status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function hasSchedulingConflict(Carbon $eventDateTime, ?int $exceptEventId = null): bool
    {
        return $this->userEventsQuery()
            ->where('status', 1)
            ->when($exceptEventId, function ($query, $exceptEventId) {
                $query->where('id', '!=', $exceptEventId);
            })
            ->whereRaw('ABS(TIMESTAMPDIFF(MINUTE, event_time, ?)) < ?', [
                $eventDateTime->toDateTimeString(),
                self::APPOINTMENT_BUFFER_MINUTES,
            ])
            ->exists();
    }

    private function userEventsQuery()
    {
        return CalendarEvent::query()->where('created_by', Auth::id());
    }

    private function logActivity(string $message, ?CalendarEvent $event = null): void
    {
        if (!function_exists('activity')) {
            return;
        }

        try {
            $logger = activity();

            if ($event) {
                $logger->performedOn($event);
            }

            if (Auth::check()) {
                $logger->causedBy(Auth::user());
            }

            $logger->log($message);
        } catch (\Throwable $e) {
            Log::warning('Activity log skipped: ' . $e->getMessage());
        }
    }

    private function formatEventResource(CalendarEvent $event): array
    {
        $event->loadMissing('creator');

        return [
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'event_date' => $event->event_date?->format('Y-m-d'),
            'event_time' => $event->event_time?->format('H:i'),
            'start' => $event->event_date && $event->event_time
                ? $event->event_date->format('Y-m-d') . 'T' . $event->event_time->format('H:i:s')
                : null,
            'email_recipients' => $event->email_recipients,
            'email_recipients_array' => $event->email_recipients_array,
            'whatsapp_recipients' => $event->whatsapp_recipients,
            'whatsapp_recipients_array' => $event->whatsapp_recipients_array,
            'notification_sent' => (bool) $event->notification_sent,
            'notification_sent_at' => optional($event->notification_sent_at)?->toISOString(),
            'reminder_10min_sent' => (bool) $event->reminder_10min_sent,
            'reminder_10min_sent_at' => optional($event->reminder_10min_sent_at)?->toISOString(),
            'event_time_notification_sent' => (bool) $event->event_time_notification_sent,
            'event_time_notification_sent_at' => optional($event->event_time_notification_sent_at)?->toISOString(),
            'status' => (bool) $event->status,
            'created_by' => $event->created_by,
            'created_by_name' => $event->creator->name ?? 'Unknown',
            'created_at' => optional($event->created_at)?->toISOString(),
            'updated_at' => optional($event->updated_at)?->toISOString(),
            'links' => [
                'web' => [
                    'show' => route('calendar.show', $event->id),
                    'update' => route('calendar.update', $event->id),
                    'delete' => route('calendar.destroy', $event->id),
                ],
                'api' => [
                    'show' => url('/api/v1/calendar/events/' . $event->id),
                    'update' => url('/api/v1/calendar/events/' . $event->id),
                    'delete' => url('/api/v1/calendar/events/' . $event->id),
                ],
            ],
        ];
    }
}
