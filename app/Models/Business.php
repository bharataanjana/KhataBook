<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'gstin',
        'pan',
        'address',
        'city',
        'state',
        'state_code',
        'pincode',
        'bank_name',
        'account_number',
        'ifsc_code',
        'branch',
        'upi_id',
        'invoice_prefix',
        'quotation_prefix',
        'purchase_prefix',
        'terms_and_conditions',
        'logo_url',
        'signature_url',
    ];

    public static function current(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Vyapar Khata Store',
                'phone' => '+91 98765 43210',
                'email' => 'contact@vyaparkhata.com',
                'gstin' => '27ABCDE1234F1Z5',
                'pan' => 'ABCDE1234F',
                'address' => 'Shop No. 12, Commercial Market, M.G. Road',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'state_code' => '27',
                'pincode' => '400001',
                'bank_name' => 'HDFC Bank',
                'account_number' => '50200012345678',
                'ifsc_code' => 'HDFC0001234',
                'branch' => 'Fort, Mumbai',
                'upi_id' => 'vyaparkhata@upi',
                'invoice_prefix' => 'INV-',
                'quotation_prefix' => 'EST-',
                'purchase_prefix' => 'PUR-',
                'terms_and_conditions' => "1. Goods once sold will not be taken back.\n2. Interest @ 18% p.a. will be charged if payment is not made within due date.\n3. Subject to local jurisdiction only.",
            ]
        );
    }
}
