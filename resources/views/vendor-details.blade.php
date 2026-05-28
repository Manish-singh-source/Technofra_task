@extends('/layout/master')
@section('content')
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
						<li class="breadcrumb-item active" aria-current="page">Vendor details</li>
					</ol>
				</nav>
			</div>
			
		</div>
		<!--end breadcrumb-->
		<div class="card">
			<div class="card-header d-flex justify-content-between align-items-center">
				<h5 class="mb-0">Vendor Details</h5>
				<div>
					<a href="{{ route('vendors.edit', $vendor->id) }}" class="btn btn-primary btn-sm">
						<i class="bx bx-edit"></i> Edit
					</a>
					<a href="{{ route('vendors.index') }}" class="btn btn-secondary btn-sm">
						<i class="bx bx-arrow-back"></i> Back to List
					</a>
				</div>
			</div>
			<div class="card-body">
				<ul class="list-group">
					<li class="list-group-item d-flex justify-content-between">
						<b>Vendor Name:</b>
						<p class="mb-0">{{ ucwords($vendor->name) }}</p>
					</li>
					<li class="list-group-item d-flex justify-content-between">
						<b>Email ID:</b>
						<p class="mb-0">{{ $vendor->email ?? 'N/A' }}</p>
					</li>
					<li class="list-group-item d-flex justify-content-between">
						<b>Contact No:</b>
						<p class="mb-0">{{ $vendor->phone ?? 'N/A' }}</p>
					</li>
					<li class="list-group-item d-flex justify-content-between">
						<b>Status:</b>
						<p class="mb-0">{{ $vendor->status == 1 ? 'Active' : 'Inactive' }}</p>
					</li>
					@if($vendor->address)
						<li class="list-group-item d-flex justify-content-between">
							<b>Address:</b>
							<p class="mb-0">{{ $vendor->address }}</p>
						</li>
					@endif
					<li class="list-group-item d-flex justify-content-between">
						<b>Created At:</b>
						<p class="mb-0">{{ $vendor->created_at->format('d M Y, h:i A') }}</p>
					</li>
					<li class="list-group-item d-flex justify-content-between">
						<b>Last Updated:</b>
						<p class="mb-0">{{ $vendor->updated_at->format('d M Y, h:i A') }}</p>
					</li>
				</ul>
			</div>
		</div>

	</div>
</div>
<div class="overlay toggle-icon"></div>
<a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
@endsection	