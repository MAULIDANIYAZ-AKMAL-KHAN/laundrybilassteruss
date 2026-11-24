<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone_number', 25)->nullable()->unique();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable()->constrained()->restrictOnDelete();
        });

        foreach (DB::table('orders')->orderBy('id')->get() as $order) {
            $customerId = null;

            if ($order->phone_number) {
                $customerId = DB::table('customers')
                    ->where('phone_number', $order->phone_number)
                    ->value('id');
            }

            if (! $customerId) {
                $customerId = DB::table('customers')->insertGetId([
                    'name' => $order->customer_name,
                    'phone_number' => $order->phone_number,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('orders')->where('id', $order->id)->update([
                'customer_id' => $customerId,
            ]);
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('customer_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
        });

        Schema::dropIfExists('customers');
    }
};
