<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ImportPenagihanTemplateExport implements FromArray, WithEvents
{
    public function array(): array
    {
        return [
            [
                'no',
                'nis',
                'nama anak',
                'tanggal komunikasi',
                'media komunikasi',
                'hasil komunikasi',
                'rencana pembayaran',
                'catatan',
            ],
            [
                1,
                '2603122759',
                'CONTOH NAMA SANTRI',
                '02/09/2026',
                'WhatsApp',
                'Meminta waktu pembayaran',
                'Minggu depan',
                'Contoh catatan',
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getStyle('A1:H1')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFFEF9C3'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);
                foreach (range('A', 'H') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
