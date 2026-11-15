<?php
/**
 * Joomla 5/6 OpenLigaDB legacy preview template counter regressions.
 *
 * Usage: php .github/tests/joomla56_openligadb_preview_template.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

\define('_JEXEC', 1);

/**
 * Render a Joomla 5/6 OpenLigaDB preview from controlled legacy view data.
 */
final class OpenLigaDbTemplateFixture
{
    public mixed $previewSummary;

    /**
     * Capture the preview HTML without bootstrapping Joomla MVC.
     *
     * @param mixed $summary Malformed or valid remote preview counters.
     * @return string HTML emitted by the legacy extension template.
     */
    public function render(mixed $summary): string
    {
        $this->previewSummary = $summary;
        ob_start();

        try {
            require dirname(__DIR__, 2)
                . '/site/extensions/jsmopenligadb/admin/views/jsmopenligadb/tmpl/default.php';

            return (string) ob_get_clean();
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }
    }
}

/**
 * Assert the rendered counter numbers remain nonnegative and well formed.
 *
 * @param string $html Output from one isolated preview rendering.
 * @param list<int> $expected Summary values in match/team/venue/goal order.
 * @return void
 */
function assertPreviewCounts(string $html, array $expected): void
{
    preg_match_all('/<dd class="col-sm-6">([0-9]+)<\/dd>/', $html, $matches);
    $actual = array_map('intval', $matches[1]);

    if ($actual !== $expected) {
        throw new \RuntimeException(
            'OpenLigaDB preview counts changed: ' . json_encode($actual)
                . ', expected ' . json_encode($expected)
        );
    }
}

set_error_handler(static function (int $level, string $message): never {
    throw new \ErrorException($message, 0, $level);
});

$view = new OpenLigaDbTemplateFixture();

assertPreviewCounts($view->render([
    'matches' => ['broken'],
    'teams' => -5,
    'playgrounds' => '12',
    'goals' => 'not-a-number',
]), [0, 0, 12, 0]);

assertPreviewCounts($view->render([
    'matches' => PHP_INT_MAX . '0',
    'teams' => true,
    'playgrounds' => new \stdClass(),
    'goals' => '10',
]), [0, 0, 0, 10]);

assertPreviewCounts($view->render(null), [0, 0, 0, 0]);
assertPreviewCounts($view->render('wrong payload'), [0, 0, 0, 0]);
assertPreviewCounts($view->render([
    'matches' => 120,
    'teams' => 7,
    'playgrounds' => 2,
    'goals' => 15,
]), [120, 7, 2, 15]);

restore_error_handler();

echo "Joomla 5/6 OpenLigaDB preview template counters passed.\n";
