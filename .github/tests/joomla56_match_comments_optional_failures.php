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

namespace Joomla\CMS\Application {
    class SiteApplication
    {
        public static bool $failComments = false;

        public function triggerEvent(string $eventName, array $arguments): array
        {
            if (self::$failComments) {
                throw new \RuntimeException('JComments plugin private path /srv/passwords');
            }

            return ['comment rendered'];
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
        public static function importPlugin(string $type, string $name): void {}
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
        || !str_contains(\Joomla\CMS\Log\Log::$errors[0][0], 'password')
        || str_contains($output, 'password')) {
        throw new \RuntimeException('Kunena database exception broke the match page.');
    }

    // Joomla should still render other pages when the optional JComments
    // event dispatcher/plugin throws; only the server log gets its details.
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

        $output = $helper::render($match, $team, $team, [], $project);

        if ($output !== 'Comments not available'
            || count(\Joomla\CMS\Log\Log::$errors) !== 2
            || !str_contains(\Joomla\CMS\Log\Log::$errors[1][0], '/srv/passwords')) {
            throw new \RuntimeException('JComments failure was exposed to match visitors.');
        }
    } finally {
        @unlink($pluginRoot . '/jcomments.class.php');
        @rmdir($pluginRoot);
        @rmdir(dirname($pluginRoot));
        @rmdir($root);
    }

    echo "Optional Kunena/JComments match failure paths passed.\n";
}
