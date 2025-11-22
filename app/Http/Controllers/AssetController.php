<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with('assetType');

        // Search
        if ($request->has('search') && $request->search) {
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
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->whereHas('assetType', function ($q) use ($request) {
                $q->where('code', $request->type);
            });
        }

        $assets = $query->latest()->paginate(10);
        $assetTypes = AssetType::all();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('assets-inv._table', compact('assets'))->render(),
                // 'pagination' => $assets->links()->render()
            ]);
        }

        return view('assets-inv.index', compact('assets', 'assetTypes'));
        // return view('assets-inv.index');
    }

    public function create()
    {
        $assetTypes = AssetType::all();
        $locations = ['Ruang IT', 'Laboratorium', 'Perpustakaan', 'Aula', 'Ruang Dosen'];
        return view('assets.create', compact('assetTypes', 'locations'));
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

        return redirect()->route('assets.index')
            ->with('success', 'Aset berhasil ditambahkan!');
    }

    public function show(Asset $asset)
    {
        $asset->load('assetType', 'qrCode', 'borrowings', 'maintenances');
        return view('assets.show', compact('asset'));
    }

    public function edit(Asset $asset)
    {
        $assetTypes = AssetType::all();
        $locations = ['Ruang IT', 'Laboratorium', 'Perpustakaan', 'Aula', 'Ruang Dosen'];
        return view('assets.edit', compact('asset', 'assetTypes', 'locations'));
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
            if ($asset->image) {
                Storage::disk('public')->delete($asset->image);
            }
            $imagePath = $request->file('image')->store('assets', 'public');
            $validated['image'] = $imagePath;
        }

        $asset->update($validated);

        return redirect()->route('assets.index')
            ->with('success', 'Aset berhasil diupdate!');
    }

    public function destroy(Asset $asset)
    {
        // Delete image
        if ($asset->image) {
            Storage::disk('public')->delete($asset->image);
        }

        // Delete related QR codes
        $asset->qrCode()->delete();

        $asset->delete();

        return redirect()->route('assets.index')
            ->with('success', 'Aset berhasil dihapus!');
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
        $ids = $request->ids;

        foreach ($ids as $id) {
            $asset = Asset::find($id);
            if ($asset) {
                if ($asset->image) {
                    Storage::disk('public')->delete($asset->image);
                }
                $asset->qrCode()->delete();
                $asset->delete();
            }
        }

        return response()->json(['success' => true, 'message' => count($ids) . ' aset berhasil dihapus.']);
    }

    public function export(Request $request)
    {
        $format = $request->format ?? 'excel';
        $ids = $request->ids;

        $query = Asset::with('assetType');

        if ($ids) {
            $query->whereIn('id', $ids);
        }

        $assets = $query->get();

        // if ($format === 'pdf') {
        //     return $this->exportPdf($assets);
        // }

        // return $this->exportExcel($assets);
    }

    // private function exportPdf($assets)
    // {
    //     $pdf = \PDF::loadView('exports.assets-pdf', compact('assets'));
    //     return $pdf->download('laporan-aset.pdf');
    // }

    // private function exportExcel($assets)
    // {
    //     return \Excel::download(new \App\Exports\AssetsExport($assets), 'laporan-aset.xlsx');
    // }
}