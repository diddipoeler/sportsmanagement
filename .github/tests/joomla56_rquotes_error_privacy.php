<?php
/**
 * Standalone Joomla 5/6 Rquotes error privacy and media URL regression tests.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Application {
    interface CMSApplicationInterface
    {
    }
}

namespace Joomla\Database {
    interface DatabaseInterface
    {
    }
}

namespace Joomla\Registry {
    final class Registry
    {
        public function __construct(private array $params = [])
        {
        }

        public function get(string $key, mixed $default = null): mixed
        {
            return $this->params[$key] ?? $default;
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $entries = [];

        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\CMS\Uri {
    final class Uri
    {
        public static function root(): string
        {
            return 'https://site.example.test/';
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Site\Service {
    final class SportsManagementDatabaseResolver
    {
        public static function resolve(
            \Joomla\Database\DatabaseInterface $fallback,
            int $selector = 0
        ): \Joomla\Database\DatabaseInterface {
            throw new \RuntimeException(
                'SQLSTATE private details: password=sensitive server=/private/mysql/socket',
                500
            );
        }
    }
}

namespace {
    \defined('_JEXEC') || define('_JEXEC', 1);

    require_once dirname(__DIR__, 2)
        . '/modules/mod_sportsmanagement_rquotes/src/Helper/RquotesHelper.php';

    final class FakeSiteApp implements \Joomla\CMS\Application\CMSApplicationInterface
    {
        public array $messages = [];

        public function enqueueMessage(string $message, string $type = 'message'): void
        {
            $this->messages[] = [$message, $type];
        }
    }

    final class FakeDatabase implements \Joomla\Database\DatabaseInterface
    {
    }

    $helper = new \Diddipoeler\Module\SportsManagementRquotes\Site\Helper\RquotesHelper();
    $app = new FakeSiteApp();
    $database = new FakeDatabase();
    $params = new \Joomla\Registry\Registry(['source' => 'db']);
    $component = new \Joomla\Registry\Registry();

    $result = $helper->getData($params, $component, $app, $database);

    if (($result['list'] ?? null) !== []
        || ($result['source'] ?? '') !== 'db'
        || $app->messages !== []
    ) {
        throw new \RuntimeException('A database error reached the public Rquotes page');
    }

    $entries = \Joomla\CMS\Log\Log::$entries;
    if (count($entries) !== 1
        || $entries[0][1] !== \Joomla\CMS\Log\Log::ERROR
        || $entries[0][2] !== 'com_sportsmanagement'
        || !str_contains($entries[0][0], 'SQLSTATE private details')
    ) {
        throw new \RuntimeException('Rquotes database details were not sent to the Joomla log');
    }

    // Private credentials in a configured media server must never be used
    // in the module's generated image base URL.
    $unsafeConfig = new \Joomla\Registry\Registry([
        'cfg_which_database' => 1,
        'cfg_which_database_server' => 'https://user:secret@cdn.example.test/images/',
    ]);
    $unsafe = $helper->getData($params, $unsafeConfig, $app, $database);

    if (($unsafe['pictureServer'] ?? null) !== '') {
        throw new \RuntimeException('Credential-bearing image server was accepted');
    }

    $safeConfig = new \Joomla\Registry\Registry([
        'cfg_which_database' => 1,
        'cfg_which_database_server' => 'https://cdn.example.test/images/',
    ]);
    $safe = $helper->getData($params, $safeConfig, $app, $database);

    if (($safe['pictureServer'] ?? null) !== 'https://cdn.example.test/images/') {
        throw new \RuntimeException('Valid image server was rejected');
    }

    echo "Rquotes database error privacy and media server contracts OK\n";
}
