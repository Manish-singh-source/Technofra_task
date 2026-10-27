<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Order::with(['client', 'vendor', 'owner', 'items'])->orderBy('vendor_expected_date')->orderBy('client_delivery_date');

        if ($request->filled('search')) {
            $term = '%'.trim($request->input('search')).'%';
            $query->where(function ($q) use ($term) {
                $q->where('order_no', 'like', $term)
                    ->orWhereHas('items', fn ($item) => $item->where('product_name', 'like', $term))
                    ->orWhereHas('vendor', fn ($vendor) => $vendor->where('name', 'like', $term))
                    ->orWhereHas('client', fn ($client) => $client->where(function ($name) use ($term) {
                        $name->where('first_name', 'like', $term)->orWhere('last_name', 'like', $term)->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$term]);
                    }));
            });
        }

        foreach (['overall_status', 'purchase_status', 'delivery_status', 'priority', 'assigned_user_id', 'client_id', 'vendor_id'] as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        }

        if ($request->boolean('overdue')) {
            $query->where(function ($q) {
                $q->where(fn ($x) => $x->where('purchase_status', '!=', 'Received')->whereDate('vendor_expected_date', '<', today()))
                    ->orWhere(fn ($x) => $x->where('delivery_status', '!=', 'Delivered')->whereDate('client_delivery_date', '<', today()));
            });
        }

        if ($request->filled('order_date_from')) $query->whereDate('order_date', '>=', $request->input('order_date_from'));
        if ($request->filled('order_date_to')) $query->whereDate('order_date', '<=', $request->input('order_date_to'));
        if ($request->filled('vendor_date_from')) $query->whereDate('vendor_expected_date', '>=', $request->input('vendor_date_from'));
        if ($request->filled('vendor_date_to')) $query->whereDate('vendor_expected_date', '<=', $request->input('vendor_date_to'));
        if ($request->filled('client_date_from')) $query->whereDate('client_delivery_date', '>=', $request->input('client_date_from'));
        if ($request->filled('client_date_to')) $query->whereDate('client_delivery_date', '<=', $request->input('client_date_to'));

        if ($request->input('tab') === 'mine') $query->where('assigned_user_id', $user->id);
        if ($request->input('tab') === 'completed') $query->where('overall_status', 'Completed');
        if ($request->input('tab') === 'cancelled') $query->where('overall_status', 'Cancelled');
        if ($request->input('tab') === 'due') $query->where(fn ($q) => $q->whereBetween('vendor_expected_date', [today(), today()->addDays(7)])->orWhereBetween('client_delivery_date', [today(), today()->addDays(7)]));

        $statsQuery = Order::query();
        $orderStats = [
            'all' => (clone $statsQuery)->count(),
            'open' => (clone $statsQuery)->where('overall_status', 'Open')->count(),
            'on_hold' => (clone $statsQuery)->where('is_on_hold', true)->count(),
            'completed' => (clone $statsQuery)->where('overall_status', 'Completed')->count(),
            'cancelled' => (clone $statsQuery)->where('overall_status', 'Cancelled')->count(),
        ];

        $orders = $query->paginate(12)->withQueryString();
        return view('orders.index', [
            'orders' => $orders,
            'orderStats' => $orderStats,
            'clients' => User::where('role', 'client')->orderBy('first_name')->get(),
            'vendors' => Vendor::orderBy('name')->get(),
            'users' => User::whereIn('role', ['staff', 'admin', 'super_admin', 'manager'])->orderBy('first_name')->get(),
        ]);
    }

    public function create() { return view('orders.create', ['clients'=>User::where('role','client')->orderBy('first_name')->get(), 'vendors'=>Vendor::orderBy('name')->get(), 'users'=>User::whereIn('role',['staff','admin','super_admin'])->orderBy('first_name')->get()]); }

    public function edit(Order $order)
    {
        $order->load('items');
        return view('orders.edit', [
            'order' => $order,
            'clients' => User::where('role', 'client')->orderBy('first_name')->get(),
            'vendors' => Vendor::orderBy('name')->get(),
            'users' => User::whereIn('role', ['staff', 'admin', 'super_admin', 'manager'])->orderBy('first_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['client_id'=>'required|exists:users,id','vendor_id'=>'required|exists:vendors,id','assigned_user_id'=>'nullable|exists:users,id','order_date'=>'required|date','vendor_expected_date'=>'required|date|after_or_equal:order_date','client_delivery_date'=>'required|date|after_or_equal:vendor_expected_date','priority'=>['required',Rule::in(['Normal','High'])],'notes'=>'nullable|string','items'=>'required|array|min:1','items.*.product_name'=>'required|string|max:255','items.*.quantity'=>'required|numeric|min:0.001','items.*.unit'=>'nullable|string|max:30','items.*.purchase_rate'=>'nullable|numeric|min:0','items.*.selling_rate'=>'nullable|numeric|min:0','items.*.remarks'=>'nullable|string|max:255']);
        $order = DB::transaction(function () use ($data, $request) {
            // `items` is a nested array for order_items, not a column on orders.
            // Remove it before passing the order payload to Eloquent.
            $orderData = $data;
            $items = $orderData['items'];
            unset($orderData['items']);
            $year = now()->year; $seq = (int) Order::withTrashed()->whereYear('created_at', $year)->lockForUpdate()->count() + 1;
            $order = Order::create(array_merge($orderData, ['order_no'=>sprintf('ORD-%d-%04d',$year,$seq),'assigned_user_id'=>$data['assigned_user_id'] ?? $request->user()->id,'created_by'=>$request->user()->id,'updated_by'=>$request->user()->id,'purchase_status'=>$request->boolean('already_placed') ? 'Ordered' : 'Draft','delivery_status'=>'Pending','overall_status'=>'Open']));
            foreach ($items as $index=>$item) $order->items()->create(array_merge($item, ['sort_order'=>$index]));
            $this->history($order, 'Order', null, 'Created', 'Order created', $request->user()->id);
            if ($order->purchase_status === 'Ordered') $this->history($order, 'Purchase', 'Draft', 'Ordered', 'Placed with vendor', $request->user()->id);
            return $order;
        });
        return redirect()->route('orders.show', $order)->with('success', $order->order_no.' created successfully.');
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'client_id' => 'required|exists:users,id',
            'vendor_id' => 'required|exists:vendors,id',
            'assigned_user_id' => 'nullable|exists:users,id',
            'order_date' => 'required|date',
            'vendor_expected_date' => 'required|date|after_or_equal:order_date',
            'client_delivery_date' => 'required|date|after_or_equal:vendor_expected_date',
            'priority' => ['required', Rule::in(['Normal', 'High'])],
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit' => 'nullable|string|max:30',
            'items.*.purchase_rate' => 'nullable|numeric|min:0',
            'items.*.selling_rate' => 'nullable|numeric|min:0',
            'items.*.remarks' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($data, $order, $request) {
            $items = $data['items'];
            unset($data['items']);
            $order->update(array_merge($data, ['updated_by' => $request->user()->id]));
            $order->items()->delete();
            foreach ($items as $index => $item) {
                $order->items()->create(array_merge($item, ['sort_order' => $index]));
            }
            $this->history($order, 'Order', null, 'Updated', 'Order details updated', $request->user()->id);
        });

        return redirect()->route('orders.show', $order)->with('success', $order->order_no.' updated successfully.');
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('orders.index')->with('success', $order->order_no.' moved to trash.');
    }

    public function show(Order $order) { $order->load(['client','vendor','owner','items','history.user','attachments']); return view('orders.show', compact('order')); }

    public function updateStatus(Request $request, Order $order)
    {
        if ($order->overall_status === 'Cancelled' || $order->is_on_hold) return back()->with('error', 'This order is cancelled or on hold.');
        $data = $request->validate(['status'=>'required|string','remark'=>'nullable|string|max:1000','date'=>'nullable|date','received'=>'nullable|array','delivered'=>'nullable|array']);
        $old = $request->track === 'Purchase' ? $order->purchase_status : $order->delivery_status; $new = $data['status'];
        if ($request->track === 'Delivery' && in_array($new, ['Ready to Dispatch','Dispatched','Delivered'], true) && !in_array($order->purchase_status, ['Partially Received','Received'], true)) return back()->with('error','Receive at least part of the purchase before dispatching.');
        if ($request->track === 'Purchase' && $new === 'Received') {
            foreach ($order->items as $item) if ((float) ($data['received'][$item->id] ?? $item->qty_received) < (float) $item->quantity) return back()->with('error','Every item must be fully received before marking the purchase received.');
            foreach ($order->items as $item) $item->update(['qty_received'=>$data['received'][$item->id] ?? $item->quantity]);
            $order->vendor_received_date = $data['date'] ?? today();
        }
        if ($request->track === 'Purchase' && $new === 'Partially Received') foreach ($order->items as $item) if (isset($data['received'][$item->id])) $item->update(['qty_received'=>$data['received'][$item->id]]);
        if ($request->track === 'Delivery' && $new === 'Delivered') $order->client_delivered_date = $data['date'] ?? today();
        if ($request->track === 'Purchase') $order->purchase_status = $new; else $order->delivery_status = $new;
        $order->refreshOverallStatus(); $order->updated_by = $request->user()->id; $order->save();
        $this->history($order, $request->track, $old, $new, $data['remark'] ?? null, $request->user()->id);
        return back()->with('success', 'Order status updated.');
    }

    public function toggleHold(Request $request, Order $order) { $order->is_on_hold = !$order->is_on_hold; $order->save(); $this->history($order,'Order',$order->is_on_hold?'Active':'On Hold',$order->is_on_hold?'On Hold':'Active',$request->input('remark'),$request->user()->id); return back()->with('success', $order->is_on_hold ? 'Order placed on hold.' : 'Order resumed.'); }
    public function cancel(Request $request, Order $order) { $data=$request->validate(['cancel_reason'=>'required|string|max:1000']); $order->update(['overall_status'=>'Cancelled','cancel_reason'=>$data['cancel_reason'],'cancelled_by'=>$request->user()->id,'cancelled_at'=>now(),'updated_by'=>$request->user()->id]); $this->history($order,'Order','Open','Cancelled',$data['cancel_reason'],$request->user()->id); return back()->with('success','Order cancelled.'); }
    private function history(Order $order, string $track, ?string $from, string $to, ?string $remark, int $user): void { OrderStatusHistory::create(['order_id'=>$order->id,'track'=>$track,'from_status'=>$from,'to_status'=>$to,'changed_by'=>$user,'changed_at'=>now(),'remark'=>$remark]); }
}
