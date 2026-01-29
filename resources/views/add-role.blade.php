@extends('layout.master')

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Roles</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Add Role</li>
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
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end"> <a class="dropdown-item"
                                href="javascript:;">Action</a>
                            <a class="dropdown-item" href="javascript:;">Another action</a>
                            <a class="dropdown-item" href="javascript:;">Something else here</a>
                            <div class="dropdown-divider"></div> <a class="dropdown-item" href="javascript:;">Separated
                                link</a>
                        </div>
                    </div>
                </div>
            </div>
            <!--end breadcrumb-->

            <div class="card">
                <div class="card-body p-4">
                    <h5 class="card-title">Add New Role</h5>
                    <hr />
                    <div class="form-body mt-4">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="border border-3 p-4 rounded">
                                    <div class="mb-3">
                                        <label for="roleName" class="form-label">Role Name</label>
                                        <input type="text" class="form-control" id="roleName" placeholder="Enter role name">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Permissions</label>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Features</th>
                                                    <th>Capabilities</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Bulk PDF Export</td>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="bulkPdfExport_viewGlobal">
                                                            <label class="form-check-label" for="bulkPdfExport_viewGlobal">View (Global)</label>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Customers</td>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="customers_viewOwn">
                                                            <label class="form-check-label" for="customers_viewOwn">View (Own)</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="customers_viewGlobal">
                                                            <label class="form-check-label" for="customers_viewGlobal">View (Global)</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="customers_create">
                                                            <label class="form-check-label" for="customers_create">Create</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="customers_edit">
                                                            <label class="form-check-label" for="customers_edit">Edit</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="customers_delete">
                                                            <label class="form-check-label" for="customers_delete">Delete</label>
                                                        </div>
                                                    </td>
                                                </tr>
                                               
                                                <tr>
                                                    <td>Projects</td>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="projects_viewOwn">
                                                            <label class="form-check-label" for="projects_viewOwn">View (Own)</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="projects_viewGlobal">
                                                            <label class="form-check-label" for="projects_viewGlobal">View (Global)</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="projects_create">
                                                            <label class="form-check-label" for="projects_create">Create</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="projects_edit">
                                                            <label class="form-check-label" for="projects_edit">Edit</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="projects_delete">
                                                            <label class="form-check-label" for="projects_delete">Delete</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="projects_createTimesheets">
                                                            <label class="form-check-label" for="projects_createTimesheets">Create Timesheets</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="projects_editMilestones">
                                                            <label class="form-check-label" for="projects_editMilestones">Edit Milestones</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="projects_deleteMilestones">
                                                            <label class="form-check-label" for="projects_deleteMilestones">Delete Milestones</label>
                                                        </div>
                                                    </td>
                                                </tr>
                                               
                                                <tr>
                                                    <td>Reports</td>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="reports_viewGlobal">
                                                            <label class="form-check-label" for="reports_viewGlobal">View (Global)</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="reports_viewTimesheetsReport">
                                                            <label class="form-check-label" for="reports_viewTimesheetsReport">View Timesheets Report</label>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Staff Roles</td>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="staffRoles_viewGlobal">
                                                            <label class="form-check-label" for="staffRoles_viewGlobal">View (Global)</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="staffRoles_create">
                                                            <label class="form-check-label" for="staffRoles_create">Create</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="staffRoles_edit">
                                                            <label class="form-check-label" for="staffRoles_edit">Edit</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="staffRoles_delete">
                                                            <label class="form-check-label" for="staffRoles_delete">Delete</label>
                                                        </div>
                                                    </td>
                                                </tr>
                                               
                                                <tr>
                                                    <td>Staff</td>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="staff_viewGlobal">
                                                            <label class="form-check-label" for="staff_viewGlobal">View (Global)</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="staff_create">
                                                            <label class="form-check-label" for="staff_create">Create</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="staff_edit">
                                                            <label class="form-check-label" for="staff_edit">Edit</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="staff_delete">
                                                            <label class="form-check-label" for="staff_delete">Delete</label>
                                                        </div>
                                                    </td>
                                                </tr>
                                                
                                                <tr>
                                                    <td>Tasks</td>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="tasks_viewOwn">
                                                            <label class="form-check-label" for="tasks_viewOwn">View (Own)</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="tasks_viewGlobal">
                                                            <label class="form-check-label" for="tasks_viewGlobal">View (Global)</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="tasks_create">
                                                            <label class="form-check-label" for="tasks_create">Create</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="tasks_edit">
                                                            <label class="form-check-label" for="tasks_edit">Edit</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="tasks_delete">
                                                            <label class="form-check-label" for="tasks_delete">Delete</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="tasks_editTimesheetsGlobal">
                                                            <label class="form-check-label" for="tasks_editTimesheetsGlobal">Edit Timesheets (Global)</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="tasks_editOwnTimesheets">
                                                            <label class="form-check-label" for="tasks_editOwnTimesheets">Edit Own Timesheets</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="tasks_deleteTimesheetsGlobal">
                                                            <label class="form-check-label" for="tasks_deleteTimesheetsGlobal">Delete Timesheets (Global)</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="tasks_deleteOwnTimesheets">
                                                            <label class="form-check-label" for="tasks_deleteOwnTimesheets">Delete Own Timesheets</label>
                                                        </div>
                                                    </td>
                                                </tr>
                                                
                                                
                                                <tr>
                                                    <td>Leads</td>
                                                    <td>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="leads_viewGlobal">
                                                            <label class="form-check-label" for="leads_viewGlobal">View (Global)</label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="leads_delete">
                                                            <label class="form-check-label" for="leads_delete">Delete</label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mb-3">
                                        <label for="roleName" class="form-label">Status</label>
                                        <select class="form-select" id="roleName">
                                            <option>Select Status</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>   
                                    </div>
                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button type="button" class="btn btn-primary">Add Role</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!--end row-->
                    </div>
                </div>
            </div>


        </div>
    </div>
    <!--end page wrapper -->
@endsection