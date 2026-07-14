<?php

namespace Tests\Feature;

use Modules\Admin\Support\CsvExporter;
use Tests\TestCase;

class CsvExporterTest extends TestCase
{
    private function body(\Symfony\Component\HttpFoundation\StreamedResponse $r): string
    {
        ob_start();
        $r->sendContent();
        return ob_get_clean();
    }

    public function test_streams_header_and_rows_as_csv(): void
    {
        $res = CsvExporter::stream('report.csv', ['Name', 'Qty'], [['Widget', 3], ['Gadget', 10]]);

        $this->assertSame('text/csv', $res->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename=report.csv', $res->headers->get('Content-Disposition'));

        $lines = preg_split('/\r?\n/', trim($this->body($res)));
        $this->assertSame('Name,Qty', $lines[0]);
        $this->assertSame('Widget,3', $lines[1]);
        $this->assertSame('Gadget,10', $lines[2]);
    }

    public function test_neutralises_formula_injection(): void
    {
        $res = CsvExporter::stream('x.csv', ['Val'], [['=1+1'], ['+cmd'], ['-2'], ['@ref'], ["\tval"], ["\rval"], ['safe']]);
        $body = $this->body($res);

        // Dangerous leading chars get a leading apostrophe; safe values untouched.
        $this->assertStringContainsString("'=1+1", $body);
        $this->assertStringContainsString("'+cmd", $body);
        $this->assertStringContainsString("'-2", $body);
        $this->assertStringContainsString("'@ref", $body);
        $this->assertStringContainsString("'\tval", $body);
        $this->assertStringContainsString("'\rval", $body);
        $this->assertMatchesRegularExpression('/(^|\n)safe(\r?\n|$)/', $body);
    }
}
