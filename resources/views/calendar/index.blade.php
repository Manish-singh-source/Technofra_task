@extends('layout.master')

@section('content')
    @php
        $categories = [
            'appointment' => ['label' => 'Appointments', 'icon' => 'bx-calendar-event', 'background' => '#edf1f5', 'color' => '#536171'],
            'task' => ['label' => 'Tasks', 'icon' => 'bx-task', 'background' => '#e5f3f6', 'color' => '#12677e'],
            'renewal' => ['label' => 'Renewals', 'icon' => 'bx-refresh', 'background' => '#fff3df', 'color' => '#965b0b'],
            'lead' => ['label' => 'Lead Management', 'icon' => 'bx-user-voice', 'background' => '#f1eafb', 'color' => '#684391'],
            'todo' => ['label' => 'To-do', 'icon' => 'bx-check-square', 'background' => '#e6f4eb', 'color' => '#236c49'],
        ];
    @endphp

    <style>
        .calendar-list-page {
            --calendar-primary: #008cff;
        }

        .calendar-list-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        .calendar-list-title {
            margin: 0 0 4px;
            color: #172033;
            font-size: 22px;
            font-weight: 700;
        }

        .calendar-list-subtitle {
            margin: 0;
            color: #718096;
            font-size: 13px;
        }

        .calendar-summary-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 8px;
            margin-bottom: 16px;
        }

        .calendar-summary-card {
            display: flex;
            min-width: 0;
            align-items: center;
            gap: 10px;
            padding: 12px;
            border: 1px solid rgba(15, 23, 42, 0.06);
            border-radius: 10px;
        }

        .calendar-summary-icon {
            display: inline-flex;
            flex: 0 0 34px;
            width: 34px;
            height: 34px;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: rgba(255, 255, 255, 0.65);
            font-size: 20px;
        }

        .calendar-summary-count {
            display: block;
            font-size: 18px;
            font-weight: 700;
            line-height: 1;
        }

        .calendar-summary-label {
            display: block;
            overflow: hidden;
            margin-top: 4px;
            font-size: 11px;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .calendar-filter-card,
        .calendar-table-card {
            border: 0;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(32, 52, 72, 0.07);
        }

        .calendar-filter-card .card-body {
            padding: 16px;
        }

        .calendar-filter-grid {
            display: grid;
            grid-template-columns: minmax(200px, 1.4fr) minmax(140px, 0.8fr) minmax(140px, 0.7fr) minmax(140px, 0.7fr) 100px auto;
            gap: 12px;
            align-items: end;
        }

        .calendar-filter-grid .form-label {
            margin-bottom: 6px;
            color: #536171;
            font-size: 11px;
            font-weight: 600;
        }

        .calendar-filter-grid .form-control,
        .calendar-filter-grid .form-select {
            min-height: 40px;
            border-color: #e2e8f0;
            border-radius: 8px;
            font-size: 13px;
        }

        .calendar-filter-actions {
            display: flex;
            gap: 8px;
        }

        .calendar-filter-actions .btn {
            min-height: 40px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        .calendar-table-card {
            overflow: hidden;
        }

        .calendar-table-card .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            background: #fff;
            border-bottom: 1px solid #edf0f3;
        }

        .calendar-items-table {
            margin: 0;
        }

        .calendar-items-table thead th {
            padding: 11px 14px;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 1px solid #e8edf3;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .calendar-items-table tbody td {
            padding: 12px 14px;
            color: #35475a;
            border-color: #edf0f3;
            font-size: 13px;
            vertical-align: middle;
        }

        .calendar-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 8px;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .calendar-item-title {
            color: #1f2d3d;
            font-weight: 600;
        }

        .calendar-item-description {
            display: block;
            max-width: 340px;
            overflow: hidden;
            color: #7b8794;
            font-size: 11px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .calendar-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 8px;
            border-radius: 7px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .calendar-status-badge.completed {
            color: #236c49;
            background: #e6f4eb;
        }

        .calendar-status-badge.pending {
            color: #965b0b;
            background: #fff3df;
        }

        .calendar-empty-state {
            padding: 48px 20px;
            text-align: center;
        }

        .calendar-empty-state i {
            color: #b9dfff;
            font-size: 52px;
        }

        @media (max-width: 991.98px) {
            .calendar-summary-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .calendar-filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .calendar-filter-actions {
                grid-column: 1 / -1;
            }
        }

        @media (max-width: 575.98px) {
            .calendar-list-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .calendar-list-title {
                font-size: 20px;
            }

            .calendar-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .calendar-filter-grid {
                grid-template-columns: minmax(0, 1fr);
            }

            .calendar-filter-actions {
                grid-column: auto;
            }
        }
    </style>

    <div class="page-wrapper calendar-list-page">
        <div class="page-content">
            <div class="calendar-list-header">
                <div>
                    <h1 class="calendar-list-title">All Calendar Items</h1>
                    <p class="calendar-list-subtitle">Appointments, tasks, renewals, lead activities and to-do items in one place.</p>
                </div>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
                    <i class="bx bx-calendar me-1"></i> Back to Calendar
                </a>
            </div>

            <div class="calendar-summary-grid">
                @foreach ($categories as $key => $category)
                    <div class="calendar-summary-card" style="background: {{ $category['background'] }}; color: {{ $category['color'] }};">
                        <span class="calendar-summary-icon"><i class="bx {{ $category['icon'] }}"></i></span>
                        <span class="min-width-0">
                            <span class="calendar-summary-count">{{ $categoryCounts->get($key, 0) }}</span>
                            <span class="calendar-summary-label">{{ $category['label'] }}</span>
                        </span>
                    </div>
                @endforeach
            </div>

            <div class="card calendar-filter-card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('calendar.index') }}" class="calendar-filter-grid">
                        <div>
                            <label for="calendar-search" class="form-label">Search</label>
                            <input type="search" id="calendar-search" name="search" class="form-control"
                                value="{{ request('search') }}" placeholder="Search title or description">
                        </div>
                        <div>
                            <label for="calendar-type" class="form-label">Category</label>
                            <select id="calendar-type" name="type" class="form-select">
                                <option value="">All categories</option>
                                @foreach ($categories as $key => $category)
                                    <option value="{{ $key }}" @selected(request('type') === $key)>{{ $category['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="calendar-start-date" class="form-label">From Date</label>
                            <input type="date" id="calendar-start-date" name="start_date" class="form-control"
                                value="{{ request('start_date', $startDate->format('Y-m-d')) }}">
                        </div>
                        <div>
                            <label for="calendar-end-date" class="form-label">To Date</label>
                            <input type="date" id="calendar-end-date" name="end_date" class="form-control"
                                value="{{ request('end_date', $endDate->format('Y-m-d')) }}">
                        </div>
                        <div>
                            <label for="calendar-per-page" class="form-label">Show</label>
                            <select id="calendar-per-page" name="per_page" class="form-select" onchange="this.form.submit()">
                                @foreach ([10, 20, 50, 100] as $pageSize)
                                    <option value="{{ $pageSize }}" @selected((int) request('per_page', 20) === $pageSize)>
                                        {{ $pageSize }} rows
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="calendar-filter-actions">
                            <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                            <a href="{{ route('calendar.index') }}" class="btn btn-light">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card calendar-table-card">
                <div class="card-header">
                    <div>
                        <h6 class="mb-1">Calendar Schedule</h6>
                        <p class="mb-0 text-muted font-13">{{ $calendarItems->total() }} items found</p>
                    </div>
                    <span class="badge bg-light text-primary px-3 py-2">
                        {{ $startDate->format('d M Y') }} – {{ $endDate->format('d M Y') }}
                    </span>
                </div>

                @if ($calendarItems->count())
                    <div class="table-responsive">
                        <table class="table calendar-items-table align-middle">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Category</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($calendarItems as $item)
                                    @php($category = $categories[$item['type']] ?? $categories['appointment'])
                                    <tr>
                                        <td class="text-nowrap">
                                            <strong>{{ $item['start']->format('d M Y') }}</strong>
                                            <span class="d-block text-muted font-13">
                                                {{ $item['all_day'] ? 'All day' : $item['start']->format('h:i A') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="calendar-type-badge" style="background: {{ $category['background'] }}; color: {{ $category['color'] }};">
                                                <i class="bx {{ $category['icon'] }}"></i>{{ $item['type_label'] }}
                                            </span>
                                        </td>
                                        <td><span class="calendar-item-title">{{ $item['title'] }}</span></td>
                                        <td>
                                            <span class="calendar-item-description" title="{{ $item['description'] }}">
                                                {{ $item['description'] ?: 'No description provided' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="calendar-status-badge {{ $item['is_completed'] ? 'completed' : 'pending' }}">
                                                <i class="bx {{ $item['is_completed'] ? 'bx-check-circle' : 'bx-time-five' }}"></i>
                                                {{ $item['is_completed'] ? 'Completed' : 'Pending' }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if ($item['url'])
                                                <a href="{{ $item['url'] }}" class="btn btn-sm btn-outline-primary" aria-label="Open {{ $item['title'] }}">
                                                    Open <i class="bx bx-right-arrow-alt ms-1"></i>
                                                </a>
                                            @else
                                                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light">View Calendar</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 border-top">
                        <span class="text-muted font-13">
                            Showing {{ $calendarItems->firstItem() }}–{{ $calendarItems->lastItem() }} of {{ $calendarItems->total() }}
                        </span>
                        {{ $calendarItems->onEachSide(1)->links() }}
                    </div>
                @else
                    <div class="calendar-empty-state">
                        <i class="bx bx-calendar-x"></i>
                        <h6 class="mt-2 mb-1">No calendar items found</h6>
                        <p class="mb-0 text-muted font-13">Try changing the category, search term, or date range.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
