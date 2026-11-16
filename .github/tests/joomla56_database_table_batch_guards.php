<?php
/**
 * Guard Joomla 5/6 destructive maintenance against malformed table batches.
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
         * Return the untranslated Joomla error key for assertions.
         *
         * @param string $key Joomla language constant.
         * @return string Same constant.
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
        public object $model;
        public array $redirect = [];

        /**
         * Supply the fake administrator runtime.
         *
         * @param object $app Mock application.
         */
        public function __construct(object $app)
        {
            $this->app = $app;
        }

        /**
         * Simulate successful Joomla form-token validation.
         *
         * @return void
         */
        protected function checkToken(): void
        {
        }

        /**
         * Resolve the injected database maintenance model.
         *
         * @param string $name Model name.
         * @param string $prefix Model namespace.
         * @param array $config MVC factory settings.
         * @return object Fake maintenance model.
         */
        public function getModel($name = '', $prefix = '', $config = [])
        {
            return $this->model;
        }

        /**
         * Capture the Joomla administrator maintenance outcome.
         *
         * @param string $url Component route.
         * @param string $message Translated user feedback.
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
         * Supply a component administrator for destructive maintenance checks.
         *
         * @return object Identity with component administrator ACL.
         */
        public function getIdentity(): object
        {
            return new class {
                /**
                 * Allow only the SportsManagement administration action.
                 *
                 * @param string $action Requested ACL action.
                 * @param string $asset Component asset.
                 * @return bool True for component administration.
                 */
                public function authorise(string $action, string $asset): bool
                {
                    return $action === 'core.admin' && $asset === 'com_sportsmanagement';
                }
            };
        }
    };

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\DatabasetoolController($app);
    $model = new class {
        public array $tables = [];
        public array $queries = [];

        /**
         * Return selected SportsManagement tables for this case.
         *
         * @return array List of arbitrary legacy model entries.
         */
        public function getSportsManagementTables(): array
        {
            return $this->tables;
        }

        /**
         * Return the legacy JoomLeague table selection.
         *
         * @return array List of arbitrary legacy model entries.
         */
        public function getJoomleagueTablesTruncate(): array
        {
            return $this->tables;
        }

        /**
         * Record database maintenance commands without issuing SQL.
         *
         * @param string $table Component table name.
         * @param string $command Requested maintenance operation.
         * @return bool Whether the operation was accepted.
         */
        public function setSportsManagementTableQuery(string $table, string $command): bool
        {
            $this->queries[] = [$table, $command];

            return true;
        }
    };
    $controller->model = $model;

    set_error_handler(
        /**
         * Fail on implicit array-to-string conversion or PHP warnings.
         *
         * @param int $severity Diagnostic severity.
         * @param string $message Diagnostic message.
         * @return never Always throws.
         */
        static function (int $severity, string $message): never {
            throw new \ErrorException($message, 0, $severity);
        }
    );

    try {
        foreach ([
            ['#__sportsmanagement_project', ['nested']],
            ['#__sportsmanagement_project', new \stdClass()],
            ['#__sportsmanagement_project', null],
            ['#__sportsmanagement_project', 'not_component_table'],
            ['#__sportsmanagement_project', str_repeat('x', 256)],
            array_fill(0, 501, '#__sportsmanagement_project'),
        ] as $tables) {
            $model->tables = $tables;
            $model->queries = [];
            $controller->truncate();

            if ($model->queries !== [] || ($controller->redirect[2] ?? null) !== 'error') {
                throw new \RuntimeException('Malformed table batch triggered partial destructive SQL.');
            }
        }

        $model->tables = ['#__sportsmanagement_project', '#__sportsmanagement_project'];
        $model->queries = [];
        $controller->truncate();

        if ($model->queries !== [['#__sportsmanagement_project', 'TRUNCATE']]
            || ($controller->redirect[2] ?? '') === 'error') {
            throw new \RuntimeException('Valid duplicate table names were not processed safely.');
        }

        $model->tables = ['#__sportsmanagement_project', '#__joomleague_project'];
        $model->queries = [];
        $controller->truncate();

        if ($model->queries !== [] || ($controller->redirect[2] ?? null) !== 'error') {
            throw new \RuntimeException('Mixed component and legacy tables bypassed selection scope.');
        }
    } finally {
        restore_error_handler();
    }

    echo "Joomla 5/6 database table batch validation: OK\n";
}
