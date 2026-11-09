<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::with('photos')->latest()->get(); // Eager load photos untuk tampilan katalog
        return view('tenant.packages.index', compact('packages'));
    }

    public function create()
    {
        return view('tenant.packages.form', ['package' => new Package()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'down_payment_min' => 'required|numeric|min:0|lte:price',
            'duration_minutes' => 'required|integer|min:1',
            'max_persons' => 'required|integer|min:1',
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        Package::create($validated);

        return redirect()->route('tenant.packages.index')->with('success', 'Paket foto berhasil ditambahkan!');
    }

    public function edit(Package $package)
    {
        return view('tenant.packages.form', compact('package'));
    }

    public function update(Request $request, Package $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'down_payment_min' => 'required|numeric|min:0|lte:price',
            'duration_minutes' => 'required|integer|min:1',
            'max_persons' => 'required|integer|min:1',
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        $package->update($validated);

        return redirect()->route('tenant.packages.index')->with('success', 'Paket foto berhasil diperbarui!');
    }

    public function destroy(Package $package)
    {
        $package->delete();
        return redirect()->route('tenant.packages.index')->with('success', 'Paket foto berhasil dihapus!');
    }
}