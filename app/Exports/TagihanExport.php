<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TagihanExport implements FromArray, WithEvents
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
            'total_tagihan' => 0,
            'total_terbayar' => 0,
            'total_piutang' => 0,
        ];
    }

    public function array(): array
    {
        $out = [];
        $out[] = [
            'No',
            'NIS',
            'Nama',
            'Kategori Beasiswa',
            'Kelas',
            'Sekolah / Unit',
            'Total Tagihan',
            'Total Terbayar',
            'Sisa Tagihan',
            'Status',
            'Keterangan',
        ];

        $no = 1;
        $beasiswaMap = [
            0 => 'NON BEASISWA',
            1 => 'KADER DALAM',
            2 => 'KADER MALANG',
            3 => 'KADER PENGURUS',
            4 => 'KADER AMAL USAHA',
            5 => 'KADER JAMAAH',
            6 => 'KADER PENGABDIAN',
            7 => 'SAUDARA',
            8 => 'TAAWWUN',
            9 => 'ALUMNI',
        ];
        foreach ($this->rows as $row) {
            $paid = (int) ($row['status'] ?? 0) === 1
                || (float) ($row['sisa_tagihan'] ?? 0) <= 0;
            $bisaUjian = (float) ($row['sisa_tagihan'] ?? 0) <= 50000;
            $code = (int) ($row['is_anak_pegawai'] ?? 0);
            if ($code < 0 || $code > 9) {
                $code = 0;
            }
            $beasiswa = trim((string) ($row['beasiswa_label'] ?? $row['kader_label'] ?? ''));
            if ($beasiswa === '') {
                $beasiswa = $beasiswaMap[$code] ?? ('KODE ' . $code);
            }

            $out[] = [
                $no++,
                (string) ($row['nis'] ?? $row['nocust'] ?? ''),
                strtoupper((string) ($row['nama'] ?? '')),
                $beasiswa,
                (string) ($row['kelas'] ?? ''),
                (string) ($row['sekolah'] ?? ''),
                (float) ($row['total_tagihan'] ?? 0),
                (float) ($row['total_terbayar'] ?? 0),
                (float) ($row['sisa_tagihan'] ?? 0),
                $paid ? 'Lunas' : 'Belum Lunas',
                $bisaUjian ? 'Bisa Ujian' : 'Belum Bisa Ujian',
            ];
        }

        $out[] = [
            '',
            '',
            'TOTAL',
            '',
            '',
            '',
            (float) ($this->totals['total_tagihan'] ?? 0),
            (float) ($this->totals['total_terbayar'] ?? 0),
            (float) ($this->totals['total_piutang'] ?? 0),
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
                    'A' => 6, 'B' => 14, 'C' => 28, 'D' => 14, 'E' => 10,
                    'F' => 16, 'G' => 14, 'H' => 14, 'I' => 14, 'J' => 12, 'K' => 16,
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
                    $sheet->getStyle('C' . $dataStartRow . ':C' . $dataEndRow)
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle('G' . $dataStartRow . ':I' . $dataEndRow)
                        ->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle('B' . $dataStartRow . ':B' . $dataEndRow)
                        ->getNumberFormat()->setFormatCode('@');

                    for ($r = $dataStartRow; $r <= $dataEndRow; $r++) {
                        $status = strtoupper(trim((string) $sheet->getCell('J' . $r)->getValue()));
                        if ($status === 'BELUM LUNAS') {
                            $sheet->getStyle('J' . $r . ':K' . $r)->applyFromArray([
                                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['argb' => 'FFEF4444'],
                                ],
                            ]);
                        }
                    }
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
                $sheet->getStyle('G' . $totalRow . ':I' . $totalRow)
                    ->getNumberFormat()->setFormatCode('#,##0');
            },
        ];
    }
}
