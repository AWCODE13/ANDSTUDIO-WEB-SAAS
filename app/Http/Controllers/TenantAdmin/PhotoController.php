<?php

namespace App\Http\Controllers\TenantAdmin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackagePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    public function store(Request $request, Package $package)
    {
        if ($package->photos()->count() >= 4) {
            return response()->json(['success' => false, 'message' => 'Maksimal 4 foto per paket'], 400);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $file = $request->file('photo');
        $path = $file->store("packages/{$package->id}", 'public');

        $maxSort = PackagePhoto::where('package_id', $package->id)->max('sort_order') ?? -1;
        $photo = PackagePhoto::create([
            'package_id' => $package->id,
            'photo_url' => $path,
            'sort_order' => $maxSort + 1,
        ]);

        return response()->json(['success' => true, 'photo' => $photo], 201);
    }

    public function destroy(PackagePhoto $photo)
    {
        Storage::disk('public')->delete($photo->photo_url);
        $photo->delete();

        return response()->json(['success' => true]);
    }

    public function reorder(Request $request, Package $package)
    {
        $request->validate([
            'photo_id' => 'required|integer',
            'swap_with_id' => 'required|integer',
        ]);

        $photo = PackagePhoto::where('id', $request->photo_id)->where('package_id', $package->id)->first();
        $swapPhoto = PackagePhoto::where('id', $request->swap_with_id)->where('package_id', $package->id)->first();

        if (!$photo || !$swapPhoto) {
            return response()->json(['success' => false, 'message' => 'Foto tidak ditemukan'], 404);
        }

        $tmpSort = $photo->sort_order;
        $photo->sort_order = $swapPhoto->sort_order;
        $swapPhoto->sort_order = $tmpSort;

        $photo->save();
        $swapPhoto->save();

        return response()->json(['success' => true]);
    }
}
