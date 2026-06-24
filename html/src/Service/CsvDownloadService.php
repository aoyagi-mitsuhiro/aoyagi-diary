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

            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: inline; filename="' . $filename . '"');

            echo "\xEF\xBB\xBF";

            $output = fopen('php://output', 'w');
            fputcsv(
                $output,
                ['id', 'title', 'date', 'is private'],
                ",",
                '"',
                "\\"
            );
            foreach ($diaries as $diary) {
                fputcsv(
                    $output,
                    [
                        $diary['id'],
                        $diary['title'],
                        $diary['date'],
                        $diary['is_private'] ? 'private' : 'public'
                    ],
                    ",",
                    '"',
                    "\\"
                );
            }

            fclose($output);
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
