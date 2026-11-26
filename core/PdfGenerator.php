<?php
declare(strict_types=1);

namespace Core;

/**
 * Pure-PHP 8.2 PDF 1.4 Generator
 * Produces crisp, branded PDF documents (Invoices & Financial Reports) without external packages.
 */
class PdfGenerator
{
    private array $objects = [];
    private string $stream = '';

    private static function escapePdf(string $text): string
    {
        $text = preg_replace('/[^\x20-\x7E]/', ' ', $text) ?? '';
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private static function hexToRgbNorm(string $hex): array
    {
        $hex = ltrim(trim($hex), '#');
        if (strlen($hex) !== 6) {
            return [0.043, 0.114, 0.180];
        }
        return [
            round(hexdec(substr($hex, 0, 2)) / 255, 3),
            round(hexdec(substr($hex, 2, 2)) / 255, 3),
            round(hexdec(substr($hex, 4, 2)) / 255, 3),
        ];
    }

    public function buildInvoicePdf(array $data): string
    {
        $brandName = $data['company_name'] ?? 'Travel Agency';
        $tagline = $data['tagline'] ?? '';
        $address = $data['company_address'] ?? '';
        $email = $data['contact_email'] ?? '';
        $phone = $data['contact_number'] ?? '';
        [$pr, $pg, $pb] = self::hexToRgbNorm($data['primary_color'] ?? '#0B1D2E');
        [$ar, $ag, $ab] = self::hexToRgbNorm($data['accent_color'] ?? '#C59B5F');

        $s = '';
        // Header banner
        $s .= sprintf("%.3f %.3f %.3f rg\n", $pr, $pg, $pb);
        $s .= "0 722 595 120 re f\n";

        // Accent bar
        $s .= sprintf("%.3f %.3f %.3f rg\n", $ar, $ag, $ab);
        $s .= "0 718 595 4 re f\n";

        // Brand title on header
        $s .= "BT /F2 20 Tf 1 1 1 rg 42 790 Td (" . self::escapePdf($brandName) . ") Tj ET\n";
        $s .= "BT /F1 9.5 Tf 0.88 0.88 0.88 rg 42 772 Td (" . self::escapePdf(substr($tagline, 0, 80)) . ") Tj ET\n";
        $s .= "BT /F1 8.5 Tf 0.82 0.82 0.82 rg 42 748 Td (" . self::escapePdf($email . '  |  ' . $phone) . ") Tj ET\n";
        $s .= "BT /F1 8.5 Tf 0.82 0.82 0.82 rg 42 735 Td (" . self::escapePdf(substr($address, 0, 85)) . ") Tj ET\n";

        // INVOICE label right aligned
        $s .= "BT /F2 22 Tf 1 1 1 rg 435 788 Td (INVOICE) Tj ET\n";
        $s .= "BT /F1 10 Tf 0.92 0.85 0.72 rg 435 770 Td (" . self::escapePdf($data['invoice_number']) . ") Tj ET\n";
        $s .= "BT /F1 9 Tf 1 1 1 rg 435 753 Td (Status: " . self::escapePdf(strtoupper($data['status'])) . ") Tj ET\n";

        // Bill To & Booking Summary boxes
        $s .= "0.97 0.96 0.94 rg 42 605 245 92 re f\n";
        $s .= "0.97 0.96 0.94 rg 308 605 245 92 re f\n";

        $s .= sprintf("BT /F2 9.5 Tf %.3f %.3f %.3f rg 54 680 Td (BILLED TO) Tj ET\n", $ar, $ag, $ab);
        $s .= "BT /F2 11 Tf 0.1 0.1 0.1 rg 54 662 Td (" . self::escapePdf($data['customer_name']) . ") Tj ET\n";
        $s .= "BT /F1 9 Tf 0.3 0.3 0.3 rg 54 647 Td (" . self::escapePdf($data['customer_email']) . ") Tj ET\n";
        $s .= "BT /F1 9 Tf 0.3 0.3 0.3 rg 54 633 Td (" . self::escapePdf($data['customer_phone']) . ") Tj ET\n";
        $s .= "BT /F1 8.5 Tf 0.35 0.35 0.35 rg 54 619 Td (" . self::escapePdf(substr($data['customer_address'] ?? '', 0, 48)) . ") Tj ET\n";

        $s .= sprintf("BT /F2 9.5 Tf %.3f %.3f %.3f rg 320 680 Td (BOOKING DETAILS) Tj ET\n", $ar, $ag, $ab);
        $s .= "BT /F1 9 Tf 0.2 0.2 0.2 rg 320 662 Td (Booking Ref: " . self::escapePdf($data['booking_reference']) . ") Tj ET\n";
        $s .= "BT /F1 9 Tf 0.2 0.2 0.2 rg 320 647 Td (Issued Date: " . self::escapePdf($data['issued_at']) . ") Tj ET\n";
        $s .= "BT /F1 9 Tf 0.2 0.2 0.2 rg 320 633 Td (Travel Date: " . self::escapePdf($data['travel_date']) . ") Tj ET\n";
        $s .= "BT /F1 9 Tf 0.2 0.2 0.2 rg 320 619 Td (Travellers: " . self::escapePdf((string) $data['travellers']) . ") Tj ET\n";

        // Line items table header
        $s .= sprintf("%.3f %.3f %.3f rg\n", $pr, $pg, $pb);
        $s .= "42 560 511 26 re f\n";
        $s .= "BT /F2 9.5 Tf 1 1 1 rg 54 569 Td (DESCRIPTION) Tj ET\n";
        $s .= "BT /F2 9.5 Tf 1 1 1 rg 350 569 Td (QTY) Tj ET\n";
        $s .= "BT /F2 9.5 Tf 1 1 1 rg 415 569 Td (RATE) Tj ET\n";
        $s .= "BT /F2 9.5 Tf 1 1 1 rg 488 569 Td (AMOUNT) Tj ET\n";

        // Item row
        $s .= "0.99 0.99 0.99 rg 42 515 511 45 re f\n";
        $s .= "0.85 0.85 0.85 RG 0.5 w 42 515 511 45 re S\n";
        $s .= "BT /F2 10 Tf 0.1 0.1 0.1 rg 54 542 Td (" . self::escapePdf(substr($data['package_title'], 0, 52)) . ") Tj ET\n";
        $s .= "BT /F1 8.5 Tf 0.4 0.4 0.4 rg 54 527 Td (" . self::escapePdf($data['destination'] ?? 'Curated Travel Experience') . ") Tj ET\n";
        $s .= "BT /F1 9.5 Tf 0.15 0.15 0.15 rg 354 538 Td (" . self::escapePdf((string) $data['travellers']) . ") Tj ET\n";
        $s .= "BT /F1 9.5 Tf 0.15 0.15 0.15 rg 415 538 Td (" . self::escapePdf($data['unit_price_fmt']) . ") Tj ET\n";
        $s .= "BT /F2 9.5 Tf 0.1 0.1 0.1 rg 488 538 Td (" . self::escapePdf($data['subtotal_fmt']) . ") Tj ET\n";

        // Totals block
        $y = 480;
        $rows = [
            ['Subtotal', $data['subtotal_fmt'], false],
            ['Discount', '-' . $data['discount_fmt'], false],
            ['Taxes & Service Fees', $data['tax_fmt'], false],
            ['Total Invoice Amount', $data['total_fmt'], true],
            ['Amount Paid', $data['paid_fmt'], false],
            ['Balance Due', $data['balance_fmt'], true],
        ];

        foreach ($rows as [$label, $val, $bold]) {
            $font = $bold ? '/F2' : '/F1';
            if ($bold) {
                $s .= "0.95 0.94 0.91 rg 330 " . ($y - 6) . " 223 20 re f\n";
            }
            $s .= "BT {$font} 9.5 Tf 0.2 0.2 0.2 rg 340 {$y} Td (" . self::escapePdf($label) . ") Tj ET\n";
            $s .= "BT {$font} 9.5 Tf 0.1 0.1 0.1 rg 482 {$y} Td (" . self::escapePdf($val) . ") Tj ET\n";
            $y -= 24;
        }

        // Payment history summary if available
        if (!empty($data['payments'])) {
            $py = 310;
            $s .= sprintf("BT /F2 10 Tf %.3f %.3f %.3f rg 42 {$py} Td (RECORDED PAYMENTS) Tj ET\n", $pr, $pg, $pb);
            $py -= 16;
            foreach (array_slice($data['payments'], 0, 5) as $p) {
                $line = sprintf('%s  |  %s  |  %s  |  %s', $p['payment_reference'], $p['paid_at'] ?? $p['created_at'], strtoupper($p['payment_method']), $p['amount_fmt']);
                $s .= "BT /F1 8.5 Tf 0.3 0.3 0.3 rg 42 {$py} Td (" . self::escapePdf($line) . ") Tj ET\n";
                $py -= 14;
            }
        }

        // Footer
        $s .= "0.85 0.85 0.85 RG 0.5 w 42 78 m 553 78 l S\n";
        $s .= "BT /F1 8.5 Tf 0.45 0.45 0.45 rg 42 60 Td (Thank you for choosing " . self::escapePdf($brandName) . ". Generated electronically; valid without signature.) Tj ET\n";
        $s .= "BT /F1 8 Tf 0.55 0.55 0.55 rg 42 46 Td (Support: " . self::escapePdf($email . ' | ' . $phone) . ") Tj ET\n";

        return $this->compileSinglePage($s);
    }

    public function buildTableReportPdf(string $title, string $brandName, string $primaryHex, array $headers, array $rows, array $summaryLines = []): string
    {
        [$pr, $pg, $pb] = self::hexToRgbNorm($primaryHex);
        $s = '';
        $s .= sprintf("%.3f %.3f %.3f rg 0 760 595 82 re f\n", $pr, $pg, $pb);
        $s .= "BT /F2 16 Tf 1 1 1 rg 36 805 Td (" . self::escapePdf($brandName) . " - " . self::escapePdf($title) . ") Tj ET\n";
        $s .= "BT /F1 9 Tf 0.88 0.88 0.88 rg 36 783 Td (Generated on " . date('Y-m-d H:i') . ") Tj ET\n";

        $y = 730;
        foreach ($summaryLines as $line) {
            $s .= "BT /F2 9.5 Tf 0.15 0.15 0.15 rg 36 {$y} Td (" . self::escapePdf($line) . ") Tj ET\n";
            $y -= 16;
        }

        $y -= 10;
        $s .= sprintf("%.3f %.3f %.3f rg 36 {$y} 523 22 re f\n", $pr, $pg, $pb);
        $colWidth = (int) floor(515 / max(1, count($headers)));
        foreach ($headers as $idx => $h) {
            $x = 42 + ($idx * $colWidth);
            $s .= "BT /F2 8.5 Tf 1 1 1 rg {$x} " . ($y + 7) . " Td (" . self::escapePdf(substr((string) $h, 0, 18)) . ") Tj ET\n";
        }

        $y -= 18;
        foreach (array_slice($rows, 0, 28) as $row) {
            $vals = array_values($row);
            foreach ($vals as $idx => $val) {
                $x = 42 + ($idx * $colWidth);
                $s .= "BT /F1 8 Tf 0.2 0.2 0.2 rg {$x} {$y} Td (" . self::escapePdf(substr((string) $val, 0, 20)) . ") Tj ET\n";
            }
            $s .= "0.9 0.9 0.9 RG 0.4 w 36 " . ($y - 4) . " m 559 " . ($y - 4) . " l S\n";
            $y -= 18;
            if ($y < 60) {
                break;
            }
        }

        return $this->compileSinglePage($s);
    }

    private function compileSinglePage(string $contentStream): string
    {
        $objects = [
            1 => "<< /Type /Catalog /Pages 2 0 R >>",
            2 => "<< /Type /Pages /Kids [3 0 R] /Count 1 >>",
            3 => "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>",
            4 => "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>",
            5 => "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>",
            6 => "<< /Length " . strlen($contentStream) . " >>\nstream\n" . $contentStream . "endstream",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0 => 0];
        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$body}\nendobj\n";
        }

        $xrefPos = strlen($pdf);
        $count = count($objects) + 1;
        $pdf .= "xref\n0 {$count}\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= "trailer\n<< /Size {$count} /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF";

        return $pdf;
    }
}
