<?php
/**
 * Joomla 5/6 TeamPlayers roster publication and image safety regressions.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface
    {
    }

    final class ParameterType
    {
        public const INTEGER = 'integer';
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        public function __construct(private array $values = [])
        {
        }

        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        public static function root(): string
        {
            return 'https://site.example.test/joomla/';
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_teamplayers/src/Helper/TeamPlayersHelper.php';

    final class RosterQuery
    {
        public array $joins = [];
        public array $wheres = [];
        public array $bindings = [];

        public function select(array $fields): self
        {
            return $this;
        }

        public function from(string $table): self
        {
            return $this;
        }

        public function join(string $type, string $condition): self
        {
            $this->joins[] = $condition;
            return $this;
        }

        public function where(string $condition): self
        {
            $this->wheres[] = $condition;
            return $this;
        }

        public function bind(string $key, mixed $value, mixed $type): self
        {
            $this->bindings[$key] = $value;
            return $this;
        }

        public function order(array $columns): self
        {
            return $this;
        }
    }

    final class RosterDatabase implements \Joomla\Database\DatabaseInterface
    {
        public ?RosterQuery $query = null;

        public function createQuery(): RosterQuery
        {
            return new RosterQuery();
        }

        public function quoteName(string $column, ?string $alias = null): string
        {
            return $column;
        }

        public function setQuery(RosterQuery $query, int $offset = 0, int $limit = 0): void
        {
            $this->query = $query;
        }

        public function loadObject(): object
        {
            return (object) [
                'id' => 11,
                'alias' => 'season-project',
                'name' => 'Project',
                'season_id' => 5,
                'team_id' => 17,
                'team_alias' => 'red-team',
            ];
        }

        public function loadObjectList(): array
        {
            return [];
        }
    }

    $helper = new \Diddipoeler\Module\SportsManagementTeamPlayers\Site\Helper\TeamPlayersHelper();
    $projectMethod = new \ReflectionMethod($helper, 'project');
    $rosterMethod = new \ReflectionMethod($helper, 'roster');
    $database = new RosterDatabase();
    $project = $projectMethod->invoke($helper, $database, 11, 17);

    if ($project?->project_slug !== '11:season-project'
        || $project?->team_slug !== '17:red-team'
        || $database->query === null
    ) {
        throw new \RuntimeException('Team project was not loaded with correct Joomla slugs');
    }

    // The season clause is part of a larger JOIN expression.
    $joinText = implode(' ', $database->query->joins);

    if (!str_contains($joinText, 'st.season_id = p.season_id')) {
        throw new \RuntimeException('Team project query lacks season identity');
    }

    foreach (['p.published = 1', 'pt.published = 1', 'st.published = 1', 't.published = 1'] as $condition) {
        if (!in_array($condition, $database->query->wheres, true)) {
            throw new \RuntimeException('Team project visibility filter missing: ' . $condition);
        }
    }

    $rosterMethod->invoke($helper, $database, $project);

    foreach ([
        'pr.show_on_frontend = 1',
        'pr.published = 1',
        'tp.published = 1',
        'tp.season_id = :seasonId',
        'tp.team_id = :teamId',
    ] as $condition) {
        if (!in_array($condition, $database->query->wheres, true)) {
            throw new \RuntimeException('Team roster visibility filter missing: ' . $condition);
        }
    }

    $images = new \ReflectionMethod($helper, 'imageUrl');

    foreach ([
        ['images/player photo.png', 'https://site.example.test/joomla/images/player%20photo.png'],
        ['/images/p.png', 'https://site.example.test/joomla/images/p.png'],
        ['https://cdn.example.test/player.png', 'https://cdn.example.test/player.png'],
        ['javascript:alert(1)', ''],
        ['data:image/svg+xml,test', ''],
        ['//evil.test/icon.png', ''],
        ['../private.png', ''],
        ['images/../private.png', ''],
        ['images/./player.png', ''],
        ['images/%2e%2e/private.png', ''],
        ['images\wrong.png', ''],
        ['https://user:pass@cdn.example.test/player.png', ''],
        ["https://cdn.example.test/\nplayer.png", ''],
    ] as [$path, $expected]) {
        if ($images->invoke($helper, $path) !== $expected) {
            throw new \RuntimeException('Unsafe player image URL: ' . json_encode($path));
        }
    }

    $flag = new \ReflectionMethod($helper, 'flagHtml');
    $flagsCss = new \Joomla\Registry\Registry(['cfg_flags_css' => 1]);
    $invalidFlag = (object) [
        'country' => 'DEU',
        'alpha2' => '../../evil',
        'country_name' => 'Germany',
        'country_picture' => 'javascript:alert(1)',
    ];

    if ($flag->invoke($helper, $invalidFlag, $flagsCss) !== '') {
        throw new \RuntimeException('Untrusted country code was placed in a CSS class');
    }

    $validFlag = clone $invalidFlag;
    $validFlag->alpha2 = 'de';
    if (!str_contains($flag->invoke($helper, $validFlag, $flagsCss), 'fi-de')) {
        throw new \RuntimeException('Valid ISO flag was removed');
    }

    $flagsImage = new \Joomla\Registry\Registry(['cfg_flags_css' => 0]);
    if ($flag->invoke($helper, $invalidFlag, $flagsImage) !== '') {
        throw new \RuntimeException('Unsafe country picture was accepted');
    }

    $render = static function (string $mode): string {
        $module = (object) ['id' => 9];
        $params = new \Joomla\Registry\Registry([
            'template' => $mode,
            'show_positions' => 1,
            'show_player_link' => 0,
            'show_player_flag' => 0,
            'show_mins_played' => 0,
            'slider_navigation' => 0,
        ]);
        $project = (object) ['name' => 'Project'];
        $players = [(object) [
            'display_name' => 'Player',
            'position' => '<svg onload=alert(1)>',
            'minutes_played' => 0,
            'flag_html' => '',
            'image_url' => '',
            'player_url' => '',
        ]];

        ob_start();
        try {
            require dirname(__DIR__, 2)
                . '/modules/mod_sportsmanagement_teamplayers/tmpl/native.php';
            return (string) ob_get_contents();
        } finally {
            ob_end_clean();
        }
    };

    foreach (['C', 'L'] as $layout) {
        $html = $render($layout);
        if (str_contains($html, '<svg')
            || !str_contains($html, '&lt;svg onload=alert(1)&gt;')
        ) {
            throw new \RuntimeException('Unsafe roster position rendered in ' . $layout . ' layout');
        }
    }

    echo "TeamPlayers roster publishing and image safety regressions OK\n";
}
