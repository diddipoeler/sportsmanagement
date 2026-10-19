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
        public static function link(string $url, string $label): string
        {
            return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . $label . '</a>';
        }

        public static function _(string $action, mixed ...$args): mixed
        {
            if ($action === 'select.option') {
                return (object) ['value' => $args[0], 'text' => $args[1]];
            }

            if ($action === 'select.genericlist') {
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

    $input = new class {
        public int $currentRound = 2;

        public function getCmd(string $name, string $default = ''): string
        {
            return $name === 'option' ? 'com_sportsmanagement' : $default;
        }

        public function getInt(string $name, int $default = 0): int
        {
            return $name === 'r' ? $this->currentRound : $default;
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

    $input->currentRound = 7;
    $html = $helper::pagenav($project);

    if (!str_contains($html, 'r=2%3Aopening')
        || !str_contains($helper::$prevlink, 'r=2%3Aopening')
        || $helper::$nextlink !== '') {
        throw new \RuntimeException('Second round navigation lost the previous round link.');
    }
    $input->currentRound = 2;

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

    $model::$options = [(object) ['value' => '7<script>', 'text' => 'Bad']];

    if ($helper::selectNavigation($project) !== '') {
        throw new \RuntimeException('Dropdown was rendered without a valid round.');
    }

    echo "Joomla 5/6 round navigation slug and stale link regressions passed.\n";
}
