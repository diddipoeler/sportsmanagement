<?php
/**
 * Joomla 5/6 regression checks for frontend editors, admin writes and birthday images.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\MVC\Model {
    class AdminModel
    {
        protected function getDatabase(): mixed
        {
            return null;
        }

        public function setError($error): void
        {
        }
    }
}

namespace Joomla\CMS\MVC\Controller {
    class AdminController
    {
        protected mixed $app = null;

        public function setTestApplication(object $app): void
        {
            $this->app = $app;
        }
    }
}

namespace Joomla\CMS\Language {
    class Text
    {
        public static function _($key): string
        {
            return (string) $key;
        }
    }
}

namespace Joomla\CMS\Session {
    class Session
    {
        public static bool $valid = true;

        public static function checkToken(string $type): bool
        {
            return $type === 'post' && self::$valid;
        }
    }
}

namespace Joomla\CMS\Uri {
    class Uri
    {
        public static function root(): string
        {
            return 'https://joomla.example/';
        }
    }
}

namespace Joomla\Registry {
    class Registry
    {
        public function __construct(private array $values = [])
        {
        }

        public function get(string $name, mixed $default = null): mixed
        {
            return $this->values[$name] ?? $default;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Table {
    abstract class FakeTable
    {
        public static array $bound = [];

        public function __construct(mixed $db)
        {
        }

        public function bind($data): bool
        {
            static::$bound = (array) $data;
            return true;
        }

        public function check(): bool
        {
            return true;
        }

        public function store(): bool
        {
            return true;
        }
    }

    class PersonTable extends FakeTable
    {
        public static array $bound = [];
    }

    class TeamTable extends FakeTable
    {
        public static array $bound = [];
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    require_once $root . '/site/src/Model/EditpersonModel.php';
    require_once $root . '/site/src/Model/EditteamModel.php';

    foreach ([
        [\Diddipoeler\Component\SportsManagement\Site\Model\EditpersonModel::class,
            \Diddipoeler\Component\SportsManagement\Administrator\Table\PersonTable::class],
        [\Diddipoeler\Component\SportsManagement\Site\Model\EditteamModel::class,
            \Diddipoeler\Component\SportsManagement\Administrator\Table\TeamTable::class],
    ] as [$modelClass, $tableClass]) {
        $model = (new \ReflectionClass($modelClass))->newInstanceWithoutConstructor();
        $saved = $model->updItem([
            'id' => 12,
            'name' => 'Test',
            'published' => 0,
            'picture' => '',
            'request' => ['disabled' => false, 'flag' => 0],
        ]);

        if ($saved !== true
            || !array_key_exists('published', $tableClass::$bound)
            || $tableClass::$bound['published'] !== 0
            || !array_key_exists('picture', $tableClass::$bound)
            || $tableClass::$bound['picture'] !== ''
            || !array_key_exists('disabled', $tableClass::$bound)
            || $tableClass::$bound['disabled'] !== false
            || array_key_exists('request', $tableClass::$bound)
        ) {
            throw new \RuntimeException($modelClass . ' did not preserve zero/cleared values.');
        }
    }

    require_once $root . '/admin/src/Controller/SportsManagementAdminController.php';

    $controller = new class extends \Diddipoeler\Component\SportsManagement\Administrator\Controller\SportsManagementAdminController {
        public function verify(string $action): void
        {
            $this->assertWritePermission($action);
        }
    };

    $identity = new class {
        public bool $granted = true;

        public function authorise(string $action, string $component): bool
        {
            return $component === 'com_sportsmanagement' && $action === 'core.edit' && $this->granted;
        }
    };

    $controller->setTestApplication(new class($identity) {
        public function __construct(private object $identity)
        {
        }

        public function getIdentity(): object
        {
            return $this->identity;
        }
    });

    $assertDenied = static function () use ($controller): void {
        try {
            $controller->verify('core.edit');
        } catch (\RuntimeException $error) {
            if ($error->getCode() === 403) {
                return;
            }
        }

        throw new \RuntimeException('Unauthorized administrator write was accepted.');
    };

    \Joomla\CMS\Session\Session::$valid = false;
    $assertDenied();
    \Joomla\CMS\Session\Session::$valid = true;
    $identity->granted = false;
    $assertDenied();
    $identity->granted = true;
    $controller->verify('core.edit');

    // A missing Joomla identity must fail closed, not trigger a PHP error.
    $controller->setTestApplication(new class {
        public function getIdentity(): ?object
        {
            return null;
        }
    });
    $assertDenied();

    try {
        $controller->saveorder();
        throw new \RuntimeException('Missing identity was allowed to reorder records.');
    } catch (\RuntimeException $error) {
        if ($error->getCode() !== 403) {
            throw $error;
        }
    }

    foreach ([
        'ClubsController.php' => ['saveshort' => ''],
        'TeamsController.php' => ['saveshort' => '', 'copysave' => "'core.create'"],
        'LeaguesController.php' => ['saveshort' => ''],
        'AgegroupsController.php' => ['saveshort' => '', 'import' => "'core.create'"],
        'SeasonsController.php' => ['applypersons' => '', 'applyteams' => ''],
    ] as $file => $tasks) {
        $source = file_get_contents($root . '/admin/src/Controller/' . $file);

        foreach ($tasks as $task => $argument) {
            $pattern = '~function\s+' . preg_quote($task, '~')
                . '\(\):\s*void\s*\{\s*\$this->assertWritePermission\('
                . preg_quote($argument, '~') . '\);~';

            if (!is_string($source) || preg_match($pattern, $source) !== 1) {
                throw new \RuntimeException($file . ' missing write permission for ' . $task);
            }
        }
    }

    $imageRoot = sys_get_temp_dir() . '/jsm-birthday-' . bin2hex(random_bytes(6));
    mkdir($imageRoot . '/images/people', 0700, true);
    file_put_contents($imageRoot . '/images/people/person.png', 'image fixture');
    define('JPATH_ROOT', $imageRoot);

    try {
        require_once $root . '/modules/mod_sportsmanagement_birthday/src/Helper/BirthdayHelper.php';
        $helper = new \Diddipoeler\Module\SportsManagementBirthday\Site\Helper\BirthdayHelper();
        $picture = new \ReflectionMethod($helper, 'pictureUrl');
        $flag = new \ReflectionMethod($helper, 'flagHtml');

        if ($picture->invoke($helper, (object) ['picture' => 'images/people/person.png'], true)
            !== 'https://joomla.example/images/people/person.png') {
            throw new \RuntimeException('Valid birthday images must remain visible.');
        }

        foreach (['../secrets.txt', 'images/../people/person.png', 'javascript:bad', '//other.site/logo.png'] as $unsafe) {
            $url = $picture->invoke($helper, (object) ['picture' => $unsafe], true);

            if ($url !== '') {
                throw new \RuntimeException('Unsafe birthday image path was accepted: ' . $unsafe);
            }
        }

        $params = new \Joomla\Registry\Registry();
        if ($flag->invoke($helper, (object) ['country' => 'DEU', 'country_alpha2' => 'de'], $params)
            !== '<img src="https://joomla.example/images/com_sportsmanagement/database/flags/de.png" alt="DEU" />') {
            throw new \RuntimeException('Valid flag URLs must remain visible.');
        }

        if ($flag->invoke($helper, (object) [
            'country' => 'DEU', 'country_alpha2' => '../bad', 'country_picture' => 'javascript:bad',
        ], $params) !== '') {
            throw new \RuntimeException('Unsafe birthday flag path was accepted.');
        }
        $birthdayText = new \ReflectionMethod($helper, 'birthdayText');
        $today = new \DateTimeImmutable('2026-10-11');
        $birth = new \DateTimeImmutable('1999-10-11');
        $maliciousFormats = new \Joomla\Registry\Registry([
            'birthdaytext' => '%DATE%|%DATE_OF_BIRTH%',
            'dayformat' => '<img src=x onerror=alert(1)>d.m.Y',
            'birthdayformat' => '<script>alert(1)</script>d.m.Y',
        ]);
        $rendered = $birthdayText->invoke(
            $helper,
            (object) ['days_to_birthday' => 0, 'age' => 27],
            $today,
            $birth,
            $maliciousFormats
        );

        if (str_contains($rendered, '<img')
            || str_contains($rendered, '<script')
            || !str_contains($rendered, '&lt;')
        ) {
            throw new \RuntimeException('Birthday date placeholders must escape HTML from custom formats.');
        }

    } finally {
        unlink($imageRoot . '/images/people/person.png');
        rmdir($imageRoot . '/images/people');
        rmdir($imageRoot . '/images');
        rmdir($imageRoot);
    }

    echo "Joomla 5/6 site editor, admin write and birthday module contracts: OK\n";
}
