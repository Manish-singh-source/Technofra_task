@extends('/layout/master')

@section('content')
<div class="page-wrapper order-page"><div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4"><div><div class="text-uppercase small fw-bold text-primary letter-space">Operations / Order Management</div><h3 class="mb-1 fw-bold">Orders workspace</h3><p class="text-muted mb-0">Track every purchase, receipt and client delivery in one place.</p></div>@can('create_orders')<a href="{{ route('orders.create') }}" class="btn btn-primary radius-30 px-4"><i class="bx bxs-plus-square me-1"></i>Add New Order</a>@endcan</div>

    <div class="row mb-3">
        <div class="col-md-6 col-lg-3"><div class="card radius-10 border-start border-0 border-4 border-info"><div class="card-body"><div class="d-flex align-items-center"><div><p class="mb-0 text-secondary">All Orders</p><h4 class="my-1 text-info">{{ $orderStats['all'] }}</h4><p class="mb-0 font-13">Total orders</p></div><div class="widgets-icons-2 rounded-circle bg-gradient-info text-white ms-auto"><i class="bx bx-list-ul"></i></div></div></div></div></div>
        <div class="col-md-6 col-lg-3"><div class="card radius-10 border-start border-0 border-4 border-primary"><div class="card-body"><div class="d-flex align-items-center"><div><p class="mb-0 text-secondary">Open</p><h4 class="my-1 text-primary">{{ $orderStats['open'] }}</h4><p class="mb-0 font-13">Active orders</p></div><div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto"><i class="bx bx-loader-alt"></i></div></div></div></div></div>
        <div class="col-md-6 col-lg-3"><div class="card radius-10 border-start border-0 border-4 border-warning"><div class="card-body"><div class="d-flex align-items-center"><div><p class="mb-0 text-secondary">On Hold</p><h4 class="my-1 text-warning">{{ $orderStats['on_hold'] }}</h4><p class="mb-0 font-13">Temporarily paused</p></div><div class="widgets-icons-2 rounded-circle bg-gradient-burning text-white ms-auto"><i class="bx bx-pause"></i></div></div></div></div></div>
        <div class="col-md-6 col-lg-3"><div class="card radius-10 border-start border-0 border-4 border-success"><div class="card-body"><div class="d-flex align-items-center"><div><p class="mb-0 text-secondary">Completed</p><h4 class="my-1 text-success">{{ $orderStats['completed'] }}</h4><p class="mb-0 font-13">Finished orders</p></div><div class="widgets-icons-2 rounded-circle bg-gradient-ohhappiness text-white ms-auto"><i class="bx bx-check"></i></div></div></div></div></div>
    </div>

    <div class="card border-0 shadow-sm mb-4"><div class="card-body"><form method="GET" action="{{ route('orders.index') }}" class="row g-3 align-items-end">
        <div class="col-lg-5"><label class="form-label small fw-semibold">Search</label><div class="input-group"><span class="input-group-text bg-white"><i class="bx bx-search"></i></span><input name="search" class="form-control" placeholder="Search order, product, client or vendor" value="{{ request('search') }}"></div></div>
        <div class="col-lg-2"><label class="form-label small fw-semibold">Purchase status</label><select name="purchase_status" class="form-select"><option value="">All purchase</option>@foreach (['Draft','Ordered','Partially Received','Received'] as $status)<option value="{{ $status }}" @selected(request('purchase_status') === $status)>{{ $status }}</option>@endforeach</select></div>
        <div class="col-lg-2"><label class="form-label small fw-semibold">Delivery status</label><select name="delivery_status" class="form-select"><option value="">All delivery</option>@foreach (['Pending','Ready to Dispatch','Dispatched','Delivered'] as $status)<option value="{{ $status }}" @selected(request('delivery_status') === $status)>{{ $status }}</option>@endforeach</select></div>
        <div class="col-lg-2"><label class="form-label small fw-semibold">Priority</label><select name="priority" class="form-select"><option value="">All priorities</option><option value="High" @selected(request('priority') === 'High')>High</option><option value="Normal" @selected(request('priority') === 'Normal')>Normal</option></select></div>
        <div class="col-lg-1 d-flex gap-1"><button class="btn btn-primary radius-30" title="Filter"><i class="bx bx-filter-alt"></i></button><a href="{{ route('orders.index') }}" class="btn btn-light radius-30" title="Clear"><i class="bx bx-refresh"></i></a></div>
    </form></div></div>

    <div class="card mt-4"><div class="card-body"><div class="order-tabs px-1 pt-1"><a class="{{ !request('tab') && !request('overdue') ? 'active' : '' }}" href="{{ route('orders.index') }}">All</a><a class="{{ request('tab') === 'mine' ? 'active' : '' }}" href="{{ route('orders.index', ['tab' => 'mine']) }}">My orders</a><a class="{{ request('tab') === 'due' ? 'active' : '' }}" href="{{ route('orders.index', ['tab' => 'due']) }}">Due soon</a><a class="{{ request('overdue') ? 'active' : '' }}" href="{{ route('orders.index', ['overdue' => 1]) }}">Overdue</a><a class="{{ request('tab') === 'completed' ? 'active' : '' }}" href="{{ route('orders.index', ['tab' => 'completed']) }}">Completed</a><a class="{{ request('tab') === 'cancelled' ? 'active' : '' }}" href="{{ route('orders.index', ['tab' => 'cancelled']) }}">Cancelled</a></div><div class="d-flex align-items-center mb-4 mt-3"><div class="position-relative order-search"><input type="text" class="form-control ps-5 radius-30" placeholder="Search Orders" name="order-search" value="{{ request('search') }}"><span class="position-absolute top-50 product-show translate-middle-y"><i class="bx bx-search"></i></span></div><div class="ms-auto"><span class="text-muted small">{{ $orders->total() }} orders found</span></div></div><div class="table-responsive"><table class="table mb-0 order-table"><thead class="table-light"><tr><th>Order</th><th>Client / vendor</th><th>Items</th><th>Dates</th><th>Purchase</th><th>Delivery</th><th>Owner</th><th>Action</th></tr></thead><tbody>@forelse ($orders as $order)<tr class="{{ $order->is_overdue ? 'overdue-row' : '' }}"><td><a class="fw-semibold text-decoration-none" href="{{ route('orders.show', $order) }}">{{ $order->order_no }}</a><div class="mt-1"><span class="badge rounded-pill {{ $order->priority === 'High' ? 'bg-danger-subtle text-danger' : 'bg-light text-muted' }}">{{ $order->priority }} priority</span>@if ($order->is_overdue)<span class="badge rounded-pill bg-danger ms-1">Overdue</span>@endif</div></td><td><div class="fw-semibold">{{ $order->client_name }}</div><small class="text-muted"><i class="bx bx-store-alt me-1"></i>{{ $order->vendor?->name }}</small></td><td><div>{{ $order->items->first()?->product_name ?: 'No items' }}</div><small class="text-muted">{{ $order->items->count() > 1 ? '+'.($order->items->count() - 1).' more' : ($order->items->first()?->quantity.' '.($order->items->first()?->unit ?? 'units')) }}</small></td><td><small class="d-block">Vendor <b>{{ $order->vendor_expected_date?->format('d M Y') }}</b></small><small class="d-block">Client <b>{{ $order->client_delivery_date?->format('d M Y') }}</b></small></td><td><span class="status-dot purchase">{{ $order->purchase_status }}</span></td><td><span class="status-dot delivery">{{ $order->delivery_status }}</span></td><td><small>{{ $order->owner?->name ?: '—' }}</small></td><td><div class="d-flex order-actions"><a href="{{ route('orders.show', $order) }}" title="View"><i class="bx bxs-show"></i></a></div></td></tr>@empty<tr><td colspan="8" class="text-center py-5 text-muted"><i class="bx bx-package fs-1 d-block mb-2"></i>No orders match these filters.</td></tr>@endforelse</tbody></table></div><div class="mt-3">{{ $orders->links() }}</div></div></div>
</div></div>
@push('styles')<style>.order-page{background:#f5f8fc}.order-page .page-content{padding:26px}.letter-space{letter-spacing:.12em}.order-page .card{border-radius:14px}.order-tabs{display:flex;gap:25px;overflow:auto}.order-tabs a{padding:11px 1px;color:#728198;text-decoration:none;font-weight:600;white-space:nowrap;border-bottom:3px solid transparent}.order-tabs a.active{color:#176ee2;border-bottom-color:#176ee2}.order-table thead th{font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#718198;padding:14px 12px;white-space:nowrap}.order-table td{padding:14px 12px;border-color:#edf1f6;vertical-align:middle;white-space:nowrap}.order-table .badge{font-size:10px;letter-spacing:.03em}.status-dot{font-size:10px;font-weight:700;border-radius:20px;padding:7px 10px;display:inline-block;line-height:1.2;text-transform:uppercase}.status-dot.purchase{background:#e9f2ff;color:#2669bb}.status-dot.delivery{background:#eeeaff;color:#6251c7}.overdue-row{background:#fff8f8}.overdue-row td:first-child{border-left:4px solid #dc3545}.order-search{min-width:260px}.order-page .widgets-icons-2{width:45px;height:45px;display:grid;place-items:center}.order-page .btn{font-weight:600}.order-page .radius-30{border-radius:30px}.order-page .order-actions{align-items:center}.order-page .order-actions a,.order-page .order-actions button{font-size:18px;line-height:1;transition:transform .15s ease,color .15s ease}.order-page .order-actions a:hover,.order-page .order-actions button:hover{transform:scale(1.15);color:#176ee2!important}.order-page .order-actions form{display:inline-flex;align-items:center;margin:0}.order-page .order-actions form button.text-danger:hover{color:#dc3545!important}</style>@endpush
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.querySelector('.order-page form[method="GET"]');
    if (!filterForm) return;

    const submitButton = filterForm.querySelector('button');
    if (submitButton) submitButton.remove();

    filterForm.querySelectorAll('select').forEach(function (field) {
        field.addEventListener('change', function () {
            filterForm.submit();
        });
    });

    const search = filterForm.querySelector('input[name="search"]');
    let timer;
    if (search) {
        search.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                filterForm.submit();
            }, 450);
        });
    }
    @can('edit_orders')
    const orderEditUrls = {
        @foreach ($orders as $order) '{{ $order->id }}': '{{ route('orders.edit', $order) }}', @endforeach
    };
    @endcan
    @can('delete_orders')
    const orderDeleteUrls = {
        @foreach ($orders as $order) '{{ $order->id }}': '{{ route('orders.destroy', $order) }}', @endforeach
    };
    @endcan
    document.querySelectorAll('.order-actions').forEach(function (actions) {
        const view = actions.querySelector('a[href*="/orders/"]');
        if (!view) return;
        const orderId = view.href.split('/').pop();
        @can('edit_orders')
        if (orderEditUrls[orderId]) {
            actions.insertAdjacentHTML('beforeend', '<a href="' + orderEditUrls[orderId] + '" class="ms-3" title="Edit"><i class="bx bxs-edit"></i></a>');
        }
        @endcan
        @can('delete_orders')
        if (orderDeleteUrls[orderId]) {
            actions.insertAdjacentHTML('beforeend', '<form method="POST" action="' + orderDeleteUrls[orderId] + '" class="ms-3" onsubmit="return confirm(\'Move this order to trash?\');"><input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="border-0 bg-transparent p-0 text-danger" title="Delete"><i class="bx bxs-trash"></i></button></form>');
        }
        @endcan
    });
});
</script>
@endsection
