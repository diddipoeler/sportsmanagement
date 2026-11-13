<?php
/**
 * Regression: strict site project navigation, admin zero dates and safe project-map images.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Database {
    /** Minimal Joomla database type for isolated navigation tests. */
    interface DatabaseInterface {}
    /** Database query parameter type markers. */
    final class ParameterType {
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }
}
namespace Joomla\CMS {
    /** Joomla date service fixture for administrator date conversion. */
    final class Factory {
        /**
         * Return a timestamp-aware Joomla Date substitute.
         *
         * @param int $timestamp Unix timestamp.
         * @return object Joomla-style date service.
         */
        public static function getDate(int $timestamp): object {
            return new class($timestamp) {
                /**
                 * Hold the supplied timestamp.
                 *
                 * @param int $timestamp Unix timestamp.
                 */
                public function __construct(private int $timestamp) {}
                /**
                 * Return the saved date timestamp.
                 *
                 * @return int Original timestamp.
                 */
                public function getTimestamp(): int { return $this->timestamp; }
            };
        }
    }
}
namespace Joomla\CMS\Language {
    /** Joomla translation fixture for project country labels. */
    final class Text {
        /**
         * Return a literal country or project name.
         *
         * @param string $key Source text.
         * @return string Unmodified text.
         */
        public static function _(string $key): string { return $key; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    /** Route helper fixture returning safe synthetic ranking URLs. */
    final class SiteRouteHelper {
        /**
         * Return a deterministic local map route.
         *
         * @param string $view Component page.
         * @param array $query Ranking route parameters.
         * @return string Local component route.
         */
        public static function view(string $view, array $query): string {
            return 'index.php?view=' . rawurlencode($view);
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Helper/ProjectNavigationHelper.php';
    require_once $root . '/admin/src/Helper/SportsManagementDateHelper.php';
    require_once $root . '/modules/mod_sportsmanagement_projectmap/src/Helper/ProjectMapHelper.php';

    $db = new class implements \Joomla\Database\DatabaseInterface {
        public int $queries = 0;
        /**
         * Create a fluent navigation query fixture.
         *
         * @return self Query builder.
         */
        public function createQuery(): self { return $this; }
        /**
         * Return a quoted SQL identifier.
         *
         * @param string $identifier Table or column.
         * @param string|null $alias Optional SQL alias.
         * @return string Quoted identifier.
         */
        public function quoteName(string $identifier, ?string $alias = null): string { return $identifier; }
        /**
         * Select the project navigation columns.
         *
         * @param mixed $columns Selected columns.
         * @return self Query builder.
         */
        public function select(mixed $columns): self { return $this; }
        /**
         * Specify the project table without querying.
         *
         * @param string $table SQL table.
         * @return self Query builder.
         */
        public function from(string $table): self { return $this; }
        /**
         * Append a navigation filter.
         *
         * @param string $condition SQL condition.
         * @return self Query builder.
         */
        public function where(string $condition): self { return $this; }
        /**
         * Bind a validated project selector.
         *
         * @param string $name Parameter name.
         * @param mixed $value Bound scalar.
         * @param mixed $type Joomla parameter type.
         * @return self Query builder.
         */
        public function bind(string $name, mixed $value, mixed $type): self { return $this; }
        /**
         * Order the project search.
         *
         * @param string $order Project ordering.
         * @return self Query builder.
         */
        public function order(string $order): self { return $this; }
        /**
         * Count prepared queries without touching a database.
         *
         * @param mixed $query Query builder.
         * @param int $offset Query offset.
         * @param int $limit Result limit.
         * @return void
         */
        public function setQuery(mixed $query, int $offset, int $limit): void { ++$this->queries; }
        /**
         * Return a matching navigation project fixture.
         *
         * @return object Project row.
         */
        public function loadObject(): object { return (object) ['id' => 9]; }
    };

    $navigation = \Diddipoeler\Component\SportsManagement\Site\Helper\ProjectNavigationHelper::class;
    foreach (['12junk', true, [], ' 12', '-1', 1.5, str_repeat('9', 128)] as $invalid) {
        if ($navigation::next($db, (object) [
            'id' => 4, 'league_id' => $invalid, 'name' => 'Premier League',
        ]) !== null) {
            throw new \RuntimeException('Malformed league ID selected another project.');
        }
    }
    if ($db->queries !== 0
        || $navigation::next($db, (object) ['id' => '0004', 'league_id' => '0012', 'name' => 'Premier'])?->id !== 9
        || $db->queries !== 1) {
        throw new \RuntimeException('Valid numeric league and project selectors regressed.');
    }

    $dates = \Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDateHelper::class;
    foreach (['0000-00-00 25:00:00', '0000-00-00 23:60:00',
        '0000-00-00 12:30:60', '0000-00-00 garbage'] as $invalid) {
        if ($dates::getTimestamp($invalid) !== 0) {
            throw new \RuntimeException('Malformed zero date was converted to current time.');
        }
    }
    if ($dates::getTimestamp('0000-00-00 15:30:00') <= 0
        || $dates::getTimestamp('2024-02-29') <= 0) {
        throw new \RuntimeException('Valid admin dates no longer produce timestamps.');
    }

    $map = new \Diddipoeler\Module\SportsManagementProjectMap\Site\Helper\ProjectMapHelper();
    $image = new \ReflectionMethod($map, 'safeImageUrl');
    foreach (['javascript:alert(1)', 'data:image/svg+xml;base64,123',
        '//another.example/badge.png', 'images/%2e%2e/badge.png',
        'images/../badge.png', 'https://user:password@example.test/a.png',
        "images/badge\n.png", ['bad']] as $invalid) {
        if ($image->invoke(null, $invalid) !== '') {
            throw new \RuntimeException('Project map accepted an unsafe icon reference.');
        }
    }
    if ($image->invoke(null, 'images/badge.png') !== 'images/badge.png'
        || $image->invoke(null, 'https://example.test/badge.png') !== 'https://example.test/badge.png') {
        throw new \RuntimeException('Valid project map pictures were rejected.');
    }

    $project = (object) [
        'country_alpha2' => 'de', 'country_federation' => 3,
        'country_picture' => 'javascript:alert(1)',
        'league_picture' => 'data:image/svg+xml;base64,abc',
        'federation_picture' => '//evil.test/icon.png',
        'country_name' => 'Germany', 'liganame' => 'Bundesliga',
        'name' => 'Bundesliga Project', 'federation_name' => 'UEFA',
        'project_slug' => '7:bundesliga',
    ];
    $regions = $map->createRegions([$project]);
    $states = $map->createStateSpecific([$project]);
    if (str_contains($regions['3']['description'] ?? '', '<img')
        || str_contains($states['de']['name'] ?? '', '<img')
        || str_contains($states['de']['description'] ?? '', '<img')
        || ($states['de']['image_url'] ?? 'unexpected') !== '') {
        throw new \RuntimeException('Unsafe project map icons were embedded in popup output.');
    }

    echo "Joomla 5/6 project navigation, admin dates and project-map icon guards: OK\n";
}
