<?php
/**
 * Joomla 5/6 regression contract for POST-only image actions and matches slider.
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

namespace Joomla\Database {
    interface DatabaseInterface {}

    final class ParameterType
    {
        public const INTEGER = 'integer';
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        public function __construct(private array $data = []) {}

        public function get(string $name, mixed $default = null): mixed
        {
            return $this->data[$name] ?? $default;
        }
    }
}

namespace Joomla\CMS {
    final class Factory {}
}

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static function getParams(string $name): object
        {
            return new class {
                public function get(string $name, mixed $default = null): mixed
                {
                    return $default;
                }
            };
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        public static function root(): string { return 'https://example.test/'; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    final class SiteRouteHelper
    {
        public static function view(string $view, array $query): string
        {
            return $view . '?' . http_build_query($query);
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        public static function resolve(object $db, int $selector): object
        {
            return $db;
        }
    }
}

namespace {
    use Diddipoeler\Module\SportsManagementMatchesSlider\Site\Helper\MatchesSliderHelper;
    use Joomla\CMS\Application\CMSApplicationInterface;
    use Joomla\Database\DatabaseInterface;
    use Joomla\Registry\Registry;

    \define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/modules/mod_sportsmanagement_matchesslider/src/Helper/MatchesSliderHelper.php';

    function ensureSame(mixed $expected, mixed $actual, string $message): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException($message . "\nExpected: " . var_export($expected, true)
                . "\nActual: " . var_export($actual, true));
        }
    }

    final class QueryStub
    {
        public array $columns = [];
        public array $filters = [];
        public array $whereIn = [];
        public int $bindCount = 0;

        public function select(array|string $columns): self
        {
            $this->columns = array_merge($this->columns, (array) $columns);
            return $this;
        }

        public function from(string $table): self { return $this; }

        public function join(string $type, string $condition): self { return $this; }

        public function where(string $condition): self
        {
            $this->filters[] = $condition;
            return $this;
        }

        public function whereIn(string $column, array $values, string $type): self
        {
            $this->whereIn[$column] = $values;
            return $this;
        }

        public function bindArray(array $ids, string $type): array
        {
            return array_map(function (int $id): string {
                return ':team_' . (++$this->bindCount);
            }, $ids);
        }

        public function order(array|string $order): self { return $this; }
    }

    final class DatabaseStub implements DatabaseInterface
    {
        public int $queries = 0;
        public QueryStub $lastQuery;
        public array $rows = [];

        public function createQuery(): QueryStub { return new QueryStub(); }

        public function quoteName(string $column, ?string $alias = null): string
        {
            return $alias !== null ? $column . ' AS ' . $alias : $column;
        }

        public function setQuery(QueryStub $query): void
        {
            $this->lastQuery = $query;
            ++$this->queries;
        }

        public function loadObjectList(): array
        {
            return array_map(static fn (object $row): object => clone $row, $this->rows);
        }
    }

    $app = new class implements CMSApplicationInterface {
        public mixed $requestProject = null;
        public function isClient(string $name): bool { return $name === 'site'; }

        public function getInput(): object
        {
            return new class($this) {
                public function __construct(private object $app) {}

                public function get(string $name, mixed $default = null, string $filter = 'cmd'): mixed
                {
                    return $name === 'p' ? ($this->app->requestProject ?? $default) : $default;
                }

                public function getInt(string $name, int $default = 0): int
                {
                    return $default;
                }
            };
        }
    };

    $helper = new MatchesSliderHelper();
    $module = (object) ['id' => 4];

    $ids = new \ReflectionMethod(MatchesSliderHelper::class, 'ids');
    ensureSame([12, 13, 14],
        $ids->invoke($helper, [12, '0012:team-one', '13:team_two,14', 'oops', '99999999999999999999999999999']),
        'Slider must accept Joomla slugs and reject bad IDs / overflow.');

    $db = new DatabaseStub();
    ensureSame([], $helper->getData(new Registry(['p' => [5], 'teams' => ['12garbage']]), $module, $app, $db),
        'Invalid explicit team selection must not reveal all teams.');
    ensureSame(0, $db->queries, 'Invalid selections must not load unfiltered matches.');

    // Invalid single-project URL overrides may not revert to the configured
    // list and accidentally show unrelated fixtures.
    foreach (['12broken', '12:bad:slug', '12,13', '999999999999999999999999', [12], true] as $invalid) {
        $app->requestProject = $invalid;
        ensureSame([], $helper->getData(new Registry(['p' => [5]]), $module, $app, $db),
            'Invalid URL project override must be rejected.');
        ensureSame(0, $db->queries, 'Invalid project overrides must not query the database.');
    }

    $app->requestProject = '8:project-eight';
    ensureSame([], $helper->getData(new Registry(['p' => [5]]), $module, $app, $db),
        'Valid project override with empty database must return no fixtures.');
    ensureSame([8], $db->lastQuery->whereIn['p.id'] ?? null,
        'A valid URL ID:alias must override the configured project.');
    ensureSame(1, $db->queries, 'One query expected for a valid project override.');
    $db->queries = 0;
    $app->requestProject = null;

    $db->rows = [
        (object) [
            'project_id' => 5,
            'project_alias' => 'five',
            'round_id' => 10,
            'round_alias' => 'semifinal',
            'season_id' => 2,
            'match_date' => '2026-10-12',
            'logohome' => '',
            'logoaway' => '',
        ],
    ];
    $matches = $helper->getData(new Registry(['p' => ['5'], 'teams' => ['12:team-one']]), $module, $app, $db);
    ensureSame(1, count($matches), 'Valid team selection must return its matches.');
    ensureSame('5:five', $matches[0]->project_slug, 'Project slug must be assembled in PHP.');
    ensureSame('10:semifinal', $matches[0]->round_slug, 'Round slug must be assembled in PHP.');
    ensureSame('results', explode('?', $matches[0]->link, 2)[0],
        'Match slider must preserve its Joomla frontend route.');
    parse_str((string) parse_url($matches[0]->link, PHP_URL_QUERY), $route);
    ensureSame('5:five', $route['p'] ?? null, 'Route must contain the project ID:alias slug.');
    ensureSame('10:semifinal', $route['r'] ?? null, 'Route must contain the round ID:alias slug.');
    ensureSame(1, $db->queries, 'Expected one filtered match query.');
    ensureSame([5], $db->lastQuery->whereIn['p.id'] ?? null,
        'Project ID must be bound as an integer.');
    ensureSame(true, str_contains(implode(' ', $db->lastQuery->filters), 'st1.team_id IN'),
        'Selected team must be applied to the SQL query.');
    ensureSame(false, str_contains(implode(' ', $db->lastQuery->columns), 'CONCAT_WS'),
        'Slider SQL must not depend on MySQL-only CONCAT_WS.');

    // POST-only mutation contract across administrator and frontend image handlers.
    $admin = file_get_contents($root . '/admin/src/Controller/ImagehandlerController.php');
    $site = file_get_contents($root . '/site/src/Controller/ImagehandlerController.php');
    ensureSame(true, $admin !== false && $site !== false,
        'Image controllers must exist.');
    ensureSame(true, str_contains($admin, "\$input->post->get('rm', [], 'array')"),
        'Administrator deletion must read selected images from POST.');
    ensureSame(true, str_contains($admin, "\$input->post->getCmd('type')"),
        'Administrator deletion must read its image type from POST.');
    ensureSame(2, substr_count($admin, '$input->post->getArray()'),
        'Both administrator upload actions must read POST-only form values.');
    ensureSame(3, substr_count($admin, '$input = $this->app->getInput()->post;'),
        'Every administrator image-selection writer must read values from POST.');
    ensureSame(true,
        str_contains($admin, "$config + ['ignore_request' => true]"),
        'Administrator image model lookup must preserve explicit caller MVC options.');
    ensureSame(true, str_contains($site, "\$input->post->getString('linkaddress', '')"),
        'Frontend remote upload URL must come from POST.');
    ensureSame(true, str_contains($site, "\$input->post->getCmd('type', '')"),
        'Frontend upload and delete must use POST image type.');

    foreach ([
        'site/views/imagehandler/tmpl/upload.php',
        'admin/tmpl/imagehandler/upload.php',
        'admin/tmpl/imagehandler/uploaddraganddrop.php',
    ] as $path) {
        $layout = file_get_contents($root . '/' . $path);

        foreach (['type', 'field', 'fieldid'] as $field) {
            ensureSame(true, $layout !== false && str_contains($layout, 'name="' . $field . '"'),
                "$path must submit the $field context as POST.");
        }

        ensureSame(true, str_contains($layout, "HTMLHelper::_('form.token')"),
            "$path must submit the Joomla CSRF token.");
    }

    $adminView = file_get_contents($root . '/admin/src/View/Imagehandler/HtmlView.php');
    ensureSame(true, $adminView !== false
        && str_contains($adminView, "\$this->type = \$input->getCmd('type');")
        && str_contains($adminView, "\$this->type = (string) (\$data['type'] ?? '');")
        && str_contains($adminView, "\$this->folder = ImageSelectHelper::getFolder(\$this->type);"),
        'Administrator image upload layouts must supply their POST image type.');

    echo "Joomla 5/6 image POST and portable matches slider regressions OK.\n";
}
