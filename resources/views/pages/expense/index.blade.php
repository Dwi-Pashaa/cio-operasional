@extends('layouts.app')

@section('title', 'Pengeluaran Operasional')

@section('page_actions')
@can('buat pengeluaran')
    <a href="{{ route('expense.create') }}" class="btn btn-primary d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14" /><path d="M5 12l14 0" /></svg>
        Tambah Pengeluaran / Beli Barang
    </a>
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

    @if(session('error'))
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-3 border-bottom bg-light-subtle">
                <form action="{{ route('expense.index') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Cari Transaksi</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
                            </span>
                            <input type="text" name="search" value="{{ $search }}" class="form-control border-start-0 ps-0" placeholder="No. ref, judul, vendor...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">Kategori</label>
                        <select name="category_id" class="form-select">
                            <option value="">Semua Kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">Saluran Saldo</label>
                        <select name="channel" class="form-select">
                            <option value="">Semua Saluran</option>
                            <option value="manual" {{ $channel === 'manual' ? 'selected' : '' }}>Saldo Manual</option>
                            <option value="xendit" {{ $channel === 'xendit' ? 'selected' : '' }}>Saldo Xendit</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-semibold text-muted mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="form-control">
                    </div>
                    <div class="col-md-1 d-flex gap-1">
                        <button type="submit" class="btn btn-primary w-100 fw-semibold">Filter</button>
                        @if($search || $categoryId || $channel || $startDate || $endDate)
                            <a href="{{ route('expense.index') }}" class="btn btn-outline-secondary" title="Reset Filter">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4" /><path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4" /></svg>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-vcenter card-table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>No. Referensi</th>
                            <th>Tanggal</th>
                            <th>Judul & Keperluan</th>
                            <th>Kategori</th>
                            <th>Sumber Saldo</th>
                            <th class="text-end">Total Belanja</th>
                            <th class="text-end">Biaya Admin</th>
                            <th class="text-end">Grand Total</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $index => $exp)
                            <tr>
                                <td class="text-muted">{{ $expenses->firstItem() + $index }}</td>
                                <td>
                                    <span class="badge bg-blue-lt fw-bold font-monospace">{{ $exp->reference_no }}</span>
                                    <div class="text-muted small mt-0.5">{{ $exp->user->name ?? 'Admin' }}</div>
                                </td>
                                <td>
                                    <span class="text-dark fw-medium">{{ $exp->transaction_date->format('d/m/Y') }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $exp->title }}</div>
                                    @if($exp->vendor_name)
                                        <div class="text-muted small">Toko/Vendor: <span class="fw-medium text-dark">{{ $exp->vendor_name }}</span></div>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-dark fw-medium">
                                        {{ $exp->category->name ?? 'Umum' }}
                                    </span>
                                </td>
                                <td>
                                    @if($exp->payment_channel === 'xendit')
                                        <div>
                                            <span class="badge bg-emerald-subtle text-success fw-bold d-inline-flex align-items-center gap-1" style="background-color: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0z" /></svg>
                                                Saldo Xendit
                                            </span>
                                        </div>
                                        <div class="mt-1">
                                            @if($exp->status === 'pending')
                                                <span class="badge bg-warning text-dark fw-bold d-inline-flex align-items-center gap-1 shadow-none" style="font-size: 11px;">
                                                    <span class="spinner-grow spinner-grow-sm" style="width: 7px; height: 7px;" role="status"></span>
                                                    Sedang Diproses Xendit
                                                </span>
                                            @elseif($exp->status === 'success')
                                                <span class="badge bg-success text-white fw-bold d-inline-flex align-items-center gap-1 shadow-none" style="font-size: 11px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                    Transfer Berhasil
                                                </span>
                                            @elseif($exp->status === 'failed')
                                                <span class="badge bg-danger text-white fw-bold d-inline-flex align-items-center gap-1 shadow-none" style="font-size: 11px;">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                                    Transfer Gagal
                                                </span>
                                            @elseif($exp->status === 'refunded')
                                                <span class="badge bg-secondary text-white fw-bold d-inline-flex align-items-center gap-1 shadow-none" style="font-size: 11px;">
                                                    Dana Direfund
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <div>
                                            <span class="badge bg-primary-subtle text-primary fw-bold d-inline-flex align-items-center gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M3 10l18 0" /><path d="M5 6l7 -3l7 3" /><path d="M4 10l0 11" /><path d="M20 10l0 11" /><path d="M8 14l0 3" /><path d="M12 14l0 3" /><path d="M16 14l0 3" /></svg>
                                                Saldo Manual
                                            </span>
                                        </div>
                                        <div class="mt-1 d-flex flex-wrap gap-1">
                                            <span class="badge bg-light text-primary fw-bold border d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                Kas Selesai
                                            </span>
                                            @if($exp->attachment_receipt)
                                                <span class="badge bg-light text-muted border d-inline-flex align-items-center gap-0.5" style="font-size: 10px;" title="Nota/Kuitansi Fisik Terlampir">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /></svg>
                                                    Nota
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end fw-medium text-muted num-currency">
                                    Rp {{ number_format($exp->subtotal_amount, 0, ',', '.') }}
                                </td>
                                <td class="text-end num-currency">
                                    @if($exp->has_admin_fee && $exp->admin_fee > 0)
                                        <span class="text-danger fw-semibold">+Rp {{ number_format($exp->admin_fee, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-dark fs-6 num-currency">
                                    Rp {{ number_format($exp->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <a href="{{ route('expense.show', $exp->id) }}" class="btn btn-sm btn-outline-primary px-2 py-1 rounded-2 d-inline-flex align-items-center justify-content-center shadow-none" style="min-width: 32px; height: 32px;" title="Lihat Detail Transaksi">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" /></svg>
                                        </a>
                                        <a href="{{ route('expense.print', $exp->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary px-2 py-1 rounded-2 d-inline-flex align-items-center justify-content-center shadow-none" style="min-width: 32px; height: 32px;" title="Cetak Bukti PDF (DomPDF)">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                                        </a>
                                        @can('hapus pengeluaran')
                                            <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1 rounded-2 d-inline-flex align-items-center justify-content-center shadow-none" style="min-width: 32px; height: 32px;" onclick="deleteExpense({{ $exp->id }}, '{{ $exp->reference_no }}')" title="Hapus & Kembalikan Saldo">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" /><path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" /><path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" /></svg>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted mb-2"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 8v-3a1 1 0 0 0 -1 -1h-10a2 2 0 0 0 0 4h12a1 1 0 0 1 1 1v3m0 4v3a1 1 0 0 1 -1 1h-12a2 2 0 0 1 -2 -2v-12" /><path d="M20 12v4h-4a2 2 0 0 1 0 -4h4" /></svg>
                                        <div class="fw-semibold">Belum ada data pengeluaran operasional</div>
                                        <div class="small text-muted">Klik tombol "Tambah Pengeluaran" di atas untuk mencatat pembelian baru.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($expenses->hasPages())
                <div class="card-footer bg-white py-2">
                    {{ $expenses->links() }}
                </div>
            @endif
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

    function deleteExpense(id, refNo) {
        Swal.fire({
            title: 'Konfirmasi Hapus Pengeluaran',
            html: `Apakah Anda yakin ingin menghapus transaksi <strong>${refNo}</strong>?<br><span class="text-danger small">Saldo Finance yang telah terpotong akan otomatis dikembalikan (Auto-Refund).</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Hapus & Refund',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/expenses/${id}/destroy`,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function(res) {
                        Toast.fire({
                            icon: 'success',
                            title: res.message || 'Transaksi berhasil dihapus'
                        });
                        setTimeout(() => window.location.reload(), 1200);
                    },
                    error: function(xhr) {
                        Toast.fire({
                            icon: 'error',
                            title: xhr.responseJSON?.message || 'Gagal menghapus data pengeluaran'
                        });
                    }
                });
            }
        });
    }
</script>
@endpush
