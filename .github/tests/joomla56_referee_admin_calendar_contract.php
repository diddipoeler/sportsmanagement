<?php
/**
 * Joomla 5/6 regression checks for frontend result/referee edits, admin membership and calendar cache.
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
        public static bool $valid = true;
        public static string $source = '';
        public static function checkToken(string $type = 'post'): bool
        {
            self::$source = $type;
            return $type === 'post' && self::$valid;
        }
    }
}
namespace Joomla\CMS\MVC\Controller {
    class BaseController
    {
        protected mixed $app = null;
        protected mixed $input = null;
        public array $redirect = [];
        public int $modelCalls = 0;
        public mixed $editor = null;
        public function setEnvironment(object $app, object $input): void
        {
            $this->app = $app;
            $this->input = $input;
        }
        public function getModel($name = '', $prefix = '', $config = [])
        {
            $this->modelCalls++;
            return $name === 'Editmatch' ? $this->editor : null;
        }
        public function setRedirect(string $url, string $message = '', string $type = 'message'): void
        {
            $this->redirect = compact('url', 'message', 'type');
        }
        public function display($cachable = false, $urlparams = []): void {}
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    class SiteRouteHelper
    {
        public static function view(string $name, array $params = []): string
        {
            return '/index.php?option=com_sportsmanagement&view=' . $name;
        }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class ResultsAccessModel
    {
        public static bool $allowed = false;
        public static array $selected = [];
        public static int $projectId = 0;
        public static int $databaseSelector = -1;

        public function setDatabaseSelector(int $selector): void { self::$databaseSelector = $selector; }
        public function setProjectId(int $project): void { self::$projectId = $project; }
        public function canEditMatches(array $matches): bool
        {
            self::$selected = $matches;
            return self::$allowed;
        }
    }
    class ResultsEditModel
    {
        public static bool $result = true;
        public static int $calls = 0;
        public function setDatabaseSelector(int $selector): void {}
        public function saveShort(array $post, array $ids): bool
        {
            self::$calls++;
            return self::$result;
        }
    }
    class EditmatchModel
    {
        public bool $result = true;
        public int $calls = 0;
        public int $databaseSelector = -1;
        public array $saved = [];
        public function setDatabaseSelector(int $selector): void { $this->databaseSelector = $selector; }
        public function updateReferees(array $post): bool
        {
            $this->saved = $post;
            $this->calls++;
            return $this->result;
        }
    }
}
namespace Joomla\CMS\Router {
    class Route
    {
        public static function _(string $url, bool $xhtml = true): string { return $url; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementAdminController extends \Joomla\CMS\MVC\Controller\BaseController
    {
        public int $permissionCalls = 0;
        public function getModel($name = '', $prefix = '', $config = [])
        {
            $this->modelCalls++;
            return $this->editor;
        }
        protected function assertWritePermission(string $action = 'core.edit'): void
        {
            $this->permissionCalls++;
            if ($action !== 'core.delete') {
                throw new \RuntimeException('Unexpected permission action');
            }
        }
    }
}
namespace Joomla\Registry {
    class Registry
    {
        public function __construct(private array $values = []) {}
        public function get(string $key, mixed $default = null): mixed
        {
            return $this->values[$key] ?? $default;
        }
    }
}
namespace Joomla\CMS\Application {
    interface CMSApplicationInterface {}
}
namespace Joomla\CMS\Cache {
    interface CacheControllerFactoryInterface {}
}
namespace Joomla\CMS\Log {
    class Log
    {
        public const ERROR = 400;
        public static array $entries = [];
        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = compact('message', 'level', 'category');
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Controller/ResultsController.php';

    class InputStub
    {
        public array $values = [];
        public object $post;
        public function __construct()
        {
            $this->post = new class($this) {
                public function __construct(private InputStub $input) {}
                public function getArray(): array { return $this->input->values; }
                public function get(string $key, mixed $default = null, string $type = ''): mixed
                {
                    return $this->input->values[$key] ?? $default;
                }
                public function getInt(string $key, int $default = 0): int
                {
                    return (int) ($this->input->values[$key] ?? $default);
                }
            };
        }
        public function getInt(string $key, int $default = 0): int
        {
            return (int) ($this->values[$key] ?? $default);
        }
        public function getCmd(string $key, string $default = ''): string
        {
            return (string) ($this->values[$key] ?? $default);
        }
    }
    class AppStub
    {
        public array $messages = [];
        public function __construct(private InputStub $input) {}
        public function getInput(): InputStub { return $this->input; }
        public function enqueueMessage(string $text, string $type = 'message'): void
        {
            $this->messages[] = ['message' => $text, 'type' => $type];
        }
    }

    $input = new InputStub();
    $app = new AppStub($input);
    $controller = new \Diddipoeler\Component\SportsManagement\Site\Controller\ResultsController();
    $controller->setEnvironment($app, $input);
    $editor = new \Diddipoeler\Component\SportsManagement\Site\Model\EditmatchModel();
    $controller->editor = $editor;

    $input->values = ['id' => 42, 'project_id' => 11, 'cfg_which_database' => 1, 'position1' => [7, 8]];

    \Joomla\CMS\Session\Session::$valid = false;
    try {
        $controller->saveReferees();
        throw new \RuntimeException('Invalid POST token was accepted');
    } catch (\RuntimeException $error) {
        if ($error->getCode() !== 403) {
            throw $error;
        }
    }
    if ($editor->calls !== 0 || \Joomla\CMS\Session\Session::$source !== 'post') {
        throw new \RuntimeException('CSRF token did not block referee writes.');
    }

    \Joomla\CMS\Session\Session::$valid = true;
    $input->values['id'] = 0;
    $controller->saveReferees();
    if ($editor->calls !== 0 || ($controller->redirect['type'] ?? '') !== 'error') {
        throw new \RuntimeException('Referee update accepted a missing match ID.');
    }

    $input->values['id'] = 42;
    $input->values['project_id'] = 0;
    $controller->saveReferees();
    if ($editor->calls !== 0 || ($controller->redirect['type'] ?? '') !== 'error') {
        throw new \RuntimeException('Referee update accepted a missing project ID.');
    }

    $input->values['project_id'] = 11;
    \Diddipoeler\Component\SportsManagement\Site\Model\ResultsAccessModel::$allowed = false;
    $controller->saveReferees();
    if ($editor->calls !== 0 || ($controller->redirect['type'] ?? '') !== 'error'
        || \Diddipoeler\Component\SportsManagement\Site\Model\ResultsAccessModel::$selected !== [42]) {
        throw new \RuntimeException('Referee update bypassed match ACL.');
    }

    \Diddipoeler\Component\SportsManagement\Site\Model\ResultsAccessModel::$allowed = true;
    $controller->saveReferees();
    if ($editor->calls !== 1 || $editor->saved['id'] !== 42
        || $editor->saved['project_id'] !== 11 || $editor->databaseSelector !== 1
        || ($controller->redirect['type'] ?? '') !== 'message') {
        throw new \RuntimeException('Authorized referee assignment was not persisted.');
    }

    $editor->result = false;
    $controller->saveReferees();
    if ($editor->calls !== 2 || ($controller->redirect['type'] ?? '') !== 'error') {
        throw new \RuntimeException('A failed referee save was reported as successful.');
    }

    $input->values = ['cid' => [42, 42, -3], 'p' => 11, 'cfg_which_database' => 1];
    \Diddipoeler\Component\SportsManagement\Site\Model\ResultsEditModel::$result = false;
    $controller->saveshort();
    if (\Diddipoeler\Component\SportsManagement\Site\Model\ResultsEditModel::$calls !== 1
        || ($app->messages[count($app->messages) - 1]['type'] ?? '') !== 'error') {
        throw new \RuntimeException('Failed frontend result writes did not display an error.');
    }

    require_once $root . '/admin/src/Controller/PredictionmembersController.php';

    class MemberRemovalStub
    {
        public bool $resultsSuccess = false;
        public int $resultCalls = 0;
        public int $memberCalls = 0;
        public function deletePredictionResults(array $ids, int $predictionId): bool
        {
            $this->resultCalls++;
            return $this->resultsSuccess;
        }
        public function deletePredictionMembers(array $ids, int $predictionId): bool
        {
            $this->memberCalls++;
            return true;
        }
    }
    $removalInput = new InputStub();
    $removalInput->values = ['cid' => [1, 2], 'prediction_id' => 5];
    $members = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\PredictionmembersController();
    $members->setEnvironment(new AppStub($removalInput), $removalInput);
    $memberWriter = new MemberRemovalStub();
    $members->editor = $memberWriter;
    $members->remove();
    if ($memberWriter->resultCalls !== 1 || $memberWriter->memberCalls !== 0
        || ($members->redirect['type'] ?? '') !== 'error') {
        throw new \RuntimeException('Memberships were removed despite a failed results deletion.');
    }
    $memberWriter->resultsSuccess = true;
    $members->remove();
    if ($memberWriter->memberCalls !== 1 || ($members->redirect['type'] ?? '') !== 'message') {
        throw new \RuntimeException('Successful member removal did not proceed.');
    }

    require_once $root . '/modules/mod_sportsmanagement_google_calendar/src/Helper/GoogleCalendarHelper.php';

    class CacheStub implements \Joomla\CMS\Cache\CacheControllerFactoryInterface
    {
        public bool $factoryThrows = false;
        public bool $callbackThrows = false;
        public int $createCalls = 0;
        public array $options = [];
        public function createCacheController(string $type, array $options): object
        {
            $this->createCalls++;
            $this->options = $options;
            if ($this->factoryThrows) {
                throw new \Error('Simulated factory failure with sensitive-token');
            }
            return new class($this) {
                public function __construct(private CacheStub $factory) {}
                public function call(callable $callback, mixed ...$args): array
                {
                    if ($this->factory->callbackThrows) {
                        throw new \TypeError('Simulated cache failure with sensitive-token');
                    }
                    return [(object) ['title' => 'Test']];
                }
            };
        }
    }
    class CalendarApp implements \Joomla\CMS\Application\CMSApplicationInterface {}

    $calendar = new \Diddipoeler\Module\SportsManagementGoogleCalendar\Site\Helper\GoogleCalendarHelper();
    $cache = new CacheStub();
    $settings = new \Joomla\Registry\Registry(['api_key' => 'sensitive-token', 'calendar_id' => 'calendar@example.org']);
    $calendarApp = new CalendarApp();
    $cache->factoryThrows = true;
    if ($calendar->getData($settings, (object) [], $calendarApp, $cache) !== ['events' => []]) {
        throw new \RuntimeException('Failed calendar cache creation broke the module.');
    }
    $cache->factoryThrows = false;
    $cache->callbackThrows = true;
    if ($calendar->getData($settings, (object) [], $calendarApp, $cache) !== ['events' => []]) {
        throw new \RuntimeException('Failed calendar cache callback broke the module.');
    }
    $cache->callbackThrows = false;
    $value = $calendar->getData($settings, (object) [], $calendarApp, $cache);
    if (count($value['events']) !== 1 || $cache->options['lifetime'] !== 60) {
        throw new \RuntimeException('Valid cached Google events were not returned.');
    }
    foreach (\Joomla\CMS\Log\Log::$entries as $record) {
        if (str_contains($record['message'], 'sensitive-token')) {
            throw new \RuntimeException('Calendar API details leaked to the Joomla log.');
        }
    }

    echo "Joomla 5/6 referees, frontend results, admin deletion and Google Calendar fallback OK\n";
}
