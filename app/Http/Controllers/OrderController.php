<?php

namespace App\Http\Controllers;

use App\Models\LaundryItem;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $laundry_items = LaundryItem::orderBy('category')->orderBy('name')->get();
        $isOrderingOpen = now()->hour >= 5;

        return view('customer.index', compact('laundry_items', 'isOrderingOpen'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (now()->hour < 5) {
            return back()->withInput()->with(
                'error',
                'Pemesanan tutup pukul 00.00–05.00 WIB. Silakan pesan kembali mulai pukul 05.00 WIB.'
            );
        }

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'min:8', 'max:25', 'regex:/^[0-9+() -]+$/'],
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $orderedItems = array_filter($validated['items'], static fn ($quantity) => $quantity > 0);

        if ($orderedItems === []) {
            return back()->withInput()->with('error', 'Pilih minimal satu layanan!');
        }

        $businessDate = now()->toDateString();

        $order = DB::transaction(function () use ($validated, $orderedItems, $businessDate): Order {
            DB::table('daily_order_sequences')->insertOrIgnore([
                'business_date' => $businessDate,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $sequence = DB::table('daily_order_sequences')
                ->where('business_date', $businessDate)
                ->lockForUpdate()
                ->first();
            $orderNumber = $sequence->last_number + 1;

            DB::table('daily_order_sequences')
                ->where('business_date', $businessDate)
                ->update([
                    'last_number' => $orderNumber,
                    'updated_at' => now(),
                ]);

            $order = Order::create([
                'customer_name' => $validated['customer_name'],
                'phone_number' => $validated['phone_number'],
                // Older databases still require a value in this legacy column.
                'queue_number' => $validated['phone_number'],
                'business_date' => $businessDate,
                'order_number' => $orderNumber,
                'total_price' => 0,
                'status' => 'pending',
            ]);

            $totalPrice = 0;
            $laundryItems = LaundryItem::query()
                ->whereKey(array_keys($orderedItems))
                ->get()
                ->keyBy('id');

            foreach ($orderedItems as $laundryItemId => $quantity) {
                $laundryItem = $laundryItems->get($laundryItemId);

                if (! $laundryItem) {
                    abort(422, 'Salah satu layanan yang dipilih tidak tersedia.');
                }

                $subtotal = $laundryItem->price * $quantity;
                $totalPrice += $subtotal;

                OrderDetail::create([
                    'order_id' => $order->id,
                    'laundry_item_id' => $laundryItem->id,
                    'quantity' => $quantity,
                    'price' => $subtotal,
                ]);
            }

            $order->update(['total_price' => $totalPrice]);

            return $order;
        });

        $order->load('orderDetails.laundryItem');

        return redirect()->route('customer.index')->with([
            'success' => 'Pesanan berhasil dibuat!',
            'receipt' => [
                'number' => $order->order_number,
                'business_date' => Carbon::parse($order->business_date)->format('d/m/Y'),
                'customer_name' => $order->customer_name,
                'phone_number' => $order->phone_number,
                'created_at' => $order->created_at->format('H:i'),
                'items' => $order->orderDetails->map(fn ($detail) => [
                    'name' => $detail->laundryItem?->name ?? 'Layanan dihapus',
                    'quantity' => $detail->quantity,
                    'subtotal' => $detail->price,
                ])->all(),
                'total_price' => $order->total_price,
            ],
        ]);
    }

    public function adminDashboard(): View
    {
        $orders = Order::with('orderDetails.laundryItem')
            ->orderBy('business_date')
            ->orderBy('order_number')
            ->orderBy('id')
            ->get();

        return view('dashboard', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,completed,cancelled'],
        ]);

        $order->update($validated);

        return back()->with('success', 'Status pesanan #'.$order->order_number.' berhasil diperbarui!');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $businessDate = $order->business_date;
        $orderNumber = $order->order_number;

        DB::transaction(function () use ($order, $businessDate): void {
            $order->delete();

            if (! $businessDate) {
                return;
            }

            $orders = Order::where('business_date', $businessDate)
                ->orderBy('order_number')
                ->lockForUpdate()
                ->get();

            Order::where('business_date', $businessDate)
                ->update(['order_number' => null]);

            foreach ($orders->values() as $index => $remainingOrder) {
                $remainingOrder->update(['order_number' => $index + 1]);
            }

            DB::table('daily_order_sequences')
                ->where('business_date', $businessDate)
                ->update([
                    'last_number' => $orders->count(),
                    'updated_at' => now(),
                ]);
        });

        return back()->with('success', 'Pesanan #'.$orderNumber.' berhasil dihapus.');
    }
}
