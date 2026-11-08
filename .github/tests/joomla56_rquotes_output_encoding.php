<?php
/**
 * Regression tests for safe random-quote rendering on Joomla 5 and 6.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

define('_JEXEC', 1);
require_once dirname(__DIR__, 2)
    . '/modules/mod_sportsmanagement_rquotes/src/Helper/QuoteText.php';

use Diddipoeler\Module\SportsManagementRquotes\Site\Helper\QuoteText;

$cases = [
    ['Plain quote', 'Plain quote'],
    ['A & B', 'A &amp; B'],
    ['A &amp; B', 'A &amp; B'],
    ['line<br>two', "line<br>\ntwo"],
    ["line\nsecond", "line<br>\nsecond"],
    ['<b>Bold</b>', 'Bold'],
    ['<img src=x onerror=alert(1)>', ''],
    ['<script>alert("bad")</script>', 'alert(&quot;bad&quot;)'],
    ['&lt;script&gt;alert(1)&lt;/script&gt;', '&lt;script&gt;alert(1)&lt;/script&gt;'],
    [['text'], ''],
    [new stdClass(), ''],
];

foreach ($cases as [$input, $expected]) {
    $rendered = QuoteText::render($input);

    if ($rendered !== $expected) {
        throw new RuntimeException('Quote output mismatch for ' . var_export($input, true)
            . ': ' . var_export($rendered, true));
    }

    if (preg_match('~<(?!br(?:\s*/?)>)[^>]+>~i', $rendered) === 1) {
        throw new RuntimeException('Unsafe HTML tag survived quote sanitisation.');
    }
}

echo "Joomla 5/6 safe random-quote output passed.\n";
