@extends('layout.master')

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="breadcrumb-title pe-3">Leads</div>
                <div class="ps-3">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0 p-0">
                            <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                            </li>
                            <li class="breadcrumb-item"><a href="#">Leads</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Add Lead</li>
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
                    <h5 class="card-title">Add New Lead</h5>
                    <hr />
                    <div class="form-body mt-4">
                        <form>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="border border-3 p-4 rounded">
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="source" class="form-label">Source</label>
                                            <select class="form-select" id="source" name="source">
                                                <option>Select Source</option>
                                                <option value="website">Website</option>
                                                <option value="referral">Referral</option>
                                                <option value="social_media">Social Media</option>
                                            </select>
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="assigned" class="form-label">Assigned</label>
                                            <select class="form-select" id="assigned" name="assigned">
                                                <option>Select Assigned</option>
                                                <option value="alice">Alice Smith</option>
                                                <option value="bob">Bob Johnson</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="tags" class="form-label">Tags</label>
                                        <input type="text" class="form-control" id="tags" name="tags" placeholder="Enter tags (comma separated)">
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="name" class="form-label">Name</label>
                                            <input type="text" class="form-control" id="name" name="name" placeholder="Enter name">
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="address" class="form-label">Address</label>
                                            <input type="text" class="form-control" id="address" name="address" placeholder="Enter address">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="position" class="form-label">Position</label>
                                            <input type="text" class="form-control" id="position" name="position" placeholder="Enter position">
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="city" class="form-label">City</label>
                                            <input type="text" class="form-control" id="city" name="city" placeholder="Enter city">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address">
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="state" class="form-label">State</label>
                                            <input type="text" class="form-control" id="state" name="state" placeholder="Enter state">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="website" class="form-label">Website</label>
                                            <input type="url" class="form-control" id="website" name="website" placeholder="Enter website">
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="country" class="form-label">Country</label>
                                            <select class="form-select" id="country" name="country">
                                                <option>Select Country</option>
                                                <option value="us">United States</option>
                                                <option value="in">India</option>
                                                <option value="uk">United Kingdom</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="phone" class="form-label">Phone</label>
                                            <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter phone">
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="zipCode" class="form-label">Zip Code</label>
                                            <input type="text" class="form-control" id="zipCode" name="zipCode" placeholder="Enter zip code">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6 mb-3">
                                            <label for="leadValue" class="form-label">Lead Value</label>
                                            <input type="number" class="form-control" id="leadValue" name="leadValue" placeholder="Enter lead value">
                                        </div>
                                        <div class="col-6 mb-3">
                                            <label for="company" class="form-label">Company</label>
                                            <input type="text" class="form-control" id="company" name="company" placeholder="Enter company">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter description"></textarea>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">Add Lead</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div><!--end row-->
                        </form>
                    </div>
                </div>
            </div>


        </div>
    </div>
    <!--end page wrapper -->
@endsection