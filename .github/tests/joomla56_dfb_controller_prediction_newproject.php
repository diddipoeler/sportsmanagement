<?php
/**
 * Joomla 5/6 regressions for DFB import writes, prediction target IDs and new-project bounds.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS {
    class Factory {
        public static function getDate(): object {
            return new class {
                public function toSql(): string { return '2026-10-16 08:00:00'; }
            };
        }
    }
}
namespace Joomla\CMS\Language {
    class Text {
        public static function _(string $key): string { return $key; }
    }
}
namespace Joomla\CMS\MVC\Controller {
    class BaseController {
        protected object $app;
        public mixed $model = false;
        public ?array $redirect = null;
        public bool $validToken = true;
        public int $modelCalls = 0;
        public function setTestApp(object $app): void { $this->app = $app; }
        protected function checkToken(): void {
            if (!$this->validToken) { throw new \RuntimeException('Invalid token', 403); }
        }
        protected function getModel($name = '', $prefix = '', $config = []) {
            ++$this->modelCalls;
            return $this->model;
        }
        protected function setRedirect(string $url, string $message = '', string $type = 'message'): void {
            $this->redirect = ['url' => $url, 'message' => $message, 'type' => $type];
        }
    }
}
namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}
namespace Joomla\CMS\Component {
    class ComponentHelper {
        public static function getParams(string $name): \Joomla\Registry\Registry {
            return new \Joomla\Registry\Registry();
        }
    }
}
namespace Joomla\Registry {
    class Registry {
        public function __construct(private array $values = []) {}
        public function get(string $name, mixed $default = null): mixed {
            return $this->values[$name] ?? $default;
        }
    }
}
namespace Joomla\Database {
    interface DatabaseInterface {}
    class ParameterType {
        public const INTEGER = 'integer';
        public const STRING = 'string';
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementDatabaseResolver {
        public static function resolve(
            \Joomla\Database\DatabaseInterface $database,
            int $selector
        ): \Joomla\Database\DatabaseInterface { return $database; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class PredictionentryModel {
        protected int $predictionGameId = 7;
        public int $matchLoads = 0;
        protected function getEntryMember(): object { return (object) ['user_id' => 3, 'receipt' => 1]; }
        protected function getSelectedMemberNumericId(): int { return 9; }
        protected function getProjectId(): int { return 4; }
        protected function getRoundId(): int { return 5; }
        protected function getEntryProject(): object { return (object) ['mode' => 0, 'joker' => 0]; }
        protected function canActAsEntryMember(object $member): bool { return true; }
        protected function getEntryMatches(): array {
            ++$this->matchLoads;
            if ($this->matchLoads > 1) {
                throw new \RuntimeException('Simulated notification-stage match reload failure.');
            }
            return [(object) ['id' => 31, 'editable' => 1, 'prediction_result_id' => 0]];
        }
        protected function getMemberProjectJokerCount(): int { return 0; }
        protected function isRoundExtrasEditable(array $matches): bool { return false; }
        protected function siteApplication(): object { return $GLOBALS['tipTestApp']; }
        protected function getDatabase(): object { return $GLOBALS['tipTestDb']; }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/admin/src/Controller/JlextdfbkeyimportController.php';
    require_once $root . '/site/src/Model/PredictiontipModel.php';
    require_once $root . '/modules/mod_sportsmanagement_new_project/src/Helper/NewProjectHelper.php';

    class DfbDatabase {
        public array $writes = [];
        public bool $fail = false;
        public function insertObject(string $table, object $record, string $pk): bool {
            if ($this->fail) { return false; }
            $this->writes[] = [$table, clone $record];
            return true;
        }
    }
    $db = new DfbDatabase();
    $post = new class {
        public array $values = [];
        public function getArray(): array { return $this->values; }
        public function getInt(string $key, int $default = 0): int {
            return (int) ($this->values[$key] ?? $default);
        }
    };
    $app = new class($post) {
        public object $identity;
        public array $messages = [];
        public function __construct(private object $post) {
            $this->identity = new class {
                public int $id = 19;
                public bool $admin = false;
                public function authorise(string $action, string $asset): bool {
                    return $this->admin && $action === 'core.admin'
                        && $asset === 'com_sportsmanagement';
                }
            };
        }
        public function getIdentity(): object { return $this->identity; }
        public function getInput(): object { return new class($this->post) {
            public function __construct(public object $post) {}
        }; }
        public function setUserState(string $name, mixed $value): void {}
        public function enqueueMessage(string $message, string $type): void {
            $this->messages[] = [$message, $type];
        }
    };
    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\JlextdfbkeyimportController();
    $controller->setTestApp($app);
    $controller->model = new class($db) {
        public function __construct(private DfbDatabase $db) {}
        public function getDatabase(): DfbDatabase { return $this->db; }
    };
    foreach (['getdivisionfirst', 'apply', 'save', 'insert'] as $method) {
        try {
            $controller->{$method}();
            throw new \RuntimeException('DFB task accepted a non-administrator.');
        } catch (\RuntimeException $e) {
            if ($e->getCode() !== 403) { throw $e; }
        }
    }
    if ($db->writes !== [] || $controller->modelCalls !== 0) {
        throw new \RuntimeException('Unauthorized importer accessed its persistence model.');
    }
    $app->identity->admin = true;
    $controller->validToken = false;
    try {
        $controller->save();
        throw new \RuntimeException('DFB import accepted invalid token.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 403) { throw $e; }
    }
    $controller->validToken = true;
    $post->values = [
        'projectid' => '7', 'divisionid' => '0',
        'roundcode' => ['1', '2'], 'name' => ['Round 1', 'Round 2'],
        'round_date_first' => ['2026-10-16', '2026-10-19'],
        'round_date_last' => ['2026-10-17', '2026-10-20'],
    ];
    if ($controller->save() !== true || count($db->writes) !== 2
        || $db->writes[0][1]->project_id !== 7
        || $db->writes[0][1]->round_date_first !== '2026-10-16') {
        throw new \RuntimeException('Valid DFB round import no longer works.');
    }
    foreach ([
        ['projectid' => '7garbage'],
        ['roundcode' => ['1', '2garbage']],
        ['roundcode' => array_fill(0, 501, '1')],
        ['name' => ['Only one name']],
        ['round_date_first' => ['2026-02-31', '2026-10-19']],
        ['roundcode' => '1'],
        ['divisionid' => '2garbage'],
    ] as $change) {
        $post->values = array_replace([
            'projectid' => '7', 'divisionid' => '0',
            'roundcode' => ['1', '2'], 'name' => ['Round 1', 'Round 2'],
            'round_date_first' => ['2026-10-16', '2026-10-19'],
            'round_date_last' => ['2026-10-17', '2026-10-20'],
        ], $change);
        $before = count($db->writes);
        try {
            $controller->save();
            throw new \RuntimeException('Invalid DFB round form was accepted.');
        } catch (\UnexpectedValueException $e) {
            if ($e->getCode() !== 400) { throw $e; }
        }
        if (count($db->writes) !== $before) {
            throw new \RuntimeException('Invalid DFB round caused a partial write.');
        }
    }
    $post->values = [
        'round_id' => ['8'], 'match_number' => ['1'],
        'projectteam1_id' => ['11'], 'projectteam2_id' => ['12'],
        'match_date' => ['2026-10-16'],
    ];
    if ($controller->insert() !== true || count($db->writes) !== 3
        || $db->writes[2][1]->round_id !== 8) {
        throw new \RuntimeException('Valid DFB match import no longer works.');
    }
    foreach ([
        ['round_id' => ['8junk']],
        ['match_number' => ['1junk']],
        ['projectteam1_id' => ['12']],
        ['match_date' => ['2026-02-31']],
        ['round_id' => array_fill(0, 5001, '8')],
        ['projectteam2_id' => []],
    ] as $change) {
        $post->values = array_replace([
            'round_id' => ['8'], 'match_number' => ['1'],
            'projectteam1_id' => ['11'], 'projectteam2_id' => ['12'],
            'match_date' => ['2026-10-16'],
        ], $change);
        $before = count($db->writes);
        try {
            $controller->insert();
            throw new \RuntimeException('Invalid DFB match form was accepted.');
        } catch (\UnexpectedValueException $e) {
            if ($e->getCode() !== 400) { throw $e; }
        }
        if (count($db->writes) !== $before) {
            throw new \RuntimeException('Invalid DFB match caused a partial write.');
        }
    }
    $db->fail = true;
    $post->values = [
        'round_id' => ['8'], 'match_number' => ['1'],
        'projectteam1_id' => ['11'], 'projectteam2_id' => ['12'],
        'match_date' => ['2026-10-16'],
    ];
    if ($controller->insert() !== false || ($controller->redirect['type'] ?? '') !== 'error') {
        throw new \RuntimeException('Failed DFB insertion was reported successful.');
    }

    $GLOBALS['tipTestApp'] = new class {
        public function getIdentity(): object { return (object) ['id' => 3]; }
    };
    $GLOBALS['tipTestDb'] = new class {
        public array $writes = [];
        public function insertObject(string $table, object $record): bool {
            $this->writes[] = [$table, clone $record];
            return true;
        }
        public function updateObject(string $table, object $record, string $key, bool $updateNulls): bool {
            $this->writes[] = [$table, clone $record];
            return true;
        }
    };
    $tips = new \Diddipoeler\Component\SportsManagement\Site\Model\PredictiontipModel();
    $targets = ['prediction_id' => 7, 'member_id' => 9, 'pj' => 4, 'r' => 5];
    foreach (['7junk', '07', '999999999999999999999999', [7], true] as $bad) {
        try {
            $tips->saveTips(array_replace($targets, ['prediction_id' => $bad]));
            throw new \RuntimeException('Malformed prediction target ID was accepted.');
        } catch (\UnexpectedValueException $e) {}
        if ($GLOBALS['tipTestDb']->writes !== []) {
            throw new \RuntimeException('Malformed prediction target reached the database.');
        }
    }
    if (!$tips->saveTips($targets + ['homes' => [31 => '1'], 'aways' => [31 => '0']])
        || count($GLOBALS['tipTestDb']->writes) !== 2) {
        throw new \RuntimeException('Receipt query failure invalidated persisted prediction tips.');
    }

    class ProjectsQuery {
        public function select(mixed $value): self { return $this; }
        public function from(string $value): self { return $this; }
        public function join(string $kind, string $what): self { return $this; }
        public function where(string $condition): self { return $this; }
        public function bind(string $key, mixed $value, string $type): self { return $this; }
        public function order(string $column): self { return $this; }
    }
    class ProjectsDb implements \Joomla\Database\DatabaseInterface {
        public array $limits = [];
        public function createQuery(): ProjectsQuery { return new ProjectsQuery(); }
        public function quoteName(string $name, ?string $alias = null): string { return $name; }
        public function setQuery(ProjectsQuery $query, int $offset = 0, int $limit = 0): void {
            $this->limits[] = $limit;
        }
        public function loadObjectList(): array { return []; }
    }
    $projApp = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        public function get(string $name, mixed $fallback = ''): mixed {
            return $name === 'offset' ? 'UTC' : $fallback;
        }
    };
    $projectHelper = new \Diddipoeler\Module\SportsManagementNewProject\Site\Helper\NewProjectHelper();
    $projectDb = new ProjectsDb();
    if ($projectHelper->getData(new \Joomla\Registry\Registry(), $projApp, $projectDb) !== []
        || $projectDb->limits !== [100]) {
        throw new \RuntimeException('New project module query has no daily project limit.');
    }

    echo "Joomla 5/6 DFB controller, prediction tips and project module bounds OK\n";
}
