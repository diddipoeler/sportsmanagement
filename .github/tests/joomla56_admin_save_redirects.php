<?php
/**
 * Joomla 5/6 administrator project, team and round form redirect regressions.
 *
 * Stubs only Joomla's form controller, POST input, user state, routing and
 * model so the saved identity and project/club navigation can be exercised
 * without a running Joomla installation or external database.
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
         * Return a stable Joomla language token in isolated routing tests.
         *
         * @param string $key Joomla language key.
         * @return string Same untranslated key for assertions.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\Router {
    class Route
    {
        /**
         * Preserve the full query string without SEF URL transformations.
         *
         * @param string $url Joomla administrator URL.
         * @param bool $xhtml Whether to encode as XHTML.
         * @return string Query URL suitable for assertion.
         */
        public static function _(string $url, bool $xhtml = true): string { return $url; }
    }
}

namespace Joomla\CMS\MVC\Controller {
    class FormController
    {
        protected string $option = 'com_sportsmanagement';
        protected string $view_item = 'project';
        protected string $view_list = 'projects';
        public array $modelCalls = [];
        public array $redirects = [];
        public string $task = 'apply';
        public object $model;
        public bool $allowed = true;

        /**
         * Simulate successful Joomla CSRF checks for test requests.
         *
         * @return void
         */
        public function checkToken(): void {}

        /**
         * Simulate Joomla's edit/create ACL permission gate.
         *
         * @param array $data Unfiltered form data.
         * @param string $key Form record key.
         * @return bool Whether this simulated editor can save.
         */
        protected function allowSave($data, $key = 'id'): bool { return $this->allowed; }

        /**
         * Record MVC model options so project overrides can be inspected.
         *
         * @param string $name Model name.
         * @param string $prefix MVC model namespace.
         * @param array $config Passed model factory options.
         * @return object Model fixture used for the save operation.
         */
        public function getModel($name = '', $prefix = '', $config = [])
        {
            $this->modelCalls[] = [$name, $prefix, $config];
            return $this->model;
        }

        /**
         * Return the active toolbar task in Joomla's controller contract.
         *
         * @return string Apply, Save, Save & New or Copy task.
         */
        public function getTask(): string { return $this->task; }

        /**
         * Capture a redirect without navigating to Joomla.
         *
         * @param string $url Target administrator URL.
         * @param string $message Optional Joomla message.
         * @param string $type Joomla message severity.
         * @return void
         */
        public function setRedirect($url, $message = '', $type = 'message'): void
        {
            $this->redirects[] = [$url, $message, $type];
        }

        /**
         * Append the identifier of the newly saved item, when available.
         *
         * @param int|null $id Item identifier or null for a new form.
         * @param string $key URL record key.
         * @return string Form record query suffix.
         */
        protected function getRedirectToItemAppend($id = null, $key = 'id'): string
        {
            return $id === null ? '' : '&' . $key . '=' . $id;
        }

        /**
         * Return no pagination state for the isolated list redirect.
         *
         * @return string Empty list query suffix.
         */
        protected function getRedirectToListAppend(): string { return ''; }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Controller\ProjectController;
    use Diddipoeler\Component\SportsManagement\Administrator\Controller\SportsManagementFormController;

    define('_JEXEC', 1);

    final class RedirectTestPost
    {
        /**
         * Store Joomla POST submission values for the controller.
         *
         * @param array<string,mixed> $values Submitted form data.
         */
        public function __construct(public array $values) {}

        /**
         * Return all submitted values before model persistence.
         *
         * @return array<string,mixed> Joomla POST values.
         */
        public function getArray(): array { return $this->values; }

        /**
         * Retrieve a Joomla field without changing its value type.
         *
         * @param string $name Requested field.
         * @param mixed $default Fallback if missing.
         * @param string $filter Joomla input filter.
         * @return mixed Submitted value or fallback.
         */
        public function get(string $name, mixed $default = null, string $filter = 'cmd'): mixed
        {
            return $this->values[$name] ?? $default;
        }
    }

    final class RedirectTestInput
    {
        /**
         * Provide the request fields used by the legacy form saver.
         *
         * @param RedirectTestPost $post POST field bag.
         * @param array<string,mixed> $query GET or merged request variables.
         */
        public function __construct(public RedirectTestPost $post, public array $query = []) {}

        /**
         * Retrieve a command-safe request setting for Joomla toolbar tasks.
         *
         * @param string $name Input variable name.
         * @param string $default Fallback when missing.
         * @return string Command string in the simulated form request.
         */
        public function getCmd(string $name, string $default = ''): string
        {
            return (string) ($this->query[$name] ?? $default);
        }

        /**
         * Read an integer request value, mirroring Joomla's basic input API.
         *
         * @param string $name Requested input.
         * @param int $default Fallback when missing.
         * @return int Integer request value.
         */
        public function getInt(string $name, int $default = 0): int
        {
            return (int) ($this->query[$name] ?? $default);
        }

        /**
         * Return a boolean toolbar option, disabled for these test cases.
         *
         * @param string $name Requested switch.
         * @return bool Whether the option is enabled.
         */
        public function getBool(string $name): bool { return false; }
    }

    final class RedirectTestApplication
    {
        public array $messages = [];

        /**
         * Supply stored administrator state from a test fixture.
         *
         * @param array<string,mixed> $state Previous user state values.
         */
        public function __construct(public array $state = []) {}

        /**
         * Read an administrator state entry.
         *
         * @param string $key State key.
         * @param mixed $default Default if missing.
         * @return mixed Stored state value or default.
         */
        public function getUserState(string $key, mixed $default = null): mixed
        {
            return $this->state[$key] ?? $default;
        }

        /**
         * Record a model save warning without Joomla's message queue.
         *
         * @param string $message Localized error text.
         * @param string $type Message severity.
         * @return void
         */
        public function enqueueMessage(string $message, string $type = 'message'): void
        {
            $this->messages[] = [$message, $type];
        }
    }

    final class RedirectTestModel
    {
        public int $calls = 0;
        public array $saved = [];

        /**
         * Simulate successful Joomla database persistence of a form record.
         *
         * @param array<string,mixed> $data Submitted form values.
         * @return bool Successful simulated save.
         */
        public function save(array $data): bool
        {
            ++$this->calls;
            $this->saved = $data;
            return true;
        }

        /**
         * Return no error because the simulated save succeeded.
         *
         * @return string Empty model error message.
         */
        public function getError(): string { return ''; }
    }

    /**
     * Throw independently of php.ini assertion settings on a mismatch.
     *
     * @param mixed $expected Required save or redirect result.
     * @param mixed $actual Observed controller result.
     * @param string $context Description of the tested save scenario.
     * @return void
     */
    function redirectAssert(mixed $expected, mixed $actual, string $context): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException($context
                . ': expected ' . var_export($expected, true)
                . ', got ' . var_export($actual, true));
        }
    }

    /**
     * Create a form controller fixture without a full Joomla DI container.
     *
     * @param string $class Native form controller or project child controller.
     * @param string $item Item view to save.
     * @param string $list List view to use after Save & Close.
     * @param string $task Joomla toolbar task.
     * @param array<string,mixed> $data Posted jform values.
     * @param array<string,mixed> $query Other request fields, including insert_id.
     * @param array<string,mixed> $state Stored administrator selection.
     * @return SportsManagementFormController Controller with injected test data.
     */
    function redirectFixture(
        string $class,
        string $item,
        string $list,
        string $task,
        array $data,
        array $query,
        array $state = []
    ): SportsManagementFormController {
        $controller = (new \ReflectionClass($class))->newInstanceWithoutConstructor();
        $controller->task = $task;
        $controller->model = new RedirectTestModel();
        foreach ([
            'jsmjinput' => new RedirectTestInput(new RedirectTestPost(['jform' => $data]), $query),
            'jsmapp' => new RedirectTestApplication($state),
            'jsmoption' => 'com_sportsmanagement',
            'view_item' => $item,
            'view_list' => $list,
            'team_club_id' => $state['com_sportsmanagement.club_id'] ?? 0,
        ] as $property => $value) {
            (new \ReflectionProperty($controller, $property))->setValue($controller, $value);
        }
        return $controller;
    }

    require_once dirname(__DIR__, 2) . '/admin/src/Controller/SportsManagementFormController.php';
    require_once dirname(__DIR__, 2) . '/admin/src/Controller/ProjectController.php';

    // ProjectModel sets insert_id. It never sets the obsolete insert_project_id.
    $project = redirectFixture(ProjectController::class, 'project', 'projects', 'apply',
        ['id' => 42, 'name' => 'Example Project'], ['insert_id' => 42, 'insert_project_id' => 0]);
    redirectAssert(true, $project->save(), 'Project Apply failed');
    redirectAssert(
        'index.php?option=com_sportsmanagement&view=project&id=42&pid=42',
        $project->redirects[0][0] ?? null,
        'Project Apply lost the saved record ID'
    );

    $newProject = redirectFixture(ProjectController::class, 'project', 'projects', 'apply',
        ['id' => 0, 'name' => 'New Project'], ['insert_id' => 73]);
    redirectAssert(true, $newProject->save(), 'New project did not save');
    redirectAssert(
        'index.php?option=com_sportsmanagement&view=project&id=73&pid=73',
        $newProject->redirects[0][0] ?? null,
        'New project redirected to the wrong ID'
    );

    // An explicit legacy project ID remains supported for old import bridges.
    $legacy = redirectFixture(ProjectController::class, 'project', 'projects', 'apply',
        ['id' => 5], ['insert_id' => 6, 'insert_project_id' => 9]);
    redirectAssert(true, $legacy->save(), 'Legacy project redirect failed');
    redirectAssert(
        'index.php?option=com_sportsmanagement&view=project&id=9&pid=9',
        $legacy->redirects[0][0] ?? null,
        'Legacy project ID was not preserved'
    );

    // Project model lookups must not silently discard explicit factory options.
    $legacy->getModel('Project', 'Administrator', ['ignore_request' => true, 'test_option' => 7]);
    redirectAssert(
        ['Project', 'Administrator', ['ignore_request' => true, 'test_option' => 7]],
        $legacy->modelCalls[array_key_last($legacy->modelCalls)],
        'Project getModel ignored caller config'
    );

    // A selected team can be moved to another club before pressing Apply.
    $team = redirectFixture(SportsManagementFormController::class, 'team', 'teams', 'apply',
        ['id' => 0, 'name' => 'Club Team', 'club_id' => '0008'],
        ['insert_id' => 45],
        ['com_sportsmanagement.club_id' => 2, 'com_sportsmanagement.team_id' => 45]);
    redirectAssert(true, $team->save(), 'Team Apply failed');
    redirectAssert(
        'index.php?option=com_sportsmanagement&view=team&club_id=8&id=45',
        $team->redirects[0][0] ?? null,
        'Team Apply redirected to a stale club'
    );

    $close = redirectFixture(SportsManagementFormController::class, 'team', 'teams', 'save',
        ['id' => 45, 'club_id' => 8], ['insert_id' => 45],
        ['com_sportsmanagement.club_id' => 2, 'com_sportsmanagement.team_id' => 45]);
    redirectAssert(true, $close->save(), 'Team Save & Close failed');
    redirectAssert(
        'index.php?option=com_sportsmanagement&view=teams&club_id=8&team_id=45',
        $close->redirects[0][0] ?? null,
        'Team Save & Close redirected to an old club'
    );

    // A malformed relationship ID must not appear in redirect parameters.
    foreach ([['8junk'], '8junk', true, '9999999999999999999999'] as $badClubId) {
        $invalid = redirectFixture(SportsManagementFormController::class, 'team', 'teams', 'apply',
            ['id' => 45, 'club_id' => $badClubId], ['insert_id' => 45],
            ['com_sportsmanagement.club_id' => 2]);
        redirectAssert(true, $invalid->save(), 'Invalid club association fixture failed');
        redirectAssert(
            'index.php?option=com_sportsmanagement&view=team&club_id=0&id=45',
            $invalid->redirects[0][0] ?? null,
            'Invalid club association was accepted by redirect'
        );
    }

    // The actual native round view is singular; keep its project context.
    $round = redirectFixture(SportsManagementFormController::class, 'round', 'rounds', 'apply',
        ['id' => 11, 'project_id' => 16], ['insert_id' => 11, 'pid' => 77],
        ['com_sportsmanagement.pid' => 22]);
    redirectAssert(true, $round->save(), 'Round Apply failed');
    redirectAssert(
        'index.php?option=com_sportsmanagement&view=round&id=11&pid=16',
        $round->redirects[0][0] ?? null,
        'Native round Apply lost current project context'
    );

    echo "Joomla 5/6 backend project, team and round redirects: OK\n";
}
