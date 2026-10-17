<?php
/**
 * Regression test for Joomla 5/6 administrator form save permission checks.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Language {
    class Text
    {
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\MVC\Controller {
    class FormController
    {
        protected string $view_item = 'club';
        public static ?string $checkedKey = null;
        public static array $checkedData = [];
        public static bool $modelSaved = false;

        public function checkToken(): void
        {
        }

        protected function allowSave($data, $key = 'id'): bool
        {
            self::$checkedData = $data;
            self::$checkedKey = $key;

            return false;
        }

        public function getModel($name = '', $prefix = '', $config = [])
        {
            return new class {
                public function save(array $data): bool
                {
                    FormController::$modelSaved = true;

                    return false;
                }
            };
        }
    }
}

namespace {
    define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/admin/src/Controller/SportsManagementFormController.php';

    $post = new class {
        public array $data = [];

        public function getArray(): array
        {
            return $this->data;
        }

        public function get(string $name, $default = null, string $filter = 'raw')
        {
            return $this->data[$name] ?? $default;
        }
    };

    $input = new class($post) {
        public function __construct(public object $post)
        {
        }

        public function getCmd(string $name): string
        {
            return '';
        }

        public function getBool(string $name): bool
        {
            return false;
        }
    };

    $controller = (new \ReflectionClass(
        \Diddipoeler\Component\SportsManagement\Administrator\Controller\SportsManagementFormController::class
    ))->newInstanceWithoutConstructor();

    (new \ReflectionProperty($controller, 'jsmjinput'))->setValue($controller, $input);

    foreach ([
        [['id' => 42], null, 'id'],
        [['entry_id' => 42], 'entry_id', 'entry_id'],
        [['id' => 0], null, 'id'],
    ] as [$jform, $key, $expectedKey]) {
        $post->data = ['jform' => $jform];
        \Joomla\CMS\MVC\Controller\FormController::$checkedKey = null;
        \Joomla\CMS\MVC\Controller\FormController::$checkedData = [];
        \Joomla\CMS\MVC\Controller\FormController::$modelSaved = false;

        try {
            $controller->save($key);
        } catch (\RuntimeException $exception) {
            if ($exception->getCode() === 403
                && \Joomla\CMS\MVC\Controller\FormController::$checkedKey === $expectedKey
                && \Joomla\CMS\MVC\Controller\FormController::$checkedData === $jform
                && !\Joomla\CMS\MVC\Controller\FormController::$modelSaved) {
                continue;
            }

            throw $exception;
        }

        throw new \RuntimeException('Administrator form save bypassed the Joomla ACL check.');
    }

    echo "Administrator form save ACL regression passed.\n";
}
