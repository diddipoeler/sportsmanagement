<?php
/**
 * Test Joomla 5/6 results controllers resolve injected access/write models.
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
         * Return the untranslated user-visible Joomla message key.
         *
         * @param string $key Requested translation.
         * @return string Untranslated key.
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
        /**
         * Accept a test POST token while checking its transport type.
         *
         * @param string $type Expected Joomla form token transport.
         * @return bool Whether the form token is valid.
         */
        public static function checkToken(string $type): bool
        {
            return $type === 'post';
        }
    }
}

namespace Joomla\CMS\MVC\Controller {
    class BaseController
    {
        protected object $app;
        protected object $input;
        public array $models = [];
        public array $requested = [];
        public array $redirect = [];

        /**
         * Bind the test application and POST input to the controller.
         *
         * @param object $app Application recorder.
         * @param object $input Joomla input test double.
         */
        public function __construct(object $app, object $input)
        {
            $this->app = $app;
            $this->input = $input;
        }

        /**
         * Simulate Joomla's MVC factory rather than constructing models.
         *
         * @param string $name Requested model alias.
         * @return mixed Mock model associated with the alias.
         */
        public function getModel(string $name): mixed
        {
            $this->requested[] = $name;

            return $this->models[$name] ?? false;
        }

        /**
         * Capture the selected redirect and optional status.
         *
         * @param string $route Destination route.
         * @param string $message Joomla translated status.
         * @param string $type Message severity.
         * @return void
         */
        public function setRedirect(string $route, string $message = '', string $type = ''): void
        {
            $this->redirect = [$route, $message, $type];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Helper {
    class SiteRouteHelper
    {
        /**
         * Return a deterministic test route for selected frontend filters.
         *
         * @param string $view Joomla component view.
         * @param array $params Route query fields.
         * @return string Test results route.
         */
        public static function view(string $view, array $params): string
        {
            return $view . '?' . http_build_query($params);
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Model {
    class EditmatchModel
    {
    }

    class ResultsAccessModel
    {
        public int $database = -1;
        public int $project = -1;
        public bool $allow = true;

        /**
         * Record the selected database for permission checks.
         *
         * @param int $selector Database context.
         * @return void
         */
        public function setDatabaseSelector(int $selector): void
        {
            $this->database = $selector;
        }

        /**
         * Record the project selected for permission checks.
         *
         * @param int $projectId Protected project.
         * @return void
         */
        public function setProjectId(int $projectId): void
        {
            $this->project = $projectId;
        }

        /**
         * Permit match edits under the configured permission switch.
         *
         * @param array<int,int> $ids Selected match records.
         * @return bool Whether changes are permitted.
         */
        public function canEditMatches(array $ids): bool
        {
            return $this->allow && $ids === [12];
        }
    }

    class ResultsEditModel
    {
        public int $database = -1;
        public array $saved = [];

        /**
         * Record the database selected for result writes.
         *
         * @param int $selector Database context.
         * @return void
         */
        public function setDatabaseSelector(int $selector): void
        {
            $this->database = $selector;
        }

        /**
         * Capture the permitted short-results update.
         *
         * @param array $post Submitted fields.
         * @param array<int,int> $ids Selected matches.
         * @return bool Whether the write succeeded.
         */
        public function saveShort(array $post, array $ids): bool
        {
            $this->saved = $ids;

            return true;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Controller/ResultsController.php';

    $app = new class {
        public array $messages = [];

        /**
         * Capture Joomla notices without writing to a production queue.
         *
         * @param string $message Translated message.
         * @param string $level Joomla message severity.
         * @return void
         */
        public function enqueueMessage(string $message, string $level = 'message'): void
        {
            $this->messages[] = [$message, $level];
        }
    };

    $post = new class {
        /**
         * Return valid short-result form fields.
         *
         * @return array<string,mixed> Submitted selectors.
         */
        public function getArray(): array
        {
            return ['p' => '5', 'cfg_which_database' => '0', 'layout' => 'form'];
        }

        /**
         * Return one selected match in the Joomla checkbox list.
         *
         * @param string $key Input field name.
         * @param mixed $default Default form value.
         * @param string $filter Joomla filter type.
         * @return array<int,int> Selected fixture ID.
         */
        public function get(string $key, mixed $default, string $filter): array
        {
            return [12];
        }
    };

    $input = new class($post) {
        public object $post;

        /**
         * Retain the form input object.
         *
         * @param object $post Mock POST collection.
         */
        public function __construct(object $post)
        {
            $this->post = $post;
        }

        /**
         * Supply a fallback route layout.
         *
         * @param string $key Requested field.
         * @param string $default Default value.
         * @return string Joomla layout.
         */
        public function getCmd(string $key, string $default = ''): string
        {
            return $default;
        }

        /**
         * Supply a fallback integer input selector.
         *
         * @param string $key Requested selector.
         * @param int $default Default ID.
         * @return int Fallback ID.
         */
        public function getInt(string $key, int $default = 0): int
        {
            return $default;
        }
    };

    $controller = new \Diddipoeler\Component\SportsManagement\Site\Controller\ResultsController($app, $input);
    $access = new \Diddipoeler\Component\SportsManagement\Site\Model\ResultsAccessModel();
    $writer = new \Diddipoeler\Component\SportsManagement\Site\Model\ResultsEditModel();
    $controller->models = ['ResultsAccess' => $access, 'ResultsEdit' => $writer];

    $controller->saveshort();

    if ($controller->requested !== ['ResultsAccess', 'ResultsEdit']
        || $access->project !== 5 || $access->database !== 0
        || $writer->database !== 0 || $writer->saved !== [12]
        || !str_starts_with($controller->redirect[0] ?? '', 'results?')) {
        throw new \RuntimeException('Results controller bypassed native Joomla model services.');
    }

    $controller->requested = [];
    $controller->models['ResultsEdit'] = false;

    try {
        $controller->saveshort();
        throw new \LogicException('An absent results editor model was accepted.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 500) {
            throw $e;
        }
    }

    $controller->models['ResultsEdit'] = $writer;
    $controller->models['ResultsAccess'] = false;

    try {
        $controller->saveshort();
        throw new \LogicException('An absent results access model was accepted.');
    } catch (\RuntimeException $e) {
        if ($e->getCode() !== 500) {
            throw $e;
        }
    }

    echo "Joomla 5/6 results MVC model injection: OK\n";
}
