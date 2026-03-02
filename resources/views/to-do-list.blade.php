@extends('/layout/master')
@section('content')
<div class="page-wrapper">
    <div class="page-content todo-page">
        <button type="button" class="btn btn-primary todo-new-btn" id="openTodoModal">
            New To Do
        </button>

        <div class="row g-3 mt-2">
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="todo-card-head todo-card-head-warning">
                        <i class="bx bxs-error-alt me-1"></i> Unfinished to do's
                    </div>
                    <div class="card-body p-0">
                        <div id="unfinishedList" class="todo-list-wrap"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="todo-card-head todo-card-head-info">
                        <i class="bx bx-check me-1"></i> Latest finished to do's
                    </div>
                    <div class="card-body p-0">
                        <div id="finishedList" class="todo-list-wrap"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="todoModal" tabindex="-1" aria-labelledby="todoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="todoModalLabel">Add New Todo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label for="todoDescription" class="form-label fw-semibold">Description</label>
                <textarea id="todoDescription" class="form-control todo-description-area" rows="4"></textarea>
                <div id="todoFormError" class="text-danger small mt-2 d-none">Description is required.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveTodoBtn">Save</button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .todo-page {
        background: #eef1f6;
        min-height: calc(100vh - 120px);
    }

    .todo-new-btn {
        border-radius: 10px;
        border: 2px solid #ffffff;
        box-shadow: 0 0 0 2px #2e65d7;
        font-weight: 600;
        padding: 8px 16px;
    }

    .todo-card-head {
        color: #fff;
        font-size: 20px;
        font-weight: 600;
        padding: 12px 14px;
    }

    .todo-card-head-warning {
        background: #c88a02;
    }

    .todo-card-head-info {
        background: #0e83be;
    }

    .todo-list-wrap {
        min-height: 420px;
    }

    .todo-item {
        border-bottom: 1px solid #edf0f4;
        padding: 14px 16px;
        position: relative;
    }

    .todo-item:last-child {
        border-bottom: 0;
    }

    .todo-top-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .todo-drag {
        color: #b7bec8;
        font-size: 16px;
        line-height: 1;
        padding-top: 2px;
    }

    .todo-check {
        margin-top: 2px;
    }

    .todo-content {
        flex: 1;
        color: #2f4361;
        font-size: 16px;
        line-height: 1.45;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .todo-content.is-done {
        text-decoration: line-through;
        color: #5a6a80;
    }

    .todo-time {
        margin-top: 8px;
        margin-left: 26px;
        color: #6f8199;
        font-size: 13px;
    }

    .todo-actions {
        position: absolute;
        right: 14px;
        bottom: 10px;
        display: flex;
        gap: 8px;
    }

    .todo-actions button {
        border: 0;
        background: transparent;
        color: #5d6f89;
        padding: 0;
        line-height: 1;
    }

    .todo-actions button:hover {
        color: #2d5dd5;
    }

    .todo-empty {
        padding: 30px 16px;
        text-align: center;
        color: #7f8ea5;
        font-weight: 500;
    }

    .todo-description-area {
        border: 2px solid #2f69eb;
        border-radius: 8px;
        resize: vertical;
        min-height: 92px;
    }

    @media (max-width: 768px) {
        .todo-content {
            font-size: 14px;
        }

        .todo-time {
            font-size: 12px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const STORAGE_KEY = 'todo-list-items-v1';
    const unfinishedList = document.getElementById('unfinishedList');
    const finishedList = document.getElementById('finishedList');
    const todoModalEl = document.getElementById('todoModal');
    const todoModal = new bootstrap.Modal(todoModalEl);
    const openTodoModalBtn = document.getElementById('openTodoModal');
    const saveTodoBtn = document.getElementById('saveTodoBtn');
    const todoDescription = document.getElementById('todoDescription');
    const todoFormError = document.getElementById('todoFormError');
    const todoModalLabel = document.getElementById('todoModalLabel');

    let todos = readTodos();
    let editTodoId = null;

    render();

    openTodoModalBtn.addEventListener('click', function () {
        openModalForCreate();
    });

    saveTodoBtn.addEventListener('click', function () {
        const description = todoDescription.value.trim();
        if (!description) {
            todoFormError.classList.remove('d-none');
            return;
        }

        if (editTodoId) {
            const target = todos.find(function (todo) { return todo.id === editTodoId; });
            if (target) {
                target.description = description;
                target.updatedAt = new Date().toISOString();
            }
        } else {
            todos.push({
                id: String(Date.now()),
                description: description,
                completed: false,
                createdAt: new Date().toISOString(),
                completedAt: null
            });
        }

        persist();
        render();
        todoModal.hide();
        resetModalState();
    });

    todoModalEl.addEventListener('hidden.bs.modal', function () {
        resetModalState();
    });

    unfinishedList.addEventListener('click', function (event) {
        handleListAction(event, false);
    });

    finishedList.addEventListener('click', function (event) {
        handleListAction(event, true);
    });

    function handleListAction(event, isFinishedList) {
        const target = event.target;
        const row = target.closest('.todo-item');
        if (!row) return;
        const todoId = row.getAttribute('data-id');
        const todo = todos.find(function (item) { return item.id === todoId; });
        if (!todo) return;

        if (target.classList.contains('todo-toggle')) {
            todo.completed = target.checked;
            todo.completedAt = target.checked ? new Date().toISOString() : null;
            persist();
            render();
            return;
        }

        const editBtn = target.closest('.todo-edit');
        if (editBtn) {
            openModalForEdit(todo);
            return;
        }

        const deleteBtn = target.closest('.todo-delete');
        if (deleteBtn) {
            todos = todos.filter(function (item) { return item.id !== todoId; });
            persist();
            render();
            return;
        }

        if (isFinishedList && target.classList.contains('todo-content')) {
            // Keep finish area consistent with screenshot behavior (read-only text click).
            return;
        }
    }

    function openModalForCreate() {
        editTodoId = null;
        todoModalLabel.textContent = 'Add New Todo';
        todoDescription.value = '';
        todoFormError.classList.add('d-none');
        todoModal.show();
        setTimeout(function () { todoDescription.focus(); }, 180);
    }

    function openModalForEdit(todo) {
        editTodoId = todo.id;
        todoModalLabel.textContent = 'Edit Todo';
        todoDescription.value = todo.description;
        todoFormError.classList.add('d-none');
        todoModal.show();
        setTimeout(function () { todoDescription.focus(); }, 180);
    }

    function resetModalState() {
        editTodoId = null;
        todoDescription.value = '';
        todoFormError.classList.add('d-none');
        todoModalLabel.textContent = 'Add New Todo';
    }

    function readTodos() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (!raw) return [];
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (err) {
            return [];
        }
    }

    function persist() {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(todos));
    }

    function render() {
        const unfinished = todos
            .filter(function (item) { return !item.completed; })
            .sort(function (a, b) { return new Date(b.createdAt) - new Date(a.createdAt); });

        const finished = todos
            .filter(function (item) { return item.completed; })
            .sort(function (a, b) {
                return new Date(b.completedAt || b.createdAt) - new Date(a.completedAt || a.createdAt);
            });

        unfinishedList.innerHTML = unfinished.length
            ? unfinished.map(function (todo) { return todoItemTemplate(todo); }).join('')
            : '<div class="todo-empty">No unfinished todo found.</div>';

        finishedList.innerHTML = finished.length
            ? finished.map(function (todo) { return todoItemTemplate(todo); }).join('')
            : '<div class="todo-empty">No finished todo found.</div>';
    }

    function todoItemTemplate(todo) {
        const stamp = todo.completed ? (todo.completedAt || todo.createdAt) : todo.createdAt;
        return [
            '<div class="todo-item" data-id="' + escapeHtml(todo.id) + '">',
                '<div class="todo-top-row">',
                    '<span class="todo-drag"><i class="bx bx-dots-vertical-rounded"></i></span>',
                    '<input class="form-check-input todo-check todo-toggle" type="checkbox" ' + (todo.completed ? 'checked' : '') + '>',
                    '<div class="todo-content ' + (todo.completed ? 'is-done' : '') + '">' + escapeHtml(todo.description) + '</div>',
                '</div>',
                '<div class="todo-time">' + formatDateTime(stamp) + '</div>',
                '<div class="todo-actions">',
                    '<button type="button" class="todo-edit" title="Edit"><i class="bx bx-edit-alt"></i></button>',
                    '<button type="button" class="todo-delete" title="Delete"><i class="bx bx-x"></i></button>',
                '</div>',
            '</div>'
        ].join('');
    }

    function formatDateTime(iso) {
        const date = new Date(iso);
        if (isNaN(date.getTime())) return '';
        const pad = function (n) { return n < 10 ? '0' + n : String(n); };
        return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()) +
            ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes()) + ':' + pad(date.getSeconds());
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
