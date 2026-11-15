<?php
/**
 * Ensure the Joomla 5/6 frontend sample updater uses Joomla MVC model injection.
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
        /**
         * Return translation keys in this isolated security test.
         *
         * @param string $key Joomla message key.
         * @return string Original key for assertion messages.
         */
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

        /**
         * Simulate Joomla's POST CSRF validation.
         *
         * @param string $method Transport to check for the token.
         * @return bool Whether the fake token is accepted.
         */
        public static function checkToken(string $method): bool
        {
            return $method === 'post' && self::$valid;
        }
    }
}

namespace Joomla\CMS\MVC\Controller {
    class BaseController
    {
        protected object $app;
        protected object $input;
        public mixed $model = null;
        public int $modelRequests = 0;

        /**
         * Install an application and POST input for this isolated MVC test.
         *
         * @param object $app Mock Joomla frontend application.
         * @param object $input Fake request input.
         */
        public function __construct(object $app, object $input)
        {
            $this->app = $app;
            $this->input = $input;
        }

        /**
         * Record Joomla MVC model resolution rather than constructing a model.
         *
         * @param string $name Native Joomla model name.
         * @return mixed Configured fake MVC model.
         */
        protected function getModel(string $name): mixed
        {
            if ($name !== 'Updsportsmanagement') {
                throw new \RuntimeException('Unexpected updater model requested.');
            }

            ++$this->modelRequests;

            return $this->model;
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    final class UpdsportsmanagementModel
    {
        public array $received = [];

        /**
         * Capture a write request without touching an installed database.
         *
         * @param array $data Submitted Joomla form values.
         * @return bool Successful update for authorised data.
         */
        public function updItem(array $data): bool
        {
            $this->received = $data;

            return true;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Controller/UpdsportsmanagementController.php';

    $app = new class {
        public bool $allowed = true;

        /**
         * Supply a controllable Joomla user identity.
         *
         * @return object Fake identity implementing authorise().
         */
        public function getIdentity(): object
        {
            return new class($this) {
                private object $app;

                /**
                 * Read the current test permission switch.
                 *
                 * @param object $app Application containing the ACL switch.
                 */
                public function __construct(object $app)
                {
                    $this->app = $app;
                }

                /**
                 * Allow only component edits when enabled by the test.
                 *
                 * @param string $action Joomla action.
                 * @param string $asset Protected component.
                 * @return bool Whether this request may edit.
                 */
                public function authorise(string $action, string $asset): bool
                {
                    return $this->app->allowed
                        && $action === 'core.edit'
                        && $asset === 'com_sportsmanagement';
                }
            };
        }
    };

    $post = new class {
        /**
         * Provide a valid sample form submission.
         *
         * @param string $name Joomla form key.
         * @param array $default Value if no form is posted.
         * @param string $filter Input filter.
         * @return array<string,mixed> Submitted update record.
         */
        public function get(string $name, array $default, string $filter): array
        {
            return ['id' => 7, 'greeting' => 'Joomla MVC'];
        }
    };

    $controller = new \Diddipoeler\Component\SportsManagement\Site\Controller\UpdsportsmanagementController(
        $app,
        (object) ['post' => $post]
    );
    $model = new \Diddipoeler\Component\SportsManagement\Site\Model\UpdsportsmanagementModel();
    $controller->model = $model;

    ob_start();
    try {
        $result = $controller->submit();
    } finally {
        ob_end_clean();
    }

    if (!$result || $controller->modelRequests !== 1 || $model->received['id'] !== 7) {
        throw new \RuntimeException('Frontend updater bypassed Joomla MVC model injection.');
    }

    $controller->model = false;
    try {
        $controller->submit();
        throw new \LogicException('Missing updater model was accepted.');
    } catch (\RuntimeException $exception) {
        if ($exception->getCode() !== 500) {
            throw $exception;
        }
    }

    $app->allowed = false;
    $requests = $controller->modelRequests;

    try {
        $controller->submit();
        throw new \LogicException('Unauthorised updater request was accepted.');
    } catch (\RuntimeException $exception) {
        if ($exception->getCode() !== 403 || $controller->modelRequests !== $requests) {
            throw $exception;
        }
    }

    echo "Joomla 5/6 site updater MVC model guard: OK\n";
}
