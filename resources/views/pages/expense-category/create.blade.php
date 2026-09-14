@extends('layouts.app')

@section('title', 'Tambah Kategori Pengeluaran')

@section('content')
<div class="row">
    <div class="col-12 col-md-8 col-lg-6 mx-auto">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h3 class="card-title fw-bold text-dark mb-0">Tambah Kategori Pengeluaran</h3>
                <a href="{{ route('expense-category.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
            </div>
            <div class="card-body">
                <form action="{{ route('expense-category.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label required fw-semibold">Nama Kategori</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Perlengkapan Kantor & ATK" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kode Kategori <span class="text-muted fw-normal">(Opsional)</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Contoh: OPR-ATK (Otomatis dibuat jika kosong)">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi / Catatan</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Keterangan mengenai jenis pengeluaran ini...">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-check form-switch cursor-pointer">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                            <span class="form-check-label fw-semibold">Status Kategori Aktif</span>
                        </label>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('expense-category.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary fw-semibold px-4">Simpan Kategori</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
