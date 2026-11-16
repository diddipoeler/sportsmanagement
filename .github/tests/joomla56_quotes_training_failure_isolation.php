<?php
/**
 * Regression test for isolated Joomla 5/6 quotes and training failures.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface
    {
    }
}

namespace Joomla\CMS {
    class Factory
    {
        /**
         * Resolve the database stub via Joomla's dependency container.
         *
         * @return object Minimal service container.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Supply a typed mock database service.
                 *
                 * @param string $service Requested service interface.
                 * @return object Database interface instance.
                 */
                public function get(string $service): object
                {
                    return new class implements \Joomla\Database\DatabaseInterface {
                    };
                }
            };
        }
    }
}

namespace Joomla\CMS\Component {
    class ComponentHelper
    {
        /**
         * Return harmless component settings for quotes rendering.
         *
         * @param string $name Joomla component identifier.
         * @return object Configuration stub.
         */
        public static function getParams(string $name): object
        {
            return new \stdClass();
        }
    }
}

namespace Joomla\CMS\Log {
    class Log
    {
        public const ERROR = 3;
        public static array $messages = [];

        /**
         * Capture sanitized server-side diagnostics.
         *
         * @param string $message Log entry text.
         * @param int $level Joomla log priority.
         * @param string $category Extension channel.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$messages[] = $message;
        }
    }
}

namespace Joomla\CMS\Document {
    class HtmlDocument
    {
    }
}

namespace Joomla\CMS\Helper {
    interface HelperFactoryAwareInterface
    {
    }

    trait HelperFactoryAwareTrait
    {
        /**
         * Supply a native helper factory that simulates database failure.
         *
         * @return object Failure-injection helper factory.
         */
        protected function getHelperFactory(): object
        {
            return new class {
                /**
                 * Resolve one intentionally failing optional module helper.
                 *
                 * @param string $name Helper alias.
                 * @return object Failing helper instance.
                 */
                public function getHelper(string $name): object
                {
                    return new class {
                        /**
                         * Raise an exception with details that may not be logged.
                         *
                         * @param mixed ...$args Joomla helper dependencies.
                         * @return array Never returns.
                         * @throws \RuntimeException Always.
                         */
                        public function getData(mixed ...$args): array
                        {
                            throw new \RuntimeException('PRIVATE_MODULE_DB_PASSWORD');
                        }
                    };
                }
            };
        }
    }
}

namespace Joomla\CMS\Dispatcher {
    class AbstractModuleDispatcher
    {
        protected object $app;

        /**
         * Assign the site application for isolated module execution.
         *
         * @param object $app Mock frontend Joomla application.
         */
        public function __construct(object $app)
        {
            $this->app = $app;
        }

        /**
         * Preserve the Joomla module context before helper execution.
         *
         * @return array<string,mixed> Framework-owned layout context.
         */
        protected function getLayoutData(): array|false
        {
            return ['module' => (object) ['id' => 1], 'params' => (object) [], 'layout' => 'default'];
        }

        /**
         * Expose the mock Joomla application.
         *
         * @return object Frontend application stub.
         */
        protected function getApplication(): object
        {
            return $this->app;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    define('JPATH_SITE', '/site');
    define('JPATH_ADMINISTRATOR', '/administrator');

    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_rquotes/src/Dispatcher/Dispatcher.php';
    require_once dirname(__DIR__, 2) . '/modules/mod_sportsmanagement_trainingsdata/src/Dispatcher/Dispatcher.php';

    $app = new class {
        public array $messages = [];

        /**
         * Identify valid Joomla frontend execution.
         *
         * @param string $client Target Joomla client.
         * @return bool Whether it is site rendering.
         */
        public function isClient(string $client): bool
        {
            return $client === 'site';
        }

        /**
         * Provide the Joomla language service expected by both modules.
         *
         * @return object Mock language loader.
         */
        public function getLanguage(): object
        {
            return new class {
                /**
                 * Simulate translation loading.
                 *
                 * @param mixed ...$args Joomla loader settings.
                 * @return bool Successful language load.
                 */
                public function load(mixed ...$args): bool
                {
                    return true;
                }
            };
        }

        /**
         * Return a document without HTML-only asset methods.
         *
         * @return object Non-HTML Joomla document.
         */
        public function getDocument(): object
        {
            return new \stdClass();
        }

        /**
         * Record any unintended visitor-visible error messages.
         *
         * @param string $message Joomla public message.
         * @param string $type Severity.
         * @return void
         */
        public function enqueueMessage(string $message, string $type = 'warning'): void
        {
            $this->messages[] = $message;
        }
    };

    $modules = [
        \Diddipoeler\Module\SportsManagementRquotes\Site\Dispatcher\Dispatcher::class => [
            'source' => '',
            'quoteStyle' => '',
            'list' => [],
            'textLine' => '',
            'pictureServer' => '',
        ],
        \Diddipoeler\Module\SportsManagementTrainingsData\Site\Dispatcher\Dispatcher::class => [
            'trainingsdata' => [],
        ],
    ];

    foreach ($modules as $class => $expected) {
        $dispatcher = new $class($app);
        $method = new \ReflectionMethod($dispatcher, 'getLayoutData');
        $data = $method->invoke($dispatcher);

        if (!is_array($data) || ($data['layout'] ?? null) !== 'default') {
            throw new \RuntimeException('Optional module outage destroyed Joomla layout context.');
        }

        foreach ($expected as $key => $value) {
            if (($data[$key] ?? null) !== $value) {
                throw new \RuntimeException('Optional module failure produced malformed ' . $key);
            }
        }
    }

    $log = implode(' ', \Joomla\CMS\Log\Log::$messages);

    if (count(\Joomla\CMS\Log\Log::$messages) !== 2
        || substr_count($log, 'RuntimeException') !== 2
        || str_contains($log, 'PRIVATE_MODULE_DB_PASSWORD')
        || $app->messages !== []) {
        throw new \RuntimeException('Optional module failure leaked private diagnostic details.');
    }

    echo "Joomla 5/6 quotes and training module failure isolation: OK\n";
}
