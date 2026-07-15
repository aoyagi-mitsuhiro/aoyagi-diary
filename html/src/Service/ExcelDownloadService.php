<?php

namespace Aoyagi\AoyagiDiary\service;

use Override;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelDownloadService implements DiaryDownloadInterface
{
    #[Override]
    public function downloadList(array $diaries): void
    {
        try {
            $filename = "diary_list_" . date('Ymd') . ".xlsx";
            $spreadSheet = new Spreadsheet();
            $sheet = $spreadSheet->getActiveSheet();

            $sheet->setCellValue('A1', 'id');
            $sheet->setCellValue('B1', 'title');
            $sheet->setCellValue('C1', 'date');
            $sheet->setCellValue('D1', 'is_private');
            $sheet->getStyle('A1:D1')->getFont()->setBold(true);

            $row = 2;
            foreach ($diaries as $diary) {
                $sheet->setCellValue('A' . $row, $diary['id']);
                $sheet->setCellValue('B' . $row, $diary['title']);
                $sheet->setCellValue('C' . $row, $diary['date']);
                $sheet->setCellValue('D' . $row, $diary['is_private'] ? 'private' : 'public');
                $row++;
            }

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new Xlsx($spreadSheet);
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            if (!headers_sent()) {
                header_remove();
                http_response_code(500);
            }
            echo "Download failed: " . $e->getMessage();
            exit;
        }
    }
}
