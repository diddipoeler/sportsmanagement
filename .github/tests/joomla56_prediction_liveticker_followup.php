<?php
/**
 * PHP 8.3/8.4 regression checks for Joomla 5/6 prediction controllers and liveticker bounds.
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
        public static function sprintf(string $key, mixed ...$args): string { return $key; }
    }
}
namespace Joomla\CMS\Session {
    class Session
    {
        public static array $sources = [];
        public static function checkToken(string $method = 'post'): bool
        {
            self::$sources[] = $method;
            return false;
        }
    }
}
namespace Joomla\CMS\MVC\Controller {
    class BaseController
    {
        protected mixed $app = null;
        protected mixed $input = null;
        public function getModel(string $name): mixed { return false; }
        public function setRedirect(string $url, string $message = '', string $type = 'message'): void {}
    }
}
namespace Joomla\CMS\Router {
    class Route
    {
        public static function _(string $route, bool $xhtml = true): string { return $route; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementAdminController extends \Joomla\CMS\MVC\Controller\BaseController
    {
        public array $redirect = [];
        public int $writes = 0;
        public int $modelRequests = 0;
        public ?object $modelStub = null;

        public function setTestApplication(object $app): void { $this->app = $app; }
        protected function assertWritePermission(string $permission = 'core.edit'): void { $this->writes++; }
        public function getModel($name = '', $prefix = '', $config = []): mixed
        {
            $this->modelRequests++;
            return $this->modelStub ?? false;
        }
        public function setRedirect(string $url, string $message = '', string $type = 'message'): void
        {
            $this->redirect = compact('url', 'message', 'type');
        }
    }
}
namespace Joomla\Registry {
    class Registry
    {
        public function __construct(private array $data = []) {}
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->data[$key] ?? $default;
        }
    }
}
namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}
namespace Joomla\Database {
    interface DatabaseInterface {}
    class ParameterType { public const INTEGER = 'integer'; }
}
namespace Diddipoeler\Component\SportsManagement\Site\Service {
    class SportsManagementDatabaseResolver
    {
        public static function resolve(object $database, int $selector): object { return $database; }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    require_once $root . '/site/src/Controller/PredictionrankingController.php';

    $ranking = new \Diddipoeler\Component\SportsManagement\Site\Controller\PredictionrankingController();
    try {
        $ranking->selectprojectround();
        throw new \RuntimeException('Missing ranking POST token was accepted');
    } catch (\RuntimeException $error) {
        if ($error->getCode() !== 403) {
            throw $error;
        }
    }
    if (\Joomla\CMS\Session\Session::$sources !== ['post']) {
        throw new \RuntimeException('Ranking token validation must use POST exclusively.');
    }

    require_once $root . '/admin/src/Controller/PredictionmembersController.php';
    $input = new class {
        public object $post;
        public int $predictionId = 0;
        public array $selected = [];

        public function __construct()
        {
            $this->post = new class($this) {
                public function __construct(private object $parent) {}
                public function getInt(string $key): int { return $this->parent->predictionId; }
                public function get(string $key, mixed $default = null, string $type = ''): mixed
                {
                    return $key === 'prediction_members' ? $this->parent->selected : $default;
                }
            };
        }
    };
    $app = new class($input) {
        public function __construct(private object $input) {}
        public function getInput(): object { return $this->input; }
    };
    $members = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\PredictionmembersController();
    $members->setTestApplication($app);
    $members->save_memberlist();
    if ($members->modelRequests !== 0 || ($members->redirect['type'] ?? '') !== 'warning') {
        throw new \RuntimeException('Invalid prediction ID should not open a model.');
    }

    $input->predictionId = 27;
    $members->save_memberlist();
    if ($members->modelRequests !== 0 || ($members->redirect['type'] ?? '') !== 'warning') {
        throw new \RuntimeException('Empty prediction members should not be saved.');
    }

    $input->selected = [-2, '5', 5, '0', 9];
    $members->modelStub = new class {
        public array $saved = [];
        public function save_memberlist(array $ids, int $prediction): int
        {
            $this->saved = [$ids, $prediction];
            return 0;
        }
    };
    $members->save_memberlist();
    if ($members->modelStub->saved !== [[5, 9], 27]
        || ($members->redirect['type'] ?? '') !== 'message') {
        throw new \RuntimeException('Prediction selection must deduplicate positive member IDs.');
    }

    require_once $root . '/modules/mod_sportsmanagement_liveticker/src/Helper/LivetickerHelper.php';

    class QueryStub
    {
        public array $binds = [];
        public function select(mixed $columns): self { return $this; }
        public function from(string $table): self { return $this; }
        public function join(string $kind, string $clause): self { return $this; }
        public function where(string $condition): self { return $this; }
        public function order(string $sorting): self { return $this; }
        public function bind(string $key, mixed $value, mixed $type): self
        {
            $this->binds[$key] = $value;
            return $this;
        }
    }
    class DbStub implements \Joomla\Database\DatabaseInterface
    {
        public ?QueryStub $query = null;
        public int $limit = 0;
        public function createQuery(): QueryStub { return new QueryStub(); }
        public function quoteName(string $name, string $alias = ''): string
        {
            return $alias !== '' ? $name . ' AS ' . $alias : $name;
        }
        public function setQuery(QueryStub $query, int $offset = 0, int $limit = 0): self
        {
            $this->query = $query;
            $this->limit = $limit;
            return $this;
        }
        public function loadObjectList(): array { return []; }
    }
    class AppStub implements \Joomla\CMS\Application\CMSApplicationInterface
    {
        public function isClient(string $name): bool { return $name === 'site'; }
        public function get(string $name, string $default = ''): string { return 'UTC'; }
    }

    $moduleHelper = new \Diddipoeler\Module\SportsManagementLiveticker\Site\Helper\LivetickerHelper();
    $params = new \Joomla\Registry\Registry([
        'display_num' => 2000000,
        'playtime' => 2000000,
        'update_timeout' => 2000000,
        'display_commentary' => 0,
    ]);
    $db = new DbStub();
    $app = new AppStub();
    $rows = $moduleHelper->getList($params, $app, 2000000, $db);
    if ($rows !== [] || $db->limit !== 100 || $db->query === null) {
        throw new \RuntimeException('Liveticker row limit is not bounded to 100.');
    }
    $duration = $db->query->binds[':timestampTo'] - $db->query->binds[':timestampFrom'];
    if ($duration !== 1440 * 2 * 60) {
        throw new \RuntimeException('Liveticker query window exceeds one day per direction.');
    }

    $result = $moduleHelper->getData($params, (object) ['id' => 41], $app, $db);
    if ($result['updateTimeout'] !== 3600 || $result['moduleId'] !== 41) {
        throw new \RuntimeException('Liveticker refresh timeout is not bounded.');
    }

    $moduleHelper->getList(new \Joomla\Registry\Registry(['playtime' => 0]), $app, 0, $db);
    if ($db->limit !== 1) {
        throw new \RuntimeException('Liveticker minimum list size must be positive.');
    }

    echo "Joomla 5/6 prediction token/selection and liveticker bounds OK\n";
}
