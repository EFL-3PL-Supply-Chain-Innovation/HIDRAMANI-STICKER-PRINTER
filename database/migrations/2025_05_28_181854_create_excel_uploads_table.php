<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('excel_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('load_user_id')->default(null)->nullable()->constrained('users')->onDelete('cascade');
            $table->string('wh_id')->nullable();
            $table->string('client_code')->nullable();
            $table->string('pallet')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('location_id')->nullable();
            $table->string('item_number')->nullable();
            $table->string('description')->nullable();
            $table->string('lot_number')->nullable();
            $table->string('actual_qty')->nullable();
            $table->string('unavailable_qty')->nullable();
            $table->string('uom')->nullable();
            $table->string('status')->nullable();
            $table->string('mlp')->nullable();
            $table->string('stored_attribute_id')->nullable();
            $table->string('fifo_date')->nullable();
            $table->string('expiration_date')->nullable();
            $table->string('grn_number')->nullable();
            $table->string('gatepass_id')->nullable();
            $table->string('cust_dec_number')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('style')->nullable();
            $table->string('supplier')->nullable();
            $table->string('plant')->nullable();
            $table->string('client_so')->nullable();
            $table->string('client_so_line')->nullable();
            $table->string('po_cust_dec')->nullable();
            $table->string('customer_ref_number')->nullable();
            $table->string('item_id')->nullable();
            $table->string('invoice_number_1')->nullable();
            $table->string('transaction')->nullable();
            $table->string('order_type')->nullable();
            $table->string('order_number')->nullable();
            $table->string('store_order_number')->nullable();
            $table->string('customer_po_number')->nullable();
            $table->string('partial_order_flag')->nullable();
            $table->string('order_date')->nullable();
            $table->string('load_id')->nullable();
            $table->string('asn_number')->nullable();
            $table->string('po_number')->nullable();
            $table->string('supplier_hu')->nullable();
            $table->string('new_item_number')->nullable();
            $table->string('printed_status')->nullable()->default('Not Printed');
            $table->foreignId('printed_user')->default(null)->nullable()->constrained('users')->onDelete('cascade');
            $table->datetime('printed_time')->nullable();
            $table->string('load_attr19')->nullable();
            $table->string('load_attr20')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excel_uploads');
    }
};
