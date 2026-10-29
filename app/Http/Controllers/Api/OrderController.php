<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
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
    private const PURCHASE_STATUSES = ['Draft', 'Ordered', 'Partially Received', 'Received'];
    private const DELIVERY_STATUSES = ['Pending', 'Ready to Dispatch', 'Dispatched', 'Partially Delivered', 'Delivered'];

    public function formOptions()
    {
        return ApiResponse::success([
            'clients' => User::where('role', 'client')->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']),
            'vendors' => Vendor::orderBy('name')->get(['id', 'name']),
            'assignees' => User::whereIn('role', ['staff', 'admin', 'super_admin', 'manager'])
                ->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']),
            'priorities' => ['Normal', 'High'],
            'purchase_statuses' => self::PURCHASE_STATUSES,
            'delivery_statuses' => self::DELIVERY_STATUSES,
        ], 'Order form options retrieved successfully.');
    }

    public function index(Request $request)
    {
        $query = $this->visibleQuery($request->user())
            ->with(['client', 'vendor', 'owner', 'items'])
            ->orderBy('vendor_expected_date')
            ->orderBy('client_delivery_date');

        $this->applyFilters($query, $request);
        $orders = $query->paginate(min(max((int) $request->input('per_page', 12), 1), 100))->withQueryString();

        $statsQuery = $this->visibleQuery($request->user());
        $stats = [
            'all' => (clone $statsQuery)->count(),
            'open' => (clone $statsQuery)->where('overall_status', 'Open')->count(),
            'on_hold' => (clone $statsQuery)->where('is_on_hold', true)->count(),
            'completed' => (clone $statsQuery)->where('overall_status', 'Completed')->count(),
            'cancelled' => (clone $statsQuery)->where('overall_status', 'Cancelled')->count(),
        ];

        return ApiResponse::success([
            'orders' => array_merge($orders->toArray(), [
                'data' => collect($orders->items())->map(fn (Order $order) => $this->orderPayload($order))->values(),
            ]),
            'stats' => $stats,
        ], 'Orders retrieved successfully.');
    }

    public function store(Request $request)
    {
        $data = $this->validatedOrder($request);
        $order = DB::transaction(function () use ($data, $request) {
            $items = $data['items'];
            unset($data['items']);
            unset($data['already_placed']);
            $year = now()->year;
            $sequence = (int) Order::withTrashed()->whereYear('created_at', $year)->lockForUpdate()->count() + 1;
            $order = Order::create(array_merge($data, [
                'order_no' => sprintf('ORD-%d-%04d', $year, $sequence),
                'assigned_user_id' => $data['assigned_user_id'] ?? $request->user()->id,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
                'purchase_status' => $request->boolean('already_placed') ? 'Ordered' : 'Draft',
                'delivery_status' => 'Pending',
                'overall_status' => 'Open',
            ]));
            $this->replaceItems($order, $items);
            $this->recordHistory($order, 'Order', null, 'Created', 'Order created', $request->user()->id);
            if ($order->purchase_status === 'Ordered') {
                $this->recordHistory($order, 'Purchase', 'Draft', 'Ordered', 'Placed with vendor', $request->user()->id);
            }
            return $order;
        });

        return ApiResponse::success($this->orderPayload($order->fresh(['client', 'vendor', 'owner', 'items', 'history.user'])), 'Order created successfully.', 201);
    }

    public function show(Request $request, int $order)
    {
        $model = $this->findVisible($request->user(), $order);
        $model->load(['client', 'vendor', 'owner', 'creator', 'items', 'history.user', 'attachments']);

        return ApiResponse::success($this->orderPayload($model, true), 'Order retrieved successfully.');
    }

    public function update(Request $request, int $order)
    {
        $model = $this->findVisible($request->user(), $order);
        $data = $this->validatedOrder($request);
        DB::transaction(function () use ($data, $model, $request) {
            $items = $data['items'];
            unset($data['items']);
            unset($data['already_placed']);
            $model->update(array_merge($data, ['updated_by' => $request->user()->id]));
            $this->replaceItems($model, $items);
            $this->recordHistory($model, 'Order', null, 'Updated', 'Order details updated', $request->user()->id);
        });

        return ApiResponse::success($this->orderPayload($model->fresh(['client', 'vendor', 'owner', 'items'])), 'Order updated successfully.');
    }

    public function destroy(Request $request, int $order)
    {
        $model = $this->findVisible($request->user(), $order);
        $model->delete();

        return ApiResponse::success(null, $model->order_no.' moved to trash.');
    }

    public function status(Request $request, int $order)
    {
        $model = $this->findVisible($request->user(), $order);
        $data = $request->validate([
            'track' => ['required', Rule::in(['Purchase', 'Delivery'])],
            'status' => ['required', 'string'],
            'remark' => ['nullable', 'string', 'max:1000'],
            'date' => ['nullable', 'date'],
            'received' => ['nullable', 'array'],
            'received.*' => ['numeric', 'min:0'],
        ]);

        $this->transition($model, $data['track'], $data['status'], $data, $request->user()->id);
        return ApiResponse::success($this->orderPayload($model->fresh(['client', 'vendor', 'owner', 'items', 'history.user'])), 'Order status updated successfully.');
    }

    public function markOrdered(Request $request, int $order)
    {
        return $this->runAction($request, $order, 'Purchase', 'Ordered');
    }

    public function recordReceipt(Request $request, int $order)
    {
        $request->merge(['received' => $request->input('received', [])]);
        $data = $request->validate([
            'received' => ['required', 'array'],
            'received.*' => ['numeric', 'min:0'],
            'date' => ['nullable', 'date'],
            'remark' => ['nullable', 'string', 'max:1000'],
        ]);
        $model = $this->findVisible($request->user(), $order);
        if ($model->overall_status === 'Cancelled' || $model->is_on_hold) abort(422, 'This order is cancelled or on hold.');
        if (!in_array($model->purchase_status, ['Ordered', 'Partially Received'], true)) abort(422, 'Receipts can be recorded only after the order is marked Ordered.');

        DB::transaction(function () use ($model, $data, $request) {
            $order = Order::with('items')->lockForUpdate()->findOrFail($model->id);
            $old = $order->purchase_status;
            $items = $order->items->keyBy('id');

            foreach ($data['received'] as $itemId => $quantity) {
                $item = $items->get($itemId);
                if (!$item) abort(422, "Order item {$itemId} does not belong to this order.");
                if ((float) $quantity < (float) $item->qty_received) abort(422, "Received quantity for {$item->product_name} cannot decrease.");
                if ((float) $quantity > (float) $item->quantity) abort(422, "Received quantity for {$item->product_name} cannot exceed ordered quantity.");
                $item->update(['qty_received' => $quantity]);
            }

            $allReceived = $order->items->every(fn ($item) => (float) $item->qty_received >= (float) $item->quantity);
            $new = $allReceived ? 'Received' : 'Partially Received';
            if ($allReceived) {
                $order->vendor_received_date = $data['date'] ?? today();
            } elseif (!empty($data['date'])) {
                $order->vendor_received_date = $data['date'];
            }
            $order->purchase_status = $new;
            $order->refreshOverallStatus();
            $order->updated_by = $request->user()->id;
            $order->save();

            $receiptDate = isset($data['date']) ? Carbon::parse($data['date'])->startOfDay() : now();
            $this->recordHistory($order, 'Purchase', $old, $new, $data['remark'] ?? null, $request->user()->id, $receiptDate, ['receipt_date' => $receiptDate->toDateString(), 'received' => $data['received']]);
        });

        return ApiResponse::success($this->orderPayload($model->fresh(['client', 'vendor', 'owner', 'items', 'history.user'])), 'Receipt recorded successfully.');
    }

    public function markReceived(Request $request, int $order)
    {
        $model = $this->findVisible($request->user(), $order);
        $received = $model->items->mapWithKeys(fn ($item) => [$item->id => $item->quantity])->all();
        return $this->runAction($request, $order, 'Purchase', 'Received', ['received' => $received] + $request->only('date', 'remark'));
    }

    public function markReadyToDispatch(Request $request, int $order)
    {
        return $this->runAction($request, $order, 'Delivery', 'Ready to Dispatch', $request->only('date', 'remark'));
    }

    public function markDispatched(Request $request, int $order)
    {
        return $this->runAction($request, $order, 'Delivery', 'Dispatched', $request->only('date', 'remark'));
    }

    public function markDelivered(Request $request, int $order)
    {
        return $this->runAction($request, $order, 'Delivery', 'Delivered', $request->only('date', 'remark'));
    }

    public function toggleHold(Request $request, int $order)
    {
        $model = $this->findVisible($request->user(), $order);
        if ($model->overall_status !== 'Open') {
            return ApiResponse::error('Only open orders can be put on hold or resumed.', null, 422);
        }
        $old = $model->is_on_hold ? 'On Hold' : 'Active';
        $model->is_on_hold = !$model->is_on_hold;
        $model->updated_by = $request->user()->id;
        $model->save();
        $new = $model->is_on_hold ? 'On Hold' : 'Active';
        $this->recordHistory($model, 'Order', $old, $new, $request->input('remark'), $request->user()->id);

        return ApiResponse::success($this->orderPayload($model->fresh(['client', 'vendor', 'owner'])), 'Order '.($model->is_on_hold ? 'placed on hold.' : 'resumed.'));
    }

    public function cancel(Request $request, int $order)
    {
        $model = $this->findVisible($request->user(), $order);
        $data = $request->validate(['cancel_reason' => ['required', 'string', 'max:1000']]);
        if ($model->overall_status === 'Cancelled') {
            return ApiResponse::error('Order is already cancelled.', null, 422);
        }
        $model->update(['overall_status' => 'Cancelled', 'cancel_reason' => $data['cancel_reason'], 'cancelled_by' => $request->user()->id, 'cancelled_at' => now(), 'updated_by' => $request->user()->id]);
        $this->recordHistory($model, 'Order', 'Open', 'Cancelled', $data['cancel_reason'], $request->user()->id);

        return ApiResponse::success($this->orderPayload($model->fresh(['client', 'vendor', 'owner', 'history.user'])), 'Order cancelled successfully.');
    }

    private function runAction(Request $request, int $id, string $track, string $status, array $extra = [])
    {
        $model = $this->findVisible($request->user(), $id);
        $data = array_merge(['track' => $track, 'status' => $status], $extra);
        $this->transition($model, $track, $status, $data, $request->user()->id);
        return ApiResponse::success($this->orderPayload($model->fresh(['client', 'vendor', 'owner', 'items', 'history.user'])), 'Order status updated successfully.');
    }

    private function transition(Order $order, string $track, string $new, array $data, int $userId): void
    {
        if ($order->overall_status === 'Cancelled' || $order->is_on_hold) {
            abort(422, 'This order is cancelled or on hold.');
        }
        $allowed = $track === 'Purchase' ? self::PURCHASE_STATUSES : self::DELIVERY_STATUSES;
        if (!in_array($new, $allowed, true)) abort(422, 'Invalid '.$track.' status.');
        $old = $track === 'Purchase' ? $order->purchase_status : $order->delivery_status;
        if ($old === $new && !($track === 'Purchase' && $new === 'Partially Received')) {
            abort(422, 'Order is already '.$new.'.');
        }
        if ($track === 'Delivery' && in_array($new, ['Ready to Dispatch', 'Dispatched', 'Delivered'], true) && !in_array($order->purchase_status, ['Partially Received', 'Received'], true)) {
            abort(422, 'Receive at least part of the purchase before dispatching.');
        }
        DB::transaction(function () use ($order, $track, $new, $data, $userId, $old) {
            if ($track === 'Purchase' && $new === 'Received') {
                foreach ($order->items as $item) {
                    $quantity = (float) ($data['received'][$item->id] ?? $item->qty_received);
                    if ($quantity < (float) $item->quantity) abort(422, 'Every item must be fully received before marking the purchase received.');
                    $item->update(['qty_received' => $quantity]);
                }
                $order->vendor_received_date = $data['date'] ?? today();
            }
            if ($track === 'Purchase' && $new === 'Partially Received') {
                foreach ($order->items as $item) if (isset($data['received'][$item->id])) {
                    if ((float) $data['received'][$item->id] > (float) $item->quantity) abort(422, 'Received quantity cannot exceed ordered quantity.');
                    $item->update(['qty_received' => $data['received'][$item->id]]);
                }
                if (!empty($data['date'])) {
                    $order->vendor_received_date = $data['date'];
                }
            }
            if ($track === 'Delivery' && $new === 'Delivered') $order->client_delivered_date = $data['date'] ?? today();
            if ($track === 'Purchase') $order->purchase_status = $new; else $order->delivery_status = $new;
            $order->refreshOverallStatus();
            $order->updated_by = $userId;
            $order->save();
            $this->recordHistory($order, $track, $old, $new, $data['remark'] ?? null, $userId);
        });
    }

    private function validatedOrder(Request $request): array
    {
        return $request->validate([
            'client_id' => ['required', 'exists:users,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'order_date' => ['required', 'date'],
            'vendor_expected_date' => ['required', 'date', 'after_or_equal:order_date'],
            'client_delivery_date' => ['required', 'date', 'after_or_equal:vendor_expected_date'],
            'priority' => ['required', Rule::in(['Normal', 'High'])],
            'notes' => ['nullable', 'string'],
            'already_placed' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit' => ['nullable', 'string', 'max:30'],
            'items.*.purchase_rate' => ['nullable', 'numeric', 'min:0'],
            'items.*.selling_rate' => ['nullable', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $term = '%'.trim($request->input('search')).'%';
            $query->where(fn ($q) => $q->where('order_no', 'like', $term)
                ->orWhereHas('items', fn ($item) => $item->where('product_name', 'like', $term))
                ->orWhereHas('vendor', fn ($vendor) => $vendor->where('name', 'like', $term))
                ->orWhereHas('client', fn ($client) => $client->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$term])));
        }
        foreach (['overall_status', 'purchase_status', 'delivery_status', 'priority', 'assigned_user_id', 'client_id', 'vendor_id'] as $filter) {
            if ($request->filled($filter)) $query->where($filter, $request->input($filter));
        }
        if ($request->boolean('overdue')) $query->where(fn ($q) => $q->where(fn ($x) => $x->where('purchase_status', '!=', 'Received')->whereDate('vendor_expected_date', '<', today()))->orWhere(fn ($x) => $x->where('delivery_status', '!=', 'Delivered')->whereDate('client_delivery_date', '<', today())));
        if ($request->input('tab') === 'mine') $query->where('assigned_user_id', $request->user()->id);
        if ($request->input('tab') === 'completed') $query->where('overall_status', 'Completed');
        if ($request->input('tab') === 'cancelled') $query->where('overall_status', 'Cancelled');
        if ($request->input('tab') === 'due') $query->where(fn ($q) => $q->whereBetween('vendor_expected_date', [today(), today()->addDays(7)])->orWhereBetween('client_delivery_date', [today(), today()->addDays(7)]));
        foreach ([['order_date_from', 'order_date', '>='], ['order_date_to', 'order_date', '<='], ['vendor_date_from', 'vendor_expected_date', '>='], ['vendor_date_to', 'vendor_expected_date', '<='], ['client_date_from', 'client_delivery_date', '>='], ['client_date_to', 'client_delivery_date', '<=']] as [$input, $column, $operator]) if ($request->filled($input)) $query->whereDate($column, $operator, $request->input($input));
    }

    private function visibleQuery(User $user)
    {
        // Access is controlled by the route's `can:view_orders` permission.
        // Do not apply role/staff ownership filters here: every user with the
        // permission must see the complete order dataset.
        return Order::query();
    }

    private function findVisible(User $user, int $id): Order
    {
        return $this->visibleQuery($user)->with('items')->findOrFail($id);
    }

    private function replaceItems(Order $order, array $items): void
    {
        $order->items()->delete();
        foreach ($items as $index => $item) $order->items()->create(array_merge($item, ['sort_order' => $index]));
    }

    private function recordHistory(Order $order, string $track, ?string $from, string $to, ?string $remark, int $userId, $changedAt = null, ?array $meta = null): void
    {
        OrderStatusHistory::create(['order_id' => $order->id, 'track' => $track, 'from_status' => $from, 'to_status' => $to, 'changed_by' => $userId, 'changed_at' => $changedAt ?? now(), 'remark' => $remark, 'meta' => $meta]);
    }

    private function orderPayload(Order $order, bool $details = false): array
    {
        $payload = $order->toArray();
        $payload['client_name'] = $order->client_name;
        $payload['is_overdue'] = $order->is_overdue;
        $payload['overdue_legs'] = $order->overdue_legs;
        if (!$details) unset($payload['history'], $payload['attachments']);
        return $payload;
    }

}
