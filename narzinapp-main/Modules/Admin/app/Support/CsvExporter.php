<?php

namespace Modules\Admin\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/** Streams an array of rows as a CSV download (mirrors OrderController::exportCsv). */
class CsvExporter
{
    /**
     * @param string   $filename Download filename (e.g. "returns-2026-07-13.csv").
     * @param string[] $headers  Column header labels.
     * @param iterable $rows     Iterable of arrays of scalar cell values (column order = $headers).
     */
    public static function stream(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        $httpHeaders = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, array_map([self::class, 'sanitize'], $headers));
            foreach ($rows as $row) {
                fputcsv($out, array_map([self::class, 'sanitize'], array_values((array) $row)));
            }
            fclose($out);
        }, 200, $httpHeaders);
    }

    /** Neutralise CSV/formula injection: prefix a leading =,+,-,@,TAB,CR with an apostrophe. */
    private static function sanitize($value): string
    {
        $s = (string) $value;
        if ($s !== '' && in_array($s[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $s;
        }
        return $s;
    }
}
