<?php

namespace App\Exports;

use App\Support\IncomeReport;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

/**
 * Membuat berkas Excel laporan pendapatan.
 * Total dan rekap per metode memakai rumus Excel, jadi ikut berubah jika data diedit.
 */
class IncomeReportSpreadsheet
{
    private const MONEY_FORMAT = '"Rp"#,##0';
    private const FIRST_DATA_ROW = 8;

    public static function build(IncomeReport $report, string $generatedBy): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Ikhtiar Laundry')
            ->setTitle($report->title());

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap');

        // Kop laporan
        $sheet->setCellValue('A1', 'Ikhtiar Laundry');
        $sheet->setCellValue('A2', 'Jalan Meranti No. 68');
        $sheet->setCellValue('A3', $report->title());
        $sheet->setCellValue('A4', 'Periode: ' . $report->periodLabel());
        $sheet->setCellValue('A5', 'Dicetak oleh ' . $generatedBy . ' pada ' . now()->translatedFormat('d F Y, H:i'));

        foreach (range(1, 5) as $row) {
            $sheet->mergeCells("A{$row}:G{$row}");
        }

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('0C4A6E');
        $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getFont()->getColor()->setRGB('475569');
        $sheet->getStyle('A4:A5')->getFont()->getColor()->setRGB('475569');

        // Judul kolom
        $headerRow = self::FIRST_DATA_ROW - 1;
        $sheet->fromArray(
            ['No', 'Nomor Transaksi', 'Pelanggan', 'Tanggal Pembayaran', 'Metode', 'Diterima Oleh', 'Nominal'],
            null,
            "A{$headerRow}"
        );

        $sheet->getStyle("A{$headerRow}:G{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '0C4A6E']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0F7FA']],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0891B2']]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("G{$headerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Baris data
        $row = self::FIRST_DATA_ROW;
        $count = $report->payments->count();

        foreach ($report->payments as $index => $payment) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $payment->transaction->code);
            $sheet->setCellValue("C{$row}", $payment->transaction->customer->name);
            $sheet->setCellValue("D{$row}", Date::PHPToExcel($payment->paid_at));
            $sheet->setCellValue("E{$row}", $payment->method_label);
            $sheet->setCellValue("F{$row}", $payment->receiver->name);
            $sheet->setCellValue("G{$row}", (int) $payment->amount);
            $row++;
        }

        $lastDataRow = self::FIRST_DATA_ROW + $count - 1;

        if ($count > 0) {
            $sheet->getStyle('D' . self::FIRST_DATA_ROW . ":D{$lastDataRow}")
                ->getNumberFormat()->setFormatCode('dd/mm/yyyy hh:mm');
            $sheet->getStyle('D' . self::FIRST_DATA_ROW . ":D{$lastDataRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('A' . self::FIRST_DATA_ROW . ":A{$lastDataRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . self::FIRST_DATA_ROW . ":G{$lastDataRow}")
                ->getNumberFormat()->setFormatCode(self::MONEY_FORMAT);
        } else {
            $sheet->setCellValue("A{$row}", 'Tidak ada pembayaran pada periode ini.');
            $sheet->mergeCells("A{$row}:G{$row}");
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$row}")->getFont()->getColor()->setRGB('64748B');
            $lastDataRow = self::FIRST_DATA_ROW - 1;
            $row++;
        }

        // Total periode (rumus)
        $totalRow = $row;
        $sheet->setCellValue("A{$totalRow}", 'Total periode');
        $sheet->mergeCells("A{$totalRow}:F{$totalRow}");
        $sheet->setCellValue("G{$totalRow}", $count > 0 ? '=SUM(G' . self::FIRST_DATA_ROW . ':G' . $lastDataRow . ')' : 0);
        $sheet->getStyle("A{$totalRow}:G{$totalRow}")->applyFromArray([
            'font' => ['bold' => true],
            'borders' => ['top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0891B2']]],
        ]);
        $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("G{$totalRow}")->getNumberFormat()->setFormatCode(self::MONEY_FORMAT);

        // Ringkasan
        $summaryRow = $totalRow + 2;
        $sheet->setCellValue("A{$summaryRow}", 'Ringkasan');
        $sheet->getStyle("A{$summaryRow}")->getFont()->setBold(true)->getColor()->setRGB('0C4A6E');

        $summary = [
            ['Jumlah pembayaran', $count, '#,##0'],
            ['Jumlah transaksi', $report->transactionCount(), '#,##0'],
        ];

        foreach ($report->byMethod() as $data) {
            $formula = $count > 0
                ? '=SUMIF($E$' . self::FIRST_DATA_ROW . ':$E$' . $lastDataRow . ',"' . $data['label'] . '",$G$' . self::FIRST_DATA_ROW . ':$G$' . $lastDataRow . ')'
                : 0;

            $summary[] = ['Diterima lewat ' . $data['label'], $formula, self::MONEY_FORMAT];
        }

        $line = $summaryRow + 1;

        foreach ($summary as [$label, $value, $format]) {
            $sheet->setCellValue("A{$line}", $label);
            $sheet->mergeCells("A{$line}:F{$line}");
            $sheet->getStyle("A{$line}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->setCellValue("G{$line}", $value);
            $sheet->getStyle("G{$line}")->getNumberFormat()->setFormatCode($format);
            $line++;
        }

        // Lebar kolom, panel beku, dan pengaturan cetak
        foreach (['A' => 6, 'B' => 24, 'C' => 28, 'D' => 20, 'E' => 12, 'F' => 20, 'G' => 18] as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->freezePane('A' . self::FIRST_DATA_ROW);

        $sheet->getPageSetup()
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(0)
            ->setRowsToRepeatAtTopByStartAndEnd($headerRow, $headerRow);

        return $spreadsheet;
    }
}