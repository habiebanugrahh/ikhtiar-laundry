<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $report->title() }}</title>
    <style>
        @page { margin: 18mm 14mm 20mm 14mm; }

        body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; color: #0f172a; }
        h1 { margin: 0; font-size: 18px; color: #0c4a6e; }
        h2 { margin: 18px 0 6px 0; font-size: 12px; color: #0c4a6e; }
        table { width: 100%; border-collapse: collapse; }

        .muted { color: #475569; }
        .right { text-align: right; }
        .center { text-align: center; }
        .header { margin-bottom: 14px; padding-bottom: 8px; border-bottom: 2px solid #0891b2; }
        .header td { vertical-align: top; }
        .report-title { font-size: 13px; font-weight: bold; color: #0c4a6e; }

        .summary td { width: 33%; padding: 8px 10px; border: 1px solid #cffafe; background: #ecfeff; }
        .summary .label { font-size: 9px; color: #475569; }
        .summary .value { margin-top: 3px; font-size: 14px; font-weight: bold; color: #0c4a6e; }

        .data th { padding: 6px; border-bottom: 1px solid #0891b2; background: #ecfeff; color: #0c4a6e; font-size: 9px; text-align: left; }
        .data td { padding: 5px 6px; border-bottom: 1px solid #e2e8f0; }
        .data thead { display: table-header-group; }
        .data tr { page-break-inside: avoid; }
        .data .total td { border-top: 1px solid #0891b2; border-bottom: none; font-weight: bold; font-size: 11px; }

        .methods td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; }
        .methods { width: 55%; }

        .footer { position: fixed; bottom: -12mm; left: 0; right: 0; text-align: center; font-size: 8px; color: #64748b; }
        .pagenum:before { content: counter(page); }
    </style>
</head>
<body>

    <div class="footer">
        Dicetak oleh {{ $generatedBy }} pada {{ $generatedAt->translatedFormat('d F Y, H:i') }} | Halaman <span class="pagenum"></span>
    </div>

    <table class="header">
        <tr>
            <td>
                <h1>Ikhtiar Laundry</h1>
                <div class="muted">Jasa pencucian dan perawatan pakaian</div>
                <div class="muted">Jalan Meranti No. 68</div>
            </td>
            <td class="right">
                <div class="report-title">{{ $report->title() }}</div>
                <div class="muted">Periode: {{ $report->periodLabel() }}</div>
            </td>
        </tr>
    </table>

    <table class="summary">
        <tr>
            <td>
                <div class="label">Total pembayaran diterima</div>
                <div class="value">{{ \App\Support\Format::rupiah($report->total()) }}</div>
            </td>
            <td>
                <div class="label">Jumlah pembayaran</div>
                <div class="value">{{ $report->count() }}</div>
            </td>
            <td>
                <div class="label">Jumlah transaksi</div>
                <div class="value">{{ $report->transactionCount() }}</div>
            </td>
        </tr>
    </table>

    <h2>Rincian pembayaran</h2>

    <table class="data">
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th style="width: 22%;">Nomor transaksi</th>
                <th style="width: 24%;">Pelanggan</th>
                <th style="width: 20%;">Tanggal pembayaran</th>
                <th style="width: 10%;">Metode</th>
                <th class="right" style="width: 18%;">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($report->payments as $payment)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $payment->transaction->code }}</td>
                    <td>{{ $payment->transaction->customer->name }}</td>
                    <td>{{ $payment->paid_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $payment->method_label }}</td>
                    <td class="right">{{ \App\Support\Format::rupiah($payment->amount) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center muted" style="padding: 18px;">Tidak ada pembayaran pada periode ini.</td>
                </tr>
            @endforelse

            <tr class="total">
                <td colspan="5" class="right">Total periode</td>
                <td class="right">{{ \App\Support\Format::rupiah($report->total()) }}</td>
            </tr>
        </tbody>
    </table>

    <h2>Rekap per metode pembayaran</h2>

    <table class="methods">
        @foreach ($report->byMethod() as $method)
            <tr>
                <td>{{ $method['label'] }}</td>
                <td class="right">{{ \App\Support\Format::rupiah($method['total']) }}</td>
            </tr>
        @endforeach
    </table>

</body>
</html>