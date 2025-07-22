<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class ExcelUpload extends Model
{
    protected $table = 'excel_uploads';

    protected $fillable = [
        'load_user_id',
        'wh_id',
        'client_code',
        'pallet',
        'invoice_number',
        'location_id',
        'item_number',
        'description',
        'lot_number',
        'actual_qty',
        'unavailable_qty',
        'uom',
        'status',
        'mlp',
        'stored_attribute_id',
        'fifo_date',
        'expiration_date',
        'grn_number',
        'gatepass_id',
        'cust_dec_number',
        'color',
        'size',
        'style',
        'supplier',
        'plant',
        'client_so',
        'client_so_line',
        'po_cust_dec',
        'customer_ref_number',
        'item_id',
        'invoice_number_1',
        'transaction',
        'order_type',
        'order_number',
        'store_order_number',
        'customer_po_number',
        'partial_order_flag',
        'order_date',
        'load_id',
        'asn_number',
        'po_number',
        'supplier_hu',
        'new_item_number',
        'ref_id',
        'printed_status',
        'printed_user',
        'printed_time',
        'load_atr19',
        'load_atr20',
    ];

    public function uploadedUser()
    {
        return $this->belongsTo(User::class, 'load_user_id');
    }
    public function getUploadedUserNameAttribute()
    {
        return $this->uploadedUser ? $this->uploadedUser->name : 'Unknown User';
    }

    // In ExcelUpload.php model
// In ExcelUpload.php
public function printedUser()
{
    return $this->belongsTo(User::class, 'printed_user');
}

public function getPrintedUserNameAttribute()
{
    return $this->printedUser ? $this->printedUser->name : 'Unknown User';
}

}
