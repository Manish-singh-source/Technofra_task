@extends('layout.master')

@push('styles')
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' rel='stylesheet' />
<link href="{{ asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" />
<style>
.social-dashboard .card{border:0;border-radius:16px;box-shadow:0 4px 18px rgba(27,59,107,.07)}
.social-dashboard .card-body{padding:1.35rem}
.social-dashboard .table thead th{font-size:.75rem;text-transform:uppercase;letter-spacing:.04em;color:#495057;background:#f8f9fa}
.social-dashboard .table td{border-color:#edf1f6;padding:.85rem .75rem}
.social-dashboard .form-select,.social-dashboard .form-control{border-color:#dce5f2;border-radius:8px}
.social-dashboard .radius-10{position:relative;overflow:hidden}
.social-dashboard .radius-10:before{content:'';position:absolute;left:0;top:0;width:4px;height:100%;background:#1674ea}
.social-dashboard .radius-10 h3{color:#1a315a;font-weight:700}
.social-dashboard .row-cols-xl-4 .col:nth-child(2n) .radius-10:before{background:#8b5cf6}
.social-dashboard .row-cols-xl-4 .col:nth-child(3n) .radius-10:before{background:#0dcaf0}
.social-dashboard .row-cols-xl-4 .col:nth-child(4n) .radius-10:before{background:#198754}

/* FullCalendar & Widget Styles */
#calendar { min-width: 0; }
#calendar .fc-toolbar-title { color: #263b53; font-size: 1.1rem; font-weight: 600; }
#calendar .fc .fc-button { border-radius: 6px; box-shadow: none; text-transform: capitalize; }
.calendar-dashboard-layout { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 1.25rem; align-items: stretch; }
.calendar-upcoming { min-width: 0; padding-left: 1.25rem; border-left: 1px solid #e8edf2; display: flex; flex-direction: column; }

.calendar-view-all { min-height: 36px; border-radius: 6px; font-size: 13px; font-weight: 600; }
.calendar-upcoming-title { margin: 0 0 0.4rem; color: #25374a; font-size: 0.95rem; font-weight: 600; }
.calendar-selected-date { margin: 0 0 0.4rem; color: #7b8794; font-size: 0.75rem; }
.calendar-event-legend { display: flex; flex-wrap: wrap; gap: 0.4rem 0.65rem; margin: 0.25rem 0 0.5rem; }
.calendar-event-legend span { display: inline-flex; align-items: center; gap: 0.3rem; color: #6d7885; font-size: 0.68rem; }
.calendar-category-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; }
.calendar-category-dot.planned { background: #6c757d; }
.calendar-category-dot.in_progress { background: #0d6efd; }
.calendar-category-dot.internal_review { background: #fd7e14; }
.calendar-category-dot.pending_client_approval { background: #0dcaf0; }
.calendar-category-dot.scheduled { background: #6f42c1; }
.calendar-category-dot.published { background: #198754; }
.calendar-event-kind { display: inline-block; margin-top: 0.25rem; padding: 0.12rem 0.38rem; border-radius: 999px; font-size: 0.62rem; line-height: 1.35; font-weight: 600; }
#calendar .calendar-category-marker { display: block; width: 12px; height: 12px; min-height: 12px; margin: 2px; border-radius: 3px; }
#calendar .fc-daygrid-event { display: none !important; }
#calendar .fc-daygrid-day-frame { position: relative; padding-bottom: 18px; }
#calendar .calendar-day-markers { display: flex; position: absolute; right: 4px; bottom: 4px; left: 4px; height: 10px; align-items: center; justify-content: center; gap: 3px; overflow: hidden; pointer-events: none; }
#calendar .calendar-day-marker { display: block; flex: 0 0 8px; width: 8px; height: 8px; border-radius: 2px; }
.calendar-upcoming-list { max-height: 460px; overflow-y: auto; }
.calendar-upcoming-item { display: flex; width: 100%; gap: 0.65rem; align-items: flex-start; margin-bottom: 0.5rem; padding: 0.75rem; border: 1px solid transparent; border-radius: 8px; text-align: left; color: inherit; background: #f8fafc; transition: filter 180ms ease, transform 180ms ease; cursor: pointer; }
.calendar-upcoming-item:hover { filter: brightness(0.97); transform: translateY(-1px); }
.calendar-event-mark { display: inline-flex; flex: 0 0 34px; width: 34px; height: 34px; align-items: center; justify-content: center; border-radius: 50%; font-size: 0.75rem; font-weight: 700; }
.calendar-event-copy { min-width: 0; flex: 1; }
.calendar-event-name { display: block; overflow-wrap: anywhere; color: inherit; font-size: 0.84rem; font-weight: 600; }
.calendar-event-time, .calendar-event-description { display: block; margin-top: 0.2rem; color: inherit; opacity: 0.75; font-size: 0.72rem; line-height: 1.4; }
.calendar-event-description { overflow: hidden; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }
.calendar-upcoming-empty { padding: 1rem 0; color: #7b8794; font-size: 0.82rem; }
.calendar-upcoming-more { align-self: center; margin-top: 0.75rem; padding-inline: 1.5rem; color: #35475a; background: #edf3f9; border: 0; }
.appointment-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 1.1rem 1.25rem; background: #fff; border-bottom: 1px solid #e8edf2; }
#calendar .fc .fc-daygrid-day-top { padding: 4px 6px; }
#calendar .fc .fc-day-today { background-color: #f0f7ff; }
.fc-event { cursor: pointer; }
.fc-daygrid-day:hover { background-color: #f8f9fa; }

/* Side-by-side cards reference styling */
.split-card-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 1rem 1.25rem; background: #fff; border-bottom: 1px solid #e8edf2; }
.split-card-header h6 { font-weight: 700; color: #172b4d; margin-bottom: 0.15rem; font-size: 0.95rem; }
.split-card-header p { font-size: 0.75rem; color: #7b8794; margin-bottom: 0; }
.split-card-badges { display: flex; align-items: center; gap: 0.4rem; }
.split-card-badges .badge { font-weight: 600; padding: 0.35rem 0.55rem; border-radius: 6px; font-size: 0.72rem; }
.split-card-body { max-height: 380px; overflow-y: auto; }
.split-card-body table thead th { position: sticky; top: 0; z-index: 2; background: #f8fafc; font-weight: 700; color: #475569; }

@media (max-width: 991.98px) {
    .calendar-dashboard-layout { grid-template-columns: minmax(0, 1fr); }
    .calendar-upcoming { padding: 1.25rem 0 0; border-top: 1px solid #e8edf2; border-left: 0; }
    .calendar-upcoming-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 1rem; }
}
@media (max-width: 768px) {
    #calendar .fc-toolbar-title { font-size: 1rem; }
    #calendar .fc-header-toolbar { flex-direction: column; gap: 0.65rem; }
    #calendar .fc-toolbar-chunk { display: flex; justify-content: center; }
}
@media (max-width: 575.98px) {
    .appointment-header, .split-card-header { align-items: flex-start; flex-direction: column; }
    .calendar-upcoming-list { grid-template-columns: minmax(0, 1fr); }
}
</style>
@endpush

@section('content')
<div class="page-wrapper"><div class="page-content social-media-page social-dashboard">

    {{-- 1. Top Section: 8 Lists Status Metric Cards (Heading Section) --}}
    @php
        $statusConfig = [
            'planned' => [
                'label' => 'Planned',
                'url' => route('social-media.plans.index'),
            ],
            'in_progress' => [
                'label' => 'In Design',
                'url' => route('social-media.posts.index', ['status' => 'in_progress']),
            ],
            'internal_review' => [
                'label' => 'Internal Review',
                'url' => route('social-media.posts.index', ['status' => 'internal_review']),
            ],
            'pending_client_approval' => [
                'label' => 'Client Approvals',
                'url' => route('social-media.posts.index', ['status' => 'pending_client_approval']),
            ],
            'client_changes_requested' => [
                'label' => 'Changes Requested',
                'url' => route('social-media.posts.index', ['status' => 'client_changes_requested']),
            ],
            'scheduled' => [
                'label' => 'Scheduled',
                'url' => route('social-media.posts.index', ['status' => 'scheduled']),
            ],
            'published' => [
                'label' => 'Published',
                'url' => route('social-media.posts.index', ['status' => 'published']),
            ],
            'rejected' => [
                'label' => 'Rejected',
                'url' => route('social-media.posts.index', ['status' => 'rejected']),
            ],
        ];
    @endphp
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3 mb-4">
        @foreach($statusConfig as $key => $cfg)
            <div class="col">
                <a href="{{ $cfg['url'] }}" class="text-decoration-none">
                    <div class="card radius-10 mb-0 shadow-sm border-0 h-100">
                        <div class="card-body">
                            <p class="mb-1 text-muted">{{ $cfg['label'] }}</p>
                            <h3 class="mb-0 text-dark">{{ $counts[$key] ?? 0 }}</h3>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- 2. Below Lists: Post Calendar Widget --}}
    <div class="row justify-content-center mb-4">
        <div class="col-12">
            <div class="card radius-10 post-calendar-card shadow-sm border-0">
                <div class="card-header appointment-header">
                    <div>
                        <h6 class="mb-0">Publishing Calendar</h6>
                        <p class="mb-0 text-muted font-13">Monthly schedule of planned, reviewed and scheduled posts</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if(!in_array(strtolower((string) auth()->user()->getRawOriginal('role')), ['client','customer']))
                            <a href="{{ route('social-media.posts.create') }}" class="btn btn-primary btn-sm calendar-add-event">
                                <i class="bx bx-plus me-1"></i> Create Post
                            </a>
                        @endif
                        <a href="{{ route('social-media.posts.index') }}" class="btn btn-outline-primary calendar-view-all">
                            <i class="bx bx-table me-1"></i> View All Posts
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="calendar-dashboard-layout">
                        <div id="calendar"></div>
                        <aside class="calendar-upcoming" aria-label="Upcoming posts">
                            <h6 class="calendar-upcoming-title">You are going to</h6>
                            <p id="calendarSelectedDate" class="calendar-selected-date"></p>
                            <div class="calendar-event-legend" aria-label="Calendar post categories">
                                <span><i class="calendar-category-dot planned"></i>Planned</span>
                                <span><i class="calendar-category-dot in_progress"></i>In Design</span>
                                <span><i class="calendar-category-dot internal_review"></i>In Review</span>
                                <span><i class="calendar-category-dot pending_client_approval"></i>Client Approval</span>
                                <span><i class="calendar-category-dot scheduled"></i>Scheduled</span>
                                <span><i class="calendar-category-dot published"></i>Published</span>
                            </div>
                            <div id="calendarUpcomingList" class="calendar-upcoming-list" aria-live="polite">
                                <p class="calendar-upcoming-empty">Loading posts...</p>
                            </div>
                            <button type="button" id="calendarUpcomingMore"
                                class="btn btn-light calendar-upcoming-more" hidden>See More</button>
                        </aside>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Side-by-Side Section (Exact structure and styling matching Dashboard Renewals reference): Monthly Content Plan (Left) & My Assigned Work (Right) --}}
    <div class="card radius-10 mb-4">
        <div class="card-body">
            <div class="row g-4">
                {{-- Left Side: Monthly Content Plan --}}
                <div class="col-12 col-xl-6 d-flex">
                    <div class="border rounded w-100 h-100 bg-white">
                        <div class="p-3 border-bottom bg-light">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <div>
                                    <h6 class="mb-1">Monthly Content Plan</h6>
                                    <p class="mb-0 text-muted font-13">Client monthly content plans and schedules</p>
                                </div>
                                <div class="ms-auto d-flex align-items-center gap-2">
                                    <span class="badge bg-warning text-dark">{{ $monthPlans->count() }} This Month</span>
                                    <span class="badge bg-danger">{{ $monthPlans->whereIn('status', ['changes_requested', 'rejected'])->count() }} Changes</span>
                                    <a href="{{ route('social-media.plans.index') }}" class="btn btn-primary btn-sm">
                                        <i class="bx bx-list-ul"></i> View All
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered align-middle mb-0" id="monthly-content-plans-table" style="width:100%">
                                <thead class="table-light">
                                    <tr>
                                        @if(!in_array(strtolower((string) auth()->user()->getRawOriginal('role')), ['client','customer']))
                                            <th>Company Name</th>
                                        @endif
                                        <th>Plan Title</th>
                                        <th>Month</th>
                                        <th>Posts</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($monthPlans as $plan)
                                        @php
                                            $isDanger = in_array($plan->status, ['changes_requested', 'rejected']);
                                            $statusBadgeClass = match($plan->status) {
                                                'client_approved' => 'bg-success',
                                                'pending_client_review' => 'bg-warning text-dark',
                                                'changes_requested' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <tr class="{{ $isDanger ? 'table-danger' : '' }}">
                                            @if(!in_array(strtolower((string) auth()->user()->getRawOriginal('role')), ['client','customer']))
                                                <td>
                                                    <strong>{{ $plan->client->name ?? '-' }}</strong>
                                                </td>
                                            @endif
                                            <td>
                                                <span class="fw-semibold">{{ $plan->title ?: 'Content Plan' }}</span>
                                            </td>
                                            <td>
                                                {{ \Carbon\Carbon::create($plan->plan_year, $plan->plan_month, 1)->format('M Y') }}
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">{{ $plan->posts_count ?? $plan->posts->count() }}</span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $statusBadgeClass }}">{{ ucwords(str_replace('_',' ', $plan->status ?? 'draft')) }}</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-flex order-actions justify-content-end">
                                                    <a href="{{ route('social-media.plans.index') }}" title="View Plan"><i class="bx bxs-show"></i></a>
                                                    @if(!in_array(strtolower((string) auth()->user()->getRawOriginal('role')), ['client','customer']))
                                                        <a href="{{ route('social-media.posts.create', ['client_id' => $plan->client_id, 'plan_id' => $plan->id]) }}" class="ms-2 text-primary" title="Create Post for this plan"><i class="bx bx-plus-circle"></i></a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ !in_array(strtolower((string) auth()->user()->getRawOriginal('role')), ['client','customer']) ? 6 : 5 }}" class="text-center text-muted py-4">No content plans found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Right Side: My Assigned Work (or My Approvals for Clients) --}}
                <div class="col-12 col-xl-6 d-flex">
                    <div class="border rounded w-100 h-100 bg-white">
                        @if(in_array(strtolower((string) auth()->user()->getRawOriginal('role')), ['client','customer']))
                            {{-- Client Approvals View --}}
                            <div class="p-3 border-bottom bg-light">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <div>
                                        <h6 class="mb-1">My Approvals</h6>
                                        <p class="mb-0 text-muted font-13">Posts awaiting your review and approval</p>
                                    </div>
                                    <div class="ms-auto d-flex align-items-center gap-2">
                                        <span class="badge bg-warning text-dark">{{ $clientApprovals->count() }} Pending</span>
                                        <span class="badge bg-danger">{{ $clientApprovals->where('status', 'client_changes_requested')->count() }} Changes</span>
                                        <a href="{{ route('social-media.approvals') }}" class="btn btn-primary btn-sm">
                                            <i class="bx bx-list-ul"></i> View All
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered align-middle mb-0" id="my-approvals-table" style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Post Title</th>
                                            <th>Platforms</th>
                                            <th>Planned Date</th>
                                            <th>Status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($clientApprovals as $post)
                                            @php
                                                $isDanger = $post->status === 'client_changes_requested';
                                            @endphp
                                            <tr class="{{ $isDanger ? 'table-danger' : '' }}">
                                                <td><strong>{{ $post->title }}</strong></td>
                                                <td>{{ $post->platforms->pluck('platform')->join(', ') ?: '-' }}</td>
                                                <td>{{ $post->planned_at ? $post->planned_at->format('d M Y') : 'N/A' }}</td>
                                                <td><span class="badge bg-warning text-dark">{{ ucwords(str_replace('_',' ',$post->status)) }}</span></td>
                                                <td class="text-end">
                                                    <div class="d-flex order-actions justify-content-end">
                                                        <a href="{{ route('social-media.posts.show', $post) }}" title="Review"><i class="bx bxs-show"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">No posts awaiting approval.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @else
                            {{-- Staff/Admin My Assigned Work View --}}
                            <div class="p-3 border-bottom bg-light">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <div>
                                        <h6 class="mb-1">My Assigned Work</h6>
                                        <p class="mb-0 text-muted font-13">Posts and creative tasks assigned to you</p>
                                    </div>
                                    <div class="ms-auto d-flex align-items-center gap-2">
                                        <span class="badge bg-warning text-dark">{{ $assignedPosts->whereIn('status', ['assigned', 'in_progress'])->count() }} Active</span>
                                        <span class="badge bg-danger">{{ $assignedPosts->whereIn('status', ['changes_requested', 'internal_changes_requested', 'client_changes_requested'])->count() }} Urgent</span>
                                        <a href="{{ route('social-media.my-assigned') }}" class="btn btn-primary btn-sm">
                                            <i class="bx bx-list-ul"></i> View All
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered align-middle mb-0" id="my-assigned-work-table" style="width:100%">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Company Name</th>
                                            <th>Post Title</th>
                                            <th>Platforms</th>
                                            <th>Planned Date</th>
                                            <th>Status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($assignedPosts as $post)
                                            @php
                                                $isDanger = in_array($post->status, ['changes_requested', 'internal_changes_requested', 'client_changes_requested']);
                                                $statusBadgeClass = match($post->status) {
                                                    'published', 'completed' => 'bg-success',
                                                    'scheduled', 'ready_for_publishing' => 'bg-primary',
                                                    'pending_client_approval' => 'bg-info text-dark',
                                                    'internal_review' => 'bg-warning text-dark',
                                                    'changes_requested', 'internal_changes_requested', 'client_changes_requested' => 'bg-danger',
                                                    'in_progress', 'assigned' => 'bg-primary',
                                                    default => 'bg-secondary'
                                                };
                                            @endphp
                                            <tr class="{{ $isDanger ? 'table-danger' : '' }}">
                                                <td><strong>{{ $post->client->name ?? '-' }}</strong></td>
                                                <td>
                                                    <a href="{{ route('social-media.posts.show', $post) }}" class="fw-semibold text-primary">
                                                        {{ $post->title }}
                                                    </a>
                                                </td>
                                                <td>{{ $post->platforms->pluck('platform')->join(', ') ?: '-' }}</td>
                                                <td>{{ $post->planned_at ? $post->planned_at->format('d M Y') : 'N/A' }}</td>
                                                <td>
                                                    <span class="badge {{ $statusBadgeClass }}">
                                                        {{ ucwords(str_replace('_',' ',$post->status)) }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex order-actions justify-content-end">
                                                        <a href="{{ route('social-media.posts.show', $post) }}" title="View"><i class="bx bxs-show"></i></a>
                                                        <a href="{{ route('social-media.posts.edit', $post) }}" class="ms-2" title="Edit"><i class="bx bxs-edit"></i></a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">No assigned posts found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>



</div></div>
@endsection

@push('scripts')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var upcomingListEl = document.getElementById('calendarUpcomingList');
    var upcomingMoreButton = document.getElementById('calendarUpcomingMore');
    var selectedDateEl = document.getElementById('calendarSelectedDate');
    var upcomingEvents = [];
    var showAllUpcomingEvents = false;
    var selectedCalendarDate = calendarDateKey(new Date());

    var calendarEventStyles = {
        planned: {
            label: 'Planned',
            background: '#edf1f5',
            color: '#536171',
            dot: '#6c757d'
        },
        in_progress: {
            label: 'In Design',
            background: '#e5f3f6',
            color: '#12677e',
            dot: '#0d6efd'
        },
        internal_review: {
            label: 'In Review',
            background: '#fff3df',
            color: '#965b0b',
            dot: '#fd7e14'
        },
        pending_client_approval: {
            label: 'Client Approval',
            background: '#f1eafb',
            color: '#684391',
            dot: '#0dcaf0'
        },
        scheduled: {
            label: 'Scheduled',
            background: '#ede7f6',
            color: '#4a148c',
            dot: '#6f42c1'
        },
        published: {
            label: 'Published',
            background: '#e6f4eb',
            color: '#236c49',
            dot: '#198754'
        }
    };

    function openCalendarItem(event) {
        var destination = event.extendedProps.url || (event.url ? event.url : null);
        if (destination) {
            window.location.href = destination;
        }
    }

    function calendarDateKey(date) {
        var d = new Date(date);
        return d.getFullYear() + '-' +
            String(d.getMonth() + 1).padStart(2, '0') + '-' +
            String(d.getDate()).padStart(2, '0');
    }

    function formatCalendarDate(dateKey) {
        return new Intl.DateTimeFormat(undefined, {
            weekday: 'long',
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        }).format(new Date(dateKey + 'T12:00:00'));
    }

    function renderCalendarMarkers(events) {
        var markersByDate = new Map();

        events.forEach(function(event) {
            if (!event.start) return;

            var dateKey = calendarDateKey(event.start);
            var eventType = event.extendedProps.event_type || 'planned';
            var dayMarkers = markersByDate.get(dateKey) || new Map();

            if (!dayMarkers.has(eventType)) {
                var style = calendarEventStyles[eventType] || calendarEventStyles.planned;
                dayMarkers.set(eventType, {
                    color: event.backgroundColor || style.dot || '#64748b',
                    label: event.extendedProps.event_type_label || style.label
                });
            }

            markersByDate.set(dateKey, dayMarkers);
        });

        document.querySelectorAll('#calendar .fc-daygrid-day').forEach(function(dayCell) {
            var frame = dayCell.querySelector('.fc-daygrid-day-frame');
            if (!frame) return;

            var markerRow = frame.querySelector('.calendar-day-markers');
            if (!markerRow) {
                markerRow = document.createElement('div');
                markerRow.className = 'calendar-day-markers';
                markerRow.setAttribute('aria-hidden', 'true');
                frame.appendChild(markerRow);
            }

            markerRow.replaceChildren();
            var dayMarkers = markersByDate.get(dayCell.dataset.date);
            if (!dayMarkers) return;

            dayMarkers.forEach(function(markerData) {
                var marker = document.createElement('span');
                marker.className = 'calendar-day-marker';
                marker.style.backgroundColor = markerData.color;
                marker.title = markerData.label;
                markerRow.appendChild(marker);
            });
        });
    }

    function renderUpcomingEvents(events) {
        if (!upcomingListEl) return;

        upcomingEvents = events
            .filter(function(event) {
                return event.start && calendarDateKey(event.start) === selectedCalendarDate;
            })
            .sort(function(first, second) {
                return first.start - second.start;
            });

        var visibleEvents = showAllUpcomingEvents ? upcomingEvents : upcomingEvents.slice(0, 4);
        upcomingListEl.replaceChildren();
        if (selectedDateEl) {
            selectedDateEl.textContent = formatCalendarDate(selectedCalendarDate);
        }

        if (visibleEvents.length === 0) {
            var emptyMessage = document.createElement('p');
            emptyMessage.className = 'calendar-upcoming-empty';
            emptyMessage.textContent = 'No scheduled posts for this date.';
            upcomingListEl.appendChild(emptyMessage);
        }

        visibleEvents.forEach(function(event) {
            var eventType = event.extendedProps.event_type || 'planned';
            var eventStyle = calendarEventStyles[eventType] || calendarEventStyles.planned;
            var item = document.createElement('button');
            item.type = 'button';
            item.className = 'calendar-upcoming-item';
            item.style.backgroundColor = eventStyle.background;
            item.style.color = eventStyle.color;
            item.setAttribute('aria-label', 'Open ' + eventStyle.label + ': ' + event.title);

            var mark = document.createElement('span');
            mark.className = 'calendar-event-mark';
            mark.style.backgroundColor = eventStyle.dot;
            mark.style.color = '#ffffff';
            mark.textContent = (event.title || 'P').trim().charAt(0).toUpperCase();

            var copy = document.createElement('span');
            copy.className = 'calendar-event-copy';

            var name = document.createElement('span');
            name.className = 'calendar-event-name';
            name.textContent = event.title || 'Untitled Post';

            var time = document.createElement('span');
            time.className = 'calendar-event-time';
            var dateLabel = calendarDateKey(event.start) === calendarDateKey(new Date()) ?
                'Today' :
                new Intl.DateTimeFormat(undefined, {
                    month: 'short',
                    day: 'numeric'
                }).format(event.start);
            var timeLabel = new Intl.DateTimeFormat(undefined, {
                hour: 'numeric',
                minute: '2-digit'
            }).format(event.start);
            time.textContent = dateLabel + (event.allDay ? '' : ' at ' + timeLabel);

            var kind = document.createElement('span');
            kind.className = 'calendar-event-kind';
            kind.style.backgroundColor = eventStyle.dot;
            kind.style.color = '#ffffff';
            kind.textContent = event.extendedProps.status_label || event.extendedProps.event_type_label || eventStyle.label;

            copy.appendChild(name);
            copy.appendChild(time);
            copy.appendChild(kind);

            var description = event.extendedProps.description;
            if (description) {
                var descriptionText = document.createElement('span');
                descriptionText.className = 'calendar-event-description';
                descriptionText.textContent = description;
                copy.appendChild(descriptionText);
            }

            item.appendChild(mark);
            item.appendChild(copy);
            item.addEventListener('click', function() {
                openCalendarItem(event);
            });
            upcomingListEl.appendChild(item);
        });

        if (upcomingMoreButton) {
            upcomingMoreButton.hidden = upcomingEvents.length <= 4;
            upcomingMoreButton.textContent = showAllUpcomingEvents ? 'Show Less' : 'See More';
        }
    }

    if (upcomingMoreButton) {
        upcomingMoreButton.addEventListener('click', function() {
            showAllUpcomingEvents = !showAllUpcomingEvents;
            renderUpcomingEvents(calendar.getEvents());
        });
    }

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        aspectRatio: 1.75,
        expandRows: true,
        themeSystem: 'bootstrap5',
        editable: false,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        events: @json($calendarEvents),
        eventsSet: function(events) {
            renderCalendarMarkers(events);
            renderUpcomingEvents(events);
        },
        eventContent: function(info) {
            var marker = document.createElement('span');
            marker.className = 'calendar-category-marker';
            marker.setAttribute('aria-hidden', 'true');
            marker.style.backgroundColor = info.event.backgroundColor || '#6c757d';

            return {
                domNodes: [marker]
            };
        },
        dateClick: function(info) {
            selectedCalendarDate = info.dateStr;
            showAllUpcomingEvents = false;
            var createBtns = document.querySelectorAll('.calendar-add-event');
            createBtns.forEach(function(btn) {
                btn.href = "{{ route('social-media.posts.create') }}?planned_at=" + encodeURIComponent(info.dateStr + ' 10:00');
            });
            renderUpcomingEvents(calendar.getEvents());
        },
        eventClick: function(info) {
            info.jsEvent.preventDefault();
            openCalendarItem(info.event);
        },
        eventDidMount: function(info) {
            var eventType = info.event.extendedProps.event_type || 'planned';
            var eventStyle = calendarEventStyles[eventType] || calendarEventStyles.planned;
            var categoryLabel = info.event.extendedProps.event_type_label || eventStyle.label;
            var tooltipTitle = categoryLabel + ': ' + info.event.title;

            info.el.setAttribute('aria-label', tooltipTitle);
            if (window.$ && $.fn.tooltip) {
                $(info.el).tooltip({
                    title: tooltipTitle,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body'
                });
            }
        }
    });

    
    calendar.render();

    // DataTables Initialization for Split View Tables (matching index.blade.php)
    const initDashboardTable = (selector) => {
        if (!window.jQuery || !$.fn.DataTable) {
            return;
        }

        const $table = $(selector);
        if (!$table.length || $.fn.DataTable.isDataTable($table)) {
            return;
        }

        $table.DataTable({
            order: [],
            lengthChange: false,
            searching: false,
            info: false,
            paging: false,
            autoWidth: false,
            columnDefs: [{
                orderable: false,
                targets: [-1] // Disable sorting on the Actions column
            }]
        });
    };

    initDashboardTable('#monthly-content-plans-table');
    initDashboardTable('#my-assigned-work-table');
    initDashboardTable('#my-approvals-table');

});
</script>
@endpush
