<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Party;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KhataAppTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Business::current();
    }

    public function test_dashboard_renders_successfully(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('KhataApp');
        $response->assertSee('Total Sales');
        $response->assertSee('Total Purchases');
    }

    public function test_can_create_party_and_syncs_initial_balance(): void
    {
        $response = $this->post(route('parties.store'), [
            'name' => 'Acme Technologies',
            'type' => 'customer',
            'phone' => '+91 9988776655',
            'email' => 'billing@acme.com',
            'gstin' => '27AAACA1234A1Z5',
            'opening_balance' => 5000,
            'opening_balance_type' => 'to_receive',
            'state' => 'Maharashtra',
            'state_code' => '27',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('parties', ['name' => 'Acme Technologies']);

        $party = Party::where('name', 'Acme Technologies')->first();
        $this->assertNotNull($party);
        $this->assertEquals(5000.00, (float) $party->current_balance);
    }

    public function test_can_create_item_and_adjust_stock(): void
    {
        $category = Category::create(['name' => 'Hardware']);

        $response = $this->post(route('items.store'), [
            'name' => 'USB Flash Drive 64GB',
            'item_code' => 'USB-64',
            'category_id' => $category->id,
            'unit' => 'PCS',
            'sale_price' => 599.00,
            'purchase_price' => 320.00,
            'tax_rate' => 18.00,
            'opening_stock' => 10,
            'low_stock_threshold' => 3,
        ]);

        $response->assertRedirect(route('items.index'));
        $item = Item::where('item_code', 'USB-64')->first();
        $this->assertNotNull($item);
        $this->assertEquals(10.00, (float) $item->current_stock);

        // Test manual stock adjustment
        $this->post(route('items.adjust-stock', $item), [
            'adjustment_type' => 'add',
            'quantity' => 5,
            'reason' => 'Inventory count match',
        ]);

        $item->refresh();
        $this->assertEquals(15.00, (float) $item->current_stock);
    }

    public function test_can_create_sale_invoice_which_reduces_stock_and_updates_balance(): void
    {
        $party = Party::create([
            'name' => 'Kunal Electronics',
            'type' => 'customer',
            'state' => 'Maharashtra',
            'state_code' => '27',
            'opening_balance' => 0,
            'opening_balance_type' => 'to_receive',
        ]);

        $item = Item::create([
            'name' => 'Power Bank 20000mAh',
            'item_code' => 'PB-20',
            'unit' => 'PCS',
            'sale_price' => 1500.00,
            'purchase_price' => 900.00,
            'tax_rate' => 18.00,
            'opening_stock' => 20,
            'current_stock' => 20,
        ]);

        $response = $this->post(route('sales.store'), [
            'invoice_type' => 'sale',
            'invoice_number' => 'INV-0099',
            'party_id' => $party->id,
            'date' => now()->toDateString(),
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'paid_amount' => 500, // Partial payment
            'payment_mode' => 'cash',
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 2,
                    'unit_price' => 1500.00,
                    'discount_percent' => 0,
                    'tax_percent' => 18.00,
                ],
            ],
        ]);

        $response->assertRedirect();

        // Check stock reduced from 20 to 18
        $item->refresh();
        $this->assertEquals(18.00, (float) $item->current_stock);

        // Check Party Balance: Total was 2 * 1500 = 3000 + 18% GST (540) = 3540. Paid 500. Balance due = 3040.
        $party->refresh();
        $this->assertEquals(3040.00, (float) $party->current_balance);
    }

    public function test_sale_show_and_print_renders_successfully(): void
    {
        $party = Party::create([
            'name' => 'Rajesh Sharma',
            'type' => 'customer',
            'state' => 'Maharashtra',
            'state_code' => '27',
        ]);

        $invoice = Invoice::create([
            'invoice_type' => 'sale',
            'invoice_number' => 'INV-0100',
            'party_id' => $party->id,
            'date' => now()->toDateString(),
            'subtotal' => 1000.00,
            'total_amount' => 1180.00,
            'paid_amount' => 1180.00,
            'balance_amount' => 0.00,
            'payment_status' => 'paid',
        ]);

        $response = $this->get(route('sales.show', $invoice));
        $response->assertStatus(200);
        $response->assertSee('INV-0100');

        $printResponse = $this->get(route('sales.print', $invoice));
        $printResponse->assertStatus(200);
        $printResponse->assertSee('TAX INVOICE');
        $printResponse->assertSee('INV-0100');
    }

    public function test_can_create_purchase_bill_which_increases_stock_and_payables(): void
    {
        $supplier = Party::create([
            'name' => 'Super Distributors',
            'type' => 'supplier',
            'state' => 'Maharashtra',
            'state_code' => '27',
            'opening_balance' => 0,
            'opening_balance_type' => 'to_pay',
        ]);

        $item = Item::create([
            'name' => 'HDMI Splitter 1x4',
            'item_code' => 'SPL-14',
            'unit' => 'PCS',
            'sale_price' => 800.00,
            'purchase_price' => 400.00,
            'tax_rate' => 18.00,
            'opening_stock' => 5,
            'current_stock' => 5,
        ]);

        $response = $this->post(route('purchases.store'), [
            'purchase_type' => 'purchase',
            'bill_number' => 'PUR-0099',
            'party_id' => $supplier->id,
            'date' => now()->toDateString(),
            'paid_amount' => 0, // Unpaid
            'payment_mode' => 'bank',
            'items' => [
                [
                    'item_id' => $item->id,
                    'quantity' => 10,
                    'unit_price' => 400.00,
                    'tax_percent' => 18.00,
                ],
            ],
        ]);

        $response->assertRedirect();

        // Check stock increased from 5 to 15
        $item->refresh();
        $this->assertEquals(15.00, (float) $item->current_stock);

        // Check Supplier balance: Total was 10 * 400 = 4000 + 18% GST (720) = 4720 payable (-4720)
        $supplier->refresh();
        $this->assertEquals(-4720.00, (float) $supplier->current_balance);
    }

    public function test_reports_endpoints_render(): void
    {
        $this->get(route('reports.index'))->assertStatus(200);
        $this->get(route('reports.daybook'))->assertStatus(200);
        $this->get(route('reports.pnl'))->assertStatus(200);
        $this->get(route('reports.stock'))->assertStatus(200);
        $this->get(route('reports.gst'))->assertStatus(200);
    }
}
