<?php
/**
 * Legacy parseCSV compatibility helper used by SportsManagement imports.
 *
 * @version    0.4.3 beta (PHP 8 compatibility maintenance)
 * @author     Jim Myhrberg (jim@zydev.info)
 * @copyright  Copyright (c) 2007 Jim Myhrberg
 * @license    MIT License; original notice retained below
 */

defined('_JEXEC') or die('Restricted access');

/*
 * parseCSV v0.4.3 beta
 * http://code.google.com/p/parsecsv-for-php/
 *
 * Based on the concept of Ming Hong Ng's CsvFileParser class.
 *
 * Copyright (c) 2007 Jim Myhrberg (jim@zydev.info).
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class JSMparseCSV
{
    public $heading = true;
    public $fields = [];
    public $sort_by = null;
    public $sort_reverse = false;
    public $sort_type = null;
    public $delimiter = ',';
    public $enclosure = '"';
    public $conditions = null;
    public $offset = null;
    public $limit = null;
    public $auto_depth = 15;
    public $auto_non_chars = "a-zA-Z0-9\n\r";
    public $auto_preferred = ",;\t.:|";
    public $convert_encoding = false;
    public $input_encoding = 'ISO-8859-1';
    public $output_encoding = 'ISO-8859-1';
    public $linefeed = "\r\n";
    public $output_delimiter = ',';
    public $output_filename = 'data.csv';
    public $keep_file_data = false;
    public $file = null;
    public $file_data = null;
    public $error = 0;
    public $error_info = [];
    public $titles = [];
    public $data = [];

    public function __construct($input = null, $offset = null, $limit = null, $conditions = null)
    {
        $this->initialize($input, $offset, $limit, $conditions);
    }

    /**
     * Historical constructor adapter retained for legacy callers.
     *
     * @deprecated Use __construct().
     */
    public function parseCSV($input = null, $offset = null, $limit = null, $conditions = null)
    {
        $this->initialize($input, $offset, $limit, $conditions);
    }

    public function parse($input = null, $offset = null, $limit = null, $conditions = null)
    {
        if ($input === null) {
            $input = $this->file;
        }

        $this->applyOptions($offset, $limit, $conditions);

        if ($input === null || $input === '') {
            return true;
        }

        if (is_string($input) && is_readable($input)) {
            $this->data = $this->parse_file($input);
        } else {
            $this->file_data = $this->convertInput((string) $input);
            $this->data = $this->parse_string();
        }

        return $this->data !== false;
    }

    public function parse_file($file = null)
    {
        if ($file === null) {
            $file = $this->file;
        }

        if (!$this->load_data($file)) {
            return false;
        }

        return $this->parse_string();
    }

    public function load_data($input = null)
    {
        if ($input === null) {
            $input = $this->file;
        }

        if ($input === null || $input === '') {
            return false;
        }

        if (is_string($input) && is_file($input) && is_readable($input)) {
            $this->file = $input;
            $data = $this->_rfile($input);

            if ($data === false) {
                return false;
            }
        } else {
            $data = (string) $input;
        }

        if ($this->file && preg_match('/\.php$/i', (string) $this->file)
            && preg_match('/<\?.*?\?>(.*)/ims', $data, $strip)
        ) {
            $data = ltrim($strip[1]);
        }

        $data = $this->convertInput($data);

        if ($data !== '' && !str_ends_with($data, "\n")) {
            $data .= "\n";
        }

        $this->file_data = $data;

        return true;
    }

    public function _rfile($file = null)
    {
        if (!is_string($file) || !is_readable($file)) {
            return false;
        }

        $data = file_get_contents($file);

        return $data === false ? false : $data;
    }

    public function parse_string($data = null)
    {
        if ($data === null || $data === '') {
            if (!$this->_check_data()) {
                return false;
            }

            $data = (string) $this->file_data;
        } else {
            $data = $this->convertInput((string) $data);
        }

        if (str_starts_with($data, "\xEF\xBB\xBF")) {
            $data = substr($data, 3);
        }

        $records = $this->readRecords($data, (string) $this->delimiter);

        if ($records === false) {
            return false;
        }

        $head = $this->fields ? array_values($this->fields) : [];
        $rows = [];
        $firstRecord = true;

        foreach ($records as $record) {
            if ($record === [null] || $record === []) {
                continue;
            }

            if ($firstRecord && $this->heading) {
                if ($head === []) {
                    $head = array_map([$this, 'normalizeHeading'], $record);
                }

                $firstRecord = false;
                continue;
            }

            $firstRecord = false;

            if ($head !== []) {
                $row = [];
                foreach ($head as $index => $field) {
                    $key = $this->normalizeHeading((string) $field);
                    $row[$key] = isset($record[$index]) ? trim((string) $record[$index]) : '';
                }
            } else {
                $row = array_map(
                    static fn($value): string => trim((string) $value),
                    $record
                );
            }

            if (!$this->_validate_row_conditions($row, $this->conditions)) {
                continue;
            }

            $rows[] = $row;
        }

        $this->titles = array_map(
            fn($value): string => $this->normalizeHeading((string) $value),
            $head
        );

        $rows = $this->sortAndSlice($rows);

        if (!$this->keep_file_data) {
            $this->file_data = null;
        }

        return $rows;
    }

    public function _check_data($file = null)
    {
        if ($this->file_data === null || $this->file_data === '') {
            return $this->load_data($file ?? $this->file);
        }

        return true;
    }

    public function _validate_offset($current_row)
    {
        return !(
            $this->sort_by === null
            && $this->offset !== null
            && $current_row < (int) $this->offset
        );
    }

    public function _validate_row_conditions($row = [], $conditions = null)
    {
        if ($row === []) {
            return false;
        }

        if ($conditions === null || $conditions === '' || $conditions === []) {
            return true;
        }

        if (is_array($conditions)) {
            $conditions = implode(' AND ', array_map('strval', $conditions));
        }

        $orParts = preg_split('/\s+OR\s+/i', (string) $conditions) ?: [];

        foreach ($orParts as $orPart) {
            $andParts = preg_split('/\s+AND\s+/i', trim($orPart)) ?: [];
            $matches = true;

            foreach ($andParts as $condition) {
                if ($condition !== '' && $this->_validate_row_condition($row, $condition) !== '1') {
                    $matches = false;
                    break;
                }
            }

            if ($matches) {
                return true;
            }
        }

        return false;
    }

    public function _validate_row_condition($row, $condition)
    {
        $operators = [
            'does not contain',
            'is less than or equals',
            'is greater than or equals',
            'is less than',
            'is greater than',
            'contains',
            'is not',
            'equals',
            'is',
            '!=',
            '<=',
            '>=',
            '=',
            '<',
            '>',
        ];

        $operatorPattern = implode('|', array_map(
            static fn(string $operator): string => preg_quote($operator, '/'),
            $operators
        ));

        if (!preg_match('/^(.+?)\s+(' . $operatorPattern . ')\s+(.+)$/i', trim((string) $condition), $capture)) {
            return '1';
        }

        $field = trim($capture[1]);
        $op = strtolower(trim($capture[2]));
        $value = trim($capture[3]);

        if (preg_match('/^(["\'])(.*)\1$/s', $value, $quoted)) {
            $value = stripcslashes($quoted[2]);
        }

        if (!array_key_exists($field, $row)) {
            return '1';
        }

        $actual = $row[$field];

        $matches = match ($op) {
            '=', 'equals', 'is' => $actual == $value,
            '!=', 'is not' => $actual != $value,
            '<', 'is less than' => $actual < $value,
            '>', 'is greater than' => $actual > $value,
            '<=', 'is less than or equals' => $actual <= $value,
            '>=', 'is greater than or equals' => $actual >= $value,
            'contains' => stripos((string) $actual, (string) $value) !== false,
            'does not contain' => stripos((string) $actual, (string) $value) === false,
            default => true,
        };

        return $matches ? '1' : '0';
    }

    public function save($file = null, $data = [], $append = false, $fields = [])
    {
        if ($file === null || $file === '') {
            $file = $this->file;
        }

        if (!is_string($file) || $file === '') {
            return false;
        }

        $mode = $append ? 'ab' : 'wb';
        $isPhp = (bool) preg_match('/\.php$/i', $file);

        return $this->_wfile(
            $file,
            $this->unparse($data, $fields, $append, $isPhp),
            $mode
        );
    }

    public function _wfile($file, $string = '', $mode = 'wb', $lock = LOCK_EX)
    {
        $fp = @fopen($file, $mode);

        if ($fp === false) {
            return false;
        }

        $locked = flock($fp, (int) $lock);
        $written = $locked ? fwrite($fp, (string) $string) : false;
        $closed = fclose($fp);

        return $written !== false && $closed;
    }

    public function unparse($data = [], $fields = [], $append = false, $is_php = false, $delimiter = null)
    {
        $data = is_array($data) && $data !== [] ? $data : $this->data;
        $fields = is_array($fields) && $fields !== [] ? $fields : $this->titles;
        $delimiter = $delimiter === null ? (string) $this->delimiter : (string) $delimiter;

        $string = $is_php ? "<?php header('Status: 403'); die(' '); ?>" . $this->linefeed : '';

        if ($this->heading && !$append && $fields !== []) {
            $string .= $this->csvLine($fields, $delimiter);
        }

        foreach ($data as $row) {
            $string .= $this->csvLine(is_array($row) ? array_values($row) : [(string) $row], $delimiter);
        }

        return $string;
    }

    public function _enclose_value($value = null)
    {
        $value = (string) ($value ?? '');

        if ($value === '') {
            return $value;
        }

        $needsEnclosure = str_contains($value, (string) $this->delimiter)
            || str_contains($value, (string) $this->enclosure)
            || str_contains($value, "\n")
            || str_contains($value, "\r")
            || $value[0] === ' '
            || substr($value, -1) === ' ';

        if (!$needsEnclosure) {
            return $value;
        }

        $escaped = str_replace(
            (string) $this->enclosure,
            (string) $this->enclosure . (string) $this->enclosure,
            $value
        );

        return (string) $this->enclosure . $escaped . (string) $this->enclosure;
    }

    public function output($filename = null, $data = [], $fields = [], $delimiter = null)
    {
        $filename = $filename ?: $this->output_filename;
        $delimiter = $delimiter === null ? $this->output_delimiter : $delimiter;
        $output = $this->unparse($data, $fields, false, false, $delimiter);

        if ($filename !== null) {
            header('Content-Type: text/csv; charset=' . $this->output_encoding);
            header('Content-Disposition: attachment; filename="' . basename((string) $filename) . '"');
            echo $output;
        }

        return $output;
    }

    public function encoding($input = null, $output = null)
    {
        $this->convert_encoding = true;

        if ($input !== null) {
            $this->input_encoding = $input;
        }

        if ($output !== null) {
            $this->output_encoding = $output;
        }
    }

    public function auto($file = null, $parse = true, $search_depth = null, $preferred = null, $enclosure = null)
    {
        if ($file !== null && !$this->load_data($file)) {
            return false;
        }

        if (!$this->_check_data($file)) {
            return false;
        }

        $data = (string) $this->file_data;
        $depth = max(1, (int) ($search_depth ?: $this->auto_depth));
        $preferred = (string) ($preferred ?? $this->auto_preferred);
        $enclosure = (string) ($enclosure ?? $this->enclosure);
        $candidates = array_values(array_unique(str_split($preferred)));

        $bestDelimiter = $this->delimiter;
        $bestScore = -1;

        foreach ($candidates as $candidate) {
            if ($candidate === "\r" || $candidate === "\n" || $candidate === $enclosure) {
                continue;
            }

            $records = $this->readRecords($data, $candidate, $enclosure, $depth);

            if (!is_array($records) || $records === []) {
                continue;
            }

            $counts = array_map('count', $records);
            $frequencies = array_count_values($counts);
            arsort($frequencies);
            $fieldCount = (int) array_key_first($frequencies);
            $consistentRows = (int) reset($frequencies);

            if ($fieldCount <= 1) {
                continue;
            }

            $score = ($consistentRows * 1000) + $fieldCount;

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestDelimiter = $candidate;
            }
        }

        $this->delimiter = $bestDelimiter;

        if ($parse) {
            $this->data = $this->parse_string($data);
        }

        return $this->delimiter;
    }

    public function _check_count($char, $array, $depth, $preferred)
    {
        if (!is_array($array) || $depth !== count($array)) {
            return false;
        }

        $first = null;
        $equal = null;
        $almost = false;

        foreach ($array as $value) {
            if ($first === null) {
                $first = $value;
            } elseif ($value == $first && $equal !== false) {
                $equal = true;
            } elseif ($value == $first + 1 && $equal !== false) {
                $equal = true;
                $almost = true;
            } else {
                $equal = false;
            }
        }

        if (!$equal) {
            return false;
        }

        $match = $almost ? 2 : 1;
        $pref = strpos((string) $preferred, (string) $char);
        $pref = $pref !== false ? str_pad((string) $pref, 3, '0', STR_PAD_LEFT) : '999';

        return $pref . $match . '.' . (99999 - (int) str_pad((string) $first, 5, '0', STR_PAD_LEFT));
    }

    private function initialize($input, $offset, $limit, $conditions): void
    {
        $this->applyOptions($offset, $limit, $conditions);

        if ($input !== null && $input !== '') {
            $this->parse($input);
        }
    }

    private function applyOptions($offset, $limit, $conditions): void
    {
        if ($offset !== null) {
            $this->offset = (int) $offset;
        }

        if ($limit !== null) {
            $this->limit = (int) $limit;
        }

        if ($conditions !== null && $conditions !== '' && $conditions !== []) {
            $this->conditions = $conditions;
        }
    }

    private function convertInput(string $data): string
    {
        if (!$this->convert_encoding || $data === '' || !function_exists('iconv')) {
            return $data;
        }

        $converted = @iconv(
            (string) $this->input_encoding,
            (string) $this->output_encoding . '//IGNORE',
            $data
        );

        return $converted === false ? $data : $converted;
    }

    private function readRecords(
        string $data,
        string $delimiter,
        ?string $enclosure = null,
        ?int $limit = null
    ) {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            return false;
        }

        fwrite($stream, $data);
        rewind($stream);

        $records = [];
        $enclosure = $enclosure ?? (string) $this->enclosure;

        while (($record = fgetcsv($stream, 0, $delimiter, $enclosure, '\\')) !== false) {
            $records[] = $record;

            if ($limit !== null && count($records) >= $limit) {
                break;
            }
        }

        fclose($stream);

        return $records;
    }

    private function sortAndSlice(array $rows): array
    {
        if ($this->sort_by !== null && $this->sort_by !== '') {
            $indexed = [];

            foreach ($rows as $row) {
                $baseKey = isset($row[$this->sort_by]) && $row[$this->sort_by] !== ''
                    ? (string) $row[$this->sort_by]
                    : (string) count($indexed);
                $key = $baseKey;
                $suffix = 0;

                while (array_key_exists($key, $indexed)) {
                    $key = $baseKey . '_' . $suffix++;
                }

                $indexed[$key] = $row;
            }

            $sortType = match ($this->sort_type) {
                'numeric' => SORT_NUMERIC,
                'string' => SORT_STRING,
                default => SORT_REGULAR,
            };

            if ($this->sort_reverse) {
                krsort($indexed, $sortType);
            } else {
                ksort($indexed, $sortType);
            }

            return array_slice(
                $indexed,
                max(0, (int) ($this->offset ?? 0)),
                $this->limit === null ? null : max(0, (int) $this->limit),
                true
            );
        }

        return array_slice(
            array_values($rows),
            max(0, (int) ($this->offset ?? 0)),
            $this->limit === null ? null : max(0, (int) $this->limit)
        );
    }

    private function normalizeHeading(string $heading): string
    {
        return str_replace(
            ['ü', 'ä', 'ö', 'Ü', 'Ä', 'Ö', ' '],
            ['ue', 'ae', 'oe', 'Ue', 'Ae', 'Oe', ''],
            $heading
        );
    }

    private function csvLine(array $values, string $delimiter): string
    {
        $stream = fopen('php://temp', 'r+');

        if ($stream === false) {
            return '';
        }

        fputcsv($stream, array_map('strval', $values), $delimiter, (string) $this->enclosure, '\\');
        rewind($stream);
        $line = stream_get_contents($stream);
        fclose($stream);

        if ($line === false) {
            return '';
        }

        return rtrim($line, "\r\n") . $this->linefeed;
    }
}
