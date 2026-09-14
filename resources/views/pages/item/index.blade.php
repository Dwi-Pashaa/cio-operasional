@extends('layouts.app')

@section('title', 'Data Barang / Katalog Item')

@section('page_actions')
    @can('buat barang')
        <button type="button" onclick="openCreateItemModal()" class="btn btn-primary d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
            Tambah Barang Baru
        </button>
    @endcan
@endsection

@section('content')
<div class="row g-3">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3 border-bottom bg-light-subtle">
                <form action="{{ route('item.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-3">
                    <div class="input-group" style="min-width: 280px; max-width: 380px; flex: 1;">
                        <span class="input-group-text bg-white text-muted border-end-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                        </span>
                        <input type="text" name="search" value="{{ $search }}" class="form-control border-start-0 ps-0" placeholder="Cari nama atau kode barang...">
                    </div>
                    <div style="min-width: 200px;">
                        <select name="category_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Semua Kategori --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary px-3 fw-semibold">Filter</button>
                        @if($search || $categoryId)
                            <a href="{{ route('item.index') }}" class="btn btn-outline-secondary ms-1" title="Reset Filter">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Kode SKU</th>
                            <th>Nama Barang</th>
                            <th>Kategori</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-end">Estimasi Harga</th>
                            <th class="text-center">Status</th>
                            <th class="text-end" style="width: 130px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $index => $item)
                            <tr>
                                <td class="text-muted">{{ $items->firstItem() + $index }}</td>
                                <td>
                                    <span class="badge bg-purple-lt fw-bold font-monospace">{{ $item->code ?? '-' }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->name }}</div>
                                    @if($item->description)
                                        <div class="text-muted small">{{ Str::limit($item->description, 45) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-blue-subtle text-primary fw-medium">{{ $item->category->name ?? 'Umum' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary fw-bold text-uppercase">{{ $item->unit }}</span>
                                </td>
                                <td class="text-end fw-bold text-dark num-currency">
                                    Rp {{ number_format($item->default_price, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    @if($item->is_active)
                                        <span class="badge bg-success-subtle text-success fw-bold">Aktif</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger fw-bold">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        @can('ubah barang')
                                            <button type="button" class="btn btn-sm btn-outline-primary px-2 py-1 rounded-2 d-inline-flex align-items-center justify-content-center shadow-none" style="min-width: 32px; height: 32px;" onclick="openEditItemModal({{ $item->id }})" title="Edit Barang">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /></svg>
                                            </button>
                                        @endcan
                                        @can('hapus barang')
                                            <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1 rounded-2 d-inline-flex align-items-center justify-content-center shadow-none" style="min-width: 32px; height: 32px;" onclick="deleteItem({{ $item->id }}, '{{ addslashes($item->name) }}')" title="Hapus Barang">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted mb-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 3l8 4.5l0 9l-8 4.5l-8 -4.5l0 -9l8 -4.5" /><path d="M12 12l8 -4.5" /><path d="M12 12l0 9" /><path d="M12 12l-8 -4.5" /></svg>
                                        <div class="fw-semibold">Belum ada barang di katalog</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->hasPages())
                <div class="card-footer bg-white py-2">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL ITEM (CREATE & EDIT) -->
<div class="modal modal-blur fade" id="modal-item" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark" id="modal-item-title">Tambah Barang Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-item" autocomplete="off">
                @csrf
                <input type="hidden" id="item_id" name="id">
                <input type="hidden" id="form_mode" value="create">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required fw-semibold">Nama Barang</label>
                        <input type="text" id="item_name" name="name" class="form-control" placeholder="Contoh: Kertas HVS A4 70gr" required>
                        <div class="invalid-feedback error_name"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kategori Barang</label>
                        <select id="item_category_id" name="category_id" class="form-select">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback error_category_id"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required fw-semibold">Satuan</label>
                            <input type="text" id="item_unit" name="unit" class="form-control" list="unitsList" placeholder="pcs, unit, box, rim..." required>
                            <datalist id="unitsList">
                                <option value="pcs">
                                <option value="unit">
                                <option value="box">
                                <option value="rim">
                                <option value="pak">
                                <option value="roll">
                                <option value="botol">
                                <option value="liter">
                                <option value="meter">
                                <option value="kg">
                                <option value="lembar">
                                <option value="paket">
                                <option value="bulan">
                            </datalist>
                            <div class="invalid-feedback error_unit"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold">Estimasi Harga (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-medium">Rp</span>
                                <input type="number" id="item_default_price" name="default_price" class="form-control text-end" placeholder="0" min="0">
                            </div>
                            <div class="invalid-feedback error_default_price"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kode SKU / Barcode (Opsional)</label>
                        <input type="text" id="item_code" name="code" class="form-control font-monospace" placeholder="Kosongkan untuk auto-generate">
                        <div class="invalid-feedback error_code"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi / Spesifikasi</label>
                        <textarea id="item_description" name="description" class="form-control" rows="2" placeholder="Catatan spesifikasi atau detail barang..."></textarea>
                        <div class="invalid-feedback error_description"></div>
                    </div>

                    <div>
                        <label class="form-check form-switch cursor-pointer">
                            <input type="checkbox" id="item_is_active" name="is_active" value="1" class="form-check-input" checked>
                            <span class="form-check-label fw-semibold">Status Aktif (Dapat dipilih saat input pengeluaran)</span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btn-save-item" class="btn btn-primary d-inline-flex align-items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                        <span id="btn-save-text">Simpan Data</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    function resetItemErrors() {
        $('#form-item input, #form-item select, #form-item textarea').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    function openCreateItemModal() {
        resetItemErrors();
        $('#form-item')[0].reset();
        $('#form_mode').val('create');
        $('#item_id').val('');
        $('#item_is_active').prop('checked', true);
        $('#modal-item-title').text('Tambah Barang Baru ke Katalog');
        $('#btn-save-text').text('Simpan Barang');
        $('#modal-item').modal('show');
        setTimeout(() => $('#item_name').focus(), 400);
    }

    function openEditItemModal(id) {
        resetItemErrors();
        $('#form_mode').val('update');
        $('#item_id').val(id);
        $('#modal-item-title').text('Edit Data Barang Katalog');
        $('#btn-save-text').text('Perbarui Data');

        $.ajax({
            url: `/items/${id}/show`,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success' && res.data) {
                    const item = res.data;
                    $('#item_name').val(item.name);
                    $('#item_category_id').val(item.category_id || '');
                    $('#item_unit').val(item.unit || 'pcs');
                    $('#item_default_price').val(item.default_price || 0);
                    $('#item_code').val(item.code || '');
                    $('#item_description').val(item.description || '');
                    $('#item_is_active').prop('checked', !!item.is_active);

                    $('#modal-item').modal('show');
                }
            },
            error: function() {
                Toast.fire({
                    icon: 'error',
                    title: 'Gagal mengambil data barang dari server'
                });
            }
        });
    }

    $('#form-item').on('submit', function(e) {
        e.preventDefault();
        resetItemErrors();

        const mode = $('#form_mode').val();
        const id   = $('#item_id').val();
        const url  = (mode === 'create') ? '{{ route("item.store") }}' : `/items/${id}/update`;
        const method = (mode === 'create') ? 'POST' : 'PUT';

        const submitBtn = $('#btn-save-item');
        submitBtn.prop('disabled', true);

        const data = {
            _token: '{{ csrf_token() }}',
            name: $('#item_name').val(),
            category_id: $('#item_category_id').val(),
            unit: $('#item_unit').val(),
            default_price: $('#item_default_price').val(),
            code: $('#item_code').val(),
            description: $('#item_description').val(),
            is_active: $('#item_is_active').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: url,
            method: method,
            data: data,
            dataType: 'json',
            success: function(response) {
                $('#modal-item').modal('hide');
                Toast.fire({
                    icon: 'success',
                    title: response.message || 'Data barang berhasil disimpan'
                });
                setTimeout(() => window.location.reload(), 1000);
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false);
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        $(`#item_${field}`).addClass('is-invalid');
                        $(`.error_${field}`).text(messages[0]);
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON?.message || 'Terjadi kesalahan sistem'
                    });
                }
            }
        });
    });

    function deleteItem(id, name) {
        Swal.fire({
            title: 'Konfirmasi Hapus Barang',
            html: `Apakah Anda yakin ingin menghapus barang <strong>"${name}"</strong> dari katalog?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/items/${id}/destroy`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function(res) {
                        Toast.fire({
                            icon: 'success',
                            title: res.message || 'Barang berhasil dihapus'
                        });
                        setTimeout(() => window.location.reload(), 1000);
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Gagal menghapus barang'
                        });
                    }
                });
            }
        });
    }
</script>
@endpush
