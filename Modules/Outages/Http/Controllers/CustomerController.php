<?php

namespace Modules\Outages\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Outages\Models\Customer;
use Modules\Outages\Models\Fat;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request): View
    {
        $query = Customer::with(['fat.fdt.ponPort.oltSlot.olt', 'creator', 'editor']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%")
                  ->orWhere('alternative_number', 'like', "%{$search}%")
                  ->orWhere('onu_type', 'like', "%{$search}%")
                  ->orWhereHas('fat.fdt.ponPort.oltSlot.olt', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $customers = $query->orderBy('name')->paginate(15);
        $statuses = ['Active', 'Inactive', 'Suspended'];

        return view('outages::customers.index', compact('customers', 'statuses'));
    }

    /**
     * Show the form for creating a new customer, optionally pre-attached to a FAT.
     */
    public function create(?Fat $fat = null): View
    {
        $allFats = null;

        if ($fat) {
            $fat->load(['fdt.ponPort.oltSlot.olt']);
        } else {
            $allFats = Fat::with('fdt.ponPort.oltSlot.olt')->orderBy('fat_number')->get();
        }

        return view('outages::customers.create', compact('fat', 'allFats'));
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request, ?Fat $fat = null): RedirectResponse
    {
        $validated = $this->validateCustomer($request);

        if ($fat) {
            $validated['fat_id'] = $fat->id;
        }

        $validated['created_by'] = Auth::id();

        $customer = Customer::create($validated);

        return $fat
            ? redirect()->route('fats.show', $fat)->with('success', 'Customer added successfully.')
            : redirect()->route('customers.show', $customer)->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): View
    {
        $customer->load(['fat.fdt.ponPort.oltSlot.olt', 'creator', 'editor']);

        return view('outages::customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): View
    {
        $customer->load(['fat.fdt.ponPort.oltSlot.olt']);
        $allFats = Fat::with('fdt.ponPort.oltSlot.olt')->orderBy('fat_number')->get();

        return view('outages::customers.edit', compact('customer', 'allFats'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $this->validateCustomer($request, $customer->id);
        $validated['edited_by'] = Auth::id();

        $customer->update($validated);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $fat = $customer->fat;

        $customer->delete();

        return $fat
            ? redirect()->route('fats.show', $fat)->with('success', 'Customer deleted successfully.')
            : redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    private function validateCustomer(Request $request, ?int $customerId = null): array
    {
        return $request->validate([
            'account_number' => 'required|string|max:255|unique:customers,account_number,' . ($customerId ?? 'NULL') . ',id',
            'name' => 'required|string|max:255',
            'mobile_number' => 'nullable|string|max:20',
            'alternative_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'onu_type' => 'nullable|string|max:255',
            'onu_physical_address' => 'nullable|string|max:255',
            'bandwidth_profile' => 'nullable|string|max:255',
            'fat_id' => 'nullable|exists:fats,id',
            'status' => 'required|in:Active,Inactive,Suspended',
        ]);
    }
}
