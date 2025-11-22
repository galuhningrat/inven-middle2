@extends('layouts.app')

@section('title', 'Pemeliharaan')
@section('page-title', 'Pemeliharaan Aset')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Pemeliharaan Aset</h3>
            <a href="{{ route('maintenances.create') }}" class="btn btn-primary">+ Catat Pemeliharaan</a>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID Maintenance</th>
                        <th>Aset</th>
                        <th>Jenis Pemeliharaan</th>
                        <th>Tanggal</th>
                        <th>Biaya</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($maintenances as $maintenance)
                        <tr>
                            <td><strong>{{ $maintenance->maintenance_id }}</strong></td>
                            <td>{{ $maintenance->asset->name }}</td>
                            <td>{{ $maintenance->type }}</td>
                            <td>{{ $maintenance->maintenance_date->format('d M Y') }}</td>
                            <td><span class="price-display">Rp {{ number_format($maintenance->cost, 0, ',', '.') }}</span></td>
                            <td>
                                <span
                                    class="status-badge {{ $maintenance->status === 'Selesai' ? 'available' : 'maintenance' }}">
                                    {{ $maintenance->status }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('maintenances.show', $maintenance) }}" class="btn btn-secondary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">Tidak ada data pemeliharaan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem 2rem;">
            {{ $maintenances->links() }}
        </div>
    </div>
@endsection