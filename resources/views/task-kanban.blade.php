@extends('/layout/master')
@section('content')
    <div class="page-wrapper">
        <div class="page-content">
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('task') }}">Tasks</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Kanban Board</li>
                        </ol>
                    </nav>
                </div>
                <div class="ms-auto d-flex gap-2">
                    <a href="{{ route('task') }}" class="btn btn-outline-secondary">Task List</a>
                    @can('create_tasks')
                        <a href="{{ route('add-task') }}" class="btn btn-primary"><i class="bx bxs-plus-square"></i> Add Task</a>
                    @endcan
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Project</label>
                            <select id="projectFilter" class="form-select">
                                <option value="">All Projects</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ (string) $selectedProjectId === (string) $project->id ? 'selected' : '' }}>
                                        {{ $project->project_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <input type="text" id="taskSearch" class="form-control" placeholder="Search by task title or description">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button id="refreshBoard" class="btn btn-outline-primary w-100">Refresh Board</button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Enterprise Kanban</h5>
                        <small class="text-muted">Drag cards between columns to update status</small>
                    </div>

                    <div class="row g-3" id="kanbanBoard"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const boardEl = document.getElementById('kanbanBoard');
            const projectFilterEl = document.getElementById('projectFilter');
            const taskSearchEl = document.getElementById('taskSearch');
            const refreshBtn = document.getElementById('refreshBoard');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            const workflowForColumn = {
                backlog: 'backlog',
                todo: 'todo',
                in_progress: 'in_progress',
                review: 'review',
                testing: 'testing',
                done: 'completed'
            };

            function badgeClass(priority) {
                const normalized = (priority || '').toLowerCase();
                if (normalized === 'high') return 'bg-danger';
                if (normalized === 'medium') return 'bg-warning text-dark';
                return 'bg-success';
            }

            function cardTemplate(task) {
                const assignees = (task.assignees || []).map(user => user.name).join(', ');
                return `
                    <div class="card mb-2 kanban-card" data-task-id="${task.id}">
                        <div class="card-body p-2">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <strong class="small text-wrap">${task.title ?? ''}</strong>
                                <span class="badge ${badgeClass(task.priority)}">${(task.priority || 'low').toUpperCase()}</span>
                            </div>
                            <div class="small text-muted mb-1">${task.project ?? 'No Project'}</div>
                            <div class="small mb-1"><strong>Assignees:</strong> ${assignees || 'Unassigned'}</div>
                            <div class="small"><strong>Due:</strong> ${task.deadline || 'N/A'}</div>
                        </div>
                    </div>
                `;
            }

            function columnTemplate(column) {
                const tasksHtml = (column.tasks || []).map(cardTemplate).join('');
                return `
                    <div class="col-xl-2 col-lg-4 col-md-6">
                        <div class="card h-100">
                            <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center">
                                <strong>${column.label}</strong>
                                <span class="badge bg-light text-dark">${(column.tasks || []).length}</span>
                            </div>
                            <div class="card-body p-2 kanban-column" data-column-key="${column.key}" style="min-height: 380px; background: #f8f9fb;">
                                ${tasksHtml}
                            </div>
                        </div>
                    </div>
                `;
            }

            async function fetchBoard() {
                const params = new URLSearchParams();
                if (projectFilterEl.value) params.set('project_id', projectFilterEl.value);
                if (taskSearchEl.value.trim()) params.set('search', taskSearchEl.value.trim());

                const response = await fetch(`{{ route('task.kanban.data') }}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const payload = await response.json();
                if (!payload.success) {
                    throw new Error(payload.message || 'Failed to load board');
                }
                return payload.data.columns || [];
            }

            function getColumnTaskIds(columnEl) {
                return Array.from(columnEl.querySelectorAll('.kanban-card')).map(card => Number(card.dataset.taskId));
            }

            async function moveTask(taskId, columnKey, columnTaskIds) {
                const workflowStatus = workflowForColumn[columnKey] || 'backlog';
                const response = await fetch(`{{ url('/task-kanban') }}/${taskId}/move`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        workflow_status: workflowStatus,
                        column_task_ids: columnTaskIds
                    })
                });
                const payload = await response.json();
                if (!payload.success) {
                    throw new Error(payload.message || 'Failed to move task');
                }
            }

            function initSortable() {
                document.querySelectorAll('.kanban-column').forEach(columnEl => {
                    new Sortable(columnEl, {
                        group: 'task-kanban',
                        animation: 150,
                        onEnd: async function (evt) {
                            const taskCard = evt.item;
                            const taskId = Number(taskCard.dataset.taskId);
                            const targetColumn = evt.to.dataset.columnKey;
                            const columnTaskIds = getColumnTaskIds(evt.to);

                            try {
                                await moveTask(taskId, targetColumn, columnTaskIds);
                            } catch (error) {
                                alert(error.message || 'Unable to move task.');
                                await loadBoard();
                            }
                        }
                    });
                });
            }

            async function loadBoard() {
                boardEl.innerHTML = '<div class="col-12 text-center py-4">Loading board...</div>';
                try {
                    const columns = await fetchBoard();
                    boardEl.innerHTML = columns.map(columnTemplate).join('');
                    initSortable();
                } catch (error) {
                    boardEl.innerHTML = `<div class="col-12"><div class="alert alert-danger mb-0">${error.message || 'Could not load Kanban board.'}</div></div>`;
                }
            }

            refreshBtn.addEventListener('click', loadBoard);
            projectFilterEl.addEventListener('change', loadBoard);

            let searchDebounce = null;
            taskSearchEl.addEventListener('input', function () {
                if (searchDebounce) clearTimeout(searchDebounce);
                searchDebounce = setTimeout(loadBoard, 300);
            });

            loadBoard();
        });
    </script>
@endsection
