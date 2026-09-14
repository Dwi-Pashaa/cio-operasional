<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $expense->payment_channel === 'xendit' ? 'Resi_Xendit_' : 'Bukti_Kas_' }}{{ $expense->reference_no }}</title>
    <style>
        @page {
            margin: 20px 25px 25px 25px;
            size: a4 portrait;
        }
        * {
            box-sizing: border-box;
            -webkit-box-sizing: border-box;
            font-family: 'Helvetica', 'DejaVu Sans', Arial, sans-serif;
        }
        body {
            font-size: 11px;
            line-height: 1.4;
            color: #1e293b;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        
        /* Utility */
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .text-muted { color: #64748b; }
        .text-success { color: #059669; }
        .text-primary { color: #2563eb; }
        .text-danger { color: #dc2626; }
        .text-warning { color: #d97706; }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }

        /* ------------------------------------------------------------- */
        /* XENDIT RECEIPT HEADER & STYLES */
        /* ------------------------------------------------------------- */
        .xendit-header-box {
            width: 100%;
            background-color: #059669;
            color: #ffffff;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 15px;
        }
        .xendit-header-box.pending {
            background-color: #d97706;
        }
        .xendit-header-box.failed {
            background-color: #dc2626;
        }
        .xendit-header-title {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .xendit-header-sub {
            font-size: 10px;
            opacity: 0.9;
            margin-top: 2px;
        }
        .xendit-badge {
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.25);
            border: 1px solid #ffffff;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
        }

        /* Highlight Box */
        .amount-highlight {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 14px;
        }
        .amount-num {
            font-size: 20px;
            font-weight: bold;
            color: #0f172a;
        }

        /* ------------------------------------------------------------- */
        /* MANUAL CASH VOUCHER HEADER & STYLES */
        /* ------------------------------------------------------------- */
        .manual-header-table {
            width: 100%;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }
        .manual-title {
            font-size: 17px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .manual-subtitle {
            font-size: 10px;
            color: #64748b;
        }

        /* ------------------------------------------------------------- */
        /* INFO TABLE */
        /* ------------------------------------------------------------- */
        .info-table {
            width: 100%;
            margin-bottom: 14px;
        }
        .info-table td {
            padding: 4px 2px;
            vertical-align: top;
            font-size: 10.5px;
        }
        .info-label {
            color: #64748b;
            font-weight: bold;
            width: 22%;
        }
        .info-sep {
            width: 2%;
            color: #94a3b8;
        }
        .info-val {
            color: #0f172a;
            width: 26%;
        }

        /* ------------------------------------------------------------- */
        /* ITEMS TABLE */
        /* ------------------------------------------------------------- */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .items-table th {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 10px;
            font-weight: bold;
            color: #334155;
            text-transform: uppercase;
        }
        .items-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 10.5px;
        }
        .items-table tfoot td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            font-size: 10.5px;
        }

        /* ------------------------------------------------------------- */
        /* LAMPIRAN NOTA FISIK */
        /* ------------------------------------------------------------- */
        .attachment-box {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 10px;
            margin-top: 15px;
            background-color: #fafafa;
            page-break-inside: avoid;
        }
        .attachment-title {
            font-size: 11px;
            font-weight: bold;
            color: #334155;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .attachment-img-wrap {
            text-align: center;
            padding: 5px;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }
        .attachment-img {
            max-width: 95%;
            max-height: 380px;
        }

        /* ------------------------------------------------------------- */
        /* TANDA TANGAN */
        /* ------------------------------------------------------------- */
        .signature-table {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .signature-table td {
            vertical-align: top;
            text-align: center;
            font-size: 10.5px;
        }
        .sig-space {
            height: 50px;
        }

        /* FOOTER DIGITAL */
        .digital-footer {
            margin-top: 15px;
            padding: 8px 12px;
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 4px;
            font-size: 9.5px;
            color: #64748b;
            line-height: 1.35;
        }
    </style>
</head>
<body>

@if($expense->payment_channel === 'xendit')
    <!-- ================================================================= -->
    <!-- 1. RESI TRANSFER ELEKTRONIK XENDIT (DOMPDF)                       -->
    <!-- ================================================================= -->
    @php
        $isPending = ($expense->status === 'pending');
        $isSuccess = ($expense->status === 'success');
        $isFailed  = ($expense->status === 'failed');
        $headerClass = $isPending ? 'pending' : ($isFailed ? 'failed' : '');
        $disbId = $expense->xendit_disbursement_id ?: ($expense->finance_response['xendit_disbursement']['id'] ?? '-');
    @endphp

    <div class="xendit-header-box {{ $headerClass }}">
        <table style="width: 100%;">
            <tr>
                <td style="width: 65%;">
                    <div class="xendit-header-title">BUKTI TRANSFER ELEKTRONIK</div>
                    <div class="xendit-header-sub">CIO NETWORK SOLUTION &bull; XENDIT DISBURSEMENT GATEWAY</div>
                </td>
                <td class="text-right" style="width: 35%;">
                    <span class="xendit-badge">
                        @if($isSuccess)
                            TRANSFER BERHASIL
                        @elseif($isPending)
                            SEDANG DIPROSES
                        @elseif($isFailed)
                            TRANSFER GAGAL
                        @else
                            {{ strtoupper($expense->status) }}
                        @endif
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Amount Highlight Box -->
    <div class="amount-highlight">
        <table style="width: 100%;">
            <tr>
                <td>
                    <div class="text-muted" style="font-size: 10px; text-transform: uppercase; font-weight: bold;">Total Dana Ditransfer:</div>
                    <div class="amount-num">Rp {{ number_format($expense->grand_total, 0, ',', '.') }}</div>
                </td>
                <td class="text-right">
                    <div class="text-muted" style="font-size: 10px; text-transform: uppercase; font-weight: bold;">Metode Pembayaran:</div>
                    <div class="fw-bold text-success" style="font-size: 12px; margin-top: 3px;">Saldo Xendit (Payment Gateway)</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Info Detail Grid -->
    <table class="info-table">
        <tr>
            <td class="info-label">No. Referensi</td>
            <td class="info-sep">:</td>
            <td class="info-val font-mono fw-bold text-primary">{{ $expense->reference_no }}</td>

            <td class="info-label">Bank Tujuan</td>
            <td class="info-sep">:</td>
            <td class="info-val fw-bold">{{ $expense->bank_name ?: ($expense->bank_code ?: 'Bank Transfer') }}</td>
        </tr>
        <tr>
            <td class="info-label">ID Xendit Payout</td>
            <td class="info-sep">:</td>
            <td class="info-val font-mono" style="font-size: 10px;">{{ $disbId }}</td>

            <td class="info-label">Nomor Rekening</td>
            <td class="info-sep">:</td>
            <td class="info-val font-mono fw-bold">{{ $expense->account_number ?: '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Waktu Transaksi</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $expense->created_at->translatedFormat('d F Y, H:i') }} WIB</td>

            <td class="info-label">Pemilik Rekening</td>
            <td class="info-sep">:</td>
            <td class="info-val fw-bold">{{ $expense->account_holder_name ?: ($expense->vendor_name ?: '-') }}</td>
        </tr>
        <tr>
            <td class="info-label">Keperluan / Judul</td>
            <td class="info-sep">:</td>
            <td class="info-val fw-bold">{{ $expense->title }}</td>

            <td class="info-label">Kategori</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $expense->category->name ?? 'Operasional Umum' }}</td>
        </tr>
        <tr>
            <td class="info-label">Dicatat Oleh</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $expense->user->name ?? 'Staf Operasional' }}</td>

            <td class="info-label">Ref API Finance</td>
            <td class="info-sep">:</td>
            <td class="info-val font-mono">{{ $expense->finance_reference_id ?? $expense->reference_no }}</td>
        </tr>
    </table>

    <!-- Rincian Item Barang / Jasa -->
    <div class="fw-bold" style="font-size: 11px; color: #1e293b; margin-bottom: 6px;">Rincian Item Pembelian:</div>
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th class="text-left">Nama Barang / Jasa</th>
                <th class="text-center" style="width: 45px;">Qty</th>
                <th class="text-center" style="width: 55px;">Satuan</th>
                <th class="text-right" style="width: 110px;">Harga Satuan</th>
                <th class="text-right" style="width: 120px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($expense->items as $idx => $item)
                <tr>
                    <td class="text-center text-muted">{{ $idx + 1 }}</td>
                    <td class="fw-bold">{{ $item->item_name }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-center">{{ strtoupper($item->unit) }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right fw-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right fw-bold">Subtotal Pembelian:</td>
                <td class="text-right fw-bold">Rp {{ number_format($expense->subtotal_amount, 0, ',', '.') }}</td>
            </tr>
            @if($expense->has_admin_fee && $expense->admin_fee > 0)
                <tr>
                    <td colspan="5" class="text-right fw-bold text-danger">Biaya Admin Transfer:</td>
                    <td class="text-right fw-bold text-danger">+Rp {{ number_format($expense->admin_fee, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr style="background-color: #f8fafc;">
                <td colspan="5" class="text-right fw-bold" style="font-size: 11.5px;">TOTAL DANA DITRANSFER:</td>
                <td class="text-right fw-bold text-success" style="font-size: 12px;">Rp {{ number_format($expense->grand_total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="digital-footer">
        <strong>Verifikasi Resi Elektronik:</strong> Transaksi ini dikirim dan diverifikasi secara langsung melalui Payment Gateway <strong>Xendit Disbursement Indonesia</strong> terintegrasi dengan <strong>CIO Central Finance</strong>. Dokumen ini adalah tanda bukti transfer digital resmi yang sah dan tidak memerlukan tanda tangan basah.
    </div>

@else
    <!-- ================================================================= -->
    <!-- 2. BUKTI PENGELUARAN KAS MANUAL (DOMPDF CASH VOUCHER & NOTA)      -->
    <!-- ================================================================= -->
    <table class="manual-header-table">
        <tr>
            <td>
                <div class="manual-title">Bukti Pengeluaran Kas Operasional</div>
                <div class="manual-subtitle">CIO NETWORK SOLUTION &bull; CASH & OPERATIONAL VOUCHER</div>
            </td>
            <td class="text-right">
                <div class="font-mono fw-bold text-primary" style="font-size: 13px;">{{ $expense->reference_no }}</div>
                <div class="text-muted" style="font-size: 10px; margin-top: 2px;">Tanggal: {{ $expense->transaction_date->translatedFormat('d F Y') }}</div>
            </td>
        </tr>
    </table>

    <table class="info-table">
        <tr>
            <td class="info-label">Keperluan / Judul</td>
            <td class="info-sep">:</td>
            <td class="info-val fw-bold" style="width: 40%;">{{ $expense->title }}</td>

            <td class="info-label">Kategori</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $expense->category->name ?? 'Operasional Umum' }}</td>
        </tr>
        <tr>
            <td class="info-label">Toko / Vendor</td>
            <td class="info-sep">:</td>
            <td class="info-val fw-bold">{{ $expense->vendor_name ?: ($expense->account_holder_name ?: 'Pembelian Langsung') }}</td>

            <td class="info-label">Sumber Dana</td>
            <td class="info-sep">:</td>
            <td class="info-val fw-bold text-primary">Saldo Kas / Manual Web</td>
        </tr>
        <tr>
            <td class="info-label">Pemohon / Pengaju</td>
            <td class="info-sep">:</td>
            <td class="info-val">{{ $expense->user->name ?? 'Staf Operasional' }}</td>

            <td class="info-label">Status Transaksi</td>
            <td class="info-sep">:</td>
            <td class="info-val fw-bold text-success">LUNAS (KAS TERPOTONG)</td>
        </tr>
        @if($expense->notes)
            <tr>
                <td class="info-label">Catatan</td>
                <td class="info-sep">:</td>
                <td class="info-val text-muted" colspan="4">{{ $expense->notes }}</td>
            </tr>
        @endif
    </table>

    <!-- Rincian Item Barang / Jasa -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th class="text-left">Nama Barang / Jasa</th>
                <th class="text-center" style="width: 45px;">Qty</th>
                <th class="text-center" style="width: 55px;">Satuan</th>
                <th class="text-right" style="width: 110px;">Harga Satuan</th>
                <th class="text-right" style="width: 120px;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($expense->items as $idx => $item)
                <tr>
                    <td class="text-center text-muted">{{ $idx + 1 }}</td>
                    <td class="fw-bold">{{ $item->item_name }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-center">{{ strtoupper($item->unit) }}</td>
                    <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-right fw-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-right fw-bold">Total Pembelian Barang:</td>
                <td class="text-right fw-bold">Rp {{ number_format($expense->subtotal_amount, 0, ',', '.') }}</td>
            </tr>
            @if($expense->has_admin_fee && $expense->admin_fee > 0)
                <tr>
                    <td colspan="5" class="text-right fw-bold text-danger">Biaya Lain-lain / Admin:</td>
                    <td class="text-right fw-bold text-danger">+Rp {{ number_format($expense->admin_fee, 0, ',', '.') }}</td>
                </tr>
            @endif
            <tr style="background-color: #f8fafc;">
                <td colspan="5" class="text-right fw-bold" style="font-size: 11.5px;">GRAND TOTAL PENGELUARAN KAS:</td>
                <td class="text-right fw-bold text-primary" style="font-size: 12px;">Rp {{ number_format($expense->grand_total, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Lampiran Nota Fisik -->
    @if(!empty($attachmentBase64))
        <div class="attachment-box">
            <div class="attachment-title">Lampiran Dokumen / Foto Nota Pembelian Fisik Asli:</div>
            <div class="attachment-img-wrap">
                <img src="{{ $attachmentBase64 }}" class="attachment-img" alt="Nota Pembelian">
            </div>
        </div>
    @elseif($expense->attachment_receipt)
        <div class="attachment-box">
            <div class="attachment-title">Lampiran Dokumen:</div>
            <div class="text-muted" style="padding: 6px; font-size: 10px;">
                Dokumen Nota Terlampir: <strong>{{ basename($expense->attachment_receipt) }}</strong>
            </div>
        </div>
    @endif

    <!-- Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td style="width: 35%;">
                <div>Pemohon / Yang Membeli:</div>
                <div class="sig-space"></div>
                <div class="fw-bold">({{ $expense->user->name ?? 'Staf Pemohon' }})</div>
            </td>
            <td style="width: 30%;"></td>
            <td style="width: 35%;">
                <div>Disetujui / Finance Kasir:</div>
                <div class="sig-space"></div>
                <div class="fw-bold">(_______________________)</div>
            </td>
        </tr>
    </table>
@endif

</body>
</html>
