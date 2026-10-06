<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Events\AfterSheet;

class CashlessExport implements FromArray, WithEvents
{
    private const LAST_COL = 'K';
    private const HEADER_ROW = 1;
    private const DATA_START_ROW = 2;

    /** @var array<int, array<string, mixed>> */
    protected array $rows;

    /** @var array<string, mixed> */
    protected array $filters;

    /** @var array<string, float|int> */
    protected array $totals;

    /**
     * @param  Collection|array<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $filters
     * @param  array<string, float|int>|null  $totals
     */
    public function __construct($rows, array $filters = [], ?array $totals = null)
    {
        if ($rows instanceof Collection) {
            $rows = $rows->all();
        }

        $this->rows = is_array($rows) ? $rows : [];
        $this->filters = $filters;
        $this->totals = $totals ?? [
            'total_transaksi' => 0,
            'total_jumlah' => 0,
        ];
    }

    public function array(): array
    {
        $out = [];
        $out[] = [
            'No',
            'Tanggal',
            'NIS',
            'Nama',
            'Kelas',
            'Unit',
            'Teller',
            'Keterangan',
            'Jumlah',
            'TRANSNO',
            'FIDBANK',
        ];

        $no = 1;
        foreach ($this->rows as $row) {
            $out[] = [
                $no++,
                (string) ($row['tanggal'] ?? ''),
                (string) ($row['nis'] ?? ''),
                strtoupper((string) ($row['nama'] ?? '')),
                (string) ($row['kelas'] ?? ''),
                (string) ($row['sekolah'] ?? $row['unit'] ?? ''),
                (string) ($row['teller'] ?? ''),
                (string) ($row['keterangan'] ?? ''),
                (float) ($row['jumlah'] ?? 0),
                (string) ($row['transno'] ?? ''),
                (string) ($row['fidbank'] ?? ''),
            ];
        }

        $out[] = [
            '',
            '',
            '',
            'TOTAL',
            '',
            '',
            '',
            (string) ((int) ($this->totals['total_transaksi'] ?? count($this->rows))) . ' transaksi',
            (float) ($this->totals['total_jumlah'] ?? 0),
            '',
            '',
        ];

        return $out;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastCol = self::LAST_COL;
                $headerRow = self::HEADER_ROW;
                $dataStartRow = self::DATA_START_ROW;
                $dataEndRow = max($dataStartRow, $lastRow - 1);
                $totalRow = $lastRow;

                $widths = [
                    'A' => 6, 'B' => 18, 'C' => 14, 'D' => 28, 'E' => 10,
                    'F' => 14, 'G' => 12, 'H' => 18, 'I' => 14, 'J' => 18, 'K' => 10,
                ];
                foreach ($widths as $col => $w) {
                    $sheet->getColumnDimension($col)->setWidth($w);
                }

                $sheet->getStyle('A' . $headerRow . ':' . $lastCol . $headerRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFFEF9C3'],
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);

                if ($dataEndRow >= $dataStartRow) {
                    $sheet->getStyle('A' . $dataStartRow . ':' . $lastCol . $dataEndRow)->applyFromArray([
                        'font' => ['size' => 10],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);

                    $sheet->getStyle('A' . $dataStartRow . ':A' . $dataEndRow)
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('I' . $dataStartRow . ':I' . $dataEndRow)
                        ->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle('C' . $dataStartRow . ':C' . $dataEndRow)
                        ->getNumberFormat()->setFormatCode('@');
                }

                $sheet->getStyle('A' . $totalRow . ':' . $lastCol . $totalRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFF1F5F9'],
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);
                $sheet->getStyle('I' . $totalRow)
                    ->getNumberFormat()->setFormatCode('#,##0');
            },
        ];
    }
}
