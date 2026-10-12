<?php
/**
 * Joomla 5/6 administrator database-tool regression and frontend loader contract.
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
        public mixed $testModel = false;
        public array $redirect = [];
        public bool $validToken = true;
        public int $tokenChecks = 0;

        public function __construct($config = [], $factory = null, $app = null, $input = null)
        {
            $this->app = $app;
        }

        protected function checkToken(): void
        {
            ++$this->tokenChecks;

            if (!$this->validToken) {
                throw new \RuntimeException('Invalid administrator CSRF token.', 403);
            }
        }

        public function getModel($name = '', $prefix = '', $config = [])
        {
            return $this->testModel;
        }

        protected function setRedirect($url, $message = '', $type = 'message'): void
        {
            $this->redirect = ['url' => $url, 'message' => $message, 'type' => $type];
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Controller\DatabasetoolController;

    \define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/DatabasetoolController.php';

    final class ToolApp
    {
        public bool $adminAllowed = true;

        public function getIdentity(): object
        {
            return new class($this) {
                public function __construct(private ToolApp $application) {}

                public function authorise(string $action, string $asset): bool
                {
                    return $this->application->adminAllowed
                        && $action === 'core.admin'
                        && $asset === 'com_sportsmanagement';
                }
            };
        }
    }

    function ensure(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    $app = new ToolApp();
    $controller = new DatabasetoolController([], null, $app);

    $controller->updatetemplatemasters();
    ensure(($controller->redirect['type'] ?? '') === 'warning',
        'Unimplemented template-master maintenance must not report success.');

    $model = new class {
        public array $tables = [];
        public array $actions = [];
        public string $failedTable = '';

        public function getSportsManagementTables(): array { return $this->tables; }
        public function getJoomleagueTablesTruncate(): array { return $this->tables; }

        public function setSportsManagementTableQuery(string $table, string $action): bool
        {
            $this->actions[] = [$table, $action];
            return $table !== $this->failedTable;
        }
    };
    $controller->testModel = $model;

    foreach (['repair', 'optimize', 'truncate', 'truncatejl'] as $task) {
        $controller->{$task}();
        ensure(($controller->redirect['type'] ?? '') === 'warning',
            $task . ' must warn if there are no matching database tables.');
        ensure($model->actions === [], $task . ' must not mutate nonexistent tables.');
    }

    $model->tables = ['jos_sportsmanagement_club', 'jos_sportsmanagement_team'];
    $model->failedTable = 'jos_sportsmanagement_team';
    $controller->repair();
    ensure(($controller->redirect['type'] ?? '') === 'error',
        'A failed table action must report an error.');
    ensure(count($model->actions) === 2, 'Expected an action for each selected table.');

    $model->failedTable = '';
    $model->actions = [];
    $controller->optimize();
    ensure(($controller->redirect['type'] ?? '') === 'message',
        'Successful table maintenance must report success.');
    ensure($model->actions === [
        ['jos_sportsmanagement_club', 'OPTIMIZE'],
        ['jos_sportsmanagement_team', 'OPTIMIZE'],
    ], 'Maintenance must pass the correct command for each table.');

    $app->adminAllowed = false;
    $model->actions = [];
    try {
        $controller->truncate();
        throw new \RuntimeException('Unauthorized table truncation was accepted.');
    } catch (\RuntimeException $e) {
        ensure($e->getCode() === 403, 'Unauthorized truncate must fail with 403.');
    }
    ensure($model->actions === [], 'Unauthorized truncation must not issue SQL actions.');

    $app->adminAllowed = true;
    $controller->validToken = false;
    try {
        $controller->truncate();
        throw new \RuntimeException('Missing POST CSRF token was accepted.');
    } catch (\RuntimeException $e) {
        ensure($e->getCode() === 403, 'Invalid CSRF token must fail with 403.');
    }
    ensure($model->actions === [], 'Invalid CSRF token must not issue SQL actions.');
    ensure($controller->tokenChecks >= 9, 'Every maintenance task must check the CSRF token.');

    $siteController = file_get_contents(
        dirname(__DIR__, 2) . '/site/src/Controller/UpdsportsmanagementController.php'
    );
    ensure($siteController !== false
        && str_contains($siteController, "__DIR__ . '/../Model/UpdsportsmanagementModel.php'")
        && !str_contains($siteController, 'JPATH_SITE'),
        'Frontend updater must resolve its model relative to the component path.');

    echo "Joomla 5/6 administrator maintenance and frontend updater loader OK.\n";
}
