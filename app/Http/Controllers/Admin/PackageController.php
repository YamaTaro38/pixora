<?php
// app/Http/Controllers/Admin/PackageController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PackageController extends Controller
{
    public function index(Request $request)
{
    $query = Package::withCount('bookings'); // Tambahkan count booking
    
    // Search
    if ($request->search) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }
    
    // Filter status
    if ($request->status == 'active') {
        $query->where('is_active', true);
    } elseif ($request->status == 'inactive') {
        $query->where('is_active', false);
    }
    
    $packages = $query->orderBy('sort_order')->paginate(10);
    
    return view('admin.packages.index', compact('packages'));
}

    public function create()
    {
        return view('admin.packages.create');
    }

    public function store(Request $request)
    {
        // Validasi
        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'required|string',
            'price' => 'required|string',
            'duration_hours' => 'required|integer|min:1',
            'edited_photos' => 'required|integer|min:1',
            'location_type' => 'required|in:studio,outdoor,both',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Format price (hapus titik dan koma)
        $price = (int) str_replace(['.', ','], '', $request->price);
        $downPayment = $request->down_payment ? (int) str_replace(['.', ','], '', $request->down_payment) : null;

        $package = new Package();
        $package->name = $request->name;
        $package->slug = Str::slug($request->name);
        $package->description = $request->description;
        $package->price = $price;
        $package->down_payment = $downPayment;
        $package->duration_hours = $request->duration_hours;
        $package->edited_photos = $request->edited_photos;
        $package->location_type = $request->location_type;
        $package->is_active = $request->has('is_active');
        $package->sort_order = Package::max('sort_order') + 1;

        // Handle inclusions (dari JSON yang dikirim dari view)
        $inclusions = [];
        if ($request->inclusions) {
            // Pastikan inclusions adalah string JSON
            $inclusions = json_decode($request->inclusions, true);
            if (!is_array($inclusions)) {
                $inclusions = [];
            }
        }
        $package->inclusions = json_encode($inclusions);

        // Upload main image
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('packages', 'public');
            $package->image = $path;
        }

        $package->save();

        // Upload gallery images
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $index => $image) {
                $path = $image->store('package-galleries', 'public');
                PackageGallery::create([
                    'package_id' => $package->id,
                    'image' => $path,
                    'sort_order' => $index
                ]);
            }
        }

        return redirect()->route('admin.packages.index')->with('success', 'Paket berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $package = Package::with('galleries')->findOrFail($id);
        return view('admin.packages.edit', compact('package'));
    }

    public function update(Request $request, $id)
    {
        $package = Package::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'required|string',
            'price' => 'required|string',
            'duration_hours' => 'required|integer|min:1',
            'edited_photos' => 'required|integer|min:1',
            'location_type' => 'required|in:studio,outdoor,both',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Format price (hapus titik dan koma)
        $price = (int) str_replace(['.', ','], '', $request->price);
        $downPayment = $request->down_payment ? (int) str_replace(['.', ','], '', $request->down_payment) : null;

        $package->name = $request->name;
        $package->slug = Str::slug($request->name);
        $package->description = $request->description;
        $package->price = $price;
        $package->down_payment = $downPayment;
        $package->duration_hours = $request->duration_hours;
        $package->edited_photos = $request->edited_photos;
        $package->location_type = $request->location_type;
        $package->is_active = $request->has('is_active');

        // Handle inclusions (dari JSON yang dikirim dari view)
        $inclusions = [];
        if ($request->inclusions) {
            // Pastikan inclusions adalah string JSON
            $inclusions = json_decode($request->inclusions, true);
            if (!is_array($inclusions)) {
                $inclusions = [];
            }
        }
        $package->inclusions = json_encode($inclusions);


        // Handle remove main image
        if ($request->remove_main_image == '1') {
            if ($package->image && Storage::disk('public')->exists($package->image)) {
                Storage::disk('public')->delete($package->image);
            }
            $package->image = null;
        }

        // Upload new main image
        if ($request->hasFile('image')) {
            if ($package->image && Storage::disk('public')->exists($package->image)) {
                Storage::disk('public')->delete($package->image);
            }
            $path = $request->file('image')->store('packages', 'public');
            $package->image = $path;
        }

        $package->save();

        // Upload new gallery images
        if ($request->hasFile('gallery_images')) {
            $currentCount = $package->galleries->count();
            foreach ($request->file('gallery_images') as $index => $image) {
                $path = $image->store('package-galleries', 'public');
                PackageGallery::create([
                    'package_id' => $package->id,
                    'image' => $path,
                    'sort_order' => $currentCount + $index
                ]);
            }
        }

        return redirect()->route('admin.packages.index')->with('success', 'Paket berhasil diupdate.');
    }

    public function deactivate($id)
    {
        $package = Package::findOrFail($id);
        $package->is_active = false;
        $package->save();

        return redirect()->route('admin.packages.index')->with(
            'success',
            'Paket "' . $package->name . '" berhasil dinonaktifkan.'
        );
    }

    // Update method destroy (sama seperti sebelumnya)
    public function destroy($id)
    {
        $package = Package::findOrFail($id);

        // Cek apakah paket sudah memiliki booking
        $bookingsCount = $package->bookings()->count();

        if ($bookingsCount > 0) {
            return redirect()->route('admin.packages.index')->with(
                'error',
                'Paket tidak dapat dihapus karena sudah memiliki ' . $bookingsCount . ' booking. Gunakan tombol nonaktifkan.'
            );
        }

        // Hapus gambar utama
        if ($package->image && Storage::disk('public')->exists($package->image)) {
            Storage::disk('public')->delete($package->image);
        }

        // Hapus gallery images
        foreach ($package->galleries as $gallery) {
            if (Storage::disk('public')->exists($gallery->image)) {
                Storage::disk('public')->delete($gallery->image);
            }
            $gallery->delete();
        }

        $package->delete();

        return redirect()->route('admin.packages.index')->with('success', 'Paket berhasil dihapus.');
    }


    public function deleteGalleryImage($id)
    {
        $gallery = PackageGallery::findOrFail($id);

        if (Storage::disk('public')->exists($gallery->image)) {
            Storage::disk('public')->delete($gallery->image);
        }

        $gallery->delete();

        return response()->json(['success' => true]);
    }
}
