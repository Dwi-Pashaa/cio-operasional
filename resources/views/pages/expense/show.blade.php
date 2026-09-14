@extends('layouts.app')

@section('title', 'Detail Pengeluaran ' . $expense->reference_no)

@section('page_actions')
<div class="d-flex gap-2">
    <a href="{{ route('expense.index') }}" class="btn btn-outline-secondary">Kembali</a>
    <a href="{{ route('expense.print', $expense->id) }}" target="_blank" class="btn btn-primary d-inline-flex align-items-center gap-1.5 shadow-sm fw-semibold">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
        @if($expense->payment_channel === 'xendit')
            Cetak Resi Transfer Xendit (PDF)
        @else
            Cetak Bukti Kas & Lampiran Nota (PDF)
        @endif
    </a>
</div>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-xl-10 mx-auto">

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Judul / Keperluan</div>
                        <div class="fs-3 fw-bold text-dark mb-2">{{ $expense->title }}</div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="text-muted small">Tanggal Transaksi:</div>
                                <div class="fw-semibold text-dark">{{ $expense->transaction_date->translatedFormat('d F Y') }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Kategori:</div>
                                <div class="fw-semibold text-dark">{{ $expense->category->name ?? 'Umum' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Vendor / Toko:</div>
                                <div class="fw-semibold text-dark">{{ $expense->vendor_name ?? '-' }}</div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Dibuat Oleh:</div>
                                <div class="fw-semibold text-dark">{{ $expense->user->name ?? 'Admin' }}</div>
                            </div>
                        </div>

                        @if($expense->notes)
                            <div class="p-3 bg-light rounded-2 border small text-muted">
                                <strong>Catatan:</strong> {{ $expense->notes }}
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6 border-start-md">
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small fw-semibold text-uppercase">Sumber Pembayaran</span>
                                @if($expense->payment_channel === 'xendit')
                                    <span class="badge bg-success text-white fw-bold d-inline-flex align-items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M13 3l0 7l6 0l-8 11l0 -7l-6 0z" /></svg>
                                        Saldo Xendit
                                    </span>
                                @else
                                    <span class="badge bg-primary text-white fw-bold d-inline-flex align-items-center gap-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M3 21l18 0" /><path d="M3 10l18 0" /><path d="M5 6l7 -3l7 3" /><path d="M4 10l0 11" /><path d="M20 10l0 11" /><path d="M8 14l0 3" /><path d="M12 14l0 3" /><path d="M16 14l0 3" /></svg>
                                        Saldo Manual Web
                                    </span>
                                @endif
                            </div>
                            @if($expense->payment_channel === 'xendit' && ($expense->bank_name || $expense->bank_code || $expense->account_number))
                                <div class="p-2 mb-2 bg-white rounded border small">
                                    <div class="text-muted fw-semibold">Transfer Tujuan:</div>
                                    <div class="fw-bold text-dark">{{ $expense->bank_name ?: $expense->bank_code }} &bull; Rek: {{ $expense->account_number }}</div>
                                    @if($expense->account_holder_name)
                                        <div class="text-muted">a.n {{ $expense->account_holder_name }}</div>
                                    @endif
                                </div>
                            @endif
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Total Belanja Barang:</span>
                                <span class="fw-semibold text-dark">Rp {{ number_format($expense->subtotal_amount, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Biaya Admin:</span>
                                <span class="fw-semibold {{ $expense->admin_fee > 0 ? 'text-danger' : 'text-muted' }}">
                                    {{ $expense->admin_fee > 0 ? '+Rp ' . number_format($expense->admin_fee, 0, ',', '.') : 'Rp 0' }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between pt-2">
                                <span class="fw-bold text-dark fs-5">Grand Total Dipotong:</span>
                                <span class="fw-bold text-primary fs-4 num-currency">Rp {{ number_format($expense->grand_total, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="small text-muted">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span>Status Transaksi:</span>
                                @if($expense->status === 'success')
                                    <span class="badge bg-success text-white fw-bold">Selesai / Sukses</span>
                                @elseif($expense->status === 'pending')
                                    <span class="badge bg-warning text-dark fw-bold">Sedang Diproses Xendit</span>
                                @elseif($expense->status === 'failed')
                                    <span class="badge bg-danger text-white fw-bold">Gagal</span>
                                @elseif($expense->status === 'refunded')
                                    <span class="badge bg-secondary text-white fw-bold">Direfund</span>
                                @endif
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Integrasi API Finance:</span>
                                <span class="badge bg-success-subtle text-success fw-bold">Tercatat & Dipotong</span>
                            </div>
                            <div class="font-monospace text-muted mt-1">Ref API: {{ $expense->finance_reference_id ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABEL RINCIAN ITEM -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-header bg-white py-3">
                <h4 class="card-title fw-bold text-dark mb-0">Rincian Barang / Jasa yang Dibeli</h4>
            </div>
            <div class="table-responsive">
                <table class="table table-vcenter card-table table-bordered">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Barang / Jasa</th>
                            <th class="text-center" style="width: 120px;">Qty</th>
                            <th class="text-center" style="width: 120px;">Satuan</th>
                            <th class="text-end" style="width: 200px;">Harga Satuan</th>
                            <th class="text-end" style="width: 200px;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expense->items as $idx => $item)
                            <tr>
                                <td class="text-muted text-center">{{ $idx + 1 }}</td>
                                <td class="fw-bold text-dark">{{ $item->item_name }}</td>
                                <td class="text-center fw-semibold">{{ $item->quantity }}</td>
                                <td class="text-center text-muted text-uppercase">{{ $item->unit }}</td>
                                <td class="text-end num-currency">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="text-end fw-bold text-dark num-currency">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <th colspan="5" class="text-end fw-bold">Total Belanja:</th>
                            <th class="text-end fw-bold text-primary fs-5 num-currency">Rp {{ number_format($expense->subtotal_amount, 0, ',', '.') }}</th>
                        </tr>
                        @if($expense->has_admin_fee && $expense->admin_fee > 0)
                            <tr>
                                <th colspan="5" class="text-end fw-bold text-danger">Biaya Admin:</th>
                                <th class="text-end fw-bold text-danger fs-5 num-currency">+Rp {{ number_format($expense->admin_fee, 0, ',', '.') }}</th>
                            </tr>
                            <tr>
                                <th colspan="5" class="text-end fw-bold fs-5">Grand Total:</th>
                                <th class="text-end fw-bold text-dark fs-4 num-currency">Rp {{ number_format($expense->grand_total, 0, ',', '.') }}</th>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- LAMPIRAN NOTA / STRUK -->
        @if($expense->attachment_receipt)
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h4 class="card-title fw-bold text-dark mb-0">Lampiran Bukti / Nota Pembayaran</h4>
                    <a href="{{ asset('storage/' . $expense->attachment_receipt) }}" target="_blank" class="btn btn-sm btn-outline-primary">Buka File</a>
                </div>
                <div class="card-body text-center p-4">
                    @php
                        $ext = pathinfo($expense->attachment_receipt, PATHINFO_EXTENSION);
                    @endphp
                    @if(in_array(strtolower($ext), ['jpg', 'jpeg', 'png']))
                        <img src="{{ asset('storage/' . $expense->attachment_receipt) }}" alt="Nota Pembayaran" class="img-fluid rounded border shadow-sm" style="max-height: 500px;">
                    @else
                        <div class="p-4 border rounded bg-light">
                            <p class="text-muted mb-2">Dokumen terlampir berformat PDF.</p>
                            <a href="{{ asset('storage/' . $expense->attachment_receipt) }}" target="_blank" class="btn btn-primary">Download / Lihat PDF</a>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
