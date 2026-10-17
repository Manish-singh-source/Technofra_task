<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use App\Models\Lead;
use App\Models\LeadFollowup;
use App\Models\LeadReminder;
use App\Models\Service;
use App\Models\Task;
use App\Models\Todo;
use App\Models\VendorService;
use App\Services\CalendarManagement\CalendarNotificationService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\{Auth, DB, Gate, Log, Validator};
use Carbon\Carbon;

class CalendarEventController extends Controller
{
    private const APPOINTMENT_BUFFER_MINUTES = 30;

    public function index(Request $request)
    {
        $validated = $request->validate([
            'type' => 'nullable|in:appointment,task,renewal,lead,todo',
            'search' => 'nullable|string|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'per_page' => 'nullable|integer|in:10,20,50,100',
        ]);

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : now()->startOfMonth();
        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : now()->endOfMonth();

        $feedRequest = clone $request;
        $feedRequest->query->set('start', $startDate->toIso8601String());
        $feedRequest->query->set('end', $endDate->copy()->addSecond()->toIso8601String());

        $items = $this->calendarFeedEvents($feedRequest)
            ->map(function (array $event) {
                $start = Carbon::parse($event['start']);
                $properties = $event['extendedProps'] ?? [];
                $allDay = (bool) ($event['allDay'] ?? false);
                $completionTime = $allDay ? $start->copy()->endOfDay() : $start;

                return [
                    'id' => $event['id'],
                    'title' => $event['title'],
                    'type' => $properties['event_type'] ?? 'appointment',
                    'type_label' => $properties['event_type_label'] ?? 'Appointment',
                    'description' => $properties['description'] ?? $event['description'] ?? null,
                    'url' => $properties['url'] ?? null,
                    'start' => $start,
                    'all_day' => $allDay,
                    'is_completed' => $completionTime->isPast(),
                ];
            })
            ->sortBy('start')
            ->values();

        $categoryCounts = $items->countBy('type');
        $search = trim((string) ($validated['search'] ?? ''));
        $type = $validated['type'] ?? null;

        $filteredItems = $items
            ->when($type, fn($collection) => $collection->where('type', $type))
            ->when($search !== '', function ($collection) use ($search) {
                return $collection->filter(function (array $item) use ($search) {
                    return mb_stripos($item['title'], $search) !== false
                        || mb_stripos((string) $item['description'], $search) !== false
                        || mb_stripos($item['type_label'], $search) !== false;
                });
            })
            ->values();

        $page = max(1, (int) $request->query('page', 1));
        $perPage = (int) ($validated['per_page'] ?? 20);
        $calendarItems = new LengthAwarePaginator(
            $filteredItems->forPage($page, $perPage)->values(),
            $filteredItems->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('calendar.index', compact(
            'calendarItems',
            'categoryCounts',
            'startDate',
            'endDate'
        ));
    }

    /**
     * API: Get all calendar events for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiIndex(Request $request)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => $this->calendarFeedEvents($request, $request->boolean('include_inactive')),
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
            return response()->json($this->calendarFeedEvents($request));
        } catch (\Exception $e) {
            Log::error('Error fetching calendar events: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error fetching events'], 500);
        }
    }

    private function calendarFeedEvents(Request $request, bool $includeInactiveAppointments = false)
    {
        $rangeStart = Carbon::parse($request->query('start', now()->startOfMonth()))->startOfDay();
        $rangeEnd = Carbon::parse($request->query('end', now()->endOfMonth()->addDay()))->subSecond();

        $appointments = $this->userEventsQuery()
            ->when(! $includeInactiveAppointments, fn($query) => $query->active())
            ->whereBetween('event_date', [$rangeStart, $rangeEnd])
            ->with('creator')
            ->get()
            ->map(function (CalendarEvent $event) {
                return array_merge(
                    $this->formatEventResource($event),
                    $this->calendarFeedItem(
                        (string) $event->id,
                        $event->title,
                        $event->event_date->format('Y-m-d') . 'T' . $event->event_time->format('H:i:s'),
                        'appointment',
                        $event->description,
                        null,
                        false
                    )
                );
            });

        return $appointments
            ->concat($this->taskCalendarEvents($rangeStart, $rangeEnd))
            ->concat($this->renewalCalendarEvents($rangeStart, $rangeEnd))
            ->concat($this->leadCalendarEvents($rangeStart, $rangeEnd))
            ->concat($this->todoCalendarEvents($rangeStart, $rangeEnd))
            ->values();
    }

    private function taskCalendarEvents(Carbon $rangeStart, Carbon $rangeEnd)
    {
        $userId = (int) Auth::id();

        if ($userId === 0) {
            return collect();
        }

        $tasks = Task::query()
            ->with('project')
            ->where(function ($builder) use ($userId) {
                $builder->whereJsonContains('assignees', $userId)
                    ->orWhereJsonContains('assignees', (string) $userId);
            })
            ->where(function ($builder) use ($rangeStart, $rangeEnd) {
                $builder->whereBetween('deadline', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
                    ->orWhere(function ($builder) use ($rangeStart, $rangeEnd) {
                        $builder->whereNull('deadline')
                            ->whereBetween('start_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()]);
                    });
            })
            ->get();

        return $tasks->map(function (Task $task) {
            $date = $task->deadline ?: $task->start_date;
            $projectName = $task->project?->project_name;

            return $this->calendarFeedItem(
                'task-' . $task->id,
                $task->title,
                $date->toDateString(),
                'task',
                $projectName ? 'Project: ' . $projectName : 'Task deadline',
                route('task-details', $task->id)
            );
        });
    }

    private function renewalCalendarEvents(Carbon $rangeStart, Carbon $rangeEnd)
    {
        $userId = (int) Auth::id();
        $canViewAll = $this->hasAdminCalendarAccess();
        $startDate = $rangeStart->toDateString();
        $endDate = $rangeEnd->toDateString();

        $services = Service::query()
            ->whereNotNull('client_id')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->whereNull('end_date')->whereBetween('billing_date', [$startDate, $endDate]);
                    });
            })
            ->when(! $canViewAll, fn($query) => $query->where('client_id', $userId))
            ->with('client')
            ->get()
            ->map(function (Service $service) {
                $date = $service->end_date ?: $service->billing_date;
                $clientName = $service->client?->name;

                return $this->calendarFeedItem(
                    'renewal-client-' . $service->id,
                    $service->service_name ?: 'Client renewal',
                    $date->toDateString(),
                    'renewal',
                    $clientName ? 'Client: ' . $clientName : 'Client renewal due',
                    route('services.index')
                );
            });

        $vendorServices = VendorService::query()
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->whereNull('end_date')->whereBetween('billing_date', [$startDate, $endDate]);
                    });
            })
            ->when(! $canViewAll, function ($query) use ($userId) {
                $query->whereIn('vendor_id', Service::query()
                    ->whereNotNull('vendor_id')
                    ->where('client_id', $userId)
                    ->select('vendor_id'));
            })
            ->with('vendor')
            ->get()
            ->map(function (VendorService $service) {
                $date = $service->end_date ?: $service->billing_date;
                $vendorName = $service->vendor?->name;

                return $this->calendarFeedItem(
                    'renewal-vendor-' . $service->id,
                    $service->service_name ?: 'Vendor renewal',
                    $date->toDateString(),
                    'renewal',
                    $vendorName ? 'Vendor: ' . $vendorName : 'Vendor renewal due',
                    route('vendor-services.index')
                );
            });

        return $services->concat($vendorServices);
    }

    private function leadCalendarEvents(Carbon $rangeStart, Carbon $rangeEnd)
    {
        $user = Auth::user();
        $userId = (int) ($user?->id ?? 0);

        if (! $user || ! $this->hasAdminCalendarAccess() && ! Gate::forUser($user)->allows('view_leads_management')) {
            return collect();
        }

        $leads = Lead::query()
            ->whereBetween('next_followup_at', [$rangeStart, $rangeEnd]);

        if (! $this->hasAdminCalendarAccess()) {
            $role = strtolower((string) data_get($user, 'role', ''));

            if ($role === 'staff') {
                $leads->whereExists(function ($query) use ($userId) {
                    $query->selectRaw('1')
                        ->from('assigned_leads')
                        ->where('assigned_leads.lead_model', 'lead')
                        ->whereColumn('assigned_leads.lead_id', 'leads.id')
                        ->where(function ($staffIds) use ($userId) {
                            $staffIds->whereRaw('JSON_CONTAINS(assigned_leads.staff_ids, ?, "$")', [(string) $userId])
                                ->orWhereRaw('JSON_CONTAINS(assigned_leads.staff_ids, JSON_QUOTE(?), "$")', [(string) $userId]);
                        });
                });
            } else {
                $leads->where('created_by', $userId);
            }
        }

        $leadEvents = $leads->get()->map(function (Lead $lead) {
            return $this->calendarFeedItem(
                'lead-' . $lead->id,
                'Follow-up: ' . ($lead->name ?: 'Lead'),
                $lead->next_followup_at->toIso8601String(),
                'lead',
                'Scheduled lead follow-up',
                route('lead-management.show', ['source' => 'lead', 'id' => $lead->id]),
                false
            );
        });

        $followups = LeadFollowup::query()
            ->with('lead')
            ->whereNotNull('next_followup_date')
            ->whereBetween('next_followup_date', [$rangeStart, $rangeEnd]);

        if (! $this->hasAdminCalendarAccess()) {
            $role = strtolower((string) data_get($user, 'role', ''));

            if ($role === 'staff') {
                $followups->whereExists(function ($query) use ($userId) {
                    $query->selectRaw('1')
                        ->from('assigned_leads')
                        ->whereColumn('assigned_leads.lead_model', 'lead_followups.source_type')
                        ->whereColumn('assigned_leads.lead_id', 'lead_followups.source_id')
                        ->where(function ($staffIds) use ($userId) {
                            $staffIds->whereRaw('JSON_CONTAINS(assigned_leads.staff_ids, ?, "$")', [(string) $userId])
                                ->orWhereRaw('JSON_CONTAINS(assigned_leads.staff_ids, JSON_QUOTE(?), "$")', [(string) $userId]);
                        });
                });
            } else {
                $followups->where(function ($query) use ($userId) {
                    $query->where('source_type', 'lead')
                        ->whereExists(function ($leadQuery) use ($userId) {
                            $leadQuery->selectRaw('1')
                                ->from('leads')
                                ->whereColumn('leads.id', 'lead_followups.source_id')
                                ->where('leads.created_by', $userId);
                        });
                });
            }
        }

        $followupEvents = $followups->get()->map(function (LeadFollowup $followup) {
            $source = $followup->source_type ?: 'lead';
            $sourceId = (int) ($followup->source_id ?: $followup->lead_id);
            $leadName = $followup->lead?->name ?: 'Lead';

            return $this->calendarFeedItem(
                'lead-followup-' . $followup->id,
                'Follow-up: ' . $leadName,
                $followup->next_followup_date->toIso8601String(),
                'lead',
                $followup->followup_type ? ucfirst(str_replace('_', ' ', $followup->followup_type)) : 'Lead follow-up',
                route('lead-management.show', ['source' => $source, 'id' => $sourceId]),
                false
            );
        });

        $reminders = LeadReminder::query()
            ->with('lead')
            ->where('status', 'pending')
            ->whereBetween('remind_at', [$rangeStart, $rangeEnd]);

        if (! $this->hasAdminCalendarAccess()) {
            $role = strtolower((string) data_get($user, 'role', ''));
            $reminders->where(function ($query) use ($userId, $role) {
                $query->where('user_id', $userId);

                if ($role === 'staff') {
                    $query->orWhereExists(function ($assignmentQuery) use ($userId) {
                        $assignmentQuery->selectRaw('1')
                            ->from('assigned_leads')
                            ->where('assigned_leads.lead_model', 'lead')
                            ->whereColumn('assigned_leads.lead_id', 'lead_reminders.lead_id')
                            ->where(function ($staffIds) use ($userId) {
                                $staffIds->whereRaw('JSON_CONTAINS(assigned_leads.staff_ids, ?, "$")', [(string) $userId])
                                    ->orWhereRaw('JSON_CONTAINS(assigned_leads.staff_ids, JSON_QUOTE(?), "$")', [(string) $userId]);
                            });
                    });
                } else {
                    $query->orWhereHas('lead', fn($leadQuery) => $leadQuery->where('created_by', $userId));
                }
            });
        }

        $reminderEvents = $reminders->get()->map(function (LeadReminder $reminder) {
            return $this->calendarFeedItem(
                'lead-reminder-' . $reminder->id,
                'Reminder: ' . ($reminder->lead?->name ?: 'Lead'),
                $reminder->remind_at->toIso8601String(),
                'lead',
                $reminder->reminder_type ? ucfirst(str_replace('_', ' ', $reminder->reminder_type)) : 'Lead reminder',
                route('lead-management.index'),
                false
            );
        });

        return $leadEvents->concat($followupEvents)->concat($reminderEvents);
    }

    private function todoCalendarEvents(Carbon $rangeStart, Carbon $rangeEnd)
    {
        $events = collect();
        $todos = Todo::ownedBy(Auth::id())
            ->incomplete()
            ->where(function ($query) use ($rangeEnd) {
                $query->whereNull('starts_on')->orWhereDate('starts_on', '<=', $rangeEnd->toDateString());
            })
            ->where(function ($query) use ($rangeStart) {
                $query->whereNull('ends_on')->orWhereDate('ends_on', '>=', $rangeStart->toDateString());
            })
            ->get();

        foreach ($todos as $todo) {
            $date = $rangeStart->copy()->startOfDay();
            while ($date->lte($rangeEnd)) {
                if ($todo->isOccurrenceDate($date)) {
                    $time = $todo->task_time ?: '09:00:00';
                    $start = Carbon::parse($date->toDateString() . ' ' . $time);
                    $events->push($this->calendarFeedItem(
                        'todo-' . $todo->id . '-' . $date->toDateString(),
                        $todo->title,
                        $start->toIso8601String(),
                        'todo',
                        $todo->description ?: 'To-do reminder',
                        route('to-do-list'),
                        false
                    ));
                }

                $date->addDay();
            }
        }

        return $events;
    }

    private function calendarFeedItem(string $id, string $title, string $start, string $type, ?string $description, ?string $url, bool $allDay = true): array
    {
        $colors = [
            'appointment' => '#64748b',
            'task' => '#167d9a',
            'renewal' => '#bc7616',
            'lead' => '#7954a5',
            'todo' => '#29845b',
        ];
        $labels = [
            'appointment' => 'Appointment',
            'task' => 'Task',
            'renewal' => 'Renewal',
            'lead' => 'Lead Management',
            'todo' => 'To-do',
        ];
        $color = $colors[$type] ?? '#64748b';

        return [
            'id' => $id,
            'title' => $title,
            'start' => $start,
            'allDay' => $allDay,
            'backgroundColor' => $color,
            'borderColor' => $color,
            'textColor' => '#ffffff',
            'description' => $description,
            'extendedProps' => [
                'event_type' => $type,
                'event_type_label' => $labels[$type] ?? 'Event',
                'description' => $description,
                'url' => $url,
            ],
        ];
    }

    private function hasAdminCalendarAccess(): bool
    {
        $user = Auth::user();
        $role = strtolower((string) data_get($user, 'role', ''));
        $roles = ['admin', 'super-admin', 'super_admin', 'super_admin2'];

        return in_array($role, $roles, true)
            || ($user && method_exists($user, 'hasAnyRole') && call_user_func([$user, 'hasAnyRole'], $roles));
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
            'event_date' => 'nullable|date',
            'event_time' => 'nullable|date_format:H:i',
            'email_recipients' => 'nullable|string',
            'whatsapp_recipients' => 'nullable|string',
            'notification_channels' => 'nullable|array|min:1',
            'notification_channels.*' => 'in:all,mail,whatsapp',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $notificationChannels = null;
        if ($request->has('notification_channels')) {
            $notificationChannels = $this->normalizeNotificationChannels($request);

            if (empty($notificationChannels)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select at least one notification platform.'
                ], 422);
            }
        }
        $emails = $this->normalizeDelimitedRecipients($request->email_recipients);
        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid email address: {$email}"
                ], 422);
            }
        }

        $phones = $this->normalizeDelimitedRecipients($request->whatsapp_recipients);
        foreach ($phones as $phone) {
            if ($phone === '') {
                continue;
            }

            $cleanPhone = preg_replace('/\D+/', '', $phone);
            if (!preg_match('/^[1-9]\d{7,14}$/', $cleanPhone)) {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid phone number: {$phone}"
                ], 422);
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
                // Use provided date/time or default to null if not provided
                'event_date' => $request->event_date ?? null,
                'event_time' => $eventDateTime ?? null,
                'email_recipients' => $emails !== [] ? implode(', ', $emails) : null,
                'whatsapp_recipients' => $phones !== [] ? implode(', ', $phones) : null,
                'notification_channels' => $notificationChannels,
                'created_by' => Auth::id(),
                'status' => 1,
            ]);

            DB::commit();

            $this->logActivity('Calendar event created: ' . $event->title, $event);
            app(CalendarNotificationService::class)->notifyCreated($event);

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
                    'notification_channels' => $event->notification_channels_array,
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
            'notification_channels' => 'nullable|array|min:1',
            'notification_channels.*' => 'in:all,mail,whatsapp',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $notificationChannels = null;
        if ($request->has('notification_channels')) {
            $notificationChannels = $this->normalizeNotificationChannels($request);

            if (empty($notificationChannels)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please select at least one notification platform.'
                ], 422);
            }
        }

        $emails = $this->normalizeDelimitedRecipients($request->email_recipients);
        foreach ($emails as $email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid email address: {$email}"
                ], 422);
            }
        }

        $phones = $this->normalizeDelimitedRecipients($request->whatsapp_recipients);
        foreach ($phones as $phone) {
            if ($phone === '') {
                continue;
            }

            $cleanPhone = preg_replace('/\D+/', '', $phone);
            if (!preg_match('/^[1-9]\d{7,14}$/', $cleanPhone)) {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid phone number: {$phone}"
                ], 422);
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
                'email_recipients' => $emails !== [] ? implode(', ', $emails) : null,
                'whatsapp_recipients' => $phones !== [] ? implode(', ', $phones) : null,
                'notification_channels' => $notificationChannels,
            ]);

            DB::commit();

            $this->logActivity('Calendar event updated: ' . $event->title, $event);
            app(CalendarNotificationService::class)->notifyUpdated($event);

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully',
                'event' => [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->event_date->format('Y-m-d') . 'T' . $event->event_time->format('H:i:s'),
                    'description' => $event->description,
                    'notification_channels' => $event->notification_channels_array,
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

    private function normalizeNotificationChannels(Request $request): array
    {
        $channels = collect($request->input('notification_channels', []))
            ->map(fn($channel) => strtolower(trim((string) $channel)))
            ->filter()
            ->unique()
            ->values();

        if ($channels->contains('all')) {
            return ['mail', 'whatsapp'];
        }

        return $channels->intersect(['mail', 'whatsapp'])->values()->all();
    }

    private function normalizeDelimitedRecipients(?string $value): array
    {
        return collect(explode(',', (string) $value))
            ->map(fn($recipient) => trim((string) $recipient))
            ->filter(fn($recipient) => $recipient !== '')
            ->values()
            ->all();
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
            'notification_channels' => $event->notification_channels_array,
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
