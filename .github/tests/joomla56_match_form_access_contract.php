<?php
/**
 * Joomla 5/6 regression checks for custom match form actions and permissions.
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
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Session {
    class Session
    {
        public static bool $valid = true;

        public static function checkToken(string $method): bool
        {
            return $method === 'post' && self::$valid;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementFormController
    {
        protected mixed $app = null;

        public function setTestApp(object $app): void
        {
            $this->app = $app;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);

    require_once $root . '/admin/src/Controller/MatchController.php';

    $identity = new class {
        public bool $allowed = true;
        public string $expected = '';

        public function authorise(string $permission, string $asset): bool
        {
            return $this->allowed
                && $asset === 'com_sportsmanagement'
                && $permission === $this->expected;
        }
    };

    $app = new class($identity) {
        public function __construct(private object $identity)
        {
        }

        public function getIdentity(): object
        {
            return $this->identity;
        }

        public function getInput(): object
        {
            return new class {
                public function getInt(string $key, int $default = 0): int
                {
                    return $default;
                }
            };
        }
    };

    $controller = (new \ReflectionClass(
        \Diddipoeler\Component\SportsManagement\Administrator\Controller\MatchController::class
    ))->newInstanceWithoutConstructor();
    $controller->setTestApp($app);

    $assertDenied = static function (string $method) use ($controller): void {
        try {
            $controller->{$method}();
        } catch (\RuntimeException $error) {
            if ($error->getCode() === 403) {
                return;
            }
        }

        throw new \RuntimeException('MatchController::' . $method . ' allowed unauthorized mutation.');
    };

    foreach ([
        'remove' => 'core.delete',
        'copyfrom' => 'core.create',
        'addmatch' => 'core.create',
        'insertgooglecalendar' => 'core.edit',
        'savepressebericht' => 'core.edit',
    ] as $method => $permission) {
        $identity->expected = $permission;
        \Joomla\CMS\Session\Session::$valid = false;
        $assertDenied($method);

        \Joomla\CMS\Session\Session::$valid = true;
        $identity->allowed = false;
        $assertDenied($method);
        $identity->allowed = true;
    }

    foreach (['picture', 'pressebericht'] as $method) {
        $identity->expected = 'core.edit';
        $identity->allowed = false;
        $assertDenied($method);
        $identity->allowed = true;
    }

    $identity->expected = 'core.edit';

    try {
        $controller->picture();
        throw new \RuntimeException('Picture action accepted a missing match ID.');
    } catch (\RuntimeException $error) {
        if ($error->getCode() !== 400) {
            throw $error;
        }
    }

    $source = file_get_contents($root . '/admin/src/Controller/MatchController.php');

    if (!is_string($source)
        || str_contains($source, '$this->setRedirect($redirect, $exception->getMessage()')
        || str_contains($source, '$this->setRedirect($failureRedirect, $exception->getMessage()')
        || str_contains($source, '$message = $exception->getMessage();')
    ) {
        throw new \RuntimeException('Internal exception messages must not be sent to match editors.');
    }

    echo "Joomla 5/6 admin match custom form permissions: OK\n";
}
