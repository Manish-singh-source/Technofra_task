@extends('/layout/master')
@section('content')
<div class="page-wrapper">
	<div class="page-content vendor-service-show">
		<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
			<div class="ps-3">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb mb-0 p-0">
						<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
						<li class="breadcrumb-item"><a href="{{ route('vendor-services.index') }}">Vendor Renewal Services</a></li>
						<li class="breadcrumb-item active" aria-current="page">Vendor Service Details</li>
					</ol>
				</nav>
			</div>
		</div>

		<div class="container">
			<div class="main-body">
				<div class="row g-3">
					<div class="col-12">
						<div class="card border-0 shadow-sm hero-card">
							<div class="card-body p-4 p-lg-5">
								<div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
									<div class="flex-grow-1">
										<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
											<span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-3 py-2">
												Vendor Service
											</span>
											<span class="badge rounded-pill bg-{{ $service->status_badge }} px-3 py-2">
												{{ ucfirst($service->status) }}
											</span>
										</div>
										<h3 class="mb-2 fw-semibold">{{ $service->service_name }}</h3>
										<div class="text-muted mb-4">
											Track the service timeline, billing window, and vendor information from one clean detail view.
										</div>

										<div class="row g-3 summary-grid">
											<div class="col-sm-6 col-xl-3">
												<div class="summary-tile">
													<span class="summary-label">Vendor</span>
													<span class="summary-value">{{ $service->vendor->name ?? 'N/A' }}</span>
												</div>
											</div>
											<div class="col-sm-6 col-xl-3">
												<div class="summary-tile">
													<span class="summary-label">Plan Type</span>
													<span class="summary-value">{{ ucfirst($service->plan_type) }}</span>
												</div>
											</div>
											<div class="col-sm-6 col-xl-3">
												<div class="summary-tile">
													<span class="summary-label">Start Date</span>
													<span class="summary-value">{{ $service->start_date->format('d M Y') }}</span>
												</div>
											</div>
											<div class="col-sm-6 col-xl-3">
												<div class="summary-tile">
													<span class="summary-label">End Date</span>
													<span class="summary-value">{{ $service->end_date->format('d M Y') }}</span>
												</div>
											</div>
										</div>
									</div>

									<div class="d-flex flex-column align-items-stretch gap-2 hero-actions">
										<a href="{{ route('vendor-services.edit', $service->id) }}" class="btn btn-primary">
											<i class="bx bx-edit me-1"></i>Edit Service
										</a>
										<a href="{{ route('vendor-services.index') }}" class="btn btn-outline-secondary">
											<i class="bx bx-arrow-back me-1"></i>Back to List
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-lg-8">
						<div class="card border-0 shadow-sm h-100">
							<div class="card-body p-4">
								<div class="d-flex justify-content-between align-items-center mb-4">
									<div>
										<h5 class="mb-1 fw-semibold">Service Details</h5>
										<div class="text-muted small">Core service information and operating window.</div>
									</div>
								</div>

								<div class="detail-grid">
									<div class="detail-item">
										<span class="detail-label">Vendor Name</span>
										<span class="detail-value">{{ $service->vendor->name ?? 'N/A' }}</span>
									</div>
									<div class="detail-item">
										<span class="detail-label">Vendor Email</span>
										<span class="detail-value">{{ $service->vendor->email ?? 'N/A' }}</span>
									</div>
									<div class="detail-item">
										<span class="detail-label">Service Name</span>
										<span class="detail-value">{{ $service->service_name }}</span>
									</div>
									<div class="detail-item">
										<span class="detail-label">Plan Type</span>
										<span class="detail-value">{{ ucfirst($service->plan_type) }}</span>
									</div>
									<div class="detail-item">
										<span class="detail-label">Start Date</span>
										<span class="detail-value">{{ $service->start_date->format('d M Y') }}</span>
									</div>
									<div class="detail-item">
										<span class="detail-label">End Date</span>
										<span class="detail-value">{{ $service->end_date->format('d M Y') }}</span>
									</div>
									<div class="detail-item">
										<span class="detail-label">Duration</span>
										<span class="detail-value">{{ $service->start_date->diffInDays($service->end_date) + 1 }} days</span>
									</div>
									<div class="detail-item">
										<span class="detail-label">Billing Date</span>
										<span class="detail-value">{{ $service->billing_date ? $service->billing_date->format('d M Y') : 'N/A' }}</span>
									</div>
									<div class="detail-item">
										<span class="detail-label">Status</span>
										<span class="detail-value">
											<span class="badge bg-{{ $service->status_badge }}">
												{{ ucfirst($service->status) }}
											</span>
										</span>
									</div>
									<div class="detail-item">
										<span class="detail-label">Created At</span>
										<span class="detail-value">{{ $service->created_at->format('d M Y, h:i A') }}</span>
									</div>
									<div class="detail-item">
										<span class="detail-label">Last Updated</span>
										<span class="detail-value">{{ $service->updated_at->format('d M Y, h:i A') }}</span>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-lg-4">
						<div class="card border-0 shadow-sm h-100">
							<div class="card-body p-4">
								<h5 class="mb-1 fw-semibold">Service Notes</h5>
								<div class="text-muted small mb-4">The service description is preserved exactly as entered.</div>

								@if($service->service_details)
									<div class="service-details-box">
										{!! $service->service_details !!}
									</div>
								@else
									<div class="empty-state">
										No additional service details were provided.
									</div>
								@endif
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="overlay toggle-icon"></div>
<a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
@endsection

@push('styles')
<style>
	.vendor-service-show .hero-card {
		background:
			linear-gradient(135deg, rgba(13, 110, 253, 0.12), rgba(255, 255, 255, 0.92)),
			linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
		border-radius: 1rem;
		overflow: hidden;
	}

	.vendor-service-show .summary-grid {
		max-width: 980px;
	}

	.vendor-service-show .summary-tile,
	.vendor-service-show .detail-item,
	.vendor-service-show .service-details-box,
	.vendor-service-show .empty-state {
		border: 1px solid #e8edf4;
		border-radius: 0.9rem;
		background: #fff;
	}

	.vendor-service-show .summary-tile,
	.vendor-service-show .detail-item {
		padding: 1rem;
		height: 100%;
	}

	.vendor-service-show .summary-label,
	.vendor-service-show .detail-label {
		display: block;
		font-size: 0.78rem;
		font-weight: 600;
		letter-spacing: 0.04em;
		text-transform: uppercase;
		color: #7a8699;
		margin-bottom: 0.45rem;
	}

	.vendor-service-show .summary-value,
	.vendor-service-show .detail-value {
		display: block;
		font-size: 0.98rem;
		font-weight: 600;
		color: #1f2937;
		word-break: break-word;
	}

	.vendor-service-show .detail-grid {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 1rem;
	}

	.vendor-service-show .service-details-box {
		padding: 1.1rem;
		min-height: 220px;
		background: linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%);
		overflow: auto;
	}

	.vendor-service-show .service-details-box img {
		max-width: 100%;
		height: auto;
	}

	.vendor-service-show .service-details-box table {
		width: 100%;
	}

	.vendor-service-show .empty-state {
		padding: 1rem;
		color: #7a8699;
		background: #fafbfd;
	}

	.vendor-service-show .hero-actions .btn {
		min-width: 180px;
	}

	@media (max-width: 991.98px) {
		.vendor-service-show .detail-grid {
			grid-template-columns: 1fr;
		}

		.vendor-service-show .hero-actions .btn {
			width: 100%;
		}
	}
</style>
@endpush