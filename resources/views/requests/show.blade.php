@extends('layouts.app')

@section('title', 'Detail Pengajuan Aset')

@section('content')
<div class="data-table-container">
    <div class="table-header">
        <h3 class="table-title">Detail Pengajuan Aset</h3>
        <a href="{{ route('requests.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
    
    <div class="modal-body">
        <div class="request-detail">
            <h4>Informasi Pengajuan</h4>
            
            <p><strong>ID Pengajuan:</strong> {{ $request->id }}</p>
            <p><strong>Pengaju:</strong> {{ $request->user->name }}</p>
            <p><strong>Aset yang Diminta:</strong> {{ $request->asset_name }}</p>
            <p><strong>Jenis Aset:</strong> 
                @switch($request->asset_type)
                    @case('ELK') Elektronik Umum @break
                    @case('KOM') Komputer & Periferal @break
                    @case('JAR') Jaringan @break
                    @case('FUR') Furniture @break
                    @case('ATK') Alat & Perkakas @break
                    @case('LAN') Lainnya @break
                    @default {{ $request->asset_type }}
                @endswitch
            </p>
            <p><strong>Jumlah:</strong> <span class="status-badge available">{{ $request->quantity }}</span></p>
            
            @if($request->estimated_price)
                <p><strong>Estimasi Harga:</strong> Rp {{ number_format($request->estimated_price, 0, ',', '.') }} per unit</p>
                <p><strong>Total Estimasi:</strong> Rp {{ number_format($request->estimated_price * $request->quantity, 0, ',', '.') }}</p>
            @endif
            
            @if($request->priority)
                <p><strong>Prioritas:</strong> 
                    <span class="status-badge {{ $request->priority === 'Urgent' ? 'maintenance' : ($request->priority === 'Tinggi' ? 'borrowed' : 'available') }}">
                        {{ $request->priority }}
                    </span>
                </p>
            @endif
            
            <p><strong>Tanggal Pengajuan:</strong> {{ \Carbon\Carbon::parse($request->request_date)->format('d F Y') }}</p>
            
            <p><strong>Status:</strong> 
                <span class="status-badge {{ $request->status === 'Pending' ? 'pending' : ($request->status === 'Disetujui' ? 'approved' : 'rejected') }}">
                    {{ $request->status }}
                </span>
            </p>
            
            <div style="margin-top: 1rem;">
                <p><strong>Alasan Pengajuan:</strong></p>
                <div style="background: var(--light-bg); padding: 1rem; border-radius: 8px;">
                    {{ $request->reason }}
                </div>
            </div>
            
            @if($request->status === 'Pending' && (auth()->user()->level === 'Admin' || auth()->user()->level === 'Keuangan'))
                <div class="btn-group" style="margin-top: 2rem;">
                    <form action="{{ route('requests.approve', $request->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success" 
                                onclick="return confirm('Apakah Anda yakin ingin menyetujui pengajuan ini?')">
                            Setujui
                        </button>
                    </form>
                    
                    <form action="{{ route('requests.reject', $request->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-danger" 
                                onclick="return confirm('Apakah Anda yakin ingin menolak pengajuan ini?')">
                            Tolak
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection