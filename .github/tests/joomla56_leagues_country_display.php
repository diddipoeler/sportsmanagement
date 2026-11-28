<?php
/**
 * Verify the native leagues layout preserves and translates assigned countries.
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\HTML {
    class HTMLHelper {
        /** Suppress unrelated Joomla controls in the layout fixture. */
        public static function _(string $task, mixed ...$args): string { return ''; }
    }
}
namespace Joomla\CMS\Language {
    class Text {
        /** Translate the fixture country labels as Joomla would. */
        public static function _(string $key): string {
            return ['COUNTRY_DEU' => 'Deutschland', 'COUNTRY_FRA' => 'Frankreich'][$key] ?? $key;
        }
    }
}
namespace Joomla\CMS\Router {
    class Route {
        /** Return the generated route without requiring a Joomla application. */
        public static function _(string $route): string { return $route; }
    }
}
namespace {
    define('_JEXEC', 1);

    final class LeagueLayoutFixture {
        public array $items = [];
        public array $inlineOptions = [];
        public mixed $filterForm = null;
        public object $state;
        public object $pagination;

        /** Set up the state and pagination used by the real list template. */
        public function __construct() {
            $this->state = new class {
                /** Supply the default list ordering and direction. */
                public function get(string $key, mixed $default): mixed { return $default; }
            };
            $this->pagination = new class {
                /** Suppress the unrelated list pagination footer. */
                public function getListFooter(): string { return ''; }
            };
        }

        /** Provide an authorised user to exercise the editable country selector. */
        public function getCurrentUser(): object {
            return new class {
                /** Permit the template's edit and publication controls. */
                public function authorise(string $action, string $asset): bool { return true; }
            };
        }

        /** Escape ordinary league labels using the Joomla view contract. */
        public function escape(mixed $value): string {
            return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        /** Render the actual production template with one league and country catalogue. */
        public function render(string $country, array $options): string {
            $this->items = [(object) [
                'id' => 7, 'name' => 'League', 'short_name' => 'L', 'sportstype' => 'Football',
                'country' => $country, 'associations' => 0, 'agegroup_id' => 0,
                'published_act_season' => 1, 'champions_complete' => 0, 'published' => 1,
            ]];
            $this->inlineOptions = ['countries' => $options, 'associations' => [], 'agegroups' => []];
            ob_start();
            try {
                require dirname(__DIR__, 2) . '/admin/tmpl/leagues/default.php';
                return (string) ob_get_contents();
            } finally {
                ob_end_clean();
            }
        }
    }

    $view = new LeagueLayoutFixture();
    $options = [
        (object) ['value' => 'DEU', 'text' => 'COUNTRY_DEU'],
        (object) ['value' => 'deu', 'text' => 'Duplicate'],
        (object) ['value' => 'FRA', 'text' => 'COUNTRY_FRA'],
    ];

    foreach ([
        ['DEU', $options, '<option value="DEU" selected>Deutschland</option>'],
        ['deu', $options, '<option value="deu" selected>Deutschland</option>'],
        [' DEU ', $options, '<option value=" DEU " selected>Deutschland</option>'],
        ['FRA', $options, '<option value="FRA" selected>Frankreich</option>'],
        ['WAL', $options, '<option value="WAL" selected>WAL</option>'],
        ['DEU', [], '<option value="DEU" selected>DEU</option>'],
        ['<bad>', [], '<option value="&lt;bad&gt;" selected>&lt;bad&gt;</option>'],
        ['', $options, '<option value=""></option>'],
    ] as [$stored, $catalogue, $expected]) {
        $markup = $view->render($stored, $catalogue);
        if (!preg_match('~<select[^>]*name="country7"[^>]*>(.*?)</select>~s', $markup, $matches)
            || !str_contains($matches[1], $expected)
            || substr_count($matches[1], ' selected') !== ($stored === '' ? 0 : 1)
            || str_contains($matches[1], 'Duplicate')) {
            throw new \RuntimeException('Assigned league country was lost or rendered incorrectly.');
        }
    }

    echo "League country display regressions: OK\n";
}
