<?php
/**
 * Regression tests for Joomla 5/6 component routing and navigation IDs.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface { public function getInput(): object; }
    class SiteApplication implements CMSApplicationInterface {
        public function __construct(private object $input) {}
        public function getInput(): object { return $this->input; }
        public function isClient(string $client): bool { return $client === 'site'; }
    }
    class AdministratorApplication {
        public function __construct(private object $identity) {}
        public function isClient(string $client): bool { return $client === 'administrator'; }
        public function getIdentity(): object { return $this->identity; }
    }
}
namespace Joomla\CMS\Dispatcher {
    class ComponentDispatcher {
        protected object $app;
        protected object $input;
        public int $nativeDispatches = 0;
        public function __construct(object $app, object $input) { $this->app = $app; $this->input = $input; }
        public function dispatch() { ++$this->nativeDispatches; }
    }
}
namespace Joomla\CMS\Router {
    class Route { public static function _(string $url, bool $xhtml = true): string { return $url; } }
}
namespace Joomla\CMS\Toolbar {
    class ToolbarHelper { public static function back(string $label, string $url): void {} }
}
namespace Joomla\Database { interface DatabaseInterface {} }
namespace Joomla\Registry {
    class Registry {
        public function __construct(private array $values = []) {}
        public function get(string $key, mixed $default = null): mixed { return $this->values[$key] ?? $default; }
    }
}
namespace {
    \define('_JEXEC', 1);
    $root = \dirname(__DIR__, 2);
    require_once $root . '/site/src/Dispatcher/Dispatcher.php';
    require_once $root . '/admin/src/Dispatcher/Dispatcher.php';
    require_once $root . '/modules/mod_sportsmanagement_navigation_menu/src/Helper/NavigationMenuHelper.php';

    $newInput = static function (array $values): object {
        return new class($values) {
            public function __construct(private array $values) {}
            public function get(string $key, mixed $default = null, string $filter = 'raw'): mixed {
                return $this->values[$key] ?? $default;
            }
            public function getCmd(string $key, string $default = ''): string {
                $raw = $this->values[$key] ?? $default;
                return \is_scalar($raw) ? \preg_replace('/[^a-zA-Z0-9_.-]/', '', (string) $raw) : $default;
            }
            public function getInt(string $key, int $default = 0): int {
                return (int) ($this->values[$key] ?? $default);
            }
            public function set(string $key, mixed $value): void { $this->values[$key] = $value; }
        };
    };
    $identity = new class {
        public function authorise(string $action, string $asset): bool {
            return $action === 'core.manage' && $asset === 'com_sportsmanagement';
        }
    };
    $makeDispatcher = static function (string $client, object $input) use ($identity): object {
        if ($client === 'site') {
            return new \Diddipoeler\Component\SportsManagement\Site\Dispatcher\Dispatcher(
                new \Joomla\CMS\Application\SiteApplication($input), $input
            );
        }
        return new \Diddipoeler\Component\SportsManagement\Administrator\Dispatcher\Dispatcher(
            new \Joomla\CMS\Application\AdministratorApplication($identity), $input
        );
    };

    foreach ([
        'site' => ['task' => 'AJAX.GETLINK', 'format' => 'JSON'],
        'admin' => ['task' => 'EVENTTYPE.SAVE', 'view' => 'EVENTTYPE'],
    ] as $client => $fields) {
        $dispatcher = $makeDispatcher($client, $newInput($fields));
        $dispatcher->dispatch();
        if ($dispatcher->nativeDispatches !== 1) {
            throw new \RuntimeException("$client failed to dispatch a valid native MVC task.");
        }
    }

    foreach ([
        ['view' => ['results']], ['task' => ['editmatch.save']],
        ['controller' => ['display']], ['layout' => ['default']],
        ['format' => ['html']], ['view' => '../results'],
        ['task' => 'editmatch.save<script>'],
    ] as $fields) {
        foreach (['site', 'admin'] as $client) {
            try {
                $makeDispatcher($client, $newInput($fields))->dispatch();
            } catch (\InvalidArgumentException $exception) {
                if ($exception->getCode() === 400) {
                    continue;
                }
            }
            throw new \RuntimeException("$client accepted invalid request input: " . \json_encode($fields));
        }
    }

    $navInput = $newInput([
        'option' => 'com_sportsmanagement',
        'p' => -20, 'r' => -2, 'division' => -3, 'tid' => -4,
    ]);
    $nav = new \Diddipoeler\Module\SportsManagementNavigationMenu\Site\Helper\NavigationMenuHelper(
        new \Joomla\Registry\Registry(['default_project_id' => -5]),
        new \Joomla\CMS\Application\SiteApplication($navInput),
        new class implements \Joomla\Database\DatabaseInterface {}
    );
    if ($nav->getProject() !== false || $nav->getTeamId() !== 0
        || $nav->getDivisionId() !== 0 || $nav->getLink('ranking') !== false) {
        throw new \RuntimeException('Negative project and selector IDs escaped navigation sanitization.');
    }
    echo "Joomla 5/6 site/admin routing and navigation ID contracts OK\n";
}
