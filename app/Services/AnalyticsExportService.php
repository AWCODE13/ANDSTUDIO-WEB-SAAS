<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsExportService
{
    /**
     * Currency format string: Rp 1.250.000 (standard ID accounting)
     */
    private const CURRENCY_FORMAT = '"Rp "#,##0;[Red]("-Rp "#,##0);"-"';
    private const DATE_FORMAT = 'DD/MM/YYYY HH:MM';

    /**
     * Palet warna professional studio: Slate + Amber + Emerald + Rose
     */
    private const COLOR_PRIMARY = '0F172A';     // Slate 900
    private const COLOR_AMBER = 'D97706';       // Amber 600
    private const COLOR_EMERALD = '059669';     // Emerald 600
    private const COLOR_ROSE = 'DC2626';        // Rose 600
    private const COLOR_BG_ZEBRA = 'F8FAFC';    // Slate 50
    private const COLOR_BORDER = 'E2E8F0';      // Slate 200
    private const COLOR_MUTED = '64748B';       // Slate 500

    public function export(
        string $scopeTitle,
        string $subjectName,
        Carbon $startDate,
        Carbon $endDate,
        Collection $bookings,
        Collection $expenses,
        string $filename
    ): StreamedResponse {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getDefaultStyle()->getFont()->setName('Segoe UI')->setSize(10);

        // ── Sheet 1: Detail Transaksi (Sheet Utama) ─────────────────
        $sheetTransactions = $spreadsheet->getActiveSheet();
        $sheetTransactions->setTitle('Detail Transaksi');
        $sheetTransactions->setShowGridLines(true);

        $this->buildTransactionsSheet(
            $sheetTransactions,
            $scopeTitle,
            $subjectName,
            $startDate,
            $endDate,
            $bookings,
            $expenses
        );

        // ── Sheet 2: Ringkasan Bulanan & Kategori ────────────────────
        $sheetSummary = $spreadsheet->createSheet();
        $sheetSummary->setTitle('Ringkasan & Analisis');
        $sheetSummary->setShowGridLines(true);

        $this->buildSummarySheet(
            $sheetSummary,
            $scopeTitle,
            $subjectName,
            $startDate,
            $endDate,
            $bookings,
            $expenses
        );

        // Focus sheet utama saat file dibuka
        $spreadsheet->setActiveSheetIndex(0);

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function buildTransactionsSheet(
        Worksheet $sheet,
        string $scopeTitle,
        string $subjectName,
        Carbon $startDate,
        Carbon $endDate,
        Collection $bookings,
        Collection $expenses
    ): void {
        // 1. Title Block
        $sheet->setCellValue('A1', strtoupper($scopeTitle));
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB(self::COLOR_PRIMARY);

        $sheet->setCellValue('A2', $subjectName);
        $sheet->getStyle('A2')->getFont()->setSize(11)->setBold(true)->getColor()->setRGB(self::COLOR_AMBER);

        $sheet->setCellValue('A3', 'Periode: ' . $startDate->format('d/m/Y') . ' s/d ' . $endDate->format('d/m/Y') . '  •  Diexport: ' . now()->format('d/m/Y H:i'));
        $sheet->getStyle('A3')->getFont()->setSize(9)->getColor()->setRGB(self::COLOR_MUTED);

        // 2. KPI Cards (Row 5 - 7)
        $this->renderKpiCard($sheet, 'A', 'B', 5, 'TOTAL PEMASUKAN', '=SUM(F11:F10000)', self::COLOR_AMBER);
        $this->renderKpiCard($sheet, 'C', 'D', 5, 'TOTAL PENGELUARAN', '=SUM(G11:G10000)', self::COLOR_ROSE);
        $this->renderKpiCard($sheet, 'E', 'F', 5, 'LABA BERSIH', '=A6-C6', self::COLOR_EMERALD);
        $this->renderKpiCard($sheet, 'G', 'H', 5, 'TOTAL TRANSAKSI', '=COUNTA(B11:B10000)', self::COLOR_PRIMARY);

        // 3. Table Header (Row 10)
        $headers = [
            'A10' => 'NO',
            'B10' => 'TANGGAL & WAKTU',
            'C10' => 'KODE / REF',
            'D10' => 'STUDIO / CUSTOMER / DESKRIPSI',
            'E10' => 'KATEGORI / STATUS',
            'F10' => 'PEMASUKAN',
            'G10' => 'PENGELUARAN',
            'H10' => 'KETERANGAN',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerRange = 'A10:H10';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_PRIMARY]],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'horizontal' => Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        $sheet->getStyle('A10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('F10:G10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getRowDimension(10)->setRowHeight(26);

        // Gabungkan seluruh item & urutkan chronological
        $rows = collect();

        foreach ($bookings as $b) {
            $rows->push([
                'type' => 'income',
                'date' => $b->created_at,
                'code' => $b->booking_code ?? ('BK-' . $b->id),
                'desc' => ($b->customer_name ?? 'Pelanggan') . ($b->tenant ? ' • ' . $b->tenant->name : ''),
                'category' => 'Booking (' . ucfirst($b->booking_status ?? 'confirmed') . ')',
                'income' => (float)$b->total_amount,
                'expense' => 0,
                'note' => $b->payment_status ? 'Status bayar: ' . $b->payment_status : '-',
            ]);
        }

        foreach ($expenses as $e) {
            $rows->push([
                'type' => 'expense',
                'date' => $e->created_at,
                'code' => 'EXP-' . $e->id,
                'desc' => ($e->title ?? $e->category) . ($e->tenant ? ' • ' . $e->tenant->name : ''),
                'category' => 'Beban: ' . ucfirst($e->category ?? 'Operasional'),
                'income' => 0,
                'expense' => (float)$e->amount,
                'note' => $e->notes ?? ($e->description ?? '-'),
            ]);
        }

        $rows = $rows->sortBy('date')->values();

        $currRow = 11;
        $no = 1;

        foreach ($rows as $item) {
            $sheet->setCellValue('A' . $currRow, $no++);
            $sheet->setCellValue('B' . $currRow, $item['date'] ? $item['date']->format('d/m/Y H:i') : '-');
            $sheet->setCellValue('C' . $currRow, $item['code']);
            $sheet->setCellValue('D' . $currRow, $item['desc']);
            $sheet->setCellValue('E' . $currRow, $item['category']);
            $sheet->setCellValue('F' . $currRow, $item['income']);
            $sheet->setCellValue('G' . $currRow, $item['expense']);
            $sheet->setCellValue('H' . $currRow, $item['note']);

            // Zebra striping
            if ($currRow % 2 === 0) {
                $sheet->getStyle('A' . $currRow . ':H' . $currRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB(self::COLOR_BG_ZEBRA);
            }

            // Alignments & formats
            $sheet->getStyle('A' . $currRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $currRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $currRow)->getFont()->setName('Consolas')->setSize(9);

            $sheet->getRowDimension($currRow)->setRowHeight(20);
            $currRow++;
        }

        $lastDataRow = max(11, $currRow - 1);

        // Format angka kolom Pemasukan & Pengeluaran
        $sheet->getStyle('F11:G' . $lastDataRow)->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);

        // Highlight sel income > 0 (soft emerald text) dan expense > 0 (soft rose text)
        for ($r = 11; $r <= $lastDataRow; $r++) {
            $sheet->getStyle('F' . $r)->getFont()->getColor()->setRGB(self::COLOR_EMERALD);
            $sheet->getStyle('G' . $r)->getFont()->getColor()->setRGB(self::COLOR_ROSE);
        }

        // Garis border tipis pada data table
        $sheet->getStyle('A10:H' . $lastDataRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => self::COLOR_BORDER],
                ],
            ],
        ]);

        // 4. Baris TOTAL (Akuntansi)
        $totalRow = $currRow;
        $sheet->setCellValue('A' . $totalRow, 'TOTAL KESELURUHAN');
        $sheet->mergeCells('A' . $totalRow . ':E' . $totalRow);

        $sheet->setCellValue('F' . $totalRow, '=SUM(F11:F' . $lastDataRow . ')');
        $sheet->setCellValue('G' . $totalRow, '=SUM(G11:G' . $lastDataRow . ')');
        $sheet->setCellValue('H' . $totalRow, '=F' . $totalRow . '-G' . $totalRow);

        $sheet->getStyle('A' . $totalRow . ':H' . $totalRow)->applyFromArray([
            'font' => ['bold' => true, 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_PRIMARY]],
                'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => self::COLOR_PRIMARY]],
            ],
        ]);
        $sheet->getStyle('A' . $totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('F' . $totalRow . ':H' . $totalRow)->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);
        $sheet->getStyle('F' . $totalRow)->getFont()->getColor()->setRGB(self::COLOR_AMBER);
        $sheet->getStyle('G' . $totalRow)->getFont()->getColor()->setRGB(self::COLOR_ROSE);
        $sheet->getStyle('H' . $totalRow)->getFont()->getColor()->setRGB(self::COLOR_EMERALD);
        $sheet->getRowDimension($totalRow)->setRowHeight(26);

        // Aktifkan Excel AutoFilter di baris header
        $sheet->setAutoFilter('A10:H' . $lastDataRow);

        // Auto-fit kolom dengan padding aman
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('D')->setAutoSize(false)->setWidth(36);
        $sheet->getColumnDimension('H')->setAutoSize(false)->setWidth(30);
    }

    private function buildSummarySheet(
        Worksheet $sheet,
        string $scopeTitle,
        string $subjectName,
        Carbon $startDate,
        Carbon $endDate,
        Collection $bookings,
        Collection $expenses
    ): void {
        // Title
        $sheet->setCellValue('A1', 'RINGKASAN & ANALISIS');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB(self::COLOR_PRIMARY);
        $sheet->setCellValue('A2', $subjectName . ' • ' . $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'));
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setRGB(self::COLOR_MUTED);

        // ── Bagian A: Rekapitulasi per Bulan ──
        $sheet->setCellValue('A4', 'REKAPITULASI BULANAN');
        $sheet->getStyle('A4')->getFont()->setBold(true)->getColor()->setRGB(self::COLOR_AMBER);

        $monthHeaders = ['A5' => 'BULAN', 'B5' => 'PEMASUKAN', 'C5' => 'PENGELUARAN', 'D5' => 'LABA BERSIH', 'E5' => 'PROFIT MARGIN'];
        foreach ($monthHeaders as $cell => $txt) {
            $sheet->setCellValue($cell, $txt);
        }
        $sheet->getStyle('A5:E5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_PRIMARY]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('B5:E5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getRowDimension(5)->setRowHeight(24);

        // Grouping data per bulan
        $months = [];
        foreach ($bookings as $b) {
            $key = $b->created_at->format('Y-m');
            $label = $b->created_at->translatedFormat('F Y');
            if (!isset($months[$key])) {
                $months[$key] = ['label' => $label, 'rev' => 0, 'exp' => 0];
            }
            $months[$key]['rev'] += (float)$b->total_amount;
        }
        foreach ($expenses as $e) {
            $key = $e->created_at->format('Y-m');
            $label = $e->created_at->translatedFormat('F Y');
            if (!isset($months[$key])) {
                $months[$key] = ['label' => $label, 'rev' => 0, 'exp' => 0];
            }
            $months[$key]['exp'] += (float)$e->amount;
        }

        ksort($months);

        $mRow = 6;
        foreach ($months as $m) {
            $sheet->setCellValue('A' . $mRow, $m['label']);
            $sheet->setCellValue('B' . $mRow, $m['rev']);
            $sheet->setCellValue('C' . $mRow, $m['exp']);
            $sheet->setCellValue('D' . $mRow, '=B' . $mRow . '-C' . $mRow);
            $sheet->setCellValue('E' . $mRow, '=IF(B' . $mRow . '>0, D' . $mRow . '/B' . $mRow . ', 0)');

            if ($mRow % 2 === 0) {
                $sheet->getStyle('A' . $mRow . ':E' . $mRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB(self::COLOR_BG_ZEBRA);
            }

            $sheet->getRowDimension($mRow)->setRowHeight(20);
            $mRow++;
        }

        $lastMRow = max(6, $mRow - 1);
        $sheet->getStyle('B6:D' . $lastMRow)->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);
        $sheet->getStyle('E6:E' . $lastMRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
        $sheet->getStyle('A5:E' . $lastMRow)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_BORDER]]],
        ]);

        // ── Bagian B: Breakdown Pengeluaran per Kategori ──
        $catStartRow = $mRow + 3;
        $sheet->setCellValue('A' . $catStartRow, 'BREAKDOWN PENGELUARAN PER KATEGORI');
        $sheet->getStyle('A' . $catStartRow)->getFont()->setBold(true)->getColor()->setRGB(self::COLOR_ROSE);

        $catHeaderRow = $catStartRow + 1;
        $sheet->setCellValue('A' . $catHeaderRow, 'KATEGORI');
        $sheet->setCellValue('B' . $catHeaderRow, 'TOTAL BEBAN');
        $sheet->setCellValue('C' . $catHeaderRow, 'PORSI DARI TOTAL');
        $sheet->getStyle('A' . $catHeaderRow . ':C' . $catHeaderRow)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLOR_PRIMARY]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('B' . $catHeaderRow . ':C' . $catHeaderRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $catData = $expenses->groupBy('category')->map(fn($g) => $g->sum('amount'))->sortDesc();
        $totalExpAll = $expenses->sum('amount');

        $cRow = $catHeaderRow + 1;
        foreach ($catData as $catName => $amount) {
            $sheet->setCellValue('A' . $cRow, ucfirst($catName ?: 'Umum'));
            $sheet->setCellValue('B' . $cRow, $amount);
            $sheet->setCellValue('C' . $cRow, $totalExpAll > 0 ? ($amount / $totalExpAll) : 0);

            if ($cRow % 2 === 0) {
                $sheet->getStyle('A' . $cRow . ':C' . $cRow)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB(self::COLOR_BG_ZEBRA);
            }
            $cRow++;
        }

        $lastCRow = max($catHeaderRow + 1, $cRow - 1);
        $sheet->getStyle('B' . ($catHeaderRow + 1) . ':B' . $lastCRow)->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);
        $sheet->getStyle('C' . ($catHeaderRow + 1) . ':C' . $lastCRow)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
        $sheet->getStyle('A' . $catHeaderRow . ':C' . $lastCRow)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_BORDER]]],
        ]);

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
    }

    private function renderKpiCard(
        Worksheet $sheet,
        string $colStart,
        string $colEnd,
        int $topRow,
        string $title,
        string $formulaOrVal,
        string $accentColor
    ): void {
        $valRow = $topRow + 1;

        $sheet->mergeCells("{$colStart}{$topRow}:{$colEnd}{$topRow}");
        $sheet->mergeCells("{$colStart}{$valRow}:{$colEnd}{$valRow}");

        $sheet->setCellValue("{$colStart}{$topRow}", $title);
        $sheet->setCellValue("{$colStart}{$valRow}", $formulaOrVal);

        $sheet->getStyle("{$colStart}{$topRow}")->applyFromArray([
            'font' => ['size' => 8, 'bold' => true, 'color' => ['rgb' => self::COLOR_MUTED]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->getStyle("{$colStart}{$valRow}")->applyFromArray([
            'font' => ['size' => 13, 'bold' => true, 'color' => ['rgb' => $accentColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        if (str_starts_with($formulaOrVal, '=COUNTA')) {
            $sheet->getStyle("{$colStart}{$valRow}")->getNumberFormat()->setFormatCode('#,##0');
        } else {
            $sheet->getStyle("{$colStart}{$valRow}")->getNumberFormat()->setFormatCode(self::CURRENCY_FORMAT);
        }

        $sheet->getStyle("{$colStart}{$topRow}:{$colEnd}{$valRow}")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8FAFC']],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => self::COLOR_BORDER]],
                'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => $accentColor]],
            ],
        ]);

        $sheet->getRowDimension($topRow)->setRowHeight(16);
        $sheet->getRowDimension($valRow)->setRowHeight(24);
    }
}
