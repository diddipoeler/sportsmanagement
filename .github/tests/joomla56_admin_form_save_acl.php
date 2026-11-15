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
        public static int $modelLoads = 0;
        public static bool $allowWrites = false;
        public static bool $validToken = true;
        public static bool $missingModel = false;

        /**
         * Reject invalid CSRF tokens in the standalone Joomla controller.
         *
         * @return void
         * @throws \RuntimeException When a forged token is simulated.
         */
        public function checkToken(): void
        {
            if (!self::$validToken) {
                throw new \RuntimeException('JINVALID_TOKEN', 403);
            }
        }

        protected function allowSave($data, $key = 'id'): bool
        {
            self::$checkedData = $data;
            self::$checkedKey = $key;

            return self::$allowWrites;
        }

        /**
         * Record model resolution and simulate a missing Joomla model.
         *
         * @param string $name Form model requested by the controller.
         * @param string $prefix MVC namespace prefix.
         * @param array $config Joomla model configuration.
         * @return object|false Model stub or an unavailable-model result.
         */
        public function getModel($name = '', $prefix = '', $config = [])
        {
            ++self::$modelLoads;

            if (self::$missingModel) {
                return false;
            }

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
        \Joomla\CMS\MVC\Controller\FormController::$modelLoads = 0;

        try {
            $controller->save($key);
        } catch (\RuntimeException $exception) {
            if ($exception->getCode() === 403
                && \Joomla\CMS\MVC\Controller\FormController::$checkedKey === $expectedKey
                && \Joomla\CMS\MVC\Controller\FormController::$checkedData === $jform
                && !\Joomla\CMS\MVC\Controller\FormController::$modelSaved
                && \Joomla\CMS\MVC\Controller\FormController::$modelLoads === 0) {
                continue;
            }

            throw $exception;
        }

        throw new \RuntimeException('Administrator form save bypassed the Joomla ACL check.');
    }

    // Empty round submissions must not generate legacy SQL zero dates.
    // They remain subject to the same permission gate as other forms.
    (new \ReflectionProperty($controller, 'view_item'))->setValue($controller, 'round');
    $post->data = ['jform' => []];
    \Joomla\CMS\MVC\Controller\FormController::$checkedData = [];
    \Joomla\CMS\MVC\Controller\FormController::$modelSaved = false;

    try {
        $controller->save();
    } catch (\RuntimeException $exception) {
        if ($exception->getCode() !== 403
            || \Joomla\CMS\MVC\Controller\FormController::$checkedData !== []
            || \Joomla\CMS\MVC\Controller\FormController::$modelSaved) {
            throw new \RuntimeException('Empty round form bypassed ACL or synthesized zero dates.', 0, $exception);
        }
    }

    // A missing Joomla form model must fail explicitly without calling
    // save() on a non-object, which would produce an opaque PHP fatal error.
    \Joomla\CMS\MVC\Controller\FormController::$allowWrites = true;
    \Joomla\CMS\MVC\Controller\FormController::$missingModel = true;
    \Joomla\CMS\MVC\Controller\FormController::$modelLoads = 0;
    $post->data = ['jform' => ['id' => 5]];

    try {
        $controller->save();
        throw new \RuntimeException('Missing administrator model was accepted.');
    } catch (\RuntimeException $exception) {
        if ($exception->getCode() !== 500
            || \Joomla\CMS\MVC\Controller\FormController::$modelLoads !== 1) {
            throw new \RuntimeException('Missing model did not fail predictably.', 0, $exception);
        }
    }

    // Invalid CSRF tokens must not even attempt to resolve the form model.
    \Joomla\CMS\MVC\Controller\FormController::$validToken = false;
    \Joomla\CMS\MVC\Controller\FormController::$modelLoads = 0;

    try {
        $controller->save();
        throw new \RuntimeException('Invalid token was allowed to load a model.');
    } catch (\RuntimeException $exception) {
        if ($exception->getCode() !== 403
            || \Joomla\CMS\MVC\Controller\FormController::$modelLoads !== 0) {
            throw new \RuntimeException('Invalid token caused a model lookup.', 0, $exception);
        }
    }

    echo "Administrator form save ACL regression passed.\n";
}
