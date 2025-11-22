@extends('layouts.app')

@section('title', 'QR Code')
@section('page-title', 'Manajemen QR Code')

@section('content')
    <div class="data-table-container">
        <div class="table-header">
            <h3 class="table-title">Manajemen QR Code</h3>
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" class="search-input" id="qrSearch" placeholder="Cari QR Code..."
                    value="{{ request('search') }}">
            </div>
            <a href="{{ route('qrcodes.export-pdf') }}" class="btn btn-primary">Export Semua PDF</a>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID QR</th>
                        <th>Aset</th>
                        <th>Kode</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($qrCodes as $qr)
                        <tr>
                            <td><strong>{{ $qr->qr_code_id }}</strong></td>
                            <td>{{ $qr->asset->name ?? 'N/A' }}</td>
                            <td><code
                                    style="background: var(--background-color); padding: 0.25rem 0.5rem; border-radius: 4px;">{{ $qr->code_content }}</code>
                            </td>
                            <td>
                                <span class="status-badge {{ $qr->status === 'Aktif' ? 'available' : 'maintenance' }}">
                                    {{ $qr->status }}
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <form action="{{ route('qrcodes.toggle-status', $qr) }}" method="POST"
                                        style="display: inline;">
                                        @csrf
                                        <button type="submit"
                                            class="btn {{ $qr->status === 'Aktif' ? 'btn-warning' : 'btn-success' }}">
                                            {{ $qr->status === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                    <a href="{{ route('qrcodes.print', $qr) }}" class="btn btn-secondary"
                                        target="_blank">Cetak</a>
                                    <a href="{{ route('qrcodes.show', $qr) }}" class="btn btn-primary">Detail</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem;">Tidak ada data QR Code.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding: 1rem 2rem;">
            {{ $qrCodes->links() }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('qrSearch').addEventListener('input', function () {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                const params = new URLSearchParams();
                if (this.value) params.append('search', this.value);
                window.location.href = '{{ route('qrcodes.index') }}?' + params.toString();
            }, 500);
        });
    </script>
@endpush