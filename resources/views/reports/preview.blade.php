@extends('layouts.app')

@section('title', $title)
@section('page-title', $title)

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">{{ $title }}</h3>
            <div class="btn-group">
                <a href="{{ route('reports.export.pdf') }}?type={{ $type }}" class="btn btn-primary">Export PDF</a>
                <a href="{{ route('reports.export.excel') }}?type={{ $type }}" class="btn btn-secondary">Export Excel</a>
                <a href="{{ route('reports.index') }}" class="btn btn-secondary">← Kembali</a>
            </div>
        </div>
        <div style="padding: 2rem;">
            <!-- Report Header -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <img src="{{ asset('images/logo-stti.png') }}" alt="Logo" style="width: 80px; height: auto;">
                <h2 style="margin: 1rem 0 0.5rem;">{{ $title }}</h2>
                <p style="color: var(--text-secondary);">Sekolah Tinggi Teknologi Indonesia Cirebon</p>
                <p style="color: var(--text-secondary); font-size: 0.875rem;">Dicetak oleh: {{ $user->name }} | Tanggal:
                    {{ now()->format('d F Y H:i') }}</p>
            </div>

            @if($type === 'assets')
                <!-- Assets Report -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Aset</th>
                            <th>Nama</th>
                            <th>Jenis</th>
                            <th>Merek</th>
                            <th>Lokasi</th>
                            <th>Kondisi</th>
                            <th>Status</th>
                            <th>Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $index => $asset)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $asset->asset_id }}</td>
                                <td>{{ $asset->name }}</td>
                                <td>{{ $asset->assetType->name }}</td>
                                <td>{{ $asset->brand }}</td>
                                <td>{{ $asset->location }}</td>
                                <td>{{ $asset->condition }}</td>
                                <td>{{ $asset->status }}</td>
                                <td>Rp {{ number_format($asset->price, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="8" style="text-align: right;"><strong>Total Nilai Aset:</strong></td>
                            <td><strong>Rp {{ number_format($data->sum('price'), 0, ',', '.') }}</strong></td>
                        </tr>
                    </tfoot>
                </table>

            @elseif($type === 'borrowing')
                <!-- Borrowing Report -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID Pinjam</th>
                            <th>Aset</th>
                            <th>Peminjam</th>
                            <th>Jabatan</th>
                            <th>Tgl Pinjam</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $index => $borrowing)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $borrowing->borrowing_id }}</td>
                                <td>{{ $borrowing->asset->name }}</td>
                                <td>{{ $borrowing->borrower_name }}</td>
                                <td>{{ $borrowing->borrower_role }}</td>
                                <td>{{ $borrowing->borrow_date->format('d/m/Y') }}</td>
                                <td>{{ $borrowing->actual_return_date ? $borrowing->actual_return_date->format('d/m/Y') : $borrowing->return_date->format('d/m/Y') }}
                                </td>
                                <td>{{ $borrowing->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            @elseif($type === 'maintenance')
                <!-- Maintenance Report -->
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID</th>
                            <th>Aset</th>
                            <th>Jenis</th>
                            <th>Tanggal</th>
                            <th>Teknisi</th>
                            <th>Biaya</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $index => $maintenance)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $maintenance->maintenance_id }}</td>
                                <td>{{ $maintenance->asset->name }}</td>
                                <td>{{ $maintenance->type }}</td>
                                <td>{{ $maintenance->maintenance_date->format('d/m/Y') }}</td>
                                <td>{{ $maintenance->technician }}</td>
                                <td>Rp {{ number_format($maintenance->cost, 0, ',', '.') }}</td>
                                <td>{{ $maintenance->status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" style="text-align: right;"><strong>Total Biaya:</strong></td>
                            <td colspan="2"><strong>Rp {{ number_format($data->sum('cost'), 0, ',', '.') }}</strong></td>
                        </tr>
                    </tfoot>
                </table>

            @elseif($type === 'financial')
                <!-- Financial Report -->
                <div
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                    <div
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 12px; text-align: center;">
                        <h4 style="margin-bottom: 0.5rem; opacity: 0.9;">Total Aset</h4>
                        <p style="font-size: 2rem; font-weight: bold;">{{ $data['total_assets'] }}</p>
                    </div>
                    <div
                        style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 1.5rem; border-radius: 12px; text-align: center;">
                        <h4 style="margin-bottom: 0.5rem; opacity: 0.9;">Total Nilai Aset</h4>
                        <p style="font-size: 1.25rem; font-weight: bold;">Rp
                            {{ number_format($data['total_value'], 0, ',', '.') }}</p>
                    </div>
                    <div
                        style="background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); color: white; padding: 1.5rem; border-radius: 12px; text-align: center;">
                        <h4 style="margin-bottom: 0.5rem; opacity: 0.9;">Biaya Pemeliharaan</h4>
                        <p style="font-size: 1.25rem; font-weight: bold;">Rp
                            {{ number_format($data['maintenance_cost'], 0, ',', '.') }}</p>
                    </div>
                    <div
                        style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 1.5rem; border-radius: 12px; text-align: center;">
                        <h4 style="margin-bottom: 0.5rem; opacity: 0.9;">Rata-rata Nilai</h4>
                        <p style="font-size: 1.25rem; font-weight: bold;">Rp
                            {{ number_format($data['average_value'], 0, ',', '.') }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection