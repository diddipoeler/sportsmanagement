<?php
/**
 * Runtime regressions for Joomla 5/6 native MVC and module data handling.
 *
 * Uses minimal framework stubs to exercise controller access checks,
 * strict model inputs, administrator model routing and ranking IDs.
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

        public function getModel($name = '', $prefix = '', $config = []): array
        {
            return [$name, $prefix, $config];
        }
    }
}

namespace Joomla\CMS\MVC\Model {
    class FormModel
    {
        public static $database;

        public function getDatabase()
        {
            return self::$database;
        }
    }
}

namespace Joomla\CMS\Session {
    final class Session
    {
        public static bool $tokenIsValid = true;

        public static function checkToken(string $method = 'post'): bool
        {
            return $method === 'post' && self::$tokenIsValid;
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace {
    use Diddipoeler\Component\SportsManagement\Administrator\Controller\DatabasetoolsController;
    use Diddipoeler\Component\SportsManagement\Site\Controller\UpdsportsmanagementController;
    use Diddipoeler\Component\SportsManagement\Site\Model\UpdsportsmanagementModel;
    use Diddipoeler\Module\SportsManagementEventsRanking\Site\Helper\EventsRankingHelper;
    use Joomla\CMS\MVC\Model\FormModel;
    use Joomla\CMS\Session\Session;

    \define('_JEXEC', 1);

    require_once __DIR__ . '/../../admin/src/Controller/DatabasetoolsController.php';
    require_once __DIR__ . '/../../site/src/Controller/UpdsportsmanagementController.php';
    require_once __DIR__ . '/../../site/src/Model/UpdsportsmanagementModel.php';
    require_once __DIR__ . '/../../modules/mod_sportsmanagement_eventsranking/src/Helper/EventsRankingHelper.php';

    function assertSameValue(mixed $expected, mixed $actual, string $message): void
    {
        if ($expected !== $actual) {
            throw new \RuntimeException(
                $message . ' Expected ' . var_export($expected, true)
                . ', got ' . var_export($actual, true)
            );
        }
    }

    final class FakeDatabase
    {
        public array $writes = [];

        public function updateObject(string $table, object $record, string $key): bool
        {
            $this->writes[] = [$table, $record, $key];
            return true;
        }
    }

    final class FakeApplication
    {
        public bool $canEdit = false;

        public function getIdentity(): object
        {
            return new class($this) {
                public function __construct(private FakeApplication $app)
                {
                }

                public function authorise(string $action, string $asset): bool
                {
                    return $this->app->canEdit
                        && $action === 'core.edit'
                        && $asset === 'com_sportsmanagement';
                }
            };
        }
    }

    final class FakePost
    {
        public function __construct(public array $data)
        {
        }

        public function get(string $name, mixed $default = null, string $filter = ''): mixed
        {
            return $this->data[$name] ?? $default;
        }
    }

    $admin = new DatabasetoolsController();
    assertSameValue(
        ['Databasetool', 'Administrator', ['ignore_request' => true]],
        $admin->getModel(),
        'Database tools must resolve the native Administrator model.'
    );
    assertSameValue(
        ['Databasetool', 'Administrator', ['ignore_request' => true, 'test' => 1]],
        $admin->getModel('Databasetool', 'Administrator', ['test' => 1]),
        'Database tools must preserve custom MVC model config.'
    );

    $database = new FakeDatabase();
    FormModel::$database = $database;
    $model = new UpdsportsmanagementModel();

    foreach ([
        ['id' => '12garbage', 'greeting' => 'Test'],
        ['id' => ['12'], 'greeting' => 'Test'],
        ['id' => '0', 'greeting' => 'Test'],
        ['id' => '12', 'greeting' => ['array']],
        ['id' => '12', 'greeting' => '   '],
    ] as $invalid) {
        assertSameValue(false, $model->updItem($invalid), 'Invalid updater input must be rejected.');
    }
    assertSameValue([], $database->writes, 'Invalid updater values must not write to the database.');

    assertSameValue(true, $model->updItem(['id' => '12', 'greeting' => '  Good  ']), 'Valid updater input should save.');
    assertSameValue(12, $database->writes[0][1]->id ?? null, 'Saved record ID must be an integer.');
    assertSameValue('Good', $database->writes[0][1]->greeting ?? null, 'Greeting should be trimmed.');
    $database->writes = [];

    $app = new FakeApplication();
    $post = new FakePost(['jform' => ['id' => '12', 'greeting' => 'Hello']]);
    $controller = new UpdsportsmanagementController([], null, $app, (object) ['post' => $post]);

    Session::$tokenIsValid = false;
    try {
        $controller->submit();
        throw new \RuntimeException('Updater must reject missing POST CSRF token.');
    } catch (\RuntimeException $e) {
        assertSameValue(403, $e->getCode(), 'Invalid token must return 403.');
    }

    Session::$tokenIsValid = true;
    try {
        $controller->submit();
        throw new \RuntimeException('Updater must reject unauthorized frontend writes.');
    } catch (\RuntimeException $e) {
        assertSameValue(403, $e->getCode(), 'Unauthorized writes must return 403.');
    }
    assertSameValue([], $database->writes, 'Denied frontend writes must not touch the database.');

    $app->canEdit = true;
    ob_start();
    try {
        assertSameValue(true, $controller->submit(), 'Authorized POST should save.');
    } finally {
        ob_end_clean();
    }
    assertSameValue(1, count($database->writes), 'Authorized submit must save one record.');

    $ids = new \ReflectionMethod(EventsRankingHelper::class, 'normaliseIds');
    $helper = new EventsRankingHelper();

    assertSameValue(
        [1, 3, 4, 6],
        $ids->invoke($helper, [
            1, '03:alias', '4:other-team', '06',
            '2garbage', '7.1', '0', '-5', '10:bad:slug',
            '999999999999999999999999999999', ['9'], true, 2.5,
        ]),
        'EventRanking IDs must accept IDs and Joomla slugs but reject malformed or overflowing values.'
    );
    assertSameValue([8, 11], $ids->invoke($helper, '8:team,11,invalid'),
        'Comma-separated legacy selector IDs must remain supported.');

    $source = file_get_contents(
        __DIR__ . '/../../modules/mod_sportsmanagement_eventsranking/src/Helper/EventsRankingHelper.php'
    );
    if ($source === false
        || str_contains($source, "'CONCAT_WS(")
        || !str_contains($source, '$row->person_slug =')
        || !str_contains($source, '$row->team_slug =')
        || !str_contains($source, '$row->club_slug =')) {
        throw new \RuntimeException('EventsRanking must construct Joomla routing slugs without MySQL-only SQL.');
    }

    echo "Native Joomla 5/6 MVC security and EventsRanking regressions OK.\n";
}
