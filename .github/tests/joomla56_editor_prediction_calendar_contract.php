<?php
/**
 * Joomla 5/6 regression checks for frontend editors, prediction scoping and calendar dates.
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
        public static function _(string $key): string { return $key; }
    }
}

namespace Joomla\CMS\MVC\Controller {
    class FormController
    {
        protected mixed $app = null;
        protected function checkToken(): void {}
        public function setTestApplication(object $application): void { $this->app = $application; }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementAdminModel
    {
        public static mixed $database = null;
        public ?string $error = null;
        protected function getDatabase(): mixed { return self::$database; }
        public function setError(string $error): void { $this->error = $error; }
    }
}

namespace Joomla\Database {
    class ParameterType { public const INTEGER = 'integer'; }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    foreach (['Editperson', 'Editteam', 'Editclub'] as $name) {
        require_once $root . '/site/src/Controller/' . $name . 'Controller.php';
        $class = 'Diddipoeler\\Component\\SportsManagement\\Site\\Controller\\' . $name . 'Controller';
        $controller = new $class();

        $identity = new class {
            public array $permissions = [];
            public function authorise(string $action, string $asset): bool
            {
                return $asset === 'com_sportsmanagement'
                    && in_array($action, $this->permissions, true);
            }
        };
        $app = new class($identity) {
            public function __construct(private object $identity) {}
            public function getIdentity(): object { return $this->identity; }
        };
        $controller->setTestApplication($app);

        // Valid session tokens are not sufficient without edit ACL.
        try {
            $controller->save();
            throw new \RuntimeException($name . ': unauthorized save was permitted');
        } catch (\RuntimeException $error) {
            if ($error->getCode() !== 403) {
                throw $error;
            }
        }

        $check = new \ReflectionMethod($controller, 'assertEditorPermission');
        $identity->permissions = ['core.edit'];
        $check->invoke($controller);

        if ($name === 'Editclub') {
            $identity->permissions = ['club.edit'];
            $check->invoke($controller);
            $identity->permissions = [];

            try {
                $controller->load();
                throw new \RuntimeException('Unauthorized club checkout was permitted');
            } catch (\RuntimeException $error) {
                if ($error->getCode() !== 403) {
                    throw $error;
                }
            }
        }
    }

    require_once $root . '/admin/src/Model/PredictionmemberModel.php';

    class QueryStub
    {
        public array $where = [];
        public array $whereIn = [];
        public array $binds = [];
        public function delete(string $table): self { return $this; }
        public function select(string $column): self { return $this; }
        public function from(string $table): self { return $this; }
        public function where(string $clause): self { $this->where[] = $clause; return $this; }
        public function whereIn(string $column, array $ids, mixed $type): self
        {
            $this->whereIn[$column] = $ids;
            return $this;
        }
        public function bind(string $name, mixed $value, mixed $type): self
        {
            $this->binds[$name] = $value;
            return $this;
        }
    }

    class DatabaseStub
    {
        public ?QueryStub $query = null;
        public int $executions = 0;
        public function createQuery(): QueryStub { return new QueryStub(); }
        public function quoteName(string $name): string { return $name; }
        public function setQuery(QueryStub $query): self { $this->query = $query; return $this; }
        public function execute(): bool { $this->executions++; return true; }
        public function loadColumn(): array { return [6, 6, -2]; }
    }

    $db = new DatabaseStub();
    \Diddipoeler\Component\SportsManagement\Administrator\Model\SportsManagementAdminModel::$database = $db;
    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\PredictionmemberModel();

    if ($model->deletePredictionMembers([2, 6], 0) !== false || $db->executions !== 0) {
        throw new \RuntimeException('Delete without prediction ID must be rejected.');
    }

    if ($model->deletePredictionMembers([2, 6], 31) !== true
        || $db->executions !== 1
        || $db->query?->binds[':deletePredictionId'] !== 31
        || !in_array('prediction_id = :deletePredictionId', $db->query->where, true)) {
        throw new \RuntimeException('Member deletion was not scoped to prediction 31.');
    }

    if ($model->publishpredmembers([6], 1, 0) !== false) {
        throw new \RuntimeException('Publishing without prediction ID must fail.');
    }

    $filter = new \ReflectionMethod($model, 'predictionMemberIdsForGame');
    $ids = $filter->invoke($model, [2, 6], 31);
    if ($ids !== [6]
        || $db->query?->binds[':selectedPredictionId'] !== 31
        || !in_array('prediction_id = :selectedPredictionId', $db->query->where, true)) {
        throw new \RuntimeException('Member selection leaked IDs from another game.');
    }

    date_default_timezone_set('Europe/Berlin');
    require_once $root . '/modules/mod_sportsmanagement_google_calendar/media/fullcalendar/demos/php/utils.php';
    if (date_default_timezone_get() !== 'Europe/Berlin') {
        throw new \RuntimeException('Calendar utilities changed Joomla timezone.');
    }

    $event = new \Event(['title' => 'Sample match', 'start' => '2026-10-11', 'end' => '2026-10-13']);
    if (!$event->isWithinDayRange(new \DateTime('2026-10-12'), new \DateTime('2026-10-14'))
        || $event->isWithinDayRange(new \DateTime('2026-10-13'), new \DateTime('2026-10-15'))) {
        throw new \RuntimeException('Calendar event intersection has regressed.');
    }

    echo "Joomla 5/6 editor ACL, scoped prediction actions and calendar utilities OK\n";
}
