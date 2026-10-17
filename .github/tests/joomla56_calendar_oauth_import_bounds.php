<?php
/**
 * Joomla 5/6 regression tests for Google OAuth imports and public calendar limits.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application { interface CMSApplicationInterface {} }

namespace Joomla\CMS\Component {
    class ComponentHelper {
        public static function getParams(string $option): \Joomla\Registry\Registry {
            return new \Joomla\Registry\Registry();
        }
    }
}
namespace Joomla\CMS {
    class Factory {
        public static function getDate(): object {
            return new class { public function toSql(): string { return '2026-10-16 09:00:00'; } };
        }
    }
}
namespace Joomla\CMS\Filter {
    class OutputFilter {
        public static function stringURLSafe(string $value): string { return strtolower(str_replace(' ', '-', $value)); }
    }
}
namespace Joomla\CMS\Log {
    class Log {
        public const WARNING = 300;
        public static array $messages = [];
        public static function add(string $message, int $level, string $category): void { self::$messages[] = $message; }
    }
}
namespace Joomla\CMS\Uri {
    class Uri {
        public static function getInstance(): self { return new self(); }
        public function getHost(): string { return 'example.org'; }
        public function setHost(string $host): void {}
        public function toString(array $parts): string { return 'https://example.org/administrator/index.php'; }
    }
}
namespace Joomla\Database {
    interface DatabaseInterface {}
    class ParameterType {
        public const STRING = 'string';
        public const INTEGER = 'integer';
    }
}
namespace Joomla\Registry {
    class Registry {
        public function __construct(private array $values = []) {}
        public function get(string $key, mixed $default = null): mixed { return $this->values[$key] ?? $default; }
        public function set(string $key, mixed $value): void { $this->values[$key] = $value; }
        public function loadString(string $json): void {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($data)) { throw new \InvalidArgumentException('Expected configuration array.'); }
            $this->values = $data;
        }
        public function toString(): string { return json_encode($this->values, JSON_THROW_ON_ERROR); }
    }
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Model {
    class SportsManagementListModel {
        public string $error = '';
        protected function administratorApplication(): \TestOAuthApp { return $GLOBALS['testApp']; }
        protected function getDatabase(): \TestCalendarDb { return $GLOBALS['testDb']; }
        public function setError(string $error): void { $this->error = $error; }
    }
}
namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class SportsManagementModel {}
}
namespace {
    define('_JEXEC', 1);
    define('JPATH_ADMINISTRATOR', '/not-installed');
    require_once dirname(__DIR__, 2) . '/admin/src/Model/JsmgcalendarimportModel.php';
    require_once dirname(__DIR__, 2) . '/site/src/Model/JsonfeedModel.php';
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_gcalendar/src/Helper/GcalendarHelper.php';

    class TestSession {
        private array $items = [];
        public function set(string $key, mixed $value): void { $this->items[$key] = $value; }
        public function get(string $key, mixed $default = null): mixed { return $this->items[$key] ?? $default; }
    }
    class TestInput {
        public object $post;
        public function __construct(private array $values) {
            $this->post = new class {
                public function getString(string $name, string $default = ''): string { return $default; }
            };
        }
        public function get(string $key, mixed $default = null, string $filter = 'raw'): mixed {
            return $this->values[$key] ?? $default;
        }
        public function getString(string $key, string $default = ''): string {
            return is_string($this->values[$key] ?? null) ? $this->values[$key] : $default;
        }
    }
    class TestOAuthApp implements \Joomla\CMS\Application\CMSApplicationInterface {
        public TestSession $session;
        public array $messages = [];
        public function __construct(public TestInput $input) { $this->session = new TestSession(); }
        public function getInput(): TestInput { return $this->input; }
        public function getSession(): TestSession { return $this->session; }
        public function get(string $name, mixed $default = null): mixed { return '/tmp'; }
        public function getIdentity(): object { return new class { public int $id = 23; public function authorise(string $action, string $asset): bool { return true; } }; }
        public function enqueueMessage(string $message, string $type = 'message'): void {
            $this->messages[] = $message;
        }
    }

    class TestQuery {
        public array $ids = [];
        public string $calendar = '';
        public function select(mixed $value): self { return $this; }
        public function from(string $table): self { return $this; }
        public function where(string $where): self { return $this; }
        public function order(string $order): self { return $this; }
        public function bind(string $key, mixed $value, string $type): self {
            if ($key === ':calendarId') { $this->calendar = (string) $value; }
            return $this;
        }
        public function whereIn(string $field, array $ids, string $type): self {
            if ($field === 'id') { $this->ids = $ids; }
            return $this;
        }
    }
    class TestCalendarDb implements \Joomla\Database\DatabaseInterface {
        public ?TestQuery $lastQuery = null;
        public int $lastLimit = 0;
        public array $existing = [];
        public array $inserted = [];
        public array $updated = [];
        public int $commits = 0;
        public int $rollbacks = 0;
        public function createQuery(): TestQuery { return new TestQuery(); }
        public function quoteName(string $name): string { return $name; }
        public function setQuery(TestQuery $query, int $offset = 0, int $limit = 0): void {
            $this->lastQuery = $query; $this->lastLimit = $limit;
        }
        public function loadObject(): ?object { return $this->existing[$this->lastQuery?->calendar ?? ''] ?? null; }
        public function loadObjectList(): array { return []; }
        public function insertObject(string $table, object $record): void { $this->inserted[] = $record; }
        public function updateObject(string $table, object $record, string $key): void { $this->updated[] = $record; }
        public function transactionStart(): void {}
        public function transactionCommit(): void { ++$this->commits; }
        public function transactionRollback(): void { ++$this->rollbacks; }
    }
    class TestCalendarEntry {
        public function __construct(private string $id) {}
        public function getID(): string { return $this->id; }
        public function getSummary(): string { return 'Fixture'; }
        public function getBackgroundColor(): string { return '#334455'; }
    }
    class TestCalendarPage {
        public function __construct(private array $items, private ?string $nextToken) {}
        public function getItems(): array { return $this->items; }
        public function getNextPageToken(): ?string { return $this->nextToken; }
    }
    class TestCalendarList {
        public int $calls = 0;
        public string $mode = 'repeat';
        public function listCalendarList(array $options): TestCalendarPage {
            ++$this->calls;
            if ($this->mode === 'repeat') {
                return new TestCalendarPage([new TestCalendarEntry('existing'), new TestCalendarEntry('new')], 'repeat-token');
            }
            if ($this->mode === 'bulk') {
                $items = [];

                for ($i = 1; $i <= 501; ++$i) {
                    $items[] = new TestCalendarEntry('calendar-' . $i);
                }

                return new TestCalendarPage($items, null);
            }
            return new TestCalendarPage([], 'unique-' . $this->calls);
        }
    }
    class Google_Client {
        public static bool $throwOnAuth = false;
        public static array $token = ['access_token' => 'usable-access'];
        public function __construct(array $config = []) {}
        public function setApplicationName(string $value): void {}
        public function setClientId(string $value): void {}
        public function setClientSecret(string $value): void {}
        public function setScopes(array $value): void {}
        public function setAccessType(string $value): void {}
        public function setPrompt(string $value): void {}
        public function setRedirectUri(string $value): void {}
        public function authenticate(string $code): array {
            if (self::$throwOnAuth) {
                throw new \RuntimeException('secret-client-password-and-token');
            }
            return self::$token;
        }
        public function setAccessToken(array $token): void {}
    }
    class Google_Service_Calendar {
        public TestCalendarList $calendarList;
        public function __construct(Google_Client $client) { $this->calendarList = $GLOBALS['testCalendarList']; }
    }

    $setup = static function (mixed $code = 'valid-code'): void {
        $GLOBALS['testApp'] = new TestOAuthApp(new TestInput(['code' => $code, 'state' => 'valid-state']));
        $GLOBALS['testApp']->session->set('com_sportsmanagement.jsmgcalendar.oauth_state', 'valid-state');
        $GLOBALS['testApp']->session->set('com_sportsmanagement.jsmgcalendar.oauth_context', [
            'client_id' => 'client', 'client_secret' => 'secret', 'mail_account' => 'user@example.org',
        ]);
        $GLOBALS['testDb'] = new TestCalendarDb();
        $GLOBALS['testCalendarList'] = new TestCalendarList();
        Google_Client::$throwOnAuth = false;
        Google_Client::$token = ['access_token' => 'usable-access'];
        \Joomla\CMS\Log\Log::$messages = [];
    };
    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\JsmgcalendarimportModel();

    $setup(['an-array']);
    if ($model->import() !== false || $GLOBALS['testCalendarList']->calls !== 0) {
        throw new \RuntimeException('Array-valued OAuth code was not rejected.');
    }

    $setup('valid-code');
    Google_Client::$throwOnAuth = true;
    if ($model->import() !== false
        || str_contains(implode(' ', $GLOBALS['testApp']->messages), 'secret-client-password')
        || str_contains(implode(' ', \Joomla\CMS\Log\Log::$messages), 'secret-client-password')) {
        throw new \RuntimeException('OAuth exception exposed sensitive data.');
    }

    $setup('valid-code');
    $GLOBALS['testDb']->existing['existing'] = (object) [
        'id' => 17,
        'params' => json_encode(['refreshToken' => 'saved-refresh-token'], JSON_THROW_ON_ERROR),
    ];
    if ($model->import() !== true || count($GLOBALS['testDb']->updated) !== 1
        || count($GLOBALS['testDb']->inserted) !== 0
        || $GLOBALS['testCalendarList']->calls !== 2
        || $GLOBALS['testDb']->commits !== 1) {
        throw new \RuntimeException(sprintf(
            'OAuth import mismatch: updated=%d inserted=%d pages=%d commits=%d; messages=%s',
            count($GLOBALS['testDb']->updated),
            count($GLOBALS['testDb']->inserted),
            $GLOBALS['testCalendarList']->calls,
            $GLOBALS['testDb']->commits,
            implode(' / ', $GLOBALS['testApp']->messages)
        ));
    }
    $stored = json_decode($GLOBALS['testDb']->updated[0]->params, true, 512, JSON_THROW_ON_ERROR);
    if (($stored['refreshToken'] ?? '') !== 'saved-refresh-token') {
        throw new \RuntimeException('Google refresh token was lost during reauthorization.');
    }

    $setup('valid-code');
    $GLOBALS['testCalendarList']->mode = 'unique';
    Google_Client::$token = ['access_token' => 'usable-access', 'refresh_token' => 'fresh-token'];
    $model->import();
    if ($GLOBALS['testCalendarList']->calls !== 10) {
        throw new \RuntimeException('Google Calendar list pagination exceeded the 10-page limit.');
    }

    $setup('valid-code');
    $GLOBALS['testCalendarList']->mode = 'bulk';
    Google_Client::$token = ['access_token' => 'usable-access', 'refresh_token' => 'fresh-token'];

    if ($model->import() !== false
        || $GLOBALS['testCalendarList']->calls !== 1
        || $GLOBALS['testDb']->commits !== 0
        || $GLOBALS['testDb']->rollbacks !== 1) {
        throw new \RuntimeException('More than 500 Google calendars was not rolled back.');
    }

    $feed = new \Diddipoeler\Component\SportsManagement\Site\Model\JsonfeedModel();
    $normaliseFeed = new \ReflectionMethod($feed, 'normalizeCalendarIds');
    if ($normaliseFeed->invoke($feed, implode(',', range(1, 75))) !== range(1, 50)) {
        throw new \RuntimeException('Site JSON feed exceeded its 50-calendar limit.');
    }

    $module = new \Diddipoeler\Module\SportsManagementGcalendar\Site\Helper\GcalendarHelper();
    $normaliseModule = new \ReflectionMethod($module, 'normaliseCalendarIds');
    if ($normaliseModule->invoke($module, implode(',', range(1, 75))) !== range(1, 50)) {
        throw new \RuntimeException('GCalendar module exceeded its 50-calendar limit.');
    }

    $setup('valid-code');
    $module->getCalendars(new \Joomla\Registry\Registry(), $GLOBALS['testApp'], $GLOBALS['testDb']);
    if ($GLOBALS['testDb']->lastLimit !== 50) {
        throw new \RuntimeException('Unrestricted GCalendar module query is missing its SQL limit.');
    }

    echo "Joomla 5/6 site/admin/module OAuth and calendar bounds OK\n";
}
