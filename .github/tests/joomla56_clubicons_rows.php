<?php
/**
 * Joomla 5/6 Club Icons layout and external-link regression tests.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

\defined('_JEXEC') || define('_JEXEC', 1);

$root = dirname(__DIR__, 2);

$render = static function (array $rankedIds, array $availableIds, int $perRow) use ($root): string {
    $params = new class($perRow) {
        public function __construct(private int $perRow)
        {
        }

        public function get(string $name, mixed $default = null): mixed
        {
            return $name === 'iconsperrow' ? $this->perRow : $default;
        }
    };

    $module = (object) ['module' => 'mod_sportsmanagement_clubicons', 'id' => 77];
    $ranking = array_map(
        static fn (int $id): object => (object) ['projectteamid' => $id],
        $rankedIds
    );
    $teams = [];
    foreach ($availableIds as $id) {
        $teams[$id] = [
            'name' => 'Team ' . $id,
            'link' => '',
            'logo_url' => '',
        ];
    }
    $count = count($teams);

    ob_start();
    try {
        require $root . '/modules/mod_sportsmanagement_clubicons/tmpl/default.php';
        return (string) ob_get_contents();
    } finally {
        ob_end_clean();
    }
};

$cases = [
    ['single full row', [1], [1], 1, 1, 1],
    ['two complete cells', [1, 2], [1, 2], 2, 1, 2],
    ['two rows, last partial', [1, 2, 3], [1, 2, 3], 2, 2, 3],
    ['ignore missing team data', [1, 999, 2], [1, 2], 2, 1, 2],
    ['no visible clubs', [999], [], 2, 0, 0],
];

foreach ($cases as [$name, $ranked, $available, $perRow, $expectedRows, $expectedCells]) {
    $html = $render($ranked, $available, $perRow);
    preg_match_all('/<tr(?:\s|>)/', $html, $rows);
    preg_match_all('/<td(?:\s|>)/', $html, $cells);
    $rowCount = count($rows[0]);
    $cellCount = count($cells[0]);

    if ($rowCount !== $expectedRows || $cellCount !== $expectedCells) {
        throw new RuntimeException(sprintf(
            '%s: expected %d rows and %d cells; got %d rows and %d cells',
            $name,
            $expectedRows,
            $expectedCells,
            $rowCount,
            $cellCount
        ));
    }
}

require_once $root . '/modules/mod_sportsmanagement_clubicons/src/Helper/ClubiconsHelper.php';
$helper = new \Diddipoeler\Module\SportsManagementClubicons\Site\Helper\ClubiconsHelper();
$validator = new ReflectionMethod($helper, 'externalWebsiteUrl');

foreach ([
    ['https://example.org/team', 'https://example.org/team'],
    ['http://example.org', 'http://example.org'],
    ['javascript:alert(1)', ''],
    ['data:text/html,hello', ''],
    ['//example.org/team', ''],
    ['https://user:secret@example.org/team', ''],
] as [$input, $expected]) {
    $actual = $validator->invoke($helper, $input);
    if ($actual !== $expected) {
        throw new RuntimeException('Unexpected external URL validation for ' . $input);
    }
}

echo "Club Icons layout and URL regressions OK\n";
