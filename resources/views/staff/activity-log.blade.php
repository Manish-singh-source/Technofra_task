@extends('layout.master')

@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            @include('layout.errors')

            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="{{ route('staff') }}">Staff</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Activity Log</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <a href="{{ route('staff') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-arrow-back"></i> Back to Staff
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div>
                            <h5 class="mb-1">Staff Activity Log</h5>
                            <p class="text-muted mb-0">Successful logins, login device, IP address, and explicit logout time. Account actions apply to every device for that staff member. Your own account cannot be blocked or remotely logged out here.</p>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('staff.activity-log') }}" class="row g-3 align-items-end mb-4">
                        <div class="col-md-4">
                            <label for="staff_id" class="form-label">Staff member</label>
                            <select name="staff_id" id="staff_id" class="form-select">
                                <option value="">All staff</option>
                                @foreach ($staffMembers as $member)
                                    <option value="{{ $member->id }}" @selected(request('staff_id') == $member->id)>
                                        {{ $member->name }} ({{ $member->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="from" class="form-label">From date</label>
                            <input type="date" name="from" id="from" class="form-control" value="{{ request('from') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="to" class="form-label">To date</label>
                            <input type="date" name="to" id="to" class="form-control" value="{{ request('to') }}">
                        </div>
                        <div class="col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('staff.activity-log') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Staff</th>
                                    <th>Login time</th>
                                    <th>Logout time</th>
                                    <th>Device</th>
                                    <th>Browser</th>
                                    <th>IP address</th>
                                    <th>Session</th>
                                    <th>Account action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $log)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $log->user?->name ?: $log->staff_name }}</div>
                                            <small class="text-muted">{{ $log->staff_email }}</small>
                                        </td>
                                        <td>{{ $log->logged_in_at->format('Y-m-d H:i:s') }}</td>
                                        <td>{{ $log->logged_out_at ? $log->logged_out_at->format('Y-m-d H:i:s') : '—' }}</td>
                                        <td>{{ $log->device_name }}</td>
                                        <td>{{ $log->browser_name }}</td>
                                        <td>{{ $log->ip_address ?: '—' }}</td>
                                        <td>
                                            <span class="badge {{ $log->logged_out_at ? 'bg-secondary' : 'bg-success' }}">
                                                {{ $log->logged_out_at ? 'Logged out' : 'Logout not recorded' }}
                                            </span>
                                        </td>
                                        <td class="text-nowrap">
                                            @php
                                                $isCurrentUser = (int) auth()->id() === (int) $log->user_id;
                                                $isBlocked = ! in_array((string) $log->user?->getRawOriginal('status'), ['1', 'active'], true);
                                            @endphp
                                            @if ($isCurrentUser)
                                                <span class="badge bg-light text-dark border">Current account</span>
                                            @elseif ($canManageStaffAccounts && $log->user)
                                                <div class="d-inline-flex gap-1">
                                                    <form method="POST" action="{{ route('staff.force-logout', $log->user) }}" onsubmit="return confirm('Log out {{ addslashes($log->user->name) }} from all devices?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-warning px-2" title="Log out from all devices" aria-label="Log out from all devices">
                                                            <i class="bx bx-log-out"></i>
                                                        </button>
                                                    </form>
                                                    @if ($isBlocked)
                                                        <form method="POST" action="{{ route('staff.unblock', $log->user) }}" onsubmit="return confirm('Allow {{ addslashes($log->user->name) }} to log in again?');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-success px-2" title="Unblock account" aria-label="Unblock account">
                                                                <i class="bx bx-check-circle"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form method="POST" action="{{ route('staff.block', $log->user) }}" onsubmit="return confirm('Block {{ addslashes($log->user->name) }}? This logs them out and prevents future login.');">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger px-2" title="Block account" aria-label="Block account">
                                                                <i class="bx bx-block"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            @elseif (! $log->user)
                                                <span class="text-muted">Account unavailable</span>
                                            @else
                                                <span class="text-muted">No permission</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No staff login activity found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
