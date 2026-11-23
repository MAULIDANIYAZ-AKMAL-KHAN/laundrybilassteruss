<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_order_sequences', function (Blueprint $table) {
            $table->string('business_date', 10)->primary();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('business_date', 10)->nullable();
            $table->unsignedInteger('order_number')->nullable();
            $table->unique(['business_date', 'order_number']);
        });

        $numbersByDate = [];

        DB::table('orders')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'created_at'])
            ->each(function (object $order) use (&$numbersByDate): void {
                $businessDate = substr((string) $order->created_at, 0, 10);
                $orderNumber = ($numbersByDate[$businessDate] ?? 0) + 1;
                $numbersByDate[$businessDate] = $orderNumber;

                DB::table('orders')
                    ->where('id', $order->id)
                    ->update([
                        'business_date' => $businessDate,
                        'order_number' => $orderNumber,
                    ]);
            });

        foreach ($numbersByDate as $businessDate => $lastNumber) {
            DB::table('daily_order_sequences')->insert([
                'business_date' => $businessDate,
                'last_number' => $lastNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['business_date', 'order_number']);
            $table->dropColumn(['business_date', 'order_number']);
        });

        Schema::dropIfExists('daily_order_sequences');
    }
};
