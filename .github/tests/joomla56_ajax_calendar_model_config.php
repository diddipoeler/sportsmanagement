<?php
/**
 * Runtime regression for AJAX calendar links and administrator model options.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Controller {
    class BaseController
    {
        protected $app;
        protected $input;

        public function __construct($config = [], $factory = null, $app = null, $input = null)
        {
            $this->app = $app;
            $this->input = $input;
        }

        public function getModel($name = '', $prefix = '', $config = [])
        {
            if ($this instanceof \Diddipoeler\Component\SportsManagement\Site\Controller\AjaxController) {
                return new \Diddipoeler\Component\SportsManagement\Site\Model\AjaxModel();
            }

            return [$name, $prefix, $config];
        }
    }

    class AdminController extends BaseController {}
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class AjaxModel
    {
        public function getLink(
            string $view,
            int $projectId = 0,
            int $teamId = 0,
            int $divisionId = 0,
            string $points = '',
            int $treeNodeId = 0
        ): string {
            return $view;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    require_once $root . '/site/src/Controller/AjaxController.php';
    require_once $root . '/admin/src/Controller/SportsManagementAdminController.php';
    require_once $root . '/admin/src/Controller/ProjectsController.php';
    require_once $root . '/admin/src/Controller/TeamsController.php';

    $input = new class {
        public string $view = 'calendar';

        public function get(string $name, mixed $default = null, string $filter = 'cmd'): mixed
        {
            return $name === 'view' ? $this->view : $default;
        }

        public function getCmd(string $name, string $default = ''): string
        {
            return $name === 'view' ? preg_replace('/[^a-zA-Z0-9_\-]/', '', $this->view) : $default;
        }

        public function getInt(string $name, int $default = 0): int { return $default; }
        public function getString(string $name, string $default = ''): string { return $default; }
    };

    $app = new class($input) {
        public int $closes = 0;
        public function __construct(private object $input) {}
        public function getInput(): object { return $this->input; }
        public function getDocument(): object
        {
            return new class { public function setMimeEncoding(string $type): void {} };
        }
        public function setHeader(string $name, string $value, bool $replace = false): void {}
        public function close(): void { ++$this->closes; }
    };

    $site = new \Diddipoeler\Component\SportsManagement\Site\Controller\AjaxController(
        [], null, $app, $input
    );

    foreach (['calendar' => 'calendar', 'CALENDAR' => 'calendar', '' => 'ranking'] as $given => $expected) {
        $input->view = $given;
        foreach (['getLink', 'getroute'] as $action) {
            ob_start();
            $site->{$action}();
            $actual = json_decode((string) ob_get_clean(), true, 512, JSON_THROW_ON_ERROR);
            $link = $action === 'getLink' ? $actual['link'] : $actual;
            if ($link !== $expected) {
                throw new \RuntimeException("AJAX {$action} routed {$given} to {$link}, expected {$expected}.");
            }
        }
    }

    $input->view = 'calendar<script>';
    try {
        $site->getroute();
        throw new \RuntimeException('Malformed AJAX view was not rejected.');
    } catch (\InvalidArgumentException $exception) {
        if ($exception->getCode() !== 400) {
            throw $exception;
        }
    }

    $projects = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\ProjectsController();
    $teams = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\TeamsController();

    foreach ([[$projects, false], [$teams, true]] as [$controller, $default]) {
        $standard = $controller->getModel();
        if (($standard[2]['ignore_request'] ?? null) !== $default) {
            throw new \RuntimeException('Administrator controller model default changed.');
        }

        $custom = $controller->getModel('Custom', 'Administrator', [
            'foo' => 42, 'ignore_request' => !$default,
        ]);
        if ($custom !== ['Custom', 'Administrator', ['ignore_request' => !$default, 'foo' => 42]]) {
            throw new \RuntimeException('Administrator controller discarded MVC model options.');
        }
    }

    echo "Joomla 5/6 AJAX calendar links and administrator model options OK\n";
}
