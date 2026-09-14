@extends('layouts.app')

@section('title', 'Kategori Pengeluaran')

@section('page_actions')
    @can('buat kategori')
        <button type="button" onclick="openCreateCategoryModal()" class="btn btn-primary d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
            Tambah Kategori
        </button>
    @endcan
@endsection

@section('content')
<div class="row g-3">

    @if(session('success'))
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3 border-bottom bg-light-subtle">
                <form action="{{ route('expense-category.index') }}" method="GET" class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 480px;">
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                            </span>
                            <input type="text" name="search" value="{{ $search }}" class="form-control border-start-0 ps-0" placeholder="Cari nama atau kode kategori...">
                            <button type="submit" class="btn btn-primary px-3 fw-semibold">Cari</button>
                        </div>
                        @if($search)
                            <a href="{{ route('expense-category.index') }}" class="btn btn-outline-secondary" title="Reset Pencarian">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Kode</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th class="text-center">Jumlah Barang</th>
                            <th class="text-center">Total Transaksi</th>
                            <th class="text-center">Status</th>
                            <th class="text-end" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $index => $cat)
                            <tr>
                                <td class="text-muted">{{ $categories->firstItem() + $index }}</td>
                                <td>
                                    <span class="badge bg-blue-lt fw-bold font-monospace">{{ $cat->code ?? '-' }}</span>
                                </td>
                                <td>
                                    <strong class="text-dark">{{ $cat->name }}</strong>
                                </td>
                                <td class="text-muted small">
                                    {{ Str::limit($cat->description ?? '-', 50) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary fw-semibold">{{ $cat->items_count }} item</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary fw-semibold">{{ $cat->expenses_count }}x</span>
                                </td>
                                <td class="text-center">
                                    @if($cat->is_active)
                                        <span class="badge bg-success-subtle text-success fw-bold">Aktif</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger fw-bold">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        @can('ubah kategori')
                                            <button type="button" class="btn btn-sm btn-outline-primary px-2 py-1 rounded-2 d-inline-flex align-items-center justify-content-center shadow-none" style="min-width: 32px; height: 32px;" onclick="openEditCategoryModal({{ $cat->id }})" title="Edit Kategori">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" /><path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" /></svg>
                                            </button>
                                        @endcan
                                        @can('hapus kategori')
                                            <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1 rounded-2 d-inline-flex align-items-center justify-content-center shadow-none" style="min-width: 32px; height: 32px;" onclick="deleteCategory({{ $cat->id }}, '{{ addslashes($cat->name) }}')" title="Hapus Kategori">
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
                                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted mb-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" /><path d="M12 9v4" /><path d="M12 16v.01" /></svg>
                                        <div class="fw-semibold">Belum ada data kategori pengeluaran</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($categories->hasPages())
                <div class="card-footer bg-white py-2">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- MODAL CATEGORY (CREATE & EDIT) -->
<div class="modal modal-blur fade" id="modal-category" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark" id="modal-category-title">Tambah Kategori Pengeluaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-category" autocomplete="off">
                @csrf
                <input type="hidden" id="category_id" name="id">
                <input type="hidden" id="category_form_mode" value="create">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required fw-semibold">Nama Kategori</label>
                        <input type="text" id="category_name" name="name" class="form-control" placeholder="Contoh: ATK & Perlengkapan Kantor" required>
                        <div class="invalid-feedback error_name"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kode Prefix (Opsional)</label>
                        <input type="text" id="category_code" name="code" class="form-control font-monospace" placeholder="Contoh: ATK, HARDWARE, KONSUMSI (Opsional)">
                        <div class="invalid-feedback error_code"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Deskripsi / Peruntukan</label>
                        <textarea id="category_description" name="description" class="form-control" rows="2" placeholder="Penjelasan singkat kategori..."></textarea>
                        <div class="invalid-feedback error_description"></div>
                    </div>

                    <div>
                        <label class="form-check form-switch cursor-pointer">
                            <input type="checkbox" id="category_is_active" name="is_active" value="1" class="form-check-input" checked>
                            <span class="form-check-label fw-semibold">Status Aktif (Tampil dalam pilihan kategori)</span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" id="btn-save-category" class="btn btn-primary d-inline-flex align-items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l5 5l10 -10" /></svg>
                        <span id="btn-save-category-text">Simpan Kategori</span>
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

    function resetCategoryErrors() {
        $('#form-category input, #form-category textarea').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }

    function openCreateCategoryModal() {
        resetCategoryErrors();
        $('#form-category')[0].reset();
        $('#category_form_mode').val('create');
        $('#category_id').val('');
        $('#category_is_active').prop('checked', true);
        $('#modal-category-title').text('Tambah Kategori Pengeluaran');
        $('#btn-save-category-text').text('Simpan Kategori');
        $('#modal-category').modal('show');
        setTimeout(() => $('#category_name').focus(), 400);
    }

    function openEditCategoryModal(id) {
        resetCategoryErrors();
        $('#category_form_mode').val('update');
        $('#category_id').val(id);
        $('#modal-category-title').text('Edit Kategori Pengeluaran');
        $('#btn-save-category-text').text('Perbarui Kategori');

        $.ajax({
            url: `/expense-categories/${id}/show`,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success' && res.data) {
                    const cat = res.data;
                    $('#category_name').val(cat.name);
                    $('#category_code').val(cat.code || '');
                    $('#category_description').val(cat.description || '');
                    $('#category_is_active').prop('checked', !!cat.is_active);

                    $('#modal-category').modal('show');
                }
            },
            error: function() {
                Toast.fire({
                    icon: 'error',
                    title: 'Gagal mengambil data kategori dari server'
                });
            }
        });
    }

    $('#form-category').on('submit', function(e) {
        e.preventDefault();
        resetCategoryErrors();

        const mode = $('#category_form_mode').val();
        const id   = $('#category_id').val();
        const url  = (mode === 'create') ? '{{ route("expense-category.store") }}' : `/expense-categories/${id}/update`;
        const method = (mode === 'create') ? 'POST' : 'PUT';

        const submitBtn = $('#btn-save-category');
        submitBtn.prop('disabled', true);

        const data = {
            _token: '{{ csrf_token() }}',
            name: $('#category_name').val(),
            code: $('#category_code').val(),
            description: $('#category_description').val(),
            is_active: $('#category_is_active').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: url,
            method: method,
            data: data,
            dataType: 'json',
            success: function(response) {
                $('#modal-category').modal('hide');
                Toast.fire({
                    icon: 'success',
                    title: response.message || 'Kategori berhasil disimpan'
                });
                setTimeout(() => window.location.reload(), 1000);
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false);
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        $(`#category_${field}`).addClass('is-invalid');
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

    function deleteCategory(id, name) {
        Swal.fire({
            title: 'Konfirmasi Hapus Kategori',
            html: `Apakah Anda yakin ingin menghapus kategori <strong>"${name}"</strong>?`,
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
                    url: `/expense-categories/${id}/destroy`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function(res) {
                        Toast.fire({
                            icon: 'success',
                            title: res.message || 'Kategori berhasil dihapus'
                        });
                        setTimeout(() => window.location.reload(), 1000);
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Gagal menghapus kategori'
                        });
                    }
                });
            }
        });
    }
</script>
@endpush
