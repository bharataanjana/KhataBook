<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(): View
    {
        $business = Business::current();

        return view('settings.index', compact('business'));
    }

    public function update(Request $request): RedirectResponse
    {
        $business = Business::current();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'gstin' => 'nullable|string|max:20',
            'pan' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'state_code' => 'nullable|string|max:10',
            'pincode' => 'nullable|string|max:10',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
            'ifsc_code' => 'nullable|string|max:20',
            'branch' => 'nullable|string|max:100',
            'upi_id' => 'nullable|string|max:100',
            'invoice_prefix' => 'nullable|string|max:20',
            'quotation_prefix' => 'nullable|string|max:20',
            'purchase_prefix' => 'nullable|string|max:20',
            'terms_and_conditions' => 'nullable|string',
        ]);

        $business->update($validated);

        return back()->with('success', 'Business settings updated successfully!');
    }
}
