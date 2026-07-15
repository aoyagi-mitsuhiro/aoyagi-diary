<?php

namespace Aoyagi\AoyagiDiary\service;

use Override;

class CsvDownloadService implements DiaryDownloadInterface
{
    #[Override]
    public function downloadList(array $diaries): void
    {
        try {
            $filename = "diary_list_" . date('Ymd') . ".csv";

            header('Content-Type: text/csv; charset=Shift_JIS');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $output = fopen('php://output', 'w');
            fputcsv(
                $output,
                ['id', 'title', 'date', 'is private'],
                ",",
                '"',
                "\\"
            );
            foreach ($diaries as $diary) {
                $row = [
                    $diary['id'],
                    $diary['title'],
                    $diary['date'],
                    $diary['is_private'] ? 'private' : 'public',
                ];

                $convertedRow = mb_convert_encoding($row, 'SJIS-win', 'UTF-8');
                fputcsv($output, $convertedRow, ",", '"', "\\");
            }

            fclose($output);
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
