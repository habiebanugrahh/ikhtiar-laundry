<?php

namespace App\Http\Controllers;

use App\Exports\IncomeReportSpreadsheet;
use App\Models\Payment;
use App\Models\Transaction;
use App\Support\IncomeReport;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ReportController extends Controller
{
    // Rekap pendapatan harian: uang yang diterima pada tanggal terpilih
    public function daily(Request $request): View
    {
        $date = $this->parseDate($request->query('date')) ?? today();

        // Tanggal yang akan datang tidak punya pembayaran, jadi dibatasi sampai hari ini
        if ($date->greaterThan(today())) {
            $date = today();
        }

        // Pembayaran dikelompokkan berdasarkan tanggal uang diterima (paid_at)
        $payments = Payment::query()
            ->with(['transaction.customer', 'receiver'])
            ->whereDate('paid_at', $date->toDateString())
            ->orderBy('paid_at')
            ->orderBy('id')
            ->get();

        $byMethod = collect([
            Payment::METHOD_CASH,
            Payment::METHOD_TRANSFER,
            Payment::METHOD_QRIS,
        ])->mapWithKeys(fn ($method) => [$method => (int) $payments->where('method', $method)->sum('amount')]);

        // Pembanding: transaksi yang dicatat pada tanggal yang sama (nilai tagihan, bukan uang masuk)
        $billed = Transaction::query()
            ->whereDate('transaction_date', $date->toDateString())
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as total')
            ->first();

        return view('reports.daily', [
            'date' => $date,
            'isToday' => $date->isToday(),
            'previousDate' => $date->copy()->subDay(),
            'nextDate' => $date->isToday() ? null : $date->copy()->addDay(),
            'payments' => $payments,
            'totalReceived' => (int) $payments->sum('amount'),
            'transactionCount' => $payments->pluck('transaction_id')->unique()->count(),
            'byMethod' => $byMethod,
            'billedCount' => (int) $billed->count,
            'billedTotal' => (int) $billed->total,
        ]);
    }

    // Rekap pendapatan bulanan: total, tren harian, dan ringkasan transaksi
    public function monthly(Request $request): View
    {
        $now = now();

        $year = (int) $request->query('year', $now->year);
        $month = (int) $request->query('month', $now->month);

        if ($month < 1 || $month > 12) {
            $month = $now->month;
        }

        if ($year < 2000 || $year > $now->year) {
            $year = $now->year;
        }

        $start = Carbon::create($year, $month, 1)->startOfMonth();

        // Bulan yang akan datang tidak punya data, jadi dibatasi sampai bulan ini
        if ($start->format('Y-m') > $now->format('Y-m')) {
            $start = $now->copy()->startOfMonth();
        }

        $end = $start->copy()->endOfMonth();
        $isCurrentMonth = $start->format('Y-m') === $now->format('Y-m');

        // Bulan berjalan dihitung sampai hari ini saja
        $elapsedDays = $isCurrentMonth ? $now->day : $start->daysInMonth;

        // Pembayaran dikelompokkan per tanggal uang diterima (paid_at)
        $dailyRows = Payment::query()
            ->whereBetween('paid_at', [$start, $end])
            ->selectRaw('DATE(paid_at) as day, COUNT(*) as payment_count, SUM(amount) as total')
            ->groupByRaw('DATE(paid_at)')
            ->get()
            ->keyBy('day');

        $days = collect(range(1, $elapsedDays))->map(function ($day) use ($start, $dailyRows) {
            $date = $start->copy()->day($day);
            $row = $dailyRows->get($date->toDateString());

            return [
                'date' => $date,
                'count' => (int) ($row->payment_count ?? 0),
                'total' => (int) ($row->total ?? 0),
            ];
        });

        $totalReceived = (int) $days->sum('total');
        $bestDay = $days->sortByDesc('total')->first();

        $byMethod = Payment::query()
            ->whereBetween('paid_at', [$start, $end])
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method');

        // Pembanding: periode yang sama pada bulan sebelumnya (jumlah hari disamakan untuk bulan berjalan)
        $previousStart = $start->copy()->subMonthNoOverflow()->startOfMonth();
        $previousEnd = $isCurrentMonth
            ? $previousStart->copy()->day(min($elapsedDays, $previousStart->daysInMonth))->endOfDay()
            : $previousStart->copy()->endOfMonth();

        $previousTotal = (int) Payment::query()
            ->whereBetween('paid_at', [$previousStart, $previousEnd])
            ->sum('amount');

        $changePercent = $previousTotal > 0
            ? (int) round(($totalReceived - $previousTotal) / $previousTotal * 100)
            : null;

        // Ringkasan transaksi yang dicatat pada bulan ini (nilai tagihan, bukan uang masuk)
        $billed = Transaction::query()
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("COUNT(*) as count,
                COALESCE(SUM(total), 0) as total,
                COALESCE(SUM(CASE WHEN payment_status <> 'paid' THEN 1 ELSE 0 END), 0) as unpaid_count,
                COALESCE(SUM(CASE WHEN payment_status <> 'paid' THEN total - paid_amount ELSE 0 END), 0) as outstanding")
            ->first();

        $firstPayment = Payment::min('paid_at');
        $firstYear = $firstPayment ? Carbon::parse($firstPayment)->year : $now->year;

        return view('reports.monthly', [
            'start' => $start,
            'isCurrentMonth' => $isCurrentMonth,
            'previousMonth' => $start->copy()->subMonthNoOverflow(),
            'nextMonth' => $isCurrentMonth ? null : $start->copy()->addMonthNoOverflow(),
            'years' => range($now->year, min($firstYear, $now->year)),
            'days' => $days,
            'elapsedDays' => $elapsedDays,
            'totalReceived' => $totalReceived,
            'paymentCount' => (int) $days->sum('count'),
            'transactionCount' => Payment::query()->whereBetween('paid_at', [$start, $end])->distinct()->count('transaction_id'),
            'averagePerDay' => (int) round($totalReceived / max(1, $elapsedDays)),
            'bestDay' => ($bestDay && $bestDay['total'] > 0) ? $bestDay : null,
            'byMethod' => $byMethod,
            'previousTotal' => $previousTotal,
            'changePercent' => $changePercent,
            'billed' => $billed,
            'chart' => [
                'labels' => $days->map(fn ($day) => (string) $day['date']->day)->all(),
                'values' => $days->pluck('total')->all(),
                'monthLabel' => $start->translatedFormat('F Y'),
            ],
        ]);
    }

    // Halaman Laporan: pilih jenis dan periode, tinjau hasilnya, lalu unduh
    public function index(Request $request): View
    {
        $report = $this->reportFromRequest($request);

        $firstPayment = Payment::min('paid_at');
        $firstYear = $firstPayment ? Carbon::parse($firstPayment)->year : now()->year;

        return view('reports.index', [
            'report' => $report,
            'previewRows' => $report->payments->take(20),
            'years' => range(now()->year, min($firstYear, now()->year)),
        ]);
    }

    // Mengunduh laporan dalam format PDF atau Excel (xlsx)
    public function export(Request $request, string $format): SymfonyResponse
    {
        $report = $this->reportFromRequest($request);
        $user = $request->user();

        // Laporan dengan ribuan baris bisa butuh waktu lebih lama
        set_time_limit(120);

        if ($format === 'pdf') {
            $options = new Options();
            $options->set('defaultFont', 'DejaVu Sans');
            $options->set('isRemoteEnabled', false);

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml(view('reports.pdf', [
                'report' => $report,
                'generatedBy' => $user->name,
                'generatedAt' => now(),
            ])->render());
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $report->fileName('pdf') . '"',
            ]);
        }

        $spreadsheet = IncomeReportSpreadsheet::build($report, $user->name);

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $report->fileName('xlsx'), [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function reportFromRequest(Request $request): IncomeReport
    {
        return IncomeReport::make(
            $request->query('type'),
            $request->query('date'),
            $request->query('year'),
            $request->query('month'),
        );
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}