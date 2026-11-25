<?php
/**
 * Joomla 5/6 match comments must tolerate optional Kunena/JComments failures.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\Database {
    interface DatabaseInterface {}
}

namespace Joomla\Event {
    final class Event
    {
        /**
         * Hold the ordered arguments passed to a native event listener.
         * @param string $name Event name.
         * @param array $arguments Positional arguments and result values.
         */
        public function __construct(public string $name, private array $arguments) {}

        /**
         * Read a mutated event argument or its fallback.
         * @param int|string $key Event argument key.
         * @param mixed $default Value when absent.
         * @return mixed Stored argument.
         */
        public function getArgument(int|string $key, mixed $default = null): mixed
        {
            return $this->arguments[$key] ?? $default;
        }

        /**
         * Store the subscriber's output in the event.
         * @param int|string $key Event argument key.
         * @param mixed $value Listener output.
         * @return void
         */
        public function setArgument(int|string $key, mixed $value): void
        {
            $this->arguments[$key] = $value;
        }
    }
}

namespace Joomla\CMS\Application {
    class SiteApplication
    {
        public static bool $failComments = false;

        public static string $lastEvent = '';

        /**
         * Provide a native dispatcher instead of the legacy application API.
         * @return object Dispatcher fixture that simulates a Joomla subscriber.
         */
        public function getDispatcher(): object
        {
            return new class {
                /**
                 * Mutate the HTML argument and return the dispatched event.
                 * @param string $eventName Custom SportsManagement event name.
                 * @param \Joomla\Event\Event $event Native event fixture.
                 * @return \Joomla\Event\Event Event containing the plugin output.
                 */
                public function dispatch(string $eventName, \Joomla\Event\Event $event): \Joomla\Event\Event
                {
                    if (SiteApplication::$failComments) {
                        throw new \RuntimeException('JComments plugin private path /srv/passwords');
                    }

                    if (!is_object($event->getArgument(0))
                        || $event->getArgument(1) !== 'Team - Team'
                        || $event->getArgument(2) !== '') {
                        throw new \RuntimeException('Unexpected native event arguments.');
                    }

                    SiteApplication::$lastEvent = $eventName;
                    $event->setArgument(2, '<div>comment rendered</div>');
                    $event->setArgument('result', [true, '<div>comment rendered</div>', 'additional comment']);

                    return $event;
                }
            };
        }
    }
}

namespace Joomla\CMS {
    final class Factory
    {
        public static \Joomla\Database\DatabaseInterface $db;

        public static function getContainer(): object
        {
            return new class {
                public function get(string $key): object
                {
                    if ($key === \Joomla\Database\DatabaseInterface::class) {
                        return Factory::$db;
                    }

                    return new \Joomla\CMS\Application\SiteApplication();
                }
            };
        }
    }
}

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static array $enabled = ['com_kunena' => true, 'com_jcomments' => false];

        public static function isEnabled(string $component): bool
        {
            return self::$enabled[$component] ?? false;
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

namespace Joomla\CMS\Plugin {
    final class PluginHelper
    {
        public static bool $failImport = false;
        public static bool $enabled = true;

        /**
         * Simulate a transient plugin loader failure before event dispatch.
         * @param string $type Joomla plugin group.
         * @param string $name Plugin element to import.
         * @param bool $autocreate Whether to register the listener.
         * @param object|null $dispatcher Native dispatcher to receive the plugin.
         * @return bool Whether the simulated plugin is enabled.
         * @throws \RuntimeException When the fixture enables import failure.
         */
        public static function importPlugin(string $type, string $name, bool $autocreate = true, ?object $dispatcher = null): bool
        {
            if (self::$failImport) {
                throw new \RuntimeException('Plugin import private path');
            }
            if (!$autocreate || !is_object($dispatcher) || !method_exists($dispatcher, 'dispatch')) {
                throw new \RuntimeException('Plugin was not registered with the native dispatcher.');
            }
            return self::$enabled;
        }
        public static function getPlugin(string $type, string $name): object
        {
            return (object) ['params' => ''];
        }
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        public function __construct(string $params = '') {}
        public function get(string $key, mixed $default = null): mixed
        {
            return $default;
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $errors = [];

        public static function add(string $message, int $level, string $category): void
        {
            self::$errors[] = [$message, $level, $category];
        }
    }
}

namespace {
    define('_JEXEC', 1);
    $root = sys_get_temp_dir() . '/jsm-comment-test-' . getmypid();
    define('JPATH_ROOT', $root);

    require_once dirname(__DIR__, 2) . '/site/src/Helper/MatchCommentsHelper.php';

    $db = new class implements \Joomla\Database\DatabaseInterface {
        public int $queries = 0;

        public function createQuery(): object
        {
            ++$this->queries;

            throw new \RuntimeException('Kunena SQLSTATE hidden database password');
        }
    };
    \Joomla\CMS\Factory::$db = $db;

    $helper = \Diddipoeler\Component\SportsManagement\Site\Helper\MatchCommentsHelper::class;
    $match = (object) ['id' => 5, 'preview' => ''];
    $project = (object) ['sb_catid' => 9];
    $team = (object) ['name' => 'Team'];

    if ($helper::render((object) ['preview' => '{jcomments off}'], $team, $team, [], $project) !== ''
        || $db->queries !== 0) {
        throw new \RuntimeException('Disabled match comments unexpectedly queried Kunena.');
    }

    $output = $helper::render($match, $team, $team, ['show_project_kunena_link' => 1], $project);

    if ($output !== 'Comments not available'
        || $db->queries !== 1
        || count(\Joomla\CMS\Log\Log::$errors) !== 1
        || !str_contains(\Joomla\CMS\Log\Log::$errors[0][0], 'RuntimeException')
        || str_contains(\Joomla\CMS\Log\Log::$errors[0][0], 'password')
        || str_contains($output, 'password')) {
        throw new \RuntimeException('Kunena database exception broke the match page.');
    }

    if ((new \ReflectionProperty($helper, 'kunenaItemResolved'))->getValue(null) !== false) {
        throw new \RuntimeException('Failed Kunena menu lookup poisoned the request cache.');
    }

    // Joomla should still render other pages when the optional JComments
    // event dispatcher/plugin throws; logs retain only its exception class.
    $pluginRoot = $root . '/components/com_jcomments';
    if (!@mkdir($pluginRoot, 0700, true)) {
        throw new \RuntimeException('Unable to create local optional-plugin fixture.');
    }
    file_put_contents($pluginRoot . '/jcomments.class.php', "<?php // test fixture\n");

    try {
        \Joomla\CMS\Component\ComponentHelper::$enabled['com_kunena'] = false;
        \Joomla\CMS\Component\ComponentHelper::$enabled['com_jcomments'] = true;
        \Joomla\CMS\Application\SiteApplication::$failComments = true;

        (new \ReflectionProperty($helper, 'kunenaEnabled'))->setValue(null, null);
        (new \ReflectionProperty($helper, 'jcommentsEnabled'))->setValue(null, null);

        \Joomla\CMS\Plugin\PluginHelper::$failImport = true;
        $output = $helper::render($match, $team, $team, [], $project);
        if ($output !== 'Comments not available'
            || (new \ReflectionProperty($helper, 'jcommentsPrepared'))->getValue(null) !== false
            || count(\Joomla\CMS\Log\Log::$errors) !== 2
            || str_contains(\Joomla\CMS\Log\Log::$errors[1][0], 'private path')) {
            throw new \RuntimeException('Failed JComments import poisoned preparation or leaked details.');
        }
        \Joomla\CMS\Plugin\PluginHelper::$failImport = false;

        $output = $helper::render($match, $team, $team, [], $project);

        if ($output !== 'Comments not available'
            || count(\Joomla\CMS\Log\Log::$errors) !== 3
            || !str_contains(\Joomla\CMS\Log\Log::$errors[2][0], 'RuntimeException')
            || str_contains(\Joomla\CMS\Log\Log::$errors[2][0], '/srv/passwords')) {
            throw new \RuntimeException('JComments failure was exposed to match visitors.');
        }
        \Joomla\CMS\Application\SiteApplication::$failComments = false;
        if ($helper::render($match, $team, $team, [], $project) !== '<div>comment rendered</div>additional comment'
            || \Joomla\CMS\Application\SiteApplication::$lastEvent !== 'onMatchComments') {
            throw new \RuntimeException('Native event output or duplicate result handling regressed.');
        }

        (new \ReflectionProperty($helper, 'separateComments'))->setValue(null, true);
        if ($helper::render($match, $team, $team, [], $project) !== '<div>comment rendered</div>additional comment'
            || \Joomla\CMS\Application\SiteApplication::$lastEvent !== 'onMatchReportComments') {
            throw new \RuntimeException('Separate match report comment event regressed.');
        }
    } finally {
        @unlink($pluginRoot . '/jcomments.class.php');
        @rmdir($pluginRoot);
        @rmdir(dirname($pluginRoot));
        @rmdir($root);
    }

    echo "Optional Kunena/JComments match failure paths passed.\n";
}
