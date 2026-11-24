<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\LaundryItem;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LaundryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_services_and_place_an_order(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('+07:00', now()->format('P'));

        $laundryItem = LaundryItem::create([
            'name' => 'Cuci Reguler',
            'category' => 'Cuci',
            'price' => 25000,
            'description' => 'Cuci pakaian reguler.',
        ]);

        $this->get(route('customer.index'))
            ->assertOk()
            ->assertSee('Cuci Reguler')
            ->assertSee('images/laundry-wash.svg')
            ->assertSee('id="orderTotal"', false)
            ->assertSee('data-price="25000"', false);

        $response = $this->post(route('customer.checkout'), [
            'customer_name' => 'Budi',
            'phone_number' => '081234567890',
            'items' => [$laundryItem->id => 2],
        ]);

        $response->assertRedirect(route('customer.index'))
            ->assertSessionHas('success', 'Pesanan berhasil dibuat!')
            ->assertSessionHas('receipt', fn (array $receipt): bool => $receipt['customer_name'] === 'Budi'
                && $receipt['items'][0]['subtotal'] === 50000);

        $this->get(route('customer.index'))
            ->assertOk()
            ->assertSee('Pesanan berhasil dibuat!')
            ->assertSee('Struk #1')
            ->assertSee('Rp 50.000');

        $order = Order::with('orderDetails')->firstOrFail();
        $this->assertSame(50000, $order->total_price);
        $this->assertSame('081234567890', $order->phone_number);
        $this->assertSame('Budi', $order->customer->name);
        $this->assertSame('081234567890', $order->customer->phone_number);
        $this->assertSame('https://wa.me/6281234567890', $order->whatsapp_url);
        $this->assertSame(50000, $order->orderDetails->first()->price);
        $this->assertSame(2, $order->orderDetails->first()->quantity);
    }

    public function test_customer_cannot_submit_an_order_with_no_services_selected(): void
    {
        $laundryItem = LaundryItem::create([
            'name' => 'Setrika',
            'category' => 'Setrika',
            'price' => 5000,
            'description' => 'Setrika pakaian.',
        ]);

        $this->from(route('customer.index'))->post(route('customer.checkout'), [
            'customer_name' => 'Budi',
            'phone_number' => '081234567890',
            'items' => [$laundryItem->id => 0],
        ])->assertRedirect(route('customer.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_daily_order_numbers_are_sequential_compacted_after_deletion_and_reset_next_day(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-23 09:00:00', 'Asia/Jakarta'));

        try {
            $laundryItem = LaundryItem::create([
                'name' => 'Cuci',
                'category' => 'Cuci',
                'price' => 10000,
                'description' => 'Cuci pakaian.',
            ]);

            $this->post(route('customer.checkout'), [
                'customer_name' => 'Customer Satu',
                'phone_number' => '081111111111',
                'items' => [$laundryItem->id => 1],
            ])->assertSessionHas('receipt.number', 1);

            $this->post(route('customer.checkout'), [
                'customer_name' => 'Customer Dua',
                'phone_number' => '082222222222',
                'items' => [$laundryItem->id => 1],
            ])->assertSessionHas('receipt.number', 2);

            $firstOrder = Order::where('customer_name', 'Customer Satu')->firstOrFail();
            $secondOrder = Order::where('customer_name', 'Customer Dua')->firstOrFail();
            $this->assertSame(1, $firstOrder->order_number);
            $this->assertSame(2, $secondOrder->order_number);
            $this->assertSame('2026-11-23', $secondOrder->business_date);

            $this->actingAs(User::factory()->create(['is_admin' => true]))
                ->delete(route('admin.orders.destroy', $firstOrder))
                ->assertSessionHas('success');

            $this->assertSame(1, $secondOrder->fresh()->order_number);

            $this->post(route('customer.checkout'), [
                'customer_name' => 'Customer Tiga',
                'phone_number' => '083333333333',
                'items' => [$laundryItem->id => 1],
            ])->assertSessionHas('receipt.number', 2);

            Carbon::setTestNow(Carbon::parse('2026-11-24 05:00:00', 'Asia/Jakarta'));

            $this->post(route('customer.checkout'), [
                'customer_name' => 'Customer Empat',
                'phone_number' => '084444444444',
                'items' => [$laundryItem->id => 1],
            ])->assertSessionHas('receipt.number', 1);

            $this->assertDatabaseCount('orders', 3);
            $this->assertDatabaseHas('orders', [
                'customer_name' => 'Customer Empat',
                'order_number' => 1,
                'business_date' => '2026-11-24',
            ]);

            $this->get(route('dashboard'))
                ->assertOk()
                ->assertSeeInOrder(['Customer Dua', 'Customer Tiga', 'Customer Empat']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_orders_are_rejected_during_closed_hours_and_allowed_at_opening_time(): void
    {
        $laundryItem = LaundryItem::create([
            'name' => 'Setrika',
            'category' => 'Setrika',
            'price' => 5000,
            'description' => 'Setrika pakaian.',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-11-24 04:59:00', 'Asia/Jakarta'));

        try {
            $this->get(route('customer.index'))
                ->assertOk()
                ->assertSee('Kami buka kembali pukul 05.00 WIB')
                ->assertSee('disabled', false);

            $this->from(route('customer.index'))->post(route('customer.checkout'), [
                'customer_name' => 'Customer',
                'phone_number' => '081234567890',
                'items' => [$laundryItem->id => 1],
            ])->assertRedirect(route('customer.index'))
                ->assertSessionHas('error');

            $this->assertDatabaseCount('orders', 0);

            Carbon::setTestNow(Carbon::parse('2026-11-24 05:00:00', 'Asia/Jakarta'));

            $this->get(route('customer.index'))
                ->assertOk()
                ->assertDontSee('Kami buka kembali pukul 05.00 WIB')
                ->assertSee('Pesan Sekarang');

            $this->post(route('customer.checkout'), [
                'customer_name' => 'Customer',
                'phone_number' => '081234567890',
                'items' => [$laundryItem->id => 1],
            ])->assertSessionHas('receipt.number', 1);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_admin_can_delete_an_order_and_its_details_from_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $laundryItem = LaundryItem::create([
            'name' => 'Cuci',
            'category' => 'Cuci',
            'price' => 10000,
            'estimated_duration' => '3-4 hari',
            'description' => 'Cuci.',
        ]);
        $customer = Customer::create([
            'name' => 'Budi',
            'phone_number' => '081234567890',
        ]);
        $order = Order::create([
            'customer_id' => $customer->id,
            'customer_name' => 'Budi',
            'phone_number' => '081234567890',
            'queue_number' => '081234567890',
            'total_price' => 10000,
            'status' => 'pending',
        ]);
        $order->orderDetails()->create([
            'laundry_item_id' => $laundryItem->id,
            'quantity' => 1,
            'price' => 10000,
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('081234567890')
            ->assertSee('https://wa.me/6281234567890')
            ->assertSee('Proses')
            ->assertSee('Estimasi: 3-4 hari')
            ->assertSee('Hapus');

        $this->delete(route('admin.orders.destroy', $order))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('order_details', ['order_id' => $order->id]);
    }

    public function test_registration_authenticates_a_user_and_protects_admin_pages(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));

        $this->post(route('register.store'), [
            'name' => 'Pelanggan Baru',
            'email' => 'pelanggan.baru@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('customer.index'));

        $this->assertAuthenticated();
        $customer = User::where('email', 'pelanggan.baru@example.com')->firstOrFail();
        $this->assertFalse($customer->is_admin);
        $this->get(route('customer.index'))
            ->assertOk()
            ->assertSeeText('Pelanggan Baru');
        $this->get(route('dashboard'))->assertForbidden();
        $this->get(route('admin.reports'))->assertForbidden();
        $this->get(route('laundry_items.index'))->assertForbidden();

        $this->post(route('laundry_items.store'), [
            'name' => 'Layanan Terlarang',
            'category' => 'Cuci',
            'price' => 10000,
            'description' => 'Tidak boleh dibuat oleh pelanggan.',
        ])->assertForbidden();

        $this->assertDatabaseMissing('laundry_items', ['name' => 'Layanan Terlarang']);

        $this->post(route('logout'));
        $this->post(route('login.store'), [
            'email' => 'pelanggan.baru@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('customer.index'));

        $this->get(route('customer.index'))
            ->assertOk()
            ->assertSeeText('Pelanggan Baru');
    }

    public function test_authenticated_user_can_manage_laundry_services(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $this->get(route('laundry_items.create'))
            ->assertOk()
            ->assertSee('Simpan Data')
            ->assertSee('admin-form');

        $this->post(route('laundry_items.store'), [
            'name' => 'Paket Hemat',
            'category' => 'Paket',
            'price' => 12000,
            'estimated_duration' => '2 hari',
            'description' => 'Cuci dan setrika.',
        ])->assertRedirect(route('laundry_items.index'));

        $laundryItem = LaundryItem::firstOrFail();
        $this->get(route('laundry_items.edit', $laundryItem))
            ->assertOk()
            ->assertSee('Update Data')
            ->assertSee('Paket Hemat')
            ->assertSee('2 hari');

        $this->put(route('laundry_items.update', $laundryItem), [
            'name' => 'Paket Hemat Baru',
            'category' => 'Paket',
            'price' => 15000,
            'estimated_duration' => '1 hari',
            'description' => 'Cuci dan setrika cepat.',
        ])->assertRedirect(route('laundry_items.index'));

        $this->assertDatabaseHas('laundry_items', [
            'id' => $laundryItem->id,
            'name' => 'Paket Hemat Baru',
            'price' => 15000,
            'estimated_duration' => '1 hari',
        ]);

        $this->get(route('laundry_items.index'))
            ->assertOk()
            ->assertSee('1 hari');

        $this->delete(route('laundry_items.destroy', $laundryItem))
            ->assertRedirect(route('laundry_items.index'));

        $this->assertDatabaseMissing('laundry_items', ['id' => $laundryItem->id]);
    }

    public function test_seeded_admin_can_access_admin_pages(): void
    {
        $this->seed();

        $this->post(route('login.store'), [
            'email' => 'admin@gmail.com',
            'password' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs(User::where('email', 'admin@gmail.com')->firstOrFail());
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSeeText('Admin Laundry')
            ->assertDontSee('Halaman Customer');
        $this->get(route('laundry_items.index'))->assertOk();
        $this->get(route('admin.reports'))->assertOk();
    }

    public function test_seeded_service_estimates_and_prices_are_shown_in_catalog_and_admin_list(): void
    {
        $this->seed();

        $this->get(route('customer.index'))
            ->assertOk()
            ->assertSee('Reguler')
            ->assertSee('Rp 7.000/kg')
            ->assertSee('3-4 hari')
            ->assertSee('Ekspres')
            ->assertSee('Rp 12.000/kg')
            ->assertSee('1-2 hari')
            ->assertSee('Superkilat')
            ->assertSee('Rp 25.000/kg')
            ->assertSee('5 jam selesai');

        $this->actingAs(User::factory()->create(['is_admin' => true]))
            ->get(route('laundry_items.index'))
            ->assertOk()
            ->assertSee('Harga/kg')
            ->assertSee('Estimasi')
            ->assertSee('5 jam selesai');
    }

    public function test_cashier_can_login_record_transactions_update_status_and_view_related_report(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-24 10:00:00', 'Asia/Jakarta'));

        try {
            $this->seed();

            $this->post(route('login.store'), [
                'email' => 'kasir@gmail.com',
                'password' => 'password123',
            ])->assertRedirect(route('dashboard'));

            $cashier = User::where('email', 'kasir@gmail.com')->firstOrFail();
            $this->assertTrue($cashier->is_cashier);
            $this->get(route('dashboard'))
                ->assertOk()
                ->assertDontSee('Data Layanan')
                ->assertSee('Rekap Transaksi');
            $this->get(route('admin.reports'))->assertOk();
            $this->get(route('laundry_items.index'))->assertForbidden();

            $laundryItem = LaundryItem::where('name', 'Reguler')->firstOrFail();
            $this->post(route('customer.checkout'), [
                'customer_name' => 'Pelanggan Kasir',
                'phone_number' => '081234567890',
                'items' => [$laundryItem->id => 1],
            ])->assertRedirect(route('customer.index'));

            $order = Order::with('customer')->firstOrFail();
            $this->assertSame('Pelanggan Kasir', $order->customer->name);
            $this->get(route('admin.reports'))
                ->assertViewHas('summary', fn (array $summary): bool => $summary['processing'] === 1
                    && $summary['completed'] === 0
                    && $summary['cancelled'] === 0);

            $this->patch(route('admin.orders.updateStatus', $order), [
                'status' => 'completed',
            ])->assertSessionHas('success');

            $this->get(route('admin.reports'))
                ->assertOk()
                ->assertSee('Pelanggan Kasir')
                ->assertSee('Reguler')
                ->assertSee('Rp 7.000')
                ->assertSee('Selesai');

            Order::create([
                'customer_id' => $order->customer_id,
                'customer_name' => 'Pelanggan Kasir',
                'phone_number' => '081234567890',
                'queue_number' => '081234567890',
                'total_price' => 9000,
                'status' => 'cancelled',
            ]);

            $this->get(route('admin.reports'))
                ->assertViewHas('summary', fn (array $summary): bool => $summary['processing'] === 0
                    && $summary['completed'] === 1
                    && $summary['cancelled'] === 1
                    && $summary['revenue'] === 7000)
                ->assertSee('Transaksi Proses')
                ->assertSee('Transaksi Dibatalkan');

            $this->delete(route('admin.orders.destroy', $order))->assertForbidden();
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_admin_cannot_delete_a_service_used_by_a_transaction(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));

        $laundryItem = LaundryItem::create([
            'name' => 'Reguler',
            'category' => 'Cuci',
            'price' => 7000,
            'description' => 'Cuci reguler.',
        ]);
        $customer = Customer::create([
            'name' => 'Pelanggan',
            'phone_number' => '081234567890',
        ]);
        $order = Order::create([
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'phone_number' => $customer->phone_number,
            'queue_number' => $customer->phone_number,
            'total_price' => 7000,
            'status' => 'pending',
        ]);
        $order->orderDetails()->create([
            'laundry_item_id' => $laundryItem->id,
            'quantity' => 1,
            'price' => 7000,
        ]);

        $this->delete(route('laundry_items.destroy', $laundryItem))
            ->assertRedirect(route('laundry_items.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('laundry_items', ['id' => $laundryItem->id]);
        $this->assertDatabaseHas('order_details', ['order_id' => $order->id]);
    }
}
