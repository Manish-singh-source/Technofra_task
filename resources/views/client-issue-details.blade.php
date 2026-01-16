@extends('/layout/master')
@section('content')
<style>
  .modal-backdrop.show {
    opacity: 0.0;
}
.modal-backdrop{
  z-index: 1;
}
.modal-body1 {
    display: grid!important;
}
.modal-body1 {
    display: flex;
    padding: 12px;
    flex: 1;
    overflow-y: auto;
    min-height: 0;
}
.card-labels span {
    padding: 4px;
    color: #fff;
    border-radius: 5px;
    font-size: 12px;
}
</style>
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">


		<!--start page wrapper -->
<div class="issue-navbar">
  <div class="navbar-content">
    <div class="navbar-left">
      <div class="user-avatars">
        <img src="{{ asset('assets/images/avatars/avatar-1.png') }}" alt="User 1" class="avatar avatar-1">
        <img src="{{ asset('assets/images/avatars/avatar-2.png') }}" alt="User 2" class="avatar avatar-2">
        <img src="{{ asset('assets/images/avatars/avatar-3.png') }}" alt="User 3" class="avatar avatar-3">
        <div class="avatar add-avatar">
          <i class="bx bx-plus"></i>
        </div>
      </div>
      <button class="navbar-btn share-btn" data-bs-toggle="modal" data-bs-target="#shareModal">
        <i class="bx bx-share"></i> Share
      </button>
      <div class="dropdown">
        <button class="navbar-btn menu-btn" data-bs-toggle="dropdown">
          <i class="bx bx-dots-vertical-rounded"></i>
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="#"><i class="bx bx-edit"></i> Edit Issue</a></li>
          <li><a class="dropdown-item" href="#"><i class="bx bx-copy"></i> Duplicate</a></li>
          <li><a class="dropdown-item" href="#"><i class="bx bx-archive"></i> Archive</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="#"><i class="bx bx-trash"></i> Delete</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div class="kanban-container">
  <h1>Technofra</h1>
  <button id="add-column-btn">Add New Column</button>
  <div class="kanban-board mt-3" id="kanban-board">
    <div class="column" data-column-id="todo">
      <div class="column-header">
        <h3>To Do <span class="count">0</span></h3>
        <button class="add-task-btn">+</button>
      </div>
      <div class="task-list" id="todo-list"></div>
    </div>
    <div class="column" data-column-id="in-progress">
      <div class="column-header">
        <h3>In Progress <span class="count">0</span></h3>
        <button class="add-task-btn">+</button>
      </div>
      <div class="task-list" id="in-progress-list"></div>
    </div>
    <div class="column" data-column-id="review">
      <div class="column-header">
        <h3>Review <span class="count">0</span></h3>
        <button class="add-task-btn">+</button>
      </div>
      <div class="task-list" id="review-list"></div>
    </div>
    <div class="column" data-column-id="done">
      <div class="column-header">
        <h3>Done <span class="count">0</span></h3>
        <button class="add-task-btn">+</button>
      </div>
      <div class="task-list" id="done-list"></div>
    </div>
  </div>
  
</div>

<!-- Modal for Add/Edit Task -->
<div id="task-modal" class="modal">
  <div class="modal-overlay"></div>
  <div class="modal-container">
    <div class="modal-header">
      <input type="text" id="task-title" placeholder="Task title" class="task-title-input">
      <select id="assigned-list" class="assigned-list-dropdown">
        <option value="todo">To Do</option>
        <option value="in-progress">In Progress</option>
        <option value="review">Review</option>
        <option value="done">Done</option>
      </select>
      <button class="close-btn">&times;</button>
    </div>
    <div class="modal-body">
      <div class="left-section">
        <div class="section description-section">
          <textarea id="task-description" placeholder="Add a more detailed description…"></textarea>
        </div>
        <div class="section checklist-section">
          <div class="section-header">
            <h3>Checklist</h3>
            <button class="add-checklist-btn">Add</button>
          </div>
          <div id="checklists-container"></div>
        </div>
        <div class="section labels-section">
          <h3>Labels</h3>
          <div class="labels-list" id="labels-list"></div>
        </div>
        <div class="section dates-section">
          <h3>Dates</h3>
          <div class="date-fields">
            <label>Start date <input type="date" id="start-date"></label>
            <label>Due date <input type="date" id="due-date"></label>
            <label>Time <input type="time" id="due-time"></label>
            <select id="reminder">
              <option value="">No reminder</option>
              <option value="5m">5 minutes before</option>
              <option value="10m">10 minutes before</option>
              <option value="1h">1 hour before</option>
              <option value="1d">1 day before</option>
            </select>
          </div>
          <div class="date-actions">
            <button id="save-dates">Save</button>
            <button id="remove-dates">Remove</button>
          </div>
        </div>
        <div class="section attachments-section">
          <h3>Attachments</h3>
          <div class="attachment-actions">
            <input type="file" id="upload-file" multiple>
            <input type="url" placeholder="Paste link" id="paste-link">
          </div>
          <div id="attachments-list"></div>
        </div>
      </div>
      <div class="right-sidebar">
        <button class="sidebar-btn" data-popup="labels">Labels</button>
        <button class="sidebar-btn" data-popup="dates">Dates</button>
        <button class="sidebar-btn" data-popup="checklist">Checklist</button>
        <button class="sidebar-btn" data-popup="members">Members</button>
        <button class="sidebar-btn" data-popup="attachment">Attachment</button>
        <button class="sidebar-btn" data-popup="custom-fields">Custom Fields</button>
      </div>
    </div>
    <div class="comments-section">
      <h3>Comments</h3>
      <textarea placeholder="Write a comment…" id="comment-input"></textarea>
      <button id="add-comment-btn">Comment</button>
      <div id="activity-log"></div>
    </div>
  </div>

  <!-- Popups -->
  <div class="popup" id="labels-popup">
    <div class="popup-header">
      <h4>Labels</h4>
      <button class="popup-close">&times;</button>
    </div>
    <div class="popup-body">
      <div class="color-labels">
        <div class="label-option" data-color="green">Green</div>
        <div class="label-option" data-color="yellow">Yellow</div>
        <div class="label-option" data-color="orange">Orange</div>
        <div class="label-option" data-color="red">Red</div>
        <div class="label-option" data-color="purple">Purple</div>
        <div class="label-option" data-color="blue">Blue</div>
      </div>
      <div class="create-label-section">
        <input type="text" placeholder="Label name" id="create-label-input">
        <div class="color-picker">
          <div class="color-option selected" data-color="green"></div>
          <div class="color-option" data-color="yellow"></div>
          <div class="color-option" data-color="orange"></div>
          <div class="color-option" data-color="red"></div>
          <div class="color-option" data-color="purple"></div>
          <div class="color-option" data-color="blue"></div>
        </div>
        <button id="create-label-btn">Create</button>
      </div>
    </div>
  </div>

  <div class="popup" id="dates-popup">
    <div class="popup-header">
      <h4>Dates</h4>
      <button class="popup-close">&times;</button>
    </div>
    <div class="popup-body">
      <label>Start date <input type="date" id="popup-start-date"></label>
      <label>Due date <input type="date" id="popup-due-date"></label>
      <label>Time <input type="time" id="popup-due-time"></label>
      <select id="popup-reminder">
        <option value="">No reminder</option>
        <option value="5m">5 minutes before</option>
        <option value="10m">10 minutes before</option>
        <option value="1h">1 hour before</option>
        <option value="1d">1 day before</option>
      </select>
      <button id="popup-save-dates">Save</button>
      <button id="popup-remove-dates">Remove</button>
    </div>
  </div>

  <div class="popup" id="checklist-popup">
    <div class="popup-header">
      <h4>Checklist</h4>
      <button class="popup-close">&times;</button>
    </div>
    <div class="popup-body">
      <input type="text" placeholder="Checklist title" id="popup-checklist-title">
      <button id="popup-create-checklist">Add</button>
    </div>
  </div>

  <div class="popup" id="members-popup">
    <div class="popup-header">
      <h4>Members</h4>
      <button class="popup-close">&times;</button>
    </div>
    <div class="popup-body">
      <input type="text" placeholder="Search members" id="search-members">
      <div id="members-list">
        <!-- Members will be added here -->
      </div>
    </div>
  </div>

  <div class="popup" id="attachment-popup">
    <div class="popup-header">
      <h4>Attachment</h4>
      <button class="popup-close">&times;</button>
    </div>
    <div class="popup-body">
      <input type="file" id="popup-upload-file" multiple>
      <input type="url" placeholder="Paste link" id="popup-paste-link">
    </div>
  </div>

  <div class="popup" id="custom-fields-popup">
    <div class="popup-header">
      <h4>Custom Fields</h4>
      <button class="popup-close">&times;</button>
    </div>
    <div class="popup-body">
      <!-- Custom fields implementation -->
      <p>Custom fields not implemented yet.</p>
    </div>
  </div>
</div>

<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="shareModalLabel">Share board</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body1">
        <div class="row">
          <div class="col-12">
            <div class="share-input-section mb-4">
              <div class="input-group">
                <input type="text" class="form-control" placeholder="Email address or name" id="shareEmail">
                <button class="btn btn-outline-primary" type="button" id="memberBtn">Member</button>
                <button class="btn btn-primary" type="button" id="shareBtn">Share</button>
              </div>
            </div>

            <div class="share-link-section mb-4 p-3 border rounded">
              <h6 class="mb-2">Share this board with a link</h6>
              <button class="btn btn-outline-secondary btn-sm">Create link</button>
            </div>

            <div class="board-members-section">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">Board members</h6>
                <span class="badge bg-secondary">2</span>
              </div>

              <div class="join-requests mb-3">
                <p class="text-muted mb-2">Join requests</p>
              </div>

              <div class="members-list">
                <div class="member-item d-flex align-items-center justify-content-between p-3 border rounded mb-2">
                  <div class="d-flex align-items-center">
                    <img src="{{ asset('assets/images/avatars/technofra.png') }}" alt="Technofra" class="rounded-circle me-3" style="width: 48px; height: 48px;">
                    <div>
                      <div class="fw-bold">Technofra (you)</div>
                      <small class="text-muted">@technofra • Workspace admin</small>
                    </div>
                  </div>
                  <span class="badge bg-primary">Admin</span>
                </div>

                <div class="member-item d-flex align-items-center justify-content-between p-3 border rounded">
                  <div class="d-flex align-items-center">
                    <div class="bg-secondary rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                      <span class="text-white fw-bold">GG</span>
                    </div>
                    <div>
                      <div class="fw-bold">Gopal Giri</div>
                      <small class="text-muted">@gopalgiri4 • Workspace guest</small>
                    </div>
                  </div>
                  <span class="badge bg-secondary">Member</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
.issue-navbar {
  background: #fff;
  border-bottom: 1px solid #e9ecef;
  padding: 12px 20px;
  margin-bottom: 20px;
}

.navbar-content {
  display: flex;
  justify-content: flex-end;
  align-items: center;
}

.user-avatars {
  display: flex;
  align-items: center;
  margin-right: 12px;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: 2px solid #fff;
  object-fit: cover;
}

.avatar-1 {
  z-index: 3;
}

.avatar-2 {
  margin-left: -8px;
  z-index: 2;
}

.avatar-3 {
  margin-left: -8px;
  z-index: 1;
}

.add-avatar {
  margin-left: -8px;
  background: #0079bf;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 16px;
  border: 2px solid #fff;
}

.add-avatar:hover {
  background: #005a87;
}

.navbar-left {
  display: flex;
  gap: 12px;
}

.navbar-right {
  display: flex;
  align-items: center;
}

.navbar-btn {
  background: #0079bf;
  color: #fff;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  transition: background 0.3s ease;
}

.navbar-btn:hover {
  background: #005a87;
}

.menu-btn {
  background: transparent;
  color: #6c757d;
  padding: 8px;
}

.menu-btn:hover {
  background: #f8f9fa;
  color: #495057;
}

.dropdown-menu {
  min-width: 180px;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
}

.dropdown-item:hover {
  background: #f8f9fa;
}

.dropdown-item.text-danger:hover {
  background: #f8d7da;
  color: #721c24 !important;
}

.kanban-container {
  padding: 20px;
  font-family: Arial, sans-serif;
}

.kanban-board {
  display: flex;
  gap: 20px;
  overflow-x: auto;
  padding-bottom: 20px;
}

.column {
  background: #0079bf26;
  border-radius: 8px;
  width: 300px;
  min-height: 400px;
  padding: 10px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.column-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.column-header h3 {
  margin: 0;
  font-size: 16px;
  color: #333;
}

.count {
  background: #0079bf;
  color: white;
  padding: 2px 6px;
  border-radius: 12px;
  font-size: 12px;
}

.add-task-btn {
  background: #0079bf;
  color: white;
  border: none;
  border-radius: 50%;
  width: 30px;
  height: 30px;
  cursor: pointer;
  font-size: 18px;
}

.task-list {
  min-height: 300px;
}

.task-card {
  background: white;
  border-radius: 6px;
  padding: 10px;
  margin-bottom: 10px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
  cursor: grab;
  transition: transform 0.2s;
}

.task-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

.task-card h4 {
  margin: 0 0 5px 0;
  font-size: 14px;
}

.task-card p {
  margin: 0 0 5px 0;
  font-size: 12px;
  color: #666;
}

.task-card small {
  color: #999;
}

.card-labels {
  display: flex;
  gap: 4px;
  margin-top: 8px;
}

.card-actions {
  display: flex;
  justify-content: flex-end;
  gap: 5px;
  margin-top: 10px;
}

.edit-btn, .delete-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 12px;
  color: #0079bf;
}

.delete-btn {
  color: #d32f2f;
}

#add-column-btn {
  background: #0079bf;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 20px;
}

/* Modal */
.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
}

.modal-overlay {
  position: absolute;
  width: 100%;
  height: 100%;
}

.modal-container {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: #1e1e1e;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.5);
  width: 90%;
  max-width: 800px;
  max-height: 85vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  animation: modalFadeIn 0.3s ease-out;
}

@keyframes modalFadeIn {
  from { opacity: 0; transform: translate(-50%, -60%); }
  to { opacity: 1; transform: translate(-50%, -50%); }
}

.modal-header {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid #333;
}

.task-title-input {
  flex: 1;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  color: #fff;
  font-size: 18px;
  font-weight: 600;
  outline: none;
  padding: 8px 12px;
}

.task-title-input:focus {
  border-color: #0079bf;
  background: rgba(255, 255, 255, 0.15);
}

.assigned-list-dropdown {
  background: #333;
  color: #fff;
  border: none;
  padding: 4px 8px;
  border-radius: 4px;
  margin-left: 10px;
}

.close-btn {
  background: none;
  border: none;
  color: #aaa;
  font-size: 24px;
  cursor: pointer;
  margin-left: 10px;
}

.close-btn:hover {
  color: #fff;
}

.modal-body {
  display: flex;
  padding: 12px;
  flex: 1;
  overflow-y: auto;
  min-height: 0;
}

.left-section {
  flex: 1;
  margin-right: 20px;
}

.section {
  margin-bottom: 16px;
}

.section h3 {
  margin: 0 0 8px 0;
  color: #fff;
  font-size: 14px;
  font-weight: 600;
}

#task-description {
  width: 100%;
  min-height: 60px;
  background: #2a2a2a;
  border: 1px solid #444;
  border-radius: 6px;
  color: #fff;
  padding: 8px;
  resize: vertical;
  outline: none;
}

#task-description:focus {
  border-color: #0079bf;
}

.checklist-section .section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.add-checklist-btn {
  background: #0079bf;
  color: #fff;
  border: none;
  padding: 4px 8px;
  border-radius: 4px;
  cursor: pointer;
}

.labels-list {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.label-item {
  position: relative;
  padding: 4px 20px 4px 8px;
  border-radius: 12px;
  color: #fff;
  font-size: 12px;
  display: inline-flex;
  align-items: center;
}

.remove-label-btn {
  position: absolute;
  right: 4px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #fff;
  font-size: 14px;
  cursor: pointer;
  padding: 0;
  width: 12px;
  height: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  opacity: 0.7;
}

.remove-label-btn:hover {
  opacity: 1;
  background: rgba(255, 255, 255, 0.2);
}

.date-fields {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.date-fields label {
  display: flex;
  align-items: center;
  color: #ccc;
  font-size: 14px;
}

.date-fields input, .date-fields select {
  margin-left: 8px;
  background: #2a2a2a;
  border: 1px solid #444;
  border-radius: 4px;
  color: #fff;
  padding: 4px;
}

.date-actions {
  margin-top: 8px;
}

.date-actions button {
  background: #0079bf;
  color: #fff;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  margin-right: 8px;
}

.attachment-actions {
  display: flex;
  gap: 8px;
  margin-bottom: 8px;
}

.attachment-actions input {
  background: #2a2a2a;
  border: 1px solid #444;
  border-radius: 4px;
  color: #fff;
  padding: 4px;
}

.right-sidebar {
  width: 150px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.sidebar-btn {
  background: #333;
  color: #fff;
  border: none;
  padding: 8px;
  border-radius: 6px;
  cursor: pointer;
  text-align: left;
  font-size: 14px;
}

.sidebar-btn:hover {
  background: #444;
}

.comments-section {
  padding: 16px;
  border-top: 1px solid #333;
}

.comments-section h3 {
  margin: 0 0 8px 0;
  color: #fff;
}

#comment-input {
  width: 100%;
  min-height: 60px;
  background: #2a2a2a;
  border: 1px solid #444;
  border-radius: 6px;
  color: #fff;
  padding: 8px;
  resize: vertical;
  outline: none;
}

#comment-input:focus {
  border-color: #0079bf;
}

#add-comment-btn {
  background: #0079bf;
  color: #fff;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  margin-top: 8px;
}

#activity-log {
  margin-top: 16px;
}

.comment {
  background: #2a2a2a;
  padding: 8px;
  border-radius: 6px;
  margin-bottom: 8px;
  color: #ccc;
}

.comment .timestamp {
  font-size: 12px;
  color: #888;
}

/* Popups */
.popup {
  display: none;
  position: absolute;
  background: #1e1e1e;
  border: 1px solid #333;
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.5);
  z-index: 1100;
  width: 300px;
}

.popup-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 16px;
  border-bottom: 1px solid #333;
}

.popup-header h4 {
  margin: 0;
  color: #fff;
  font-size: 16px;
}

.popup-close {
  background: none;
  border: none;
  color: #aaa;
  font-size: 20px;
  cursor: pointer;
}

.popup-close:hover {
  color: #fff;
}

.popup-body {
  padding: 16px;
}

.popup-body input[type="text"],
.popup-body input[type="date"],
.popup-body input[type="time"],
.popup-body select {
  width: 100%;
  background: #2a2a2a;
  border: 1px solid #444;
  border-radius: 4px;
  color: #fff;
  padding: 8px;
  margin-bottom: 8px;
  outline: none;
}

.popup-body input[type="text"]:focus,
.popup-body input[type="date"]:focus,
.popup-body input[type="time"]:focus,
.popup-body select:focus {
  border-color: #0079bf;
}

.popup-body button {
  background: #0079bf;
  color: #fff;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.popup-body button:hover {
  background: #005a87;
}

.color-labels {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 8px;
  margin-bottom: 16px;
}

.label-option {
  padding: 8px;
  border-radius: 4px;
  color: #fff;
  text-align: center;
  cursor: pointer;
  font-size: 14px;
}

.label-option[data-color="green"] { background: #61bd4f; }
.label-option[data-color="yellow"] { background: #f2d600; }
.label-option[data-color="orange"] { background: #ff9f43; }
.label-option[data-color="red"] { background: #eb5a46; }
.label-option[data-color="purple"] { background: #c377e0; }
.label-option[data-color="blue"] { background: #0079bf; }

#create-label-input {
  width: 100%;
  background: #2a2a2a;
  border: 1px solid #444;
  border-radius: 4px;
  color: #fff;
  padding: 8px;
  margin-bottom: 8px;
}

.create-label-section {
  margin-top: 16px;
}

.color-picker {
  display: flex;
  gap: 8px;
  margin-bottom: 8px;
}

.color-option {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  cursor: pointer;
  border: 2px solid transparent;
}

.color-option.selected {
  border-color: #fff;
}

.color-option[data-color="green"] { background: #61bd4f; }
.color-option[data-color="yellow"] { background: #f2d600; }
.color-option[data-color="orange"] { background: #ff9f43; }
.color-option[data-color="red"] { background: #eb5a46; }
.color-option[data-color="purple"] { background: #c377e0; }
.color-option[data-color="blue"] { background: #0079bf; }

#create-label-btn {
  background: #0079bf;
  color: #fff;
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
}

.member-item {
  display: flex;
  align-items: center;
  padding: 8px;
  cursor: pointer;
  border-radius: 4px;
}

.member-item:hover {
  background: #333;
}

.member-item.assigned {
  background: #2a4d69;
}

.member-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #0079bf;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  margin-right: 8px;
}

.assign-btn {
  margin-left: auto;
  background: #0079bf;
  color: #fff;
  border: none;
  padding: 4px 8px;
  border-radius: 4px;
  cursor: pointer;
}

/* Responsive */
@media (max-width: 768px) {
  .kanban-board {
    flex-direction: column;
  }
  .column {
    width: 100%;
  }
  .modal-container {
    width: 95%;
    max-height: 95vh;
  }
  .modal-body {
    flex-direction: column;
  }
  .left-section {
    margin-right: 0;
    margin-bottom: 20px;
  }
  .right-sidebar {
    width: 100%;
    flex-direction: row;
    flex-wrap: wrap;
  }
  .sidebar-btn {
    flex: 1;
    text-align: center;
  }
}

/* Hide scrollbars */
.modal-body::-webkit-scrollbar,
.modal-container::-webkit-scrollbar {
  display: none;
}

.modal-body,
.modal-container {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

/* Responsive */
@media (max-width: 768px) {
  .kanban-board {
    flex-direction: column;
  }
  .column {
    width: 100%;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const board = document.getElementById('kanban-board');
  const addColumnBtn = document.getElementById('add-column-btn');
  const modal = document.getElementById('task-modal');
  const taskTitle = document.getElementById('task-title');
  const assignedList = document.getElementById('assigned-list');
  const closeBtn = document.querySelector('.close-btn');
  const taskDescription = document.getElementById('task-description');
  const checklistsContainer = document.getElementById('checklists-container');
  const labelsList = document.getElementById('labels-list');
  const startDate = document.getElementById('start-date');
  const dueDate = document.getElementById('due-date');
  const dueTime = document.getElementById('due-time');
  const reminder = document.getElementById('reminder');
  const saveDates = document.getElementById('save-dates');
  const removeDates = document.getElementById('remove-dates');
  const attachmentsList = document.getElementById('attachments-list');
  const commentInput = document.getElementById('comment-input');
  const addCommentBtn = document.getElementById('add-comment-btn');
  const activityLog = document.getElementById('activity-log');

  let tasks = JSON.parse(localStorage.getItem('kanban-tasks')) || {};
  let columns = JSON.parse(localStorage.getItem('kanban-columns')) || ['todo', 'in-progress', 'review', 'done'];
  let editingTask = null;
  let currentColumn = null;
  let currentTask = null;
  const members = [
    { id: 1, name: 'Alice Johnson', initials: 'AJ' },
    { id: 2, name: 'Bob Smith', initials: 'BS' },
    { id: 3, name: 'Charlie Brown', initials: 'CB' },
    { id: 4, name: 'Diana Prince', initials: 'DP' }
  ];

  // Initialize
  renderBoard();

  // Add column
  addColumnBtn.addEventListener('click', function() {
    const columnName = prompt('Enter column name:');
    if (columnName) {
      const columnId = columnName.toLowerCase().replace(/\s+/g, '-');
      columns.push(columnId);
      tasks[columnId] = [];
      renderBoard();
      saveData();
    }
  });

  // Add task buttons
  board.addEventListener('click', function(e) {
    if (e.target.classList.contains('add-task-btn')) {
      currentColumn = e.target.closest('.column').dataset.columnId;
      resetModal();
      assignedList.value = currentColumn;
      modal.style.display = 'block';
    }
  });

  // Edit task
  board.addEventListener('click', function(e) {
    if (e.target.closest('.task-card')) {
      const card = e.target.closest('.task-card');
      const taskId = card.dataset.taskId;
      const columnId = card.closest('.column').dataset.columnId;
      editingTask = { taskId, columnId };
      const task = tasks[columnId].find(t => t.id == taskId);
      loadTaskIntoModal(task);
      modal.style.display = 'block';
    }
  });

  // Modal close
  closeBtn.addEventListener('click', () => modal.style.display = 'none');
  modal.addEventListener('click', (e) => {
    if (e.target === modal || e.target.classList.contains('modal-overlay')) {
      modal.style.display = 'none';
    }
  });

  // Save task
  taskTitle.addEventListener('blur', saveTask);
  assignedList.addEventListener('change', saveTask);
  taskDescription.addEventListener('blur', saveTask);

  // Checklists
  document.querySelector('.add-checklist-btn').addEventListener('click', () => {
    const title = prompt('Checklist title:');
    if (title) {
      addChecklist(title);
      saveTask();
    }
  });

  // Labels popup
  document.querySelector('[data-popup="labels"]').addEventListener('click', (e) => {
    togglePopup('labels-popup', e.target);
  });

  document.querySelectorAll('.label-option').forEach(option => {
    option.addEventListener('click', () => {
      addLabel(option.dataset.color);
      saveTask();
      closePopups();
    });
  });

  let selectedColor = 'green';
  document.querySelectorAll('.color-option').forEach(option => {
    option.addEventListener('click', () => {
      document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('selected'));
      option.classList.add('selected');
      selectedColor = option.dataset.color;
    });
  });

  document.getElementById('create-label-btn').addEventListener('click', () => {
    const name = document.getElementById('create-label-input').value.trim();
    if (name) {
      addLabel(selectedColor, name);
      document.getElementById('create-label-input').value = '';
      saveTask();
      closePopups();
    }
  });

  // Members popup
  document.querySelector('[data-popup="members"]').addEventListener('click', (e) => {
    renderMembersPopup();
    togglePopup('members-popup', e.target);
  });

  document.getElementById('search-members').addEventListener('input', (e) => {
    renderMembersPopup(e.target.value);
  });

  // Dates popup
  document.querySelector('[data-popup="dates"]').addEventListener('click', (e) => {
    document.getElementById('popup-start-date').value = startDate.value;
    document.getElementById('popup-due-date').value = dueDate.value;
    document.getElementById('popup-due-time').value = dueTime.value;
    document.getElementById('popup-reminder').value = reminder.value;
    togglePopup('dates-popup', e.target);
  });

  document.getElementById('popup-save-dates').addEventListener('click', () => {
    startDate.value = document.getElementById('popup-start-date').value;
    dueDate.value = document.getElementById('popup-due-date').value;
    dueTime.value = document.getElementById('popup-due-time').value;
    reminder.value = document.getElementById('popup-reminder').value;
    saveTask();
    closePopups();
  });

  document.getElementById('popup-remove-dates').addEventListener('click', () => {
    startDate.value = '';
    dueDate.value = '';
    dueTime.value = '';
    reminder.value = '';
    saveTask();
    closePopups();
  });

  // Checklist popup
  document.querySelector('[data-popup="checklist"]').addEventListener('click', (e) => {
    togglePopup('checklist-popup', e.target);
  });

  document.getElementById('popup-create-checklist').addEventListener('click', () => {
    const title = document.getElementById('popup-checklist-title').value.trim();
    if (title) {
      addChecklist(title);
      saveTask();
      closePopups();
    }
  });

  // Attachment popup
  document.querySelector('[data-popup="attachment"]').addEventListener('click', (e) => {
    togglePopup('attachment-popup', e.target);
  });

  // Custom fields popup
  document.querySelector('[data-popup="custom-fields"]').addEventListener('click', (e) => {
    togglePopup('custom-fields-popup', e.target);
  });

  // Dates
  saveDates.addEventListener('click', () => {
    saveTask();
  });

  removeDates.addEventListener('click', () => {
    startDate.value = '';
    dueDate.value = '';
    dueTime.value = '';
    reminder.value = '';
    saveTask();
  });

  // Attachments
  document.getElementById('upload-file').addEventListener('change', (e) => {
    Array.from(e.target.files).forEach(file => {
      addAttachment(file.name, 'file');
    });
    saveTask();
  });

  document.getElementById('paste-link').addEventListener('change', (e) => {
    if (e.target.value) {
      addAttachment(e.target.value, 'link');
      e.target.value = '';
      saveTask();
    }
  });

  // Comments
  addCommentBtn.addEventListener('click', () => {
    const text = commentInput.value.trim();
    if (text) {
      addComment(text);
      commentInput.value = '';
      saveTask();
    }
  });

  // Popup close
  document.querySelectorAll('.popup-close').forEach(btn => {
    btn.addEventListener('click', closePopups);
  });

  // Drag and Drop
  let draggedElement = null;

  board.addEventListener('dragstart', function(e) {
    if (e.target.classList.contains('task-card')) {
      draggedElement = e.target;
      e.dataTransfer.effectAllowed = 'move';
    }
  });

  board.addEventListener('dragover', function(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
  });

  board.addEventListener('drop', function(e) {
    e.preventDefault();
    if (draggedElement && e.target.classList.contains('task-list')) {
      const fromColumn = draggedElement.closest('.column').dataset.columnId;
      const toColumn = e.target.closest('.column').dataset.columnId;
      const taskId = draggedElement.dataset.taskId;
      const task = tasks[fromColumn].find(t => t.id == taskId);
      tasks[fromColumn] = tasks[fromColumn].filter(t => t.id != taskId);
      if (!tasks[toColumn]) tasks[toColumn] = [];
      tasks[toColumn].push(task);
      renderBoard();
      saveData();
    }
  });

  function resetModal() {
    taskTitle.value = '';
    taskDescription.value = '';
    checklistsContainer.innerHTML = '';
    labelsList.innerHTML = '';
    startDate.value = '';
    dueDate.value = '';
    dueTime.value = '';
    reminder.value = '';
    attachmentsList.innerHTML = '';
    activityLog.innerHTML = '';
    editingTask = null;
    currentTask = {
      id: Date.now(),
      title: '',
      description: '',
      checklists: [],
      labels: [],
      startDate: '',
      dueDate: '',
      dueTime: '',
      reminder: '',
      attachments: [],
      comments: [],
      members: []
    };
  }

  function loadTaskIntoModal(task) {
    currentTask = task;
    taskTitle.value = task.title || '';
    assignedList.value = editingTask.columnId;
    taskDescription.value = task.description || '';
    renderChecklists(task.checklists || []);
    renderLabels(task.labels || []);
    startDate.value = task.startDate || '';
    dueDate.value = task.dueDate || '';
    dueTime.value = task.dueTime || '';
    reminder.value = task.reminder || '';
    renderAttachments(task.attachments || []);
    renderComments(task.comments || []);
  }

  function saveTask() {
    if (!currentTask) return;
    currentTask.title = taskTitle.value;
    currentTask.description = taskDescription.value;
    currentTask.startDate = startDate.value;
    currentTask.dueDate = dueDate.value;
    currentTask.dueTime = dueTime.value;
    currentTask.reminder = reminder.value;
    // checklists, labels, attachments, comments are updated in place

    if (editingTask) {
      const index = tasks[editingTask.columnId].findIndex(t => t.id == editingTask.taskId);
      tasks[editingTask.columnId][index] = currentTask;
    } else {
      if (!tasks[currentColumn]) tasks[currentColumn] = [];
      tasks[currentColumn].push(currentTask);
    }
    renderBoard();
    saveData();
  }

  function addChecklist(title) {
    const checklist = { id: Date.now(), title, items: [] };
    currentTask.checklists.push(checklist);
    renderChecklists(currentTask.checklists);
  }

  function renderChecklists(checklists) {
    checklistsContainer.innerHTML = checklists.map(checklist => `
      <div class="checklist" data-id="${checklist.id}">
        <h4>${checklist.title} (${checklist.items.filter(i => i.checked).length}/${checklist.items.length})</h4>
        <div class="checklist-items">
          ${checklist.items.map(item => `
            <div class="checklist-item">
              <input type="checkbox" ${item.checked ? 'checked' : ''} data-id="${item.id}">
              <span>${item.text}</span>
            </div>
          `).join('')}
        </div>
        <input type="text" placeholder="Add item" class="add-item-input">
      </div>
    `).join('');

    // Add event listeners for new inputs
    document.querySelectorAll('.add-item-input').forEach(input => {
      input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
          const text = input.value.trim();
          if (text) {
            const checklistId = input.closest('.checklist').dataset.id;
            const checklist = currentTask.checklists.find(c => c.id == checklistId);
            checklist.items.push({ id: Date.now(), text, checked: false });
            renderChecklists(currentTask.checklists);
            saveTask();
          }
        }
      });
    });

    document.querySelectorAll('.checklist-item input[type="checkbox"]').forEach(cb => {
      cb.addEventListener('change', () => {
        const itemId = cb.dataset.id;
        const checklistId = cb.closest('.checklist').dataset.id;
        const checklist = currentTask.checklists.find(c => c.id == checklistId);
        const item = checklist.items.find(i => i.id == itemId);
        item.checked = cb.checked;
        renderChecklists(currentTask.checklists);
        saveTask();
      });
    });
  }

  function addLabel(color, name = '') {
    const label = { color, name };
    currentTask.labels.push(label);
    renderLabels(currentTask.labels);
  }

  function renderLabels(labels) {
    labelsList.innerHTML = labels.map((label, index) => `
      <div class="label-item" style="background: ${getColor(label.color)}">
        <span>${label.name || label.color}</span>
        <button class="remove-label-btn" data-index="${index}">&times;</button>
      </div>
    `).join('');

    // Add event listeners for remove buttons
    document.querySelectorAll('.remove-label-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const index = parseInt(e.target.dataset.index);
        currentTask.labels.splice(index, 1);
        renderLabels(currentTask.labels);
        saveTask();
      });
    });
  }

  function getColor(color) {
    const colors = {
      green: '#61bd4f',
      yellow: '#f2d600',
      orange: '#ff9f43',
      red: '#eb5a46',
      purple: '#c377e0',
      blue: '#0079bf'
    };
    return colors[color] || color;
  }

  function addAttachment(name, type) {
    const attachment = { id: Date.now(), name, type };
    currentTask.attachments.push(attachment);
    renderAttachments(currentTask.attachments);
  }

  function renderAttachments(attachments) {
    attachmentsList.innerHTML = attachments.map(att => `
      <div class="attachment">${att.name} (${att.type})</div>
    `).join('');
  }

  function addComment(text) {
    const comment = { id: Date.now(), text, timestamp: new Date().toLocaleString() };
    currentTask.comments.push(comment);
    renderComments(currentTask.comments);
  }

  function renderComments(comments) {
    activityLog.innerHTML = comments.map(comment => `
      <div class="comment">
        ${comment.text}
        <div class="timestamp">${comment.timestamp}</div>
      </div>
    `).join('');
  }

  function renderMembersPopup(search = '') {
    const filteredMembers = members.filter(m => m.name.toLowerCase().includes(search.toLowerCase()));
    const membersList = document.getElementById('members-list');
    membersList.innerHTML = filteredMembers.map(member => {
      const isAssigned = currentTask.members.some(m => m.id === member.id);
      return `
        <div class="member-item ${isAssigned ? 'assigned' : ''}" data-id="${member.id}">
          <div class="member-avatar">${member.initials}</div>
          <span>${member.name}</span>
          <button class="assign-btn">${isAssigned ? 'Unassign' : 'Assign'}</button>
        </div>
      `;
    }).join('');

    document.querySelectorAll('.assign-btn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const memberId = parseInt(e.target.closest('.member-item').dataset.id);
        const member = members.find(m => m.id === memberId);
        if (currentTask.members.some(m => m.id === memberId)) {
          currentTask.members = currentTask.members.filter(m => m.id !== memberId);
        } else {
          currentTask.members.push(member);
        }
        renderMembersPopup(search);
        saveTask();
      });
    });
  }

  function togglePopup(popupId, button) {
    closePopups();
    const popup = document.getElementById(popupId);
    popup.style.display = 'block';
    const rect = button.getBoundingClientRect();
    popup.style.left = rect.left + 'px';
    popup.style.top = rect.bottom + 'px';
  }

  function closePopups() {
    document.querySelectorAll('.popup').forEach(p => p.style.display = 'none');
  }

  function renderBoard() {
    board.innerHTML = '';
    columns.forEach(columnId => {
      if (!tasks[columnId]) tasks[columnId] = [];
      const column = document.createElement('div');
      column.className = 'column';
      column.dataset.columnId = columnId;
      column.innerHTML = `
        <div class="column-header">
          <h3>${columnId.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase())} <span class="count">${tasks[columnId].length}</span></h3>
          <button class="add-task-btn">+</button>
        </div>
        <div class="task-list" id="${columnId}-list">
          ${tasks[columnId].map(task => `
            <div class="task-card" data-task-id="${task.id}" draggable="true">
              <h4>${task.title || 'Untitled'}</h4>
              <p>${task.description || ''}</p>
              ${task.labels && task.labels.length ? `<div class="card-labels">${task.labels.map(l => `<span class="label" style="background: ${getColor(l.color)}">${l.name || l.color}</span>`).join('')}</div>` : ''}
              ${task.dueDate ? `<small>Due: ${task.dueDate}</small>` : ''}
            </div>
          `).join('')}
        </div>
      `;
      board.appendChild(column);
    });
  }

  function saveData() {
    localStorage.setItem('kanban-tasks', JSON.stringify(tasks));
    localStorage.setItem('kanban-columns', JSON.stringify(columns));
  }
});
</script>
<!--end page wrapper -->


	</div>
</div>
<!--end page wrapper -->
@endsection