@extends('layouts.app')

@section('title', 'Tambah Barang Baru')

@section('content')
<div class="row">
    <div class="col-12 col-md-8 col-lg-6 mx-auto">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h3 class="card-title fw-bold text-dark mb-0">Tambah Barang ke Katalog</h3>
                <a href="{{ route('item.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
            </div>
            <div class="card-body">
                <form action="{{ route('item.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label required fw-semibold">Nama Barang</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Kertas HVS A4 70gr" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kategori</label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required fw-semibold">Satuan Default</label>
                            <input type="text" name="unit" class="form-control @error('unit') is-invalid @enderror" value="{{ old('unit', 'pcs') }}" placeholder="Contoh: pcs, rim, box, unit" required>
                            @error('unit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Estimasi Harga Satuan (Rp)</label>
                            <input type="number" step="any" name="default_price" class="form-control @error('default_price') is-invalid @enderror" value="{{ old('default_price', 0) }}" placeholder="0">
                            @error('default_price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kode SKU / Barcode <span class="text-muted fw-normal">(Opsional)</span></label>
                        <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Otomatis dibuat jika kosong">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi / Spesifikasi</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Keterangan spesifikasi barang...">{{ old('description') }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-check form-switch cursor-pointer">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                            <span class="form-check-label fw-semibold">Barang Aktif (Dapat dipilih saat transaksi)</span>
                        </label>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('item.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary fw-semibold px-4">Simpan Barang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
