<?php
/**
 * Joomla 5/6 native routing and administrator navigation field contract.
 *
 * Exercises the real site route helper, administrator menu field and module
 * link builder with isolated framework services and without a database.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Joomla\Registry {
    final class Registry
    {
        /**
         * Store isolated module configuration for the navigation fixture.
         *
         * @param array<string,mixed> $values Initial Joomla module settings.
         */
        public function __construct(private array $values = []) {}

        /**
         * Read a raw setting without coercing arrays, booleans or numbers.
         *
         * @param string $name Requested Joomla parameter name.
         * @param mixed $default Fallback for absent settings.
         * @return mixed Stored parameter or caller-provided fallback.
         */
        public function get(string $name, mixed $default = null): mixed
        {
            return $this->values[$name] ?? $default;
        }
    }
}

namespace Joomla\CMS\Form\Field {
    class ListField
    {
        /**
         * Return the empty XML-defined options used by the isolated test.
         *
         * @return array<int,object> Base list field choices.
         */
        protected function getOptions(): array { return []; }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Preserve translation keys to compare the native field choices.
         *
         * @param string $key Joomla language constant.
         * @return string Unchanged language constant.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\Router {
    final class Route
    {
        public static array $calls = [];

        /**
         * Capture URLs passed to Joomla's native frontend router.
         *
         * @param string $url Complete unencoded frontend route.
         * @param bool $xhtml Whether Joomla would escape ampersands.
         * @return string Unchanged route used by the assertions.
         */
        public static function _(string $url, bool $xhtml = true): string
        {
            self::$calls[] = $url;

            return $url;
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        /**
         * Encode Joomla query parameters with PHP's RFC3986 URL builder.
         *
         * @param array<string,mixed> $parameters Requested route parameters.
         * @return string Percent-encoded component query string.
         */
        public static function buildQuery(array $parameters): string
        {
            return http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementSiteApplicationResolver
    {
        public static ?\Joomla\CMS\Application\CMSApplicationInterface $app = null;

        /**
         * Resolve the Joomla frontend application used by the module router.
         *
         * @return \Joomla\CMS\Application\CMSApplicationInterface Active fixture.
         */
        public static function resolve(): \Joomla\CMS\Application\CMSApplicationInterface
        {
            if (self::$app === null) {
                throw new \RuntimeException('Test Joomla site application was not configured.');
            }

            return self::$app;
        }
    }
}

namespace Diddipoeler\Module\SportsManagementNavigationMenu\Site\Helper {
    class NavigationMenuHelper
    {
        public object|false $project = false;
        public int $teamId = 0;
        public int $divisionId = 0;
        public mixed $databaseSelector = 0;

        /**
         * Preserve the parent native navigation configuration signature.
         *
         * @param \Joomla\Registry\Registry $params Module configuration.
         * @param \Joomla\CMS\Application\CMSApplicationInterface $app Frontend Joomla application.
         * @param \Joomla\Database\DatabaseInterface $db Primary Joomla database.
         * @return array<string,mixed> Empty test menu context.
         */
        public function getData(
            \Joomla\Registry\Registry $params,
            \Joomla\CMS\Application\CMSApplicationInterface $app,
            \Joomla\Database\DatabaseInterface $db
        ): array {
            return [];
        }

        /**
         * Return the configured project or false for the missing-project case.
         *
         * @return object|false Current selected project.
         */
        public function getProject(): object|false { return $this->project; }

        /**
         * Return the selected team ID for route parameter assertions.
         *
         * @return int Joomla team identifier or zero.
         */
        public function getTeamId(): int { return $this->teamId; }

        /**
         * Return the selected division for team/tree navigation.
         *
         * @return int Joomla division identifier or zero.
         */
        public function getDivisionId(): int { return $this->divisionId; }

        /**
         * Provide the database-selector setting, including corrupt legacy values.
         *
         * @param string $name Requested module option key.
         * @param mixed $default Fallback for an unrelated key.
         * @return mixed Configured database selector.
         */
        protected function getParam(string $name, mixed $default = null): mixed
        {
            return $name === 'cfg_which_database' ? $this->databaseSelector : $default;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    require_once $root . '/site/src/Helper/SiteRouteHelper.php';
    require_once $root . '/admin/src/Field/JsmmenuitemsField.php';
    require_once $root . '/modules/mod_sportsmanagement_navigation_menu/src/Helper/NativeNavigationMenuHelper.php';

    $route = \Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper::class;
    $field = new \Diddipoeler\Component\SportsManagement\Administrator\Field\JsmmenuitemsField();
    $options = (new \ReflectionMethod($field, 'getOptions'))->invoke($field);
    $menuValues = array_column($options, 'value');

    // Every migrated link is a selectable administrator module field value.
    foreach ([
        'allprojectrounds', 'jltournamenttree', 'jlxmlexports',
        'leaguechampionoverview', 'rosteralltime', 'statsrankingteams',
        'teams', 'teamstree',
    ] as $target) {
        if (!in_array($target, $menuValues, true)) {
            throw new \RuntimeException('Native Joomla 5/6 navigation option missing: ' . $target);
        }

        if (!is_dir($root . '/site/src/View/' . ucfirst($target))) {
            // Most native view namespaces are ucfirst(name), but the actual
            // case-insensitive component mapping remains a Joomla concern.
            throw new \RuntimeException('Navigation target has no native frontend view: ' . $target);
        }
    }

    $input = new class {
        public array $values = [
            'cfg_which_database' => 1,
            's' => 2026,
            'r' => 5,
        ];

        /**
         * Supply the filtered integer selectors used by module routing.
         *
         * @param string $key Joomla frontend input key.
         * @param int $default Caller-provided input default.
         * @return int Filtered integer from the test request.
         */
        public function getInt(string $key, int $default = 0): int
        {
            return $this->values[$key] ?? $default;
        }

        /**
         * Supply unfiltered Joomla input for database selector validation.
         *
         * Preserves malformed arrays, booleans and strings rather than
         * allowing getInt() to silently select another database connection.
         *
         * @param string $key Raw request parameter.
         * @param mixed $default Fallback when the parameter is absent.
         * @param string $filter Joomla raw-value filter mode.
         * @return mixed Exact saved test request value or fallback.
         */
        public function get(string $key, mixed $default = null, string $filter = 'raw'): mixed
        {
            return array_key_exists($key, $this->values)
                ? $this->values[$key]
                : $default;
        }
    };

    $app = new class($input) implements \Joomla\CMS\Application\CMSApplicationInterface {
        /**
         * Inject the configured Joomla frontend request into the app stub.
         *
         * @param object $input Input containing filtered request identifiers.
         */
        public function __construct(private object $input) {}

        /**
         * Verify Joomla's site client for native menu rendering.
         *
         * @param string $client Expected application client name.
         * @return bool True for Joomla's public site.
         */
        public function isClient(string $client): bool { return $client === 'site'; }

        /**
         * Return Joomla filtered input for the native link builder.
         *
         * @return object Test input exposing getInt().
         */
        public function getInput(): object { return $this->input; }
    };

    \Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver::$app = $app;

    $helper = new \Diddipoeler\Module\SportsManagementNavigationMenu\Site\Helper\NativeNavigationMenuHelper();
    $helper->project = (object) ['id' => 47];
    $helper->teamId = 13;
    $helper->divisionId = 4;
    $helper->databaseSelector = '0001';

    /**
     * Decode a routed SportsManagement menu URL into its query parameters.
     *
     * @param string $url URL produced by the native module helper.
     * @return array<string,mixed> Parsed query string key-value pairs.
     */
    function parseNavigationUrl(string $url): array
    {
        parse_str((string) (parse_url($url, PHP_URL_QUERY) ?? ''), $data);

        return $data;
    }

    set_error_handler(static function (int $severity, string $message): never {
        throw new \ErrorException($message, 0, $severity);
    });

    try {
        foreach ([
            'allprojectrounds' => 'allprojectrounds',
            'jltournamenttree' => 'jltournamenttree',
            'resultsmatrix' => 'resultsmatrix',
            'resultsranking' => 'resultsranking',
            'resultsrankingmatrix' => 'resultsrankingmatrix',

            'jlxmlexports' => 'jlxmlexports',
            'leaguechampionoverview' => 'leaguechampionoverview',
            'rosteralltime' => 'rosteralltime',
            'statsrankingteams' => 'statsrankingteams',
            'teams' => 'teams',
            'teamstree' => 'teamstree',
        ] as $selected => $expectedView) {
            $url = $helper->getLink($selected);

            if (!is_string($url)) {
                throw new \RuntimeException('Selected native view yielded no module link: ' . $selected);
            }

            $params = parseNavigationUrl($url);

            if (($params['option'] ?? null) !== 'com_sportsmanagement'
                || ($params['view'] ?? null) !== $expectedView
                || ($params['p'] ?? null) !== '47'
                || ($params['cfg_which_database'] ?? null) !== '1') {
                throw new \RuntimeException('Native Joomla 5/6 navigation lost project or database context.');
            }

            if (in_array($selected, ['rosteralltime'], true)
                && ($params['tid'] ?? null) !== '13') {
                throw new \RuntimeException('Team-specific navigation dropped the selected team.');
            }

            if (in_array($selected, ['teams', 'teamstree', 'statsrankingteams'], true)
                && ($params['division'] ?? null) !== '4') {
                throw new \RuntimeException('Navigation omitted its selected division.');
            }
        }

        $helper->teamId = 0;

        foreach (['rosteralltime', 'roster', 'teaminfo', 'teamplan', 'teamstats'] as $target) {
            if ($helper->getLink($target) !== false) {
                throw new \RuntimeException('Team-only view was routed without a team: ' . $target);
            }
        }

        $helper->teamId = 13;
        foreach ([null, true, false, [], ['1'], new \stdClass(), -1, 2, 1.5,
            '2', '-1', '1junk', ' 1', '1 ', '', str_repeat('0', 65) . '1',
        ] as $badSelector) {
            $helper->databaseSelector = $badSelector;

            if ($helper->getLink('teams') !== false) {
                throw new \RuntimeException('Malformed database selector generated a navigation link.');
            }
        }

        // Invalid raw URL parameters must not be coerced by Joomla's
        // getInt() into a different configured database connection.
        $helper->databaseSelector = 1;
        foreach ([[], ['1'], new \stdClass(), false, true, -1, 2, 1.5,
            '1junk', ' 1', '1 ', '01suffix', '2',
        ] as $invalidRequest) {
            $input->values['cfg_which_database'] = $invalidRequest;

            if ($helper->getLink('matrix') !== false) {
                throw new \RuntimeException('Malformed raw URL selector changed navigation database.');
            }
        }

        unset($input->values['cfg_which_database']);
        $defaulted = $helper->getLink('matrix');

        if (!is_string($defaulted)
            || (parseNavigationUrl($defaulted)['cfg_which_database'] ?? null) !== '1') {
            throw new \RuntimeException('Absent raw URL selector did not use stored module database.');
        }

        $input->values['cfg_which_database'] = 1;
        $helper->databaseSelector = 0;

        if ($helper->getLink('../admin') !== false
            || $helper->getLink('typo') !== false
            || $helper->getLink(' separator') !== false) {
            throw new \RuntimeException('Unsafe or unknown navigation view was accepted.');
        }

        $helper->project = false;
        if ($helper->getLink('teams') !== false) {
            throw new \RuntimeException('Project-dependent navigation was routed without a project.');
        }

        $before = count(\Joomla\CMS\Router\Route::$calls);

        foreach ([['bad'], true, '../admin', 'evil?x=1', ''] as $invalid) {
            try {
                $route::query(['option' => 'com_sportsmanagement', 'view' => $invalid]);
                throw new \RuntimeException('Malformed generic component view was accepted.');
            } catch (\InvalidArgumentException) {
                // Expected boundary before calling Joomla's router.
            }
        }

        if (count(\Joomla\CMS\Router\Route::$calls) !== $before) {
            throw new \RuntimeException('Malformed view reached Joomla routing services.');
        }

        $encoded = $route::view('teams', [
            'option' => 'com_users',
            'view' => 'administrator',
            'Itemid' => ['bad'],
            'p' => 47,
        ]);
        $params = parseNavigationUrl($encoded);

        if (($params['option'] ?? null) !== 'com_sportsmanagement'
            || ($params['view'] ?? null) !== 'teams'
            || isset($params['Itemid'])
            || ($params['p'] ?? null) !== '47') {
            throw new \RuntimeException('Native router failed to enforce SportsManagement view authority.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 native navigation field, view links and route selectors OK\n";
}
