<?php
/**
 * Test Joomla 5/6 administrator database tools with missing model methods.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    class Text
    {
        /**
         * Return the untranslated Joomla language key.
         *
         * @param string $key Translation identifier.
         * @return string Untranslated key.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\MVC\Controller {
    class BaseController
    {
        protected object $app;
        public mixed $model = null;
        public array $redirect = [];

        /**
         * Inject a fake administrator application into the controller.
         *
         * @param object $app Application exposing getIdentity().
         */
        public function __construct(object $app)
        {
            $this->app = $app;
        }

        /**
         * Simulate a successful CSRF token validation.
         *
         * @return void
         */
        protected function checkToken(): void
        {
        }

        /**
         * Return the configured administrator maintenance model.
         *
         * @param string $name Model class name.
         * @param string $prefix MVC namespace prefix.
         * @param array $config Joomla model configuration.
         * @return mixed Mock model or missing-model sentinel.
         */
        public function getModel($name = '', $prefix = '', $config = [])
        {
            return $this->model;
        }

        /**
         * Record a component redirect for the test.
         *
         * @param string $url Destination.
         * @param string $message User-facing status.
         * @param string $type Message severity.
         * @return void
         */
        public function setRedirect(string $url, string $message = '', string $type = ''): void
        {
            $this->redirect = [$url, $message, $type];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/DatabasetoolController.php';

    $app = new class {
        /**
         * Supply an authorised Joomla administrator.
         *
         * @return object Mock administrator identity.
         */
        public function getIdentity(): object
        {
            return new class {
                /**
                 * Grant administrator maintenance permission only.
                 *
                 * @param string $action Joomla authorisation name.
                 * @param string $asset Component asset.
                 * @return bool Whether the request is authorised.
                 */
                public function authorise(string $action, string $asset): bool
                {
                    return $action === 'core.admin' && $asset === 'com_sportsmanagement';
                }
            };
        }
    };

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\DatabasetoolController($app);

    foreach ([false, new \stdClass()] as $missing) {
        $controller->model = $missing;

        foreach (['repair', 'optimize', 'truncate', 'truncatejl', 'picturepath'] as $task) {
            $controller->$task();

            if (($controller->redirect[2] ?? '') !== 'error') {
                throw new \RuntimeException('Missing database model method did not fail: ' . $task);
            }
        }
    }

    $controller->model = new class {
        public array $commands = [];

        /**
         * Supply valid SportsManagement tables for maintenance.
         *
         * @return array<int,string> Table names.
         */
        public function getSportsManagementTables(): array
        {
            return ['#__sportsmanagement_project'];
        }

        /**
         * Supply the legacy JoomLeague table list.
         *
         * @return array<int,string> Legacy table names.
         */
        public function getJoomleagueTablesTruncate(): array
        {
            return ['#__joomleague_project'];
        }

        /**
         * Record a maintenance command rather than issuing SQL.
         *
         * @param string $table Table selected for maintenance.
         * @param string $command SQL maintenance command.
         * @return bool Always true in the successful regression path.
         */
        public function setSportsManagementTableQuery(string $table, string $command): bool
        {
            $this->commands[] = [$table, $command];

            return true;
        }

        /**
         * Simulate a successful picture-path update.
         *
         * @return void
         */
        public function setNewPicturePath(): void
        {
        }
    };

    foreach (['repair' => 'REPAIR', 'optimize' => 'OPTIMIZE', 'truncate' => 'TRUNCATE'] as $task => $command) {
        $controller->$task();
    }

    $controller->truncatejl();
    $controller->picturepath();

    $expected = [
        ['#__sportsmanagement_project', 'REPAIR'],
        ['#__sportsmanagement_project', 'OPTIMIZE'],
        ['#__sportsmanagement_project', 'TRUNCATE'],
        ['#__joomleague_project', 'TRUNCATE'],
    ];

    if ($controller->model->commands !== $expected) {
        throw new \RuntimeException('Valid database maintenance model stopped working.');
    }

    echo "Joomla 5/6 administrator database action guards: OK\n";
}
