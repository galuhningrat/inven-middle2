<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with('assetType');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('asset_id', 'ILIKE', "%{$search}%")
                    ->orWhere('name', 'ILIKE', "%{$search}%")
                    ->orWhere('brand', 'ILIKE', "%{$search}%")
                    ->orWhere('location', 'ILIKE', "%{$search}%")
                    ->orWhere('serial_number', 'ILIKE', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->whereHas('assetType', function ($q) use ($request) {
                $q->where('code', $request->type);
            });
        }

        // Get all assets (NO PAGINATION - as requested)
        $assets = $query->orderBy('created_at', 'desc')->get();
        $assetTypes = AssetType::all();

        return view('assets-inv.index', compact('assets', 'assetTypes'));
    }

    public function create()
    {
        $assetTypes = AssetType::all();
        $locations = ['Ruang IT', 'Laboratorium', 'Perpustakaan', 'Aula', 'Ruang Dosen'];

        // PERBAIKAN: Ganti dari 'assets.create' ke 'assets-inv.create'
        return view('assets-inv.create', compact('assetTypes', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_type_id' => 'required|exists:asset_types,id',
            'brand' => 'required|string|max:255',
            'serial_number' => 'required|string|unique:assets,serial_number',
            'price' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'location' => 'required|string|max:255',
            'condition' => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Generate Asset ID
        $assetType = AssetType::find($request->asset_type_id);
        $year = date('Y');
        $month = date('m');
        $lastAsset = Asset::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('asset_type_id', $request->asset_type_id)
            ->latest()
            ->first();

        $counter = $lastAsset ? intval(substr($lastAsset->asset_id, -4)) + 1 : 1;
        $validated['asset_id'] = sprintf('%s/%s/%s-%04d', $year, $month, $assetType->code, $counter);

        // Generate QR Code
        $qrCode = $this->generateUniqueCode($request->asset_type_id);
        $validated['qr_code'] = $qrCode;
        $validated['status'] = 'Tersedia';

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('assets', 'public');
            $validated['image'] = $imagePath;
        }

        $asset = Asset::create($validated);

        // Create QR Code record
        QrCode::create([
            'asset_id' => $asset->id,
            'code_content' => $qrCode,
            'status' => 'Aktif',
        ]);

        return redirect()->route('assets-inv.index')
            ->with('success', 'Aset berhasil ditambahkan!');
    }

    public function show(Asset $asset)
    {
        // Load relations dengan safe check
        $asset->load([
            'assetType',
            'qrCodes' => function ($query) {
                $query->latest();
            },
            'borrowings' => function ($query) {
                $query->latest()->limit(10);
            },
            'maintenances' => function ($query) {
                $query->latest()->limit(10);
            }
        ]);

        return view('assets-inv.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $assetTypes = AssetType::all();
        $locations = ['Ruang IT', 'Laboratorium', 'Perpustakaan', 'Aula', 'Ruang Dosen'];

        // PERBAIKAN: Ganti dari 'assets.edit' ke 'assets-inv.edit'
        return view('assets-inv.edit', compact('asset', 'assetTypes', 'locations'));
    }

    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_type_id' => 'required|exists:asset_types,id',
            'brand' => 'required|string|max:255',
            'serial_number' => 'required|string|unique:assets,serial_number,' . $asset->id,
            'price' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
            'location' => 'required|string|max:255',
            'condition' => 'required|in:Baik,Rusak Ringan,Rusak Berat',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($asset->image && Storage::disk('public')->exists($asset->image)) {
                Storage::disk('public')->delete($asset->image);
            }
            $imagePath = $request->file('image')->store('assets', 'public');
            $validated['image'] = $imagePath;
        }

        $asset->update($validated);

        return redirect()->route('assets-inv.index')
            ->with('success', 'Aset berhasil diupdate!');
    }

    public function destroy(Asset $asset)
    {
        try {
            // Delete image
            if ($asset->image && Storage::disk('public')->exists($asset->image)) {
                Storage::disk('public')->delete($asset->image);
            }

            // Delete related QR codes
            $asset->qrCodes()->delete();

            $asset->delete();

            return redirect()->route('assets-inv.index')
                ->with('success', 'Aset berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->route('assets-inv.index')
                ->with('error', 'Gagal menghapus aset: ' . $e->getMessage());
        }
    }

    public function generateQrCode(Request $request)
    {
        $assetTypeId = $request->asset_type_id;
        $qrCode = $this->generateUniqueCode($assetTypeId);

        return response()->json(['qr_code' => $qrCode]);
    }

    private function generateUniqueCode($assetTypeId)
    {
        $type = AssetType::find($assetTypeId);
        $prefix = $type ? $type->code : 'AST';
        $timestamp = base_convert(time(), 10, 36);
        $random = strtoupper(Str::random(5));
        return "{$prefix}-{$timestamp}-{$random}";
    }

    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->ids;
            $deleted = 0;

            foreach ($ids as $id) {
                $asset = Asset::find($id);
                if ($asset) {
                    if ($asset->image && Storage::disk('public')->exists($asset->image)) {
                        Storage::disk('public')->delete($asset->image);
                    }
                    $asset->qrCodes()->delete();
                    $asset->delete();
                    $deleted++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "{$deleted} aset berhasil dihapus."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus aset: ' . $e->getMessage()
            ], 500);
        }
    }
}