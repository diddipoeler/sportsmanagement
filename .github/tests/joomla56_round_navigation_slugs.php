<?php
/**
 * Joomla 5/6 results navigation slug validation and stale link regression.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Joomla\CMS\Language {
    final class Text
    {
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\HTML {
    final class HTMLHelper
    {
        public static array $optionLabels = [];
        public static string $selectedValue = '';

        public static function link(string $url, string $label): string
        {
            return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . $label . '</a>';
        }

        public static function _(string $action, mixed ...$args): mixed
        {
            if ($action === 'select.option') {
                self::$optionLabels[] = $args[1];

                return (object) ['value' => $args[0], 'text' => $args[1]];
            }

            if ($action === 'select.genericlist') {
                self::$selectedValue = $args[5];

                return implode('|', array_map(
                    static fn (object $o): string => $o->value,
                    $args[0]
                ));
            }

            throw new \RuntimeException('Unexpected Joomla HTML helper.');
        }
    }
}

namespace Joomla\CMS\Router {
    final class Route
    {
        public static function _(string $url): string { return $url; }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        public static function buildQuery(array $data): string
        {
            return http_build_query($data);
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementSiteApplicationResolver
    {
        public static object $app;

        public static function resolve(): \Joomla\CMS\Application\CMSApplicationInterface
        {
            return self::$app;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    final class ResultsDataModel
    {
        public static array $rounds = [];
        public static array $options = [];

        public function setDatabaseSelector(int $value): void {}
        public function setProjectId(int $value): void {}
        public function getRounds(string $order, bool $includePublished): array
        {
            return self::$rounds;
        }
        public function getRoundOptions(string $order): array
        {
            return self::$options;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Helper/RoundPaginationHelper.php';

    $helper = \Diddipoeler\Component\SportsManagement\Site\Helper\RoundPaginationHelper::class;
    $slug = new \ReflectionMethod($helper, 'roundIdAndSlug');

    foreach ([
        ['0007:final', ['id' => 7, 'slug' => '7:final']],
        [7, ['id' => 7, 'slug' => '7']],
        ['0009', ['id' => 9, 'slug' => '9']],
        ['12:zweite-runde', ['id' => 12, 'slug' => '12:zweite-runde']],
        ['15:überraschung', ['id' => 15, 'slug' => '15:überraschung']],
        ['8:' . str_repeat('a', 254), ['id' => 8, 'slug' => '8:' . str_repeat('a', 254)]],
        ['8:' . str_repeat('a', 255), null],
        ['8:' . str_repeat('ü', 127), null],
        [str_repeat('0', 257) . '9', null],
        ['0', null],
        ['0000:bad', null],
        ['3junk', null],
        ['7:bad:extra', null],
        ['7:bad<script>', null],
        ['-7', null],
        ['7.5', null],
        ['999999999999999999999999999999', null],
        [true, null],
        [['7'], null],
        [new \stdClass(), null],
    ] as [$value, $expected]) {
        if ($slug->invoke(null, $value) !== $expected) {
            throw new \RuntimeException('Invalid round pagination slug: ' . var_export($value, true));
        }
    }

    // Older callers pass selectors through untyped navigation parameters.
    // Invalid or partially numeric selectors must never select another DB.
    $databaseSelector = new \ReflectionMethod($helper, 'databaseSelector');
    foreach ([
        [0, 0], [1, 1], ['0', 0], ['1', 1],
        ['0000', 0], ['00001', 1],
        [false, null], [true, null], [[], null], [['1'], null],
        [-1, null], [2, null], [0.0, null], ['2', null],
        ['-1', null], ['1junk', null], [' 1', null],
        ['', null], [str_repeat('0', 65), null],
    ] as [$raw, $expected]) {
        if ($databaseSelector->invoke(null, $raw) !== $expected) {
            throw new \RuntimeException('Invalid matchday database selection accepted.');
        }
    }

    $input = new class {
        public ?int $currentRound = 2;
        public int $lastRoundDefault = -1;

        public function getCmd(string $name, string $default = ''): string
        {
            return $name === 'option' ? 'com_sportsmanagement' : $default;
        }

        public function getInt(string $name, int $default = 0): int
        {
            if ($name === 'r') {
                $this->lastRoundDefault = $default;
                return $this->currentRound ?? $default;
            }

            return $default;
        }
    };

    $app = new class($input) implements \Joomla\CMS\Application\CMSApplicationInterface {
        public function __construct(private object $input) {}
        public function getInput(): object { return $this->input; }
    };
    \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver::$app = $app;

    $model = \Diddipoeler\Component\SportsManagement\Site\Model\ResultsDataModel::class;
    $model::$rounds = [
        (object) ['id' => '0002:opening', 'roundcode' => 1],
        (object) ['id' => '7:final', 'roundcode' => 2],
        (object) ['id' => '8invalid', 'roundcode' => 3],
        (object) ['id' => '999999999999999999999999', 'roundcode' => 4],
    ];

    $project = (object) ['id' => 11, 'season_id' => 5, 'current_round' => 2];
    $html = $helper::pagenav($project);

    if (!str_contains($html, 'r=7%3Afinal')
        || str_contains($html, '8invalid')
        || !str_contains($helper::$nextlink, 'r=7%3Afinal')
        || $helper::$prevlink !== '') {
        throw new \RuntimeException('First round navigation includes invalid links.');
    }

    // Project aliases from imported data must correspond to the selected
    // numeric project ID and must never turn arrays into URL parameters.
    foreach ([
        [['id' => 11, 'slug' => '11:team-a'], 'p=11%3Ateam-a'],
        [['id' => 11, 'slug' => '12:other'], 'p=11'],
        [['id' => 11, 'slug' => ['11:invalid']], 'p=11'],
        [['id' => 11, 'slug' => '11:bad<script>'], 'p=11'],
    ] as [$fields, $expectedParam]) {
        $projectWithSlug = (object) ($fields + ['season_id' => 5, 'current_round' => 2]);
        $slugHtml = $helper::pagenav($projectWithSlug);

        if (!str_contains($slugHtml, $expectedParam)
            || str_contains($slugHtml, 'p%5B')
            || str_contains($slugHtml, 'Array')) {
            throw new \RuntimeException('Imported project slug was not normalised.');
        }
    }

    // Older project imports may carry partially numeric seasons. A cast
    // would silently route a request to an unrelated season.
    $seasonParser = new \ReflectionMethod($helper, 'nonNegativeSeasonId');
    foreach ([
        [0, 0], [11, 11], ['0000', 0], ['00011', 11],
        [-1, 0], [true, 0], [['11'], 0], ['11junk', 0],
        ['11:season', 0], [' 11', 0], ['99999999999999999999999', 0],
    ] as [$value, $expected]) {
        if ($seasonParser->invoke(null, $value) !== $expected) {
            throw new \RuntimeException('Malformed navigation season selector was accepted.');
        }
    }

    $projectWithInvalidSeason = (object) [
        'id' => 11, 'season_id' => '9junk', 'current_round' => 2,
    ];
    $seasonHtml = $helper::pagenav($projectWithInvalidSeason, 0, ['10']);
    if (!str_contains($seasonHtml, 's=0')
        || str_contains($seasonHtml, 's=9')
        || str_contains($seasonHtml, 's=1')) {
        throw new \RuntimeException('Invalid project season was loosely cast in round links.');
    }

    $seasonHtml = $helper::pagenav($projectWithInvalidSeason, 0, '00012');
    if (!str_contains($seasonHtml, 's=12')) {
        throw new \RuntimeException('Valid zero-padded season was lost in navigation.');
    }

    // Imported round codes must not be loosely cast into adjacent matchdays
    // or rendered as meaningless zero/negative labels.
    $model::$rounds = [
        (object) ['id' => '2:opening', 'roundcode' => '0001'],
        (object) ['id' => '3:invalid', 'roundcode' => '2junk'],
        (object) ['id' => '4:nested', 'roundcode' => ['2']],
        (object) ['id' => '5:zero', 'roundcode' => 0],
        (object) ['id' => '7:final', 'roundcode' => 2],
    ];
    $roundCodeHtml = $helper::pagenav($project);
    if (!str_contains($roundCodeHtml, 'r=7%3Afinal')
        || str_contains($roundCodeHtml, 'r=3%3Ainvalid')
        || str_contains($roundCodeHtml, 'r=4%3Anested')
        || str_contains($roundCodeHtml, 'r=5%3Azero')) {
        throw new \RuntimeException('Malformed imported matchday sequence was rendered.');
    }
    $model::$rounds = [
        (object) ['id' => '0002:opening', 'roundcode' => 1],
        (object) ['id' => '7:final', 'roundcode' => 2],
        (object) ['id' => '8invalid', 'roundcode' => 3],
        (object) ['id' => '999999999999999999999999', 'roundcode' => 4],
    ];

    $input->currentRound = 7;
    $html = $helper::pagenav($project);

    if (!str_contains($html, 'r=2%3Aopening')
        || !str_contains($helper::$prevlink, 'r=2%3Aopening')
        || $helper::$nextlink !== '') {
        throw new \RuntimeException('Second round navigation lost the previous round link.');
    }
    $input->currentRound = 2;

    foreach (['1junk', ['1'], true, 2, -1, ' 0'] as $invalid) {
        if ($helper::pagenav($project, $invalid) !== ''
            || $helper::$prevlink !== '' || $helper::$nextlink !== '') {
            throw new \RuntimeException('Invalid database selector generated round links.');
        }
    }

    $projectId = new \ReflectionMethod($helper, 'positiveProjectId');
    foreach ([
        [11, 11], ['00011', 11], [0, 0], [-1, 0], ['0', 0],
        ['11junk', 0], ['11:alias', 0], [' 11', 0], [true, 0],
        [['11'], 0], [new \stdClass(), 0],
        ['999999999999999999999999999', 0],
    ] as [$raw, $expected]) {
        if ($projectId->invoke(null, $raw) !== $expected) {
            throw new \RuntimeException('Malformed project ID passed pagination validation.');
        }
    }

    foreach ([['11'], '11junk', true, '999999999999999999999999999'] as $invalidId) {
        $invalidProject = (object) ['id' => $invalidId, 'season_id' => 5];

        if ($helper::pagenav($invalidProject) !== ''
            || $helper::selectNavigation($invalidProject) !== ''
            || $helper::$prevlink !== '' || $helper::$nextlink !== '') {
            throw new \RuntimeException('Malformed project ID generated round navigation.');
        }
    }

    if ($helper::selectNavigation($project, 2) !== '') {
        throw new \RuntimeException('Dropdown accepted unsupported database selector.');
    }

    if ($helper::pagenav((object) ['id' => 0]) !== ''
        || $helper::$prevlink !== '' || $helper::$nextlink !== '') {
        throw new \RuntimeException('Invalid project reused a previous page navigation link.');
    }

    $model::$options = [
        (object) ['value' => '0002:opening', 'text' => 'Opening'],
        (object) ['value' => '7:final', 'text' => 'Final'],
        (object) ['value' => '8invalid', 'text' => 'Bad'],
    ];
    $select = $helper::selectNavigation($project);

    if (!str_contains($select, 'r=2%3Aopening')
        || !str_contains($select, 'r=7%3Afinal')
        || str_contains($select, '8invalid')) {
        throw new \RuntimeException('Results dropdown accepted malformed round IDs.');
    }

    // Oversized imported round aliases must be ignored by both the matchday
    // pagination and the dropdown, not merely rejected by the parser.
    $longRoundAlias = '18:' . str_repeat('x', 255);
    $model::$rounds = [
        (object) ['id' => '2:opening', 'roundcode' => 1],
        (object) ['id' => $longRoundAlias, 'roundcode' => 2],
        (object) ['id' => '7:final', 'roundcode' => 3],
    ];
    $input->currentRound = 2;
    $longRoundHtml = $helper::pagenav($project);
    if (str_contains($longRoundHtml, $longRoundAlias)
        || str_contains($longRoundHtml, 'r=18')
        || !str_contains($helper::$nextlink, 'r=7%3Afinal')) {
        throw new \RuntimeException('Oversized imported round alias reached pagination.');
    }

    $model::$options = [
        (object) ['value' => '2:opening', 'text' => 'Opening'],
        (object) ['value' => $longRoundAlias, 'text' => 'Too long'],
        (object) ['value' => '7:final', 'text' => 'Final'],
    ];
    $longRoundOptions = $helper::selectNavigation($project);
    if (str_contains($longRoundOptions, 'r=18')
        || !str_contains($longRoundOptions, 'r=7%3Afinal')) {
        throw new \RuntimeException('Oversized imported round alias reached dropdown.');
    }

    // Old imported rounds can carry non-string labels or control characters.
    // Joomla must receive safe fallback strings rather than PHP array casts.
    $model::$options = [
        (object) ['value' => '2:opening', 'text' => ['untrusted']],
        (object) ['value' => '7:final', 'text' => "Label\nInjected"],
        (object) ['value' => '9:semifinal', 'text' => str_repeat('x', 257)],
        (object) ['value' => '10:regular', 'text' => 'Regular round'],
    ];
    \Joomla\CMS\HTML\HTMLHelper::$optionLabels = [];

    $helper::selectNavigation($project);
    if (\Joomla\CMS\HTML\HTMLHelper::$optionLabels !== [
        '2:opening', '7:final', '9:semifinal', 'Regular round',
    ]) {
        throw new \RuntimeException('Malformed round option labels were not normalised.');
    }

    // When no explicit round is submitted, corrupt project metadata must
    // not silently select a different existing round through integer casts.
    $model::$rounds = [
        (object) ['id' => '2:opening', 'roundcode' => 1],
        (object) ['id' => '7:final', 'roundcode' => 2],
    ];
    $model::$options = [
        (object) ['value' => '2:opening', 'text' => 'Opening'],
        (object) ['value' => '7:final', 'text' => 'Final'],
    ];
    $input->currentRound = null;
    $invalidDefaultRound = (object) [
        'id' => 11, 'season_id' => 5, 'current_round' => '7junk',
    ];
    $helper::pagenav($invalidDefaultRound);
    if ($input->lastRoundDefault !== 0
        || $helper::$prevlink !== ''
        || !str_contains($helper::$nextlink, 'r=7%3Afinal')) {
        throw new \RuntimeException('Malformed default round selected an unrelated matchday.');
    }

    $helper::selectNavigation($invalidDefaultRound);
    if ($input->lastRoundDefault !== 0) {
        throw new \RuntimeException('Dropdown loosely cast malformed default round.');
    }

    $validDefaultRound = (object) [
        'id' => 11, 'season_id' => 5, 'current_round' => '0007',
    ];
    $helper::pagenav($validDefaultRound);
    if ($input->lastRoundDefault !== 7) {
        throw new \RuntimeException('Valid zero-padded default round was discarded.');
    }
    $helper::selectNavigation($validDefaultRound);
    if ($input->lastRoundDefault !== 7) {
        throw new \RuntimeException('Dropdown lost valid zero-padded current round.');
    }
    $input->currentRound = 2;

    // A deleted, malformed or missing current round must select an
    // existing option rather than a synthetic r=0 or stale-ID URL.
    $input->currentRound = null;
    $helper::selectNavigation($invalidDefaultRound);
    if (!str_contains(\Joomla\CMS\HTML\HTMLHelper::$selectedValue, 'r=2%3Aopening')
        || str_contains(\Joomla\CMS\HTML\HTMLHelper::$selectedValue, '&r=0')) {
        throw new \RuntimeException('Missing current round created a synthetic dropdown URL.');
    }

    $input->currentRound = 999;
    $helper::selectNavigation($project);
    if (!str_contains(\Joomla\CMS\HTML\HTMLHelper::$selectedValue, 'r=2%3Aopening')
        || str_contains(\Joomla\CMS\HTML\HTMLHelper::$selectedValue, '&r=999')) {
        throw new \RuntimeException('Deleted current round remained selected in dropdown.');
    }

    $input->currentRound = 7;
    $helper::selectNavigation($project);
    if (!str_contains(\Joomla\CMS\HTML\HTMLHelper::$selectedValue, 'r=7%3Afinal')) {
        throw new \RuntimeException('Valid selected round lost its dropdown selection.');
    }
    $input->currentRound = 2;

    // Two imported rows can share a numeric ID with different aliases.
    // Duplicates must neither create a self-referential next link nor yield
    // multiple select entries for one logical round.
    $model::$rounds = [
        (object) ['id' => '2:first', 'roundcode' => 1],
        (object) ['id' => '0002:duplicate', 'roundcode' => 2],
        (object) ['id' => '7:last', 'roundcode' => 3],
    ];
    $input->currentRound = 2;
    $duplicateNav = $helper::pagenav($project);
    if (str_contains($duplicateNav, 'duplicate')
        || !str_contains($helper::$nextlink, 'r=7%3Alast')
        || str_contains($helper::$nextlink, 'r=2%3Aduplicate')) {
        throw new \RuntimeException('Duplicate round ID changed pagination neighbour.');
    }

    $model::$options = [
        (object) ['value' => '2:first', 'text' => 'First'],
        (object) ['value' => '0002:duplicate', 'text' => 'Duplicate'],
        (object) ['value' => '7:last', 'text' => 'Last'],
    ];
    \Joomla\CMS\HTML\HTMLHelper::$optionLabels = [];
    $duplicateSelect = $helper::selectNavigation($project);
    if (\Joomla\CMS\HTML\HTMLHelper::$optionLabels !== ['First', 'Last']
        || str_contains($duplicateSelect, 'duplicate')) {
        throw new \RuntimeException('Duplicate round ID appeared in dropdown.');
    }

    $model::$options = [(object) ['value' => '7<script>', 'text' => 'Bad']];

    if ($helper::selectNavigation($project) !== '') {
        throw new \RuntimeException('Dropdown was rendered without a valid round.');
    }

    echo "Joomla 5/6 round navigation slug and stale link regressions passed.\n";
}
