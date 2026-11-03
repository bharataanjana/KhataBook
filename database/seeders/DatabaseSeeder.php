<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Item;
use App\Models\Party;
use App\Models\Unit;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\PurchaseService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Business Profile
        $business = Business::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Bharat Electronics & Enterprises',
                'phone' => '+91 98200 87654',
                'email' => 'sales@bharatelectronics.com',
                'gstin' => '27ABCDE1234F1Z5',
                'pan' => 'ABCDE1234F',
                'address' => 'Shop #14, Lamington Road, Grant Road East',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'state_code' => '27',
                'pincode' => '400007',
                'bank_name' => 'HDFC Bank',
                'account_number' => '50200088991122',
                'ifsc_code' => 'HDFC0000123',
                'branch' => 'Grant Road, Mumbai',
                'upi_id' => 'bharatenterprise@okhdfcbank',
                'invoice_prefix' => 'INV-',
                'quotation_prefix' => 'EST-',
                'purchase_prefix' => 'PUR-',
                'terms_and_conditions' => "1. Goods once sold cannot be returned without warranty inspection.\n2. 1 Year standard manufacturer replacement warranty applies.\n3. Interest @ 18% p.a. applicable for delayed payments over 30 days.\n4. Subject to Mumbai Jurisdiction.",
            ]
        );

        // 2. Units
        $units = [
            ['name' => 'Pieces', 'short_name' => 'PCS'],
            ['name' => 'Box', 'short_name' => 'BOX'],
            ['name' => 'Numbers', 'short_name' => 'NOS'],
            ['name' => 'Meters', 'short_name' => 'MTR'],
        ];
        foreach ($units as $u) {
            Unit::firstOrCreate(['short_name' => $u['short_name']], $u);
        }

        // 3. Categories
        $catElectronics = Category::firstOrCreate(['name' => 'Audio & Accessories'], ['description' => 'Earbuds, headphones and cables']);
        $catComputer = Category::firstOrCreate(['name' => 'Computer Peripherals'], ['description' => 'Keyboards, mouse, chargers']);
        $catWearables = Category::firstOrCreate(['name' => 'Smart Wearables'], ['description' => 'Fitness bands and watches']);

        // 4. Products / Items
        $itemsData = [
            [
                'name' => 'Wireless ANC Earbuds Pro',
                'item_code' => 'ITM-EAR-01',
                'hsn_code' => '8518',
                'category_id' => $catElectronics->id,
                'unit' => 'PCS',
                'sale_price' => 1999.00,
                'purchase_price' => 1250.00,
                'tax_rate' => 18.00,
                'opening_stock' => 30.00,
                'low_stock_threshold' => 8.00,
            ],
            [
                'name' => 'Fast 65W GaN Dual USB-C Charger',
                'item_code' => 'ITM-CHG-65',
                'hsn_code' => '8504',
                'category_id' => $catComputer->id,
                'unit' => 'PCS',
                'sale_price' => 1499.00,
                'purchase_price' => 850.00,
                'tax_rate' => 18.00,
                'opening_stock' => 25.00,
                'low_stock_threshold' => 6.00,
            ],
            [
                'name' => 'Mechanical RGB Gaming Keyboard (Blue Switch)',
                'item_code' => 'ITM-KEY-RGB',
                'hsn_code' => '8471',
                'category_id' => $catComputer->id,
                'unit' => 'PCS',
                'sale_price' => 3299.00,
                'purchase_price' => 2100.00,
                'tax_rate' => 18.00,
                'opening_stock' => 15.00,
                'low_stock_threshold' => 5.00,
            ],
            [
                'name' => 'Smart Fitness Band Series 7',
                'item_code' => 'ITM-WCH-07',
                'hsn_code' => '8517',
                'category_id' => $catWearables->id,
                'unit' => 'PCS',
                'sale_price' => 2499.00,
                'purchase_price' => 1600.00,
                'tax_rate' => 18.00,
                'opening_stock' => 3.00, // Trigger low stock
                'low_stock_threshold' => 5.00,
            ],
            [
                'name' => 'High-Speed Braided 4K HDMI Cable (2m)',
                'item_code' => 'ITM-CBL-4K',
                'hsn_code' => '8544',
                'category_id' => $catElectronics->id,
                'unit' => 'PCS',
                'sale_price' => 449.00,
                'purchase_price' => 190.00,
                'tax_rate' => 18.00,
                'opening_stock' => 60.00,
                'low_stock_threshold' => 10.00,
            ],
            [
                'name' => 'Ergonomic Silent Wireless Mouse',
                'item_code' => 'ITM-MOU-SIL',
                'hsn_code' => '8471',
                'category_id' => $catComputer->id,
                'unit' => 'PCS',
                'sale_price' => 749.00,
                'purchase_price' => 390.00,
                'tax_rate' => 18.00,
                'opening_stock' => 2.00, // Trigger low stock
                'low_stock_threshold' => 5.00,
            ],
        ];

        $createdItems = [];
        foreach ($itemsData as $data) {
            $item = Item::firstOrCreate(['item_code' => $data['item_code']], array_merge($data, ['current_stock' => $data['opening_stock']]));
            $item->syncStock();
            $createdItems[] = $item;
        }

        // 5. Parties (Customers & Suppliers)
        $customer1 = Party::firstOrCreate(
            ['name' => 'Ramesh Trading Corporation'],
            [
                'type' => 'customer',
                'phone' => '+91 98201 11222',
                'email' => 'accounts@rameshtrading.in',
                'gstin' => '27AABCR1234F1Z1',
                'pan' => 'AABCR1234F',
                'billing_address' => 'Gala 102, APMC Market, Vashi, Navi Mumbai',
                'state' => 'Maharashtra',
                'state_code' => '27',
                'opening_balance' => 4500.00,
                'opening_balance_type' => 'to_receive',
            ]
        );

        $customer2 = Party::firstOrCreate(
            ['name' => 'Priya Digital Studio'],
            [
                'type' => 'customer',
                'phone' => '+91 98199 33445',
                'email' => 'priya@digitalstudio.com',
                'billing_address' => 'Flat 4B, Silver Oak, Baner, Pune',
                'state' => 'Maharashtra',
                'state_code' => '27',
                'opening_balance' => 0.00,
                'opening_balance_type' => 'to_receive',
            ]
        );

        // Inter-State Customer (IGST demonstration)
        $customer3 = Party::firstOrCreate(
            ['name' => 'Bengaluru IT Infra Pvt Ltd'],
            [
                'type' => 'customer',
                'phone' => '+91 80234 56789',
                'email' => 'procurement@bengaluruitinfra.com',
                'gstin' => '29AAACN1234P1Z3',
                'pan' => 'AAACN1234P',
                'billing_address' => 'Level 3, Embassy Tech Village, Outer Ring Road, Bengaluru',
                'state' => 'Karnataka',
                'state_code' => '29',
                'opening_balance' => 0.00,
                'opening_balance_type' => 'to_receive',
            ]
        );

        $supplier1 = Party::firstOrCreate(
            ['name' => 'Shree Balaji Electronics Wholesale'],
            [
                'type' => 'supplier',
                'phone' => '+91 98212 99887',
                'email' => 'orders@balajielectronics.com',
                'gstin' => '27BBBCS4321A1Z8',
                'pan' => 'BBBCS4321A',
                'billing_address' => 'Shop 5, Lohar Chawl, Kalbadevi, Mumbai',
                'state' => 'Maharashtra',
                'state_code' => '27',
                'opening_balance' => 8500.00,
                'opening_balance_type' => 'to_pay',
            ]
        );

        $supplier2 = Party::firstOrCreate(
            ['name' => 'Apex Impex Distributor'],
            [
                'type' => 'supplier',
                'phone' => '+91 11234 56780',
                'email' => 'dispatch@apeximpex.com',
                'gstin' => '07AAACA9876C1Z2',
                'billing_address' => 'Plot 44, Okhla Industrial Area Phase 2, New Delhi',
                'state' => 'Delhi',
                'state_code' => '07',
                'opening_balance' => 0.00,
                'opening_balance_type' => 'to_pay',
            ]
        );

        // 6. Expense Categories & Expenses
        $expCatRent = ExpenseCategory::firstOrCreate(['name' => 'Shop Rent']);
        $expCatSalary = ExpenseCategory::firstOrCreate(['name' => 'Staff Salaries']);
        $expCatTea = ExpenseCategory::firstOrCreate(['name' => 'Tea, Snacks & Office Expenses']);
        $expCatElectricity = ExpenseCategory::firstOrCreate(['name' => 'Electricity & Utilities']);

        Expense::firstOrCreate(
            ['expense_number' => 'EXP-0001'],
            [
                'expense_category_id' => $expCatRent->id,
                'date' => now()->startOfMonth()->toDateString(),
                'amount' => 18000.00,
                'payment_mode' => 'bank',
                'paid_to' => 'Commercial Arcade Landlord',
                'notes' => 'Monthly shop lease payment',
            ]
        );

        Expense::firstOrCreate(
            ['expense_number' => 'EXP-0002'],
            [
                'expense_category_id' => $expCatElectricity->id,
                'date' => now()->subDays(5)->toDateString(),
                'amount' => 2450.00,
                'payment_mode' => 'upi',
                'paid_to' => 'Adani Electricity Mumbai',
                'notes' => 'Shop power bill',
            ]
        );

        Expense::firstOrCreate(
            ['expense_number' => 'EXP-0003'],
            [
                'expense_category_id' => $expCatTea->id,
                'date' => now()->subDays(2)->toDateString(),
                'amount' => 680.00,
                'payment_mode' => 'cash',
                'paid_to' => 'Daily Refreshments',
                'notes' => 'Tea & water jars for staff',
            ]
        );

        // 7. Seed Transactions using the business services
        $invoiceService = app(InvoiceService::class);
        $purchaseService = app(PurchaseService::class);
        $paymentService = app(PaymentService::class);

        // Sale Invoice 1 (Intra-state to Ramesh Trading, partial payment)
        $invoiceService->createInvoice([
            'invoice_type' => 'sale',
            'invoice_number' => 'INV-0001',
            'party_id' => $customer1->id,
            'date' => now()->subDays(6)->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'discount_type' => 'fixed',
            'discount_value' => 200,
            'round_off' => 0.00,
            'paid_amount' => 3000.00,
            'payment_mode' => 'bank',
            'notes' => 'Payment terms 15 days',
            'items' => [
                [
                    'item_id' => $createdItems[0]->id,
                    'quantity' => 2,
                    'unit_price' => 1999.00,
                    'discount_percent' => 0,
                    'tax_percent' => 18,
                ],
                [
                    'item_id' => $createdItems[1]->id,
                    'quantity' => 2,
                    'unit_price' => 1499.00,
                    'discount_percent' => 5,
                    'tax_percent' => 18,
                ],
            ],
        ]);

        // Sale Invoice 2 (Inter-state to Bengaluru IT Infra, fully paid)
        $invoiceService->createInvoice([
            'invoice_type' => 'sale',
            'invoice_number' => 'INV-0002',
            'party_id' => $customer3->id,
            'date' => now()->subDays(3)->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'round_off' => 0.00,
            'paid_amount' => 7785.64, // Full amount
            'payment_mode' => 'upi',
            'notes' => 'Supply to Bengaluru Office',
            'items' => [
                [
                    'item_id' => $createdItems[2]->id,
                    'quantity' => 2,
                    'unit_price' => 3299.00,
                    'discount_percent' => 0,
                    'tax_percent' => 18,
                ],
            ],
        ]);

        // Quotation / Estimate
        $invoiceService->createInvoice([
            'invoice_type' => 'quotation',
            'invoice_number' => 'EST-0001',
            'party_id' => $customer2->id,
            'date' => now()->subDays(1)->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'discount_type' => 'percentage',
            'discount_value' => 5,
            'paid_amount' => 0,
            'items' => [
                [
                    'item_id' => $createdItems[0]->id,
                    'quantity' => 5,
                    'unit_price' => 1999.00,
                    'tax_percent' => 18,
                ],
                [
                    'item_id' => $createdItems[4]->id,
                    'quantity' => 10,
                    'unit_price' => 449.00,
                    'tax_percent' => 18,
                ],
            ],
        ]);

        // Purchase Bill 1 (From Shree Balaji Electronics)
        $purchaseService->createPurchase([
            'purchase_type' => 'purchase',
            'bill_number' => 'PUR-0001',
            'party_id' => $supplier1->id,
            'date' => now()->subDays(5)->toDateString(),
            'due_date' => now()->addDays(20)->toDateString(),
            'discount_amount' => 500.00,
            'paid_amount' => 10000.00,
            'payment_mode' => 'bank',
            'notes' => 'Bulk monthly procurement',
            'items' => [
                [
                    'item_id' => $createdItems[0]->id,
                    'quantity' => 10,
                    'unit_price' => 1250.00,
                    'tax_percent' => 18,
                ],
                [
                    'item_id' => $createdItems[1]->id,
                    'quantity' => 15,
                    'unit_price' => 850.00,
                    'tax_percent' => 18,
                ],
            ],
        ]);

        // Standalone Payment In from Ramesh Trading
        $paymentService->recordPayment([
            'payment_type' => 'payment_in',
            'payment_number' => 'PAYIN-0001',
            'party_id' => $customer1->id,
            'date' => now()->subDays(2)->toDateString(),
            'amount' => 2000.00,
            'payment_mode' => 'upi',
            'reference_number' => 'UPI/42817281928',
            'notes' => 'Part payment against ledger',
        ]);
    }
}
