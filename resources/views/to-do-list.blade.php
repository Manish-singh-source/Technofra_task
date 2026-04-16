@extends('/layout/master')
@section('content')
<div class="page-wrapper">
    <div class="page-content todo-page">
        <div class="todo-shell">
            <section class="todo-hero card border-0 shadow-sm">
                <div class="todo-hero__copy">
                    <div class="todo-hero__eyebrow">My Day</div>
                    <h1 class="todo-hero__title">Focus on what matters today</h1>
                    <p class="todo-hero__text">Each logged-in user will only see their own recurring todos.</p>
                    <div class="todo-hero__actions">
                        <button type="button" class="btn btn-primary todo-new-btn" id="openTodoModal">
                            <i class="bx bx-plus"></i>
                            <span>Add a task</span>
                        </button>
                    </div>
                </div>
                <div class="todo-hero__stats">
                    <div class="todo-stat-card">
                        <span class="todo-stat-card__label">Planned</span>
                        <strong class="todo-stat-card__value" id="plannedCount">0</strong>
                    </div>
                    <div class="todo-stat-card">
                        <span class="todo-stat-card__label">Completed</span>
                        <strong class="todo-stat-card__value" id="completedCount">0</strong>
                    </div>
                    <div class="todo-stat-card">
                        <span class="todo-stat-card__label">Due Today</span>
                        <strong class="todo-stat-card__value" id="todayCount">0</strong>
                    </div>
                </div>
            </section>

            <div class="row g-4 mt-1 align-items-stretch">
                {{-- <div class="col-12 col-xl-4">
                    <aside class="todo-panel todo-panel--sidebar card border-0 shadow-sm h-100">
                        <div class="todo-panel__head">
                            <div>
                                <div class="todo-panel__eyebrow">Overview</div>
                                <h5 class="mb-0">Smart lists</h5>
                            </div>
                        </div>
                        <div class="todo-smart-list">
                            <div class="todo-smart-item">
                                <span class="todo-smart-item__icon bg-soft-blue"><i class="bx bx-sun"></i></span>
                                <div>
                                    <strong>My Day</strong>
                                    <div class="text-muted small">Tasks you should keep in front of you.</div>
                                </div>
                            </div>
                            <div class="todo-smart-item">
                                <span class="todo-smart-item__icon bg-soft-gold"><i class="bx bx-repeat"></i></span>
                                <div>
                                    <strong>Recurring</strong>
                                    <div class="text-muted small">Day, week, month and year schedules.</div>
                                </div>
                            </div>
                            <div class="todo-smart-item">
                                <span class="todo-smart-item__icon bg-soft-green"><i class="bx bx-bell"></i></span>
                                <div>
                                    <strong>Reminders</strong>
                                    <div class="text-muted small">Email alerts arrive at your selected time.</div>
                                </div>
                            </div>
                        </div>
                        <div class="todo-note-box">
                            <div class="todo-note-box__title">How it works</div>
                            <p class="mb-0">Set a repeat pattern, choose reminder time, and the task stays personal to the current login user.</p>
                        </div>
                    </aside>
                </div> --}}
                <div class="col-12 col-xl-12">
                    <div class="row g-4">
                        <div class="col-12">
                            <div class="todo-panel card border-0 shadow-sm">
                                <div class="todo-panel__head">
                                    <div>
                                        <div class="todo-panel__eyebrow">Active</div>
                                        <h5 class="mb-0">Unfinished Tasks</h5>
                                    </div>
                                    <span class="todo-panel__count" id="unfinishedCountBadge">0</span>
                                </div>
                                <div class="card-body p-0">
                                    <div id="unfinishedList" class="todo-list-wrap"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="todo-panel card border-0 shadow-sm">
                                <div class="todo-panel__head">
                                    <div>
                                        <div class="todo-panel__eyebrow">Archive</div>
                                        <h5 class="mb-0">Completed Tasks</h5>
                                    </div>
                                    <span class="todo-panel__count todo-panel__count--done" id="finishedCountBadge">0</span>
                                </div>
                                <div class="card-body p-0">
                                    <div id="finishedList" class="todo-list-wrap"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="todoModal" tabindex="-1" aria-labelledby="todoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content todo-modal-content">
            <div class="modal-header border-0 pb-0">
                <div>
                    <div class="todo-panel__eyebrow">Task Setup</div>
                    <h5 class="modal-title" id="todoModalLabel">Add New Todo</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="todoForm" class="row g-3">
                    <div class="col-md-6">
                        <label for="todoTitle" class="form-label fw-semibold">Title</label>
                        <input type="text" id="todoTitle" class="form-control" maxlength="255" required>
                    </div>
                    <div class="col-md-6">
                        <label for="todoTaskDate" class="form-label fw-semibold">Select Date</label>
                        <input type="date" id="todoTaskDate" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label for="todoDescription" class="form-label fw-semibold">Description</label>
                        <textarea id="todoDescription" class="form-control todo-description-area" rows="4" placeholder="Add extra details for this task"></textarea>
                    </div>
                    <div class="col-12">
                        <label for="todoAttachments" class="form-label fw-semibold">Attachments</label>
                        <input type="file" id="todoAttachments" class="form-control" multiple>
                        <div class="form-text">You can upload multiple files. When editing a todo, new files are added to the existing attachments.</div>
                        <div id="todoExistingAttachments" class="todo-attachment-list mt-3 d-none"></div>
                    </div>
                    <div class="col-md-4">
                        <label for="todoTaskTime" class="form-label fw-semibold">Select Time</label>
                        <input type="time" id="todoTaskTime" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label for="todoRepeatInterval" class="form-label fw-semibold">Repeats Every</label>
                        <div class="input-group">
                            <input type="number" id="todoRepeatInterval" class="form-control" min="1" value="1" required>
                            <select id="todoRepeatUnit" class="form-select" required>
                                <option value="day">Day</option>
                                <option value="week">Week</option>
                                <option value="month">Month</option>
                                <option value="year">Year</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="todoReminderTime" class="form-label fw-semibold">Set Time</label>
                        <input type="time" id="todoReminderTime" class="form-control">
                    </div>
                    <div class="col-12 d-none" id="repeatDaysWrap">
                        <label class="form-label fw-semibold">Select Day</label>
                        <div class="todo-days-grid">
                            <label class="todo-day-option"><input type="checkbox" value="sunday"> Sun</label>
                            <label class="todo-day-option"><input type="checkbox" value="monday"> Mon</label>
                            <label class="todo-day-option"><input type="checkbox" value="tuesday"> Tue</label>
                            <label class="todo-day-option"><input type="checkbox" value="wednesday"> Wed</label>
                            <label class="todo-day-option"><input type="checkbox" value="thursday"> Thu</label>
                            <label class="todo-day-option"><input type="checkbox" value="friday"> Fri</label>
                            <label class="todo-day-option"><input type="checkbox" value="saturday"> Sat</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="todoStartsOn" class="form-label fw-semibold">Starts</label>
                        <input type="date" id="todoStartsOn" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold d-block">Ends</label>
                        <div class="todo-ends-box">
                            <label><input type="radio" name="todoEndsType" value="never" checked> Never</label>
                            <label><input type="radio" name="todoEndsType" value="on"> On</label>
                            <label><input type="radio" name="todoEndsType" value="after"> After</label>
                        </div>
                    </div>
                    <div class="col-md-6 d-none" id="endsOnWrap">
                        <label for="todoEndsOn" class="form-label fw-semibold">Ends On</label>
                        <input type="date" id="todoEndsOn" class="form-control">
                    </div>
                    <div class="col-md-6 d-none" id="endsAfterWrap">
                        <label for="todoEndsAfter" class="form-label fw-semibold">Count Day Input</label>
                        <input type="number" id="todoEndsAfter" class="form-control" min="1" placeholder="Occurrences count">
                    </div>
                </form>
                <div id="todoFormError" class="alert alert-danger mt-3 mb-0 d-none"></div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveTodoBtn">Save Task</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    :root {
        --todo-brand-cyan: #1fb9e3;
        --todo-brand-navy: #123f74;
        --todo-brand-indigo: #3d3ea3;
        --todo-brand-green: #0cab4b;
        --todo-brand-orange: #ff8a17;
        --todo-surface: #f4fafd;
        --todo-card: #ffffff;
        --todo-border: #dfeaf3;
        --todo-text: #294764;
        --todo-muted: #70879d;
    }
    .todo-page {
        min-height: calc(100vh - 120px);
        background:
            radial-gradient(circle at top left, rgba(31, 185, 227, 0.18), transparent 30%),
            radial-gradient(circle at bottom right, rgba(12, 171, 75, 0.12), transparent 24%),
            linear-gradient(180deg, #fbfdff 0%, #f3f9fd 50%, #edf5fb 100%);
    }
    .todo-shell { max-width: 1400px; margin: 0 auto; }
    .todo-hero {
    display: grid;
    grid-template-columns: minmax(0, 1.6fr) minmax(320px, 0.9fr);
    gap: 24px;
    padding: 28px;
    background: linear-gradient(
349deg, var(--todo-brand-navy) 0%, var(--todo-brand-cyan) 58%, #7dd7ee 100%);
    color: #fff;
    overflow: hidden;
    position: relative;
}
    .todo-hero::after {
        content: ''; position: absolute; inset: auto -60px -100px auto; width: 220px; height: 220px;
        border-radius: 50%; background: rgba(255, 255, 255, 0.08);
    }
    .todo-hero__eyebrow, .todo-panel__eyebrow {
        text-transform: uppercase; letter-spacing: 0.14em; font-size: 11px; font-weight: 700;
    }
    .todo-hero__title { font-size: clamp(2rem, 3vw, 2.7rem); font-weight: 700; margin: 10px 0 12px; line-height: 1.08; }
    .todo-hero__text { max-width: 560px; color: rgba(255, 255, 255, 0.92); margin: 0 0 20px; }
    .todo-hero__actions { display: flex; gap: 12px; flex-wrap: wrap; }
    .todo-new-btn {
        display: inline-flex; align-items: center; gap: 8px; border-radius: 999px; padding: 12px 18px; font-weight: 700;
        background: #ffffff; color: var(--todo-brand-navy); border: 0; box-shadow: 0 12px 30px rgba(9, 24, 65, 0.18);
    }
    .todo-new-btn:hover { color: var(--todo-brand-indigo); background: #f8fcff; }
    .todo-hero__stats { display: grid; gap: 14px; align-content: center; position: relative; z-index: 1; }
    .todo-stat-card {
        padding: 18px 20px; border-radius: 20px; background: rgba(255, 255, 255, 0.28);
        border: 1px solid rgba(255, 255, 255, 0.24); backdrop-filter: blur(10px);
    }
    .todo-stat-card__label { display: block; font-size: 12px; color: rgba(255, 255, 255, 0.72); text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 6px; }
    .todo-stat-card__value { font-size: 30px; line-height: 1; }
    .todo-panel { border-radius: 24px; background: rgba(255, 255, 255, 0.94); backdrop-filter: blur(10px); }
    .todo-panel--sidebar { padding: 24px; }
    .todo-panel__head {
        display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 22px 24px 0;
    }
    .todo-panel__count {
        min-width: 40px; height: 40px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center;
        font-weight: 700; background: #ebf8fd; color: var(--todo-brand-navy);
    }
    .todo-panel__count--done { background: #eefaf2; color: var(--todo-brand-green); }
    .todo-smart-list { display: grid; gap: 14px; margin-top: 22px; }
    .todo-smart-item {
        display: flex; gap: 14px; align-items: flex-start; padding: 14px; border-radius: 18px; background: #fbfdff; border: 1px solid var(--todo-border);
    }
    .todo-smart-item__icon { width: 42px; height: 42px; border-radius: 14px; display: inline-flex; align-items: center; justify-content: center; font-size: 20px; }
    .bg-soft-blue { background: #e9f9fd; color: var(--todo-brand-cyan); }
    .bg-soft-gold { background: #fff4e8; color: var(--todo-brand-orange); }
    .bg-soft-green { background: #ecfbf2; color: var(--todo-brand-green); }
    .todo-note-box { margin-top: 18px; padding: 18px; border-radius: 18px; background: linear-gradient(135deg, var(--todo-brand-cyan), #78d8f0); color: #ffffff; }
    .todo-note-box__title { font-weight: 700; margin-bottom: 8px; }
    .todo-list-wrap { min-height: 240px; padding: 16px 18px 18px; }
    .todo-item { padding: 16px; border-radius: 20px; background: #ffffff; border: 1px solid var(--todo-border); box-shadow: 0 10px 24px rgba(96, 126, 173, 0.08); }
    .todo-item + .todo-item { margin-top: 14px; }
    .todo-top-row { display: flex; align-items: flex-start; gap: 14px; }
    .todo-check { width: 20px; height: 20px; margin-top: 3px; border-radius: 50%; cursor: pointer; }
    .todo-title { font-size: 18px; font-weight: 700; color: var(--todo-text); margin-bottom: 4px; }
    .todo-title.is-done, .todo-description.is-done { text-decoration: line-through; color: #8191a8; }
    .todo-description { color: var(--todo-muted); white-space: pre-wrap; font-size: 14px; line-height: 1.6; }
    .todo-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
    .todo-badge { display: inline-flex; align-items: center; gap: 6px; background: #eef7fb; color: var(--todo-text); border-radius: 999px; padding: 7px 11px; font-size: 12px; font-weight: 600; }
    .todo-actions { display: flex; gap: 10px; margin-left: auto; }
    .todo-actions button {
        border: 0; width: 38px; height: 38px; border-radius: 50%; background: #f2f8fb; color: var(--todo-brand-navy); transition: 0.2s ease;
    }
    .todo-actions button:hover { background: #e8f7fc; color: var(--todo-brand-cyan); transform: translateY(-1px); }
    .todo-empty { padding: 34px 20px; border-radius: 18px; background: rgba(255, 255, 255, 0.96); border: 1px dashed #dfe8f5; text-align: center; color: #8b9bb1; font-weight: 600; }
    .todo-modal-content { border-radius: 26px; border: 0; }
    .todo-description-area { resize: vertical; min-height: 100px; }
    .todo-attachment-list { display: flex; flex-wrap: wrap; gap: 10px; }
    .todo-attachment-chip { display: inline-flex; align-items: center; gap: 8px; padding: 9px 12px; border-radius: 999px; background: #eef7fb; color: var(--todo-text); font-size: 12px; font-weight: 600; text-decoration: none; position: relative; }
    .todo-attachment-chip:hover { background: #e3f3fa; color: var(--todo-brand-navy); }
    .todo-attachment-remove { display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; border-radius: 50%; background: #ff6b6b; color: #fff; cursor: pointer; margin-left: 4px; font-size: 14px; line-height: 1; }
    .todo-attachment-remove:hover { background: #e74c3c; }
    .todo-attachment-inline { margin-top: 12px; display: flex; flex-wrap: wrap; gap: 8px; }
    .todo-attachment-summary { color: var(--todo-muted); font-size: 13px; margin-top: 12px; }
    .todo-days-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(90px, 1fr)); gap: 10px; }
    .todo-day-option, .todo-ends-box label {
        display: flex; align-items: center; gap: 8px; border: 1px solid var(--todo-border); border-radius: 14px; padding: 11px 12px; background: #fbfdff; font-weight: 600;
    }
    .todo-ends-box { display: flex; gap: 10px; flex-wrap: wrap; }
    @media (max-width: 1199px) { .todo-hero { grid-template-columns: 1fr; } }
    @media (max-width: 767px) {
        :root {
        --todo-brand-cyan: #1fb9e3;
        --todo-brand-navy: #123f74;
        --todo-brand-indigo: #3d3ea3;
        --todo-brand-green: #0cab4b;
        --todo-brand-orange: #ff8a17;
        --todo-surface: #f4fafd;
        --todo-card: #ffffff;
        --todo-border: #dfeaf3;
        --todo-text: #294764;
        --todo-muted: #70879d;
    }
    .todo-page { padding-inline: 0; }
        .todo-hero, .todo-panel--sidebar, .todo-panel__head, .todo-list-wrap { padding-left: 18px; padding-right: 18px; }
        .todo-top-row { flex-wrap: wrap; }
        .todo-actions { width: 100%; margin-top: 4px; margin-left: 34px; }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const routes = {
        list: "{{ route('todos.list') }}",
        store: "{{ route('todos.store') }}",
        update: "{{ url('/todos') }}/" + "__ID__",
        delete: "{{ url('/todos') }}/" + "__ID__",
        status: "{{ url('/todos') }}/" + "__ID__" + "/status",
    };
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const todoModalEl = document.getElementById('todoModal');
    const todoModal = new bootstrap.Modal(todoModalEl);
    const unfinishedList = document.getElementById('unfinishedList');
    const finishedList = document.getElementById('finishedList');
    const todoFormError = document.getElementById('todoFormError');
    const todoModalLabel = document.getElementById('todoModalLabel');
    const repeatDaysWrap = document.getElementById('repeatDaysWrap');
    const endsOnWrap = document.getElementById('endsOnWrap');
    const endsAfterWrap = document.getElementById('endsAfterWrap');
    const todoRepeatUnit = document.getElementById('todoRepeatUnit');
    const plannedCount = document.getElementById('plannedCount');
    const completedCount = document.getElementById('completedCount');
    const todayCount = document.getElementById('todayCount');
    const unfinishedCountBadge = document.getElementById('unfinishedCountBadge');
    const finishedCountBadge = document.getElementById('finishedCountBadge');

    const fields = {
        title: document.getElementById('todoTitle'),
        description: document.getElementById('todoDescription'),
        attachments: document.getElementById('todoAttachments'),
        task_date: document.getElementById('todoTaskDate'),
        task_time: document.getElementById('todoTaskTime'),
        repeat_interval: document.getElementById('todoRepeatInterval'),
        repeat_unit: document.getElementById('todoRepeatUnit'),
        reminder_time: document.getElementById('todoReminderTime'),
        starts_on: document.getElementById('todoStartsOn'),
        ends_on: document.getElementById('todoEndsOn'),
        ends_after_occurrences: document.getElementById('todoEndsAfter')
    };
    const todoExistingAttachments = document.getElementById('todoExistingAttachments');

    let todos = @json($formattedTodos);
    let editingId = null;
    let removedAttachmentIndices = [];

    render();
    syncRepeatDaysVisibility();
    syncEndsVisibility();

    document.getElementById('openTodoModal').addEventListener('click', openCreateModal);
    document.getElementById('saveTodoBtn').addEventListener('click', saveTodo);
    todoRepeatUnit.addEventListener('change', syncRepeatDaysVisibility);
    document.querySelectorAll('input[name="todoEndsType"]').forEach(function (radio) {
        radio.addEventListener('change', syncEndsVisibility);
    });

    todoModalEl.addEventListener('hidden.bs.modal', resetForm);
    todoExistingAttachments.addEventListener('click', handleAttachmentRemove);
    unfinishedList.addEventListener('click', handleListAction);
    finishedList.addEventListener('click', handleListAction);

    function openCreateModal() {
        resetForm();
        removedAttachmentIndices = [];
        todoModalLabel.textContent = 'Add New Todo';
        const today = new Date().toISOString().slice(0, 10);
        fields.task_date.value = today;
        fields.starts_on.value = today;
        todoModal.show();
    }

    function openEditModal(todo) {
        resetForm();
        removedAttachmentIndices = [];
        editingId = todo.id;
        todoModalLabel.textContent = 'Edit Todo';
        fields.title.value = todo.title || '';
        fields.description.value = todo.description || '';
        fields.task_date.value = normalizeDate(todo.task_date);
        fields.task_time.value = normalizeTime(todo.task_time);
        fields.repeat_interval.value = todo.repeat_interval || 1;
        fields.repeat_unit.value = todo.repeat_unit || 'day';
        fields.reminder_time.value = normalizeTime(todo.reminder_time);
        fields.starts_on.value = normalizeDate(todo.starts_on || todo.task_date);
        fields.ends_on.value = normalizeDate(todo.ends_on);
        fields.ends_after_occurrences.value = todo.ends_after_occurrences || '';
        document.querySelector('input[name="todoEndsType"][value="' + (todo.ends_type || 'never') + '"]').checked = true;
        document.querySelectorAll('#repeatDaysWrap input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.checked = Array.isArray(todo.repeat_days_list) && todo.repeat_days_list.includes(checkbox.value);
        });
        syncRepeatDaysVisibility();
        syncEndsVisibility();
        renderExistingAttachments(todo.attachments || []);
        todoModal.show();
    }

    async function saveTodo() {
        const formData = buildFormData();
        const url = editingId ? routes.update.replace('__ID__', editingId) : routes.store;

        if (editingId) {
            formData.append('_method', 'PUT');
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData,
            });

            const result = await response.json();
            if (!response.ok) {
                throw new Error(extractError(result));
            }

            await reloadTodos();
            todoModal.hide();
        } catch (error) {
            showError(error.message || 'Unable to save todo.');
        }
    }
    async function reloadTodos() {
        const response = await fetch(routes.list, { headers: { 'Accept': 'application/json' } });
        const result = await response.json();
        todos = Array.isArray(result.data) ? result.data : [];
        render();
    }

    async function handleListAction(event) {
        const row = event.target.closest('.todo-item');
        if (!row) return;

        const todoId = row.getAttribute('data-id');
        const todo = todos.find(function (item) { return String(item.id) === String(todoId); });
        if (!todo) return;

        if (event.target.closest('.todo-edit')) {
            openEditModal(todo);
            return;
        }

        if (event.target.closest('.todo-delete')) {
            if (!confirm('Do you want to delete this todo?')) return;
            await fetch(routes.delete.replace('__ID__', todoId), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            await reloadTodos();
            return;
        }

        if (event.target.classList.contains('todo-toggle')) {
            await fetch(routes.status.replace('__ID__', todoId), {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ is_completed: event.target.checked })
            });
            await reloadTodos();
        }
    }

    function handleAttachmentRemove(event) {
        const removeBtn = event.target.closest('.todo-attachment-remove');
        if (!removeBtn) return;

        event.preventDefault();
        event.stopPropagation();

        const index = parseInt(removeBtn.getAttribute('data-index'), 10);
        if (!isNaN(index) && !removedAttachmentIndices.includes(index)) {
            removedAttachmentIndices.push(index);
            const todo = todos.find(function (t) { return String(t.id) === String(editingId); });
            if (todo && todo.attachments) {
                const filteredAttachments = todo.attachments.filter(function (_, i) {
                    return !removedAttachmentIndices.includes(i);
                });
                renderExistingAttachments(filteredAttachments);
            }
        }
    }

    function collectPayload() {
        const endsType = document.querySelector('input[name="todoEndsType"]:checked').value;
        const repeatDays = Array.from(document.querySelectorAll('#repeatDaysWrap input[type="checkbox"]:checked')).map(function (item) {
            return item.value;
        });

        return {
            title: fields.title.value.trim(),
            description: fields.description.value.trim(),
            task_date: fields.task_date.value,
            task_time: fields.task_time.value || null,
            repeat_interval: Number(fields.repeat_interval.value || 1),
            repeat_unit: fields.repeat_unit.value,
            repeat_days: fields.repeat_unit.value === 'week' ? repeatDays : [],
            reminder_time: fields.reminder_time.value || null,
            starts_on: fields.starts_on.value,
            ends_type: endsType,
            ends_on: endsType === 'on' ? (fields.ends_on.value || null) : null,
            ends_after_occurrences: endsType === 'after' ? Number(fields.ends_after_occurrences.value || 0) || null : null,
            remove_attachments: removedAttachmentIndices,
        };
    }

    function buildFormData() {
        const payload = collectPayload();
        const formData = new FormData();

        Object.keys(payload).forEach(function (key) {
            const value = payload[key];

            if (key === 'remove_attachments' && Array.isArray(value) && value.length > 0) {
                value.forEach(function (item) {
                    formData.append('remove_attachments[]', item);
                });
                return;
            }

            if (Array.isArray(value) && value.length > 0) {
                value.forEach(function (item) {
                    formData.append(key + '[]', item);
                });
                return;
            }

            if (!Array.isArray(value) && value !== null && value !== '') {
                formData.append(key, value);
            }
        });

        Array.from(fields.attachments.files || []).forEach(function (file) {
            formData.append('attachments[]', file);
        });

        return formData;
    }
    function render() {
        const unfinished = todos.filter(function (item) { return !item.is_completed; });
        const finished = todos.filter(function (item) { return item.is_completed; });
        const todayIso = new Date().toISOString().slice(0, 10);
        const dueToday = unfinished.filter(function (item) { return normalizeDate(item.task_date) === todayIso; });

        plannedCount.textContent = String(unfinished.length);
        completedCount.textContent = String(finished.length);
        todayCount.textContent = String(dueToday.length);
        unfinishedCountBadge.textContent = String(unfinished.length);
        finishedCountBadge.textContent = String(finished.length);

        unfinishedList.innerHTML = unfinished.length
            ? unfinished.map(todoTemplate).join('')
            : '<div class="todo-empty">No unfinished tasks yet. Start by adding your first task.</div>';

        finishedList.innerHTML = finished.length
            ? finished.map(todoTemplate).join('')
            : '<div class="todo-empty">Completed tasks will appear here once you finish them.</div>';
    }

    function todoTemplate(todo) {
        const titleClass = todo.is_completed ? 'todo-title is-done' : 'todo-title';
        const descClass = todo.is_completed ? 'todo-description is-done' : 'todo-description';
        const repeatDays = Array.isArray(todo.repeat_days_list) && todo.repeat_days_list.length ? 'Days: ' + todo.repeat_days_list.map(shortDay).join(', ') : '';
        const endsText = todo.ends_type === 'on'
            ? 'Ends on ' + formatDate(todo.ends_on)
            : (todo.ends_type === 'after' ? 'Ends after ' + todo.ends_after_occurrences + ' times' : 'Never ends');
        const attachments = Array.isArray(todo.attachments) ? todo.attachments : [];
        const attachmentHtml = attachments.length
            ? '<div class="todo-attachment-inline">' + attachments.map(function (attachment) {
                const fileName = attachment.name || attachment.path || 'Attachment';
                return '<a class="todo-attachment-chip" href="' + escapeHtml(attachment.url || '#') + '" target="_blank" rel="noopener"><i class="bx bx-paperclip"></i>' + escapeHtml(fileName) + '</a>';
            }).join('') + '</div>'
            : '';

        return [
            '<div class="todo-item" data-id="' + escapeHtml(todo.id) + '">',
                '<div class="todo-top-row">',
                    '<input class="form-check-input todo-check todo-toggle" type="checkbox" ' + (todo.is_completed ? 'checked' : '') + '>',
                    '<div class="flex-grow-1">',
                        '<div class="' + titleClass + '">' + escapeHtml(todo.title || '') + '</div>',
                        '<div class="' + descClass + '">' + escapeHtml(todo.description || 'No description') + '</div>',
                        '<div class="todo-meta">',
                            '<span class="todo-badge"><i class="bx bx-calendar"></i> ' + escapeHtml(formatDate(todo.task_date)) + '</span>',
                            '<span class="todo-badge"><i class="bx bx-time"></i> ' + escapeHtml(formatTime(todo.task_time) || 'Any time') + '</span>',
                            '<span class="todo-badge"><i class="bx bx-refresh"></i> ' + escapeHtml(todo.display_schedule || '') + '</span>',
                            '<span class="todo-badge"><i class="bx bx-play-circle"></i> ' + escapeHtml('Starts ' + formatDate(todo.starts_on)) + '</span>',
                            '<span class="todo-badge"><i class="bx bx-stop-circle"></i> ' + escapeHtml(endsText) + '</span>',
                            (repeatDays ? '<span class="todo-badge"><i class="bx bx-repeat"></i> ' + escapeHtml(repeatDays) + '</span>' : ''),
                            (todo.reminder_time ? '<span class="todo-badge"><i class="bx bx-bell"></i> ' + escapeHtml('Reminder ' + formatTime(todo.reminder_time)) + '</span>' : ''),
                            (attachments.length ? '<span class="todo-badge"><i class="bx bx-paperclip"></i> ' + escapeHtml(String(attachments.length) + ' attachment(s)') + '</span>' : ''),
                        '</div>',
                        attachmentHtml,
                    '</div>',
                    '<div class="todo-actions">',
                        '<button type="button" class="todo-edit" title="Edit"><i class="bx bx-edit-alt"></i></button>',
                        '<button type="button" class="todo-delete" title="Delete"><i class="bx bx-trash"></i></button>',
                    '</div>',
                '</div>',
            '</div>'
        ].join('');
    }
    function resetForm() {
        editingId = null;
        removedAttachmentIndices = [];
        document.getElementById('todoForm').reset();
        document.querySelector('input[name="todoEndsType"][value="never"]').checked = true;
        document.querySelectorAll('#repeatDaysWrap input[type="checkbox"]').forEach(function (checkbox) {
            checkbox.checked = false;
        });
        fields.repeat_interval.value = 1;
        fields.attachments.value = '';
        renderExistingAttachments([]);
        hideError();
        syncRepeatDaysVisibility();
        syncEndsVisibility();
    }
    function syncRepeatDaysVisibility() {
        repeatDaysWrap.classList.toggle('d-none', fields.repeat_unit.value !== 'week');
    }

    function syncEndsVisibility() {
        const value = document.querySelector('input[name="todoEndsType"]:checked').value;
        endsOnWrap.classList.toggle('d-none', value !== 'on');
        endsAfterWrap.classList.toggle('d-none', value !== 'after');
    }

    function normalizeDate(value) {
        if (!value) return '';
        return String(value).slice(0, 10);
    }

    function normalizeTime(value) {
        if (!value) return '';
        return String(value).slice(0, 5);
    }

    function formatDate(value) {
        if (!value) return '';
        return normalizeDate(value).split('-').reverse().join('-');
    }

    function formatTime(value) {
        if (!value) return '';
        const raw = String(value).slice(0, 5).split(':');
        let hour = Number(raw[0]);
        const minute = raw[1];
        const suffix = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12 || 12;
        return hour + ':' + minute + ' ' + suffix;
    }

    function shortDay(day) {
        return day ? day.slice(0, 3).toUpperCase() : '';
    }

    function extractError(result) {
        if (result && result.message) return result.message;
        if (result && result.errors) {
            const firstKey = Object.keys(result.errors)[0];
            if (firstKey && Array.isArray(result.errors[firstKey]) && result.errors[firstKey][0]) {
                return result.errors[firstKey][0];
            }
        }
        return 'Validation failed.';
    }

    function showError(message) {
        todoFormError.textContent = message;
        todoFormError.classList.remove('d-none');
    }

    function hideError() {
        todoFormError.textContent = '';
        todoFormError.classList.add('d-none');
    }

    function renderExistingAttachments(attachments) {
        const items = Array.isArray(attachments) ? attachments : [];

        if (!items.length) {
            todoExistingAttachments.innerHTML = '';
            todoExistingAttachments.classList.add('d-none');
            return;
        }

        todoExistingAttachments.innerHTML = items.map(function (attachment, index) {
            const fileName = attachment.name || attachment.path || 'Attachment';
            const removeBtn = '<span class="todo-attachment-remove" data-index="' + index + '" title="Remove"><i class="bx bx-x"></i></span>';
            return '<a class="todo-attachment-chip" href="' + escapeHtml(attachment.url || '#') + '" target="_blank" rel="noopener"><i class="bx bx-paperclip"></i>' + escapeHtml(fileName) + removeBtn + '</a>';
        }).join('');
        todoExistingAttachments.classList.remove('d-none');
    }
    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
</script>
@endpush
@endsection






