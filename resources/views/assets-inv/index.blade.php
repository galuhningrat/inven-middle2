@extends('layouts.app')

{{-- Menentukan Judul Halaman di Navbar --}}
@section('page-title', 'Manajemen Aset')

@section('content')
<div class="table-container data-table-wrapper" id="assetsContent">
    <div class="table-header">
        <h3 class="table-title">Manajemen Data Aset</h3>
        {{-- Tombol Tambah, Export, dll. --}}
        {{-- <a href="{{ route('assets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Aset
        </a> --}}
    </div>

    {{-- Tabel Data Aset --}}
    <table class="data-table" id="assetsTable">
        <thead>
            <tr>
                <th>ID Aset</th>
                <th>Nama Aset</th>
                <th>Jenis</th>
                <th>Lokasi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            {{-- @include('assets._table') Ini memuat konten dari file _table.blade.php --}}
        </tbody>
    </table>

    {{-- Pagination akan muncul di sini (jika menggunakan $assets->links()) --}}
    @if(isset($assets))
        <div class="table-footer">
            {{ $assets->links() }}
        </div>
    @endif
</div>
@endsection