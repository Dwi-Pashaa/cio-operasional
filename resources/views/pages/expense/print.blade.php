<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if($expense->payment_channel === 'xendit')
            Resi Transfer Xendit - {{ $expense->reference_no }}
        @else
            Bukti Pengeluaran Kas - {{ $expense->reference_no }}
        @endif
    </title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #1e293b;
            margin: 0;
            padding: 30px;
            font-size: 13px;
            background-color: #ffffff;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .no-print {
            margin-bottom: 24px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            background-color: #2563eb;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            box-shadow: 0 2px 4px rgba(37,99,235,0.2);
        }
        .btn-print:hover {
            background-color: #1d4ed8;
        }

        /* ------------------------------------------------------------- */
        /* STIL RESI XENDIT (DIGITAL RECEIPT) */
        /* ------------------------------------------------------------- */
        .xendit-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.04);
            margin-bottom: 25px;
        }
        .xendit-header {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: #ffffff;
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .xendit-header.pending {
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
        }
        .xendit-header.failed {
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
        }
        .xendit-title {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }
        .xendit-subtitle {
            font-size: 12px;
            opacity: 0.9;
            margin-top: 4px;
        }
        .xendit-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            padding: 8px 14px;
            border-radius: 30px;
            font-weight: 700;
            font-size: 12px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .xendit-body {
            padding: 24px;
            background: #ffffff;
        }
        .amount-highlight-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .amount-label {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }
        .amount-value {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
        }

        /* ------------------------------------------------------------- */
        /* STIL BUKTI KAS MANUAL (CASH VOUCHER) */
        /* ------------------------------------------------------------- */
        .manual-header {
            border-bottom: 2px solid #1e293b;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .manual-title {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .manual-subtitle {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }

        /* ------------------------------------------------------------- */
        /* TABEL & INFORMASI */
        /* ------------------------------------------------------------- */
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-grid td {
            padding: 6px 4px;
            vertical-align: top;
        }
        .info-label {
            color: #64748b;
            font-weight: 600;
            width: 22%;
        }
        .info-sep {
            width: 2%;
            color: #94a3b8;
        }
        .info-val {
            color: #0f172a;
            font-weight: 500;
            width: 26%;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #cbd5e1;
            padding: 9px 12px;
            font-size: 12.5px;
        }
        .items-table th {
            background-color: #f1f5f9;
            font-weight: 700;
            color: #334155;
            text-align: left;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: 700; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }

        /* LAMPIRAN NOTA FISIK UNTUK KAS MANUAL */
        .receipt-attachment-card {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 16px;
            margin-top: 25px;
            background: #fafafa;
            page-break-inside: avoid;
        }
        .receipt-attachment-header {
            font-weight: 700;
            font-size: 13px;
            color: #334155;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
        }
        .receipt-img-container {
            text-align: center;
            padding: 10px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
        }
        .receipt-img {
            max-width: 100%;
            max-height: 480px;
            border-radius: 4px;
            object-fit: contain;
        }

        /* TANDA TANGAN */
        .signature-table {
            width: 100%;
            margin-top: 35px;
            page-break-inside: avoid;
        }
        .signature-box {
            text-align: center;
            width: 30%;
        }
        .signature-space {
            height: 65px;
        }

        /* FOOTER DIGITAL */
        .digital-footer {
            margin-top: 25px;
            padding: 12px 16px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            font-size: 11.5px;
            color: #64748b;
            line-height: 1.5;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .xendit-card { box-shadow: none; border-color: #cbd5e1; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print">
            <button onclick="window.print()" class="btn-print">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" /><path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" /><path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" /></svg>
                Cetak / Simpan PDF
            </button>
        </div>

        @if($expense->payment_channel === 'xendit')
            <!-- ============================================================= -->
            <!-- 1. RESI TRANSFER ELEKTRONIK XENDIT PAYOUT -->
            <!-- ============================================================= -->
            @php
                $isPending = ($expense->status === 'pending');
                $isSuccess = ($expense->status === 'success');
                $isFailed  = ($expense->status === 'failed');
                $headerClass = $isPending ? 'pending' : ($isFailed ? 'failed' : '');
                $disbId = $expense->xendit_disbursement_id ?: ($expense->finance_response['xendit_disbursement']['id'] ?? '-');
            @endphp

            <div class="xendit-card">
                <div class="xendit-header {{ $headerClass }}">
                    <div>
                        <h1 class="xendit-title">Bukti Transfer Elektronik</h1>
                        <div class="xendit-subtitle">CIO Network Solution &bull; Xendit Disbursement Gateway</div>
                    </div>
                    <div>
                        <span class="xendit-status-badge">
                            @if($isSuccess)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Transfer Berhasil
                            @elseif($isPending)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>
                                Sedang Diproses Xendit
                            @elseif($isFailed)
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                Transfer Gagal
                            @else
                                {{ strtoupper($expense->status) }}
                            @endif
                        </span>
                    </div>
                </div>

                <div class="xendit-body">
                    <!-- Amount Box -->
                    <div class="amount-highlight-box">
                        <div>
                            <div class="amount-label">Total Dana Ditransfer</div>
                            <div class="amount-value">Rp {{ number_format($expense->grand_total, 0, ',', '.') }}</div>
                        </div>
                        <div class="text-end">
                            <div class="amount-label">Metode Sumber Dana</div>
                            <div style="font-weight: 700; color: #059669; font-size: 15px; margin-top: 4px;">Saldo Xendit (Payment Gateway)</div>
                        </div>
                    </div>

                    <!-- Grid Info Xendit -->
                    <table class="info-grid">
                        <tr>
                            <td class="info-label">No. Referensi Transaksi</td>
                            <td class="info-sep">:</td>
                            <td class="info-val font-mono" style="font-weight: 700; color: #2563eb;">{{ $expense->reference_no }}</td>

                            <td class="info-label">Bank Tujuan</td>
                            <td class="info-sep">:</td>
                            <td class="info-val" style="font-weight: 700;">{{ $expense->bank_name ?: ($expense->bank_code ?: 'Bank Transfer') }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">ID Disbursement Xendit</td>
                            <td class="info-sep">:</td>
                            <td class="info-val font-mono" style="font-size: 12px;">{{ $disbId }}</td>

                            <td class="info-label">Nomor Rekening</td>
                            <td class="info-sep">:</td>
                            <td class="info-val font-mono" style="font-weight: 700;">{{ $expense->account_number ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Waktu Transaksi</td>
                            <td class="info-sep">:</td>
                            <td class="info-val">{{ $expense->created_at->translatedFormat('d F Y, H:i') }} WIB</td>

                            <td class="info-label">Nama Pemilik Rekening</td>
                            <td class="info-sep">:</td>
                            <td class="info-val" style="font-weight: 700;">{{ $expense->account_holder_name ?: ($expense->vendor_name ?: '-') }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Keperluan Pengeluaran</td>
                            <td class="info-sep">:</td>
                            <td class="info-val" style="font-weight: 600;">{{ $expense->title }}</td>

                            <td class="info-label">Kategori</td>
                            <td class="info-sep">:</td>
                            <td class="info-val">{{ $expense->category->name ?? 'Operasional Umum' }}</td>
                        </tr>
                        <tr>
                            <td class="info-label">Dicatat / Dibuat Oleh</td>
                            <td class="info-sep">:</td>
                            <td class="info-val">{{ $expense->user->name ?? 'Staf Operasional' }}</td>

                            <td class="info-label">Ref API Finance</td>
                            <td class="info-sep">:</td>
                            <td class="info-val font-mono">{{ $expense->finance_reference_id ?? $expense->reference_no }}</td>
                        </tr>
                    </table>

                    <!-- Rincian Barang -->
                    <div style="font-weight: 700; font-size: 13px; color: #1e293b; margin-bottom: 8px;">Rincian Item Pembelian:</div>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;" class="text-center">No</th>
                                <th>Nama Barang / Jasa</th>
                                <th style="width: 80px;" class="text-center">Qty</th>
                                <th style="width: 80px;" class="text-center">Satuan</th>
                                <th style="width: 130px;" class="text-end">Harga Satuan</th>
                                <th style="width: 140px;" class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expense->items as $idx => $item)
                                <tr>
                                    <td class="text-center text-muted">{{ $idx + 1 }}</td>
                                    <td class="fw-bold">{{ $item->item_name }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-center">{{ strtoupper($item->unit) }}</td>
                                    <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end fw-bold">Subtotal Pembelian:</td>
                                <td class="text-end fw-bold">Rp {{ number_format($expense->subtotal_amount, 0, ',', '.') }}</td>
                            </tr>
                            @if($expense->has_admin_fee && $expense->admin_fee > 0)
                                <tr>
                                    <td colspan="5" class="text-end fw-bold" style="color: #b91c1c;">Biaya Admin Transfer:</td>
                                    <td class="text-end fw-bold" style="color: #b91c1c;">+Rp {{ number_format($expense->admin_fee, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            <tr style="background-color: #f8fafc; font-size: 14px;">
                                <td colspan="5" class="text-end fw-bold">TOTAL DANA DITRANSFER:</td>
                                <td class="text-end fw-bold" style="color: #059669;">Rp {{ number_format($expense->grand_total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <!-- Digital Verification Footer -->
                    <div class="digital-footer">
                        <strong>Verifikasi Resi Elektronik:</strong> Transaksi ini dikirim dan diverifikasi secara langsung melalui Payment Gateway <strong>Xendit Disbursement Indonesia</strong> terintegrasi dengan <strong>CIO Central Finance</strong>. Dokumen ini adalah tanda bukti transfer digital resmi yang sah dan tidak memerlukan tanda tangan basah.
                    </div>
                </div>
            </div>

        @else
            <!-- ============================================================= -->
            <!-- 2. BUKTI PENGELUARAN KAS MANUAL (CASH VOUCHER & NOTA FISIK) -->
            <!-- ============================================================= -->
            <table class="info-grid manual-header">
                <tr>
                    <td>
                        <div class="manual-title">Bukti Pengeluaran Kas Operasional</div>
                        <div class="manual-subtitle">CIO Network Solution &bull; Cash & Operational Voucher</div>
                    </td>
                    <td class="text-end">
                        <div style="font-size: 15px; font-weight: 800; color: #2563eb; font-family: monospace;">{{ $expense->reference_no }}</div>
                        <div style="color: #64748b; font-size: 12px; margin-top: 3px;">Tanggal: {{ $expense->transaction_date->translatedFormat('d F Y') }}</div>
                    </td>
                </tr>
            </table>

            <table class="info-grid">
                <tr>
                    <td class="info-label">Keperluan / Judul</td>
                    <td class="info-sep">:</td>
                    <td class="info-val" style="font-weight: 700; width: 40%;">{{ $expense->title }}</td>

                    <td class="info-label">Kategori</td>
                    <td class="info-sep">:</td>
                    <td class="info-val">{{ $expense->category->name ?? 'Operasional Umum' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Toko / Vendor / Supplier</td>
                    <td class="info-sep">:</td>
                    <td class="info-val" style="font-weight: 600;">{{ $expense->vendor_name ?: ($expense->account_holder_name ?: 'Pembelian Langsung') }}</td>

                    <td class="info-label">Sumber Dana</td>
                    <td class="info-sep">:</td>
                    <td class="info-val" style="font-weight: 700; color: #2563eb;">Saldo Kas / Manual Web</td>
                </tr>
                <tr>
                    <td class="info-label">Pemohon / Pengaju</td>
                    <td class="info-sep">:</td>
                    <td class="info-val">{{ $expense->user->name ?? 'Staf Operasional' }}</td>

                    <td class="info-label">Status Transaksi</td>
                    <td class="info-sep">:</td>
                    <td class="info-val"><strong style="color: #059669;">LUNAS (KAS TERPOTONG)</strong></td>
                </tr>
                @if($expense->notes)
                    <tr>
                        <td class="info-label">Catatan Tambahan</td>
                        <td class="info-sep">:</td>
                        <td class="info-val" colspan="4" style="color: #475569;">{{ $expense->notes }}</td>
                    </tr>
                @endif
            </table>

            <!-- Rincian Barang -->
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 40px;" class="text-center">No</th>
                        <th>Nama Barang / Jasa</th>
                        <th style="width: 80px;" class="text-center">Qty</th>
                        <th style="width: 80px;" class="text-center">Satuan</th>
                        <th style="width: 130px;" class="text-end">Harga Satuan</th>
                        <th style="width: 140px;" class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expense->items as $idx => $item)
                        <tr>
                            <td class="text-center text-muted">{{ $idx + 1 }}</td>
                            <td class="fw-bold">{{ $item->item_name }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-center">{{ strtoupper($item->unit) }}</td>
                            <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end fw-bold">Total Pembelian Barang:</td>
                        <td class="text-end fw-bold">Rp {{ number_format($expense->subtotal_amount, 0, ',', '.') }}</td>
                    </tr>
                    @if($expense->has_admin_fee && $expense->admin_fee > 0)
                        <tr>
                            <td colspan="5" class="text-end fw-bold" style="color: #b91c1c;">Biaya Lain-lain / Admin:</td>
                            <td class="text-end fw-bold" style="color: #b91c1c;">+Rp {{ number_format($expense->admin_fee, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr style="background-color: #f8fafc; font-size: 14px;">
                        <td colspan="5" class="text-end fw-bold">GRAND TOTAL PENGELUARAN KAS:</td>
                        <td class="text-end fw-bold" style="color: #2563eb;">Rp {{ number_format($expense->grand_total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>

            <!-- LAMPIRAN FOTO NOTA / STRUK ASLI YANG DI-UPLOAD LEWAT FORM -->
            @if($expense->attachment_receipt)
                @php
                    $ext = pathinfo($expense->attachment_receipt, PATHINFO_EXTENSION);
                    $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp']);
                @endphp
                <div class="receipt-attachment-card">
                    <div class="receipt-attachment-header">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M15 8h.01" /><path d="M3 6a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v12a3 3 0 0 1 -3 3h-12a3 3 0 0 1 -3 -3v-12z" /><path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l5 5" /><path d="M14 14l1 -1c.928 -.893 2.072 -.893 3 0l3 3" /></svg>
                        Lampiran Dokumen / Foto Nota Pembelian Fisik Asli
                    </div>

                    @if($isImage)
                        <div class="receipt-img-container">
                            <img src="{{ asset('storage/' . $expense->attachment_receipt) }}" alt="Foto Nota Pembelian" class="receipt-img">
                        </div>
                    @else
                        <div style="padding: 15px; text-align: center; color: #64748b; background: #ffffff; border-radius: 4px; border: 1px dashed #cbd5e1;">
                            <strong>Dokumen Nota Terlampir (Format PDF):</strong> {{ basename($expense->attachment_receipt) }}
                        </div>
                    @endif
                </div>
            @endif

            <!-- Kolom Tanda Tangan Kas Manual -->
            <table class="signature-table">
                <tr>
                    <td class="signature-box">
                        <div>Pemohon / Yang Membeli:</div>
                        <div class="signature-space"></div>
                        <div class="fw-bold">({{ $expense->user->name ?? 'Staf Pemohon' }})</div>
                    </td>
                    <td style="width: 40%;"></td>
                    <td class="signature-box">
                        <div>Disetujui / Finance Kasir:</div>
                        <div class="signature-space"></div>
                        <div class="fw-bold">(_______________________)</div>
                    </td>
                </tr>
            </table>
        @endif
    </div>
</body>
</html>
