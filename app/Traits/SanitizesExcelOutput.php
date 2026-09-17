<?php

namespace App\Traits;

/**
 * Neutralizes CSV/Excel formula injection in exported cell values.
 *
 * maatwebsite/excel does not escape cell content — a string starting with
 * =, +, -, @ (or tab/CR) is interpreted as a formula by Excel/LibreOffice/
 * Google Sheets when the file is opened, letting a value that originated as
 * free-text user input (a ticket description, a customer name, an imported
 * dispatch note) execute a formula/DDE payload for whoever opens the export.
 * Prefixing a leading apostrophe forces the cell to render as literal text.
 */
trait SanitizesExcelOutput
{
    protected function sanitizeExcelValue($value)
    {
        if (! is_string($value) || $value === '') {
            return $value;
        }

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
