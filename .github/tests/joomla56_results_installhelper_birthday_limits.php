<?php
/**
 * Joomla 5/6 regression checks for results limits, installation ACL and birthday filters.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Language {
    class Text {
        public static function _(string $key): string { return $key; }
    }
}
namespace Joomla\CMS\MVC\Controller {
    class BaseController {}
}
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller {
    class SportsManagementFormController {
        protected mixed $app = null;
        public mixed $model = null;
        public ?array $redirect = null;
        public bool $tokenValid = true;
        public int $modelCalls = 0;
        public function setTestApp(object $app): void { $this->app = $app; }
        protected function checkToken(): void {
            if (!$this->tokenValid) { throw new \RuntimeException('Invalid token', 403); }
        }
        protected function getModel(string $name, string $prefix, array $options): mixed {
            ++$this->modelCalls;
            return $this->model;
        }
        protected function setRedirect(string $url, string $message = '', string $type = 'message'): void {
            $this->redirect = [$url, $message, $type];
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    require_once $root . '/site/src/Controller/ResultsController.php';
    require_once $root . '/admin/src/Controller/InstallhelperController.php';
    require_once $root . '/modules/mod_sportsmanagement_birthday/src/Helper/BirthdayHelper.php';

    $results = new \ReflectionMethod(
        \Diddipoeler\Component\SportsManagement\Site\Controller\ResultsController::class,
        'validMatchIds'
    );
    foreach ([
        [[1, '2', 2], [1, 2]],
        [[1, '2junk'], []],
        [[0], []],
        [[-1], []],
        [[[]], []],
        [[1, '99999999999999999999999'], []],
        [range(1, 500), range(1, 500)],
        [range(1, 501), []],
    ] as [$input, $expected]) {
        if ($results->invoke(null, $input) !== $expected) {
            throw new \RuntimeException('Results selection was malformed or exceeded its bound.');
        }
    }

    $birthdays = new \Diddipoeler\Module\SportsManagementBirthday\Site\Helper\BirthdayHelper();
    $normalise = new \ReflectionMethod($birthdays, 'normaliseIds');
    foreach ([
        [['2, 3;2', '4'], [2, 3, 4]],
        [[['1'], null, true, false, '17junk', '+9', '-1', '0', '0005', '7'], [7]],
        [[str_repeat('3', 4200), '8'], [8]],
        [[str_repeat('9', 30), 19], [19]],
        [[implode(',', range(1, 150))], range(1, 100)],
    ] as [$input, $expected]) {
        if ($normalise->invoke($birthdays, $input) !== $expected) {
            throw new \RuntimeException('Birthday project/team filter did not validate or bound database IDs.');
        }
    }

    $model = new class {
        public array $writes = [];
        public function saveSportstype(array $data): array { $this->writes[] = $data; return []; }
    };
    $input = new class {
        public object $post;
        public function __construct() {
            $this->post = new class {
                public function getArray(): array { return ['filter_sports_type' => 'soccer']; }
            };
        }
    };
    $app = new class($input) {
        public array $messages = [];
        public bool $allowed = false;
        public function __construct(private object $input) {}
        public function getInput(): object { return $this->input; }
        public function getIdentity(): object {
            return new class($this) {
                public function __construct(private object $app) {}
                public function authorise(string $action, string $asset): bool {
                    return $this->app->allowed
                        && $action === 'core.admin'
                        && $asset === 'com_sportsmanagement';
                }
            };
        }
        public function enqueueMessage(string $value, string $type): void {
            $this->messages[] = [$value, $type];
        }
    };

    $controller = new \Diddipoeler\Component\SportsManagement\Administrator\Controller\InstallhelperController();
    $controller->setTestApp($app);
    $controller->model = $model;
    try {
        $controller->savesportstype();
        throw new \RuntimeException('Non-admin sports type creation was allowed.');
    } catch (\RuntimeException $exception) {
        if ($exception->getCode() !== 403) { throw $exception; }
    }
    if ($controller->modelCalls !== 0 || $model->writes !== []) {
        throw new \RuntimeException('Unauthorized user reached the sports type model.');
    }

    $app->allowed = true;
    $controller->tokenValid = false;
    try {
        $controller->savesportstype();
        throw new \RuntimeException('Invalid token was accepted.');
    } catch (\RuntimeException $exception) {
        if ($exception->getCode() !== 403) { throw $exception; }
    }
    if ($model->writes !== []) {
        throw new \RuntimeException('Missing CSRF token reached a sports type write.');
    }

    $controller->tokenValid = true;
    $controller->model = false;
    $controller->savesportstype();
    if ($controller->redirect[2] !== 'error') {
        throw new \RuntimeException('Missing Joomla installation model caused no error redirect.');
    }

    $controller->model = $model;
    $controller->savesportstype();
    if (count($model->writes) !== 1
        || $controller->redirect[0] !== 'index.php?option=com_sportsmanagement&view=installhelper&step=2') {
        throw new \RuntimeException('Authorized Joomla installation action no longer writes.');
    }

    echo "Joomla 5/6 results/installhelper/birthday guards OK\n";
}
