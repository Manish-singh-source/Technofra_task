@extends('/layout/master')
@section('content')
    @php
        $taskTabs = [
            'all' => ['label' => 'All', 'table_id' => 'tasksAllTable'],
            'not_started' => ['label' => 'Not Started', 'table_id' => 'tasksNotStartedTable'],
            'in_progress' => ['label' => 'In Progress', 'table_id' => 'tasksInProgressTable'],
            'on_hold' => ['label' => 'On Hold', 'table_id' => 'tasksOnHoldTable'],
            'completed' => ['label' => 'Completed', 'table_id' => 'tasksCompletedTable'],
            'cancelled' => ['label' => 'Cancelled', 'table_id' => 'tasksCancelledTable'],
        ];
    @endphp
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Tasks</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto">
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary">Settings</button>
                        <button type="button"
                            class="btn btn-primary split-bg-primary dropdown-toggle dropdown-toggle-split"
                            data-bs-toggle="dropdown"> <span class="visually-hidden">Toggle Dropdown</span>
                        </button>
                        @can('delete_tasks')
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end">
                            <a class="dropdown-item cursor-pointer" id="delete-selected">Delete All</a>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
            <!--end breadcrumb-->
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(!empty($isRestrictedToAssignedTasks))
                {{-- <div class="alert alert-info">Aapko sirf wahi tasks dikh rahe hain jo aapko assign kiye gaye hain.</div> --}}
            @endif

            <div class="row mb-3">
                <div class="col-lg-3">
                    <div class="card radius-10 border-start border-0 border-4 border-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Running Tasks</p>
                                    <h4 class="my-1 text-primary">{{ $runningTasks ?? 0 }}</h4>
                                    <p class="mb-0 font-13">Currently in progress</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-blues text-white ms-auto">
                                    <i class='bx bx-play'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card radius-10 border-start border-0 border-4 border-success">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Completed Tasks</p>
                                    <h4 class="my-1 text-success">{{ $completedTasks ?? 0 }}</h4>
                                    <p class="mb-0 font-13">Finished successfully</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto">
                                    <i class='bx bx-check'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card radius-10 border-start border-0 border-4 border-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Late Tasks</p>
                                    <h4 class="my-1 text-warning">{{ $lateTasks ?? 0 }}</h4>
                                    <p class="mb-0 font-13">Overdue but not delayed</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto">
                                    <i class='bx bx-time-five'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="card radius-10 border-start border-0 border-4 border-danger">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div>
                                    <p class="mb-0 text-secondary">Hold Tasks</p>
                                    <h4 class="my-1 text-danger">{{ $delayedTasks ?? 0 }}</h4>
                                    <p class="mb-0 font-13">Significantly behind</p>
                                </div>
                                <div class="widgets-icons-2 rounded-circle bg-gradient-bloody text-white ms-auto">
                                    <i class='bx bx-error'></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-lg-flex align-items-center mb-4 gap-3">
                        <div class="ms-auto d-flex gap-2">
                            <div class="btn-group">
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                                        <i class="bx bx-upload"></i> Upload Excel File
                                    </a></li>
                                    <li><a class="dropdown-item" href="#">
                                        <i class="bx bx-download"></i> Download Template
                                    </a></li>
                                </ul>
                            </div>
                            @can('create_tasks')
                            <a href="{{ route('add-task') }}" class="btn btn-primary radius-30 mt-2 mt-lg-0">
                                <i class="bx bxs-plus-square"></i>Add New Task
                            </a>
                            @endcan
                        </div>
                    </div>

                    <ul class="nav nav-tabs" id="taskTabs" role="tablist">
                        @foreach($taskTabs as $statusKey => $tab)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ $statusKey }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $statusKey }}" type="button" role="tab" aria-controls="{{ $statusKey }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">{{ $tab['label'] }}</button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="tab-content mt-3" id="taskTabsContent">
                        @foreach($taskTabs as $statusKey => $tab)
                            @php
                                $statusTasks = $statusKey === 'all' ? $tasks : $tasks->where('status', $statusKey);
                            @endphp
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $statusKey }}" role="tabpanel" aria-labelledby="{{ $statusKey }}-tab">
                                <div class="table-responsive">
                                    <table id="{{ $tab['table_id'] }}" class="table table-striped table-bordered tasks-status-table" style="width:100%">
                                        <thead class="table-light">
                                            <tr>
                                                @can('delete_tasks')
                                                <th><input class="form-check-input select-all-tab" type="checkbox"></th>
                                                @endcan
                                                <th>Project & Task</th>
                                                <th>Created On</th>
                                                <th>Total Hours</th>
                                                <th>Priority</th>
                                                <th>Assignee</th>
                                                <th>Attachments</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($statusTasks as $task)
                                            <tr>
                                                @can('delete_tasks')
                                                <td><input class="form-check-input row-checkbox" type="checkbox" name="ids[]" value="{{ $task->id }}"></td>
                                                @endcan
                                                <td>
                                                    <strong>Project:</strong> {{ $task->project->project_name ?? 'N/A' }} <br>
                                                    <strong>Task:</strong> {{ $task->title }}
                                                </td>
                                                <td>{{ $task->created_at->format('Y-m-d') }}</td>
                                                <td>N/A</td>
                                                <td>
                                                    @if($task->priority == 'high')
                                                        <span class="badge bg-danger">High</span>
                                                    @elseif($task->priority == 'medium')
                                                        <span class="badge bg-warning text-dark">Medium</span>
                                                    @else
                                                        <span class="badge bg-success">Low</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($task->assignees)
                                                        @foreach($task->assignees as $assigneeId)
                                                            @if(isset($staff[$assigneeId]))
                                                                <div class="d-flex align-items-center mb-1">
                                                                    <img src="{{ $staff[$assigneeId]->profile_image ? asset('uploads/staff/' . $staff[$assigneeId]->profile_image) : 'https://placehold.co/30x30' }}" class="rounded-circle me-2" alt="Assignee" width="30" height="30">
                                                                    {{ $staff[$assigneeId]->first_name }} {{ $staff[$assigneeId]->last_name }}
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        <span>No assignees</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($task->attachments->count() > 0)
                                                        <span class="badge bg-info">{{ $task->attachments->count() }} file(s)</span>
                                                    @else
                                                        <span class="text-muted">No attachments</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $isLate = $task->deadline
                                                            && $task->deadline->lt(\Carbon\Carbon::today())
                                                            && !in_array($task->status, ['completed', 'cancelled', 'on_hold'], true);
                                                    @endphp
                                                    @if($isLate)
                                                        <span class="badge bg-warning text-dark">Late</span>
                                                    @elseif($task->status == 'in_progress')
                                                        <span class="badge bg-primary">Running</span>
                                                    @elseif($task->status == 'completed')
                                                        <span class="badge bg-success">Completed</span>
                                                    @elseif($task->status == 'on_hold')
                                                        <span class="badge bg-danger">Hold</span>
                                                    @elseif($task->status == 'not_started')
                                                        <span class="badge bg-secondary">Not Started</span>
                                                    @elseif($task->status == 'cancelled')
                                                        <span class="badge bg-dark">Cancelled</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ ucfirst($task->status) }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex order-actions">
                                                        @can('view_tasks')
                                                        <a href="{{ route('task-details', $task->id) }}"><i class='bx bxs-show'></i></a>
                                                        @endcan
                                                        @can('edit_tasks')
                                                        <a href="{{ route('edit-task', $task->id) }}" class="ms-2"><i class='bx bxs-edit'></i></a>
                                                        @endcan
                                                        @can('delete_tasks')
                                                        <form action="{{ route('task.destroy', $task->id) }}" method="POST" class="d-inline ms-2" onsubmit="return confirm('Are you sure you want to delete this task?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-link p-0 border-0 text-danger" aria-label="Delete task">
                                                                <i class='bx bxs-trash'></i>
                                                            </button>
                                                        </form>
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="10" class="text-center">No tasks found.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end page wrapper -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('.tasks-status-table').each(function() {
                $(this).DataTable({
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                    pageLength: 10,
                    order: [[1, 'desc']],
                    language: {
                        search: 'Search Tasks:',
                        lengthMenu: 'Show _MENU_ entries',
                        info: 'Showing _START_ to _END_ of _TOTAL_ tasks',
                        paginate: {
                            first: 'First',
                            last: 'Last',
                            next: 'Next',
                            previous: 'Previous'
                        }
                    }
                });
            });

            document.querySelectorAll('.select-all-tab').forEach(function(selectAll) {
                selectAll.addEventListener('change', function() {
                    const table = selectAll.closest('table');
                    if (!table) {
                        return;
                    }

                    table.querySelectorAll('.row-checkbox').forEach(function(checkbox) {
                        checkbox.checked = selectAll.checked;
                    });
                });
            });

            const deleteSelectedBtn = document.getElementById('delete-selected');
            if (deleteSelectedBtn) {
                deleteSelectedBtn.addEventListener('click', function() {
                    let selected = [];
                    document.querySelectorAll('.row-checkbox:checked').forEach(cb => {
                        selected.push(cb.value);
                    });

                    if (selected.length === 0) {
                        alert('Please select at least one record.');
                        return;
                    }

                    if (confirm('Are you sure you want to delete selected records?')) {
                        let form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route('delete.selected.task') }}';
                        form.innerHTML = `
                            @csrf
                            <input type="hidden" name="_method" value="DELETE">
                            <input type="hidden" name="ids" value="${selected.join(',')}">
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }
        });
    </script>
@endsection


