<?php
/**
 * Regression guards for encoded club icons, remote download bounds and map error privacy.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\Uri {
    /** Joomla root URL fixture for KML media. */
    final class Uri {
        /**
         * Return the configured local site URL.
         * @return string Site URL.
         */
        public static function root(): string { return 'https://example.test/'; }
    }
}
namespace Joomla\Filesystem {
    /** Joomla directory service used by the standalone download fixture. */
    final class Folder {
        /**
         * Create a destination fixture folder.
         * @param string $path Absolute target directory.
         * @return bool Whether directory creation succeeded.
         */
        public static function create(string $path): bool { return is_dir($path) || mkdir($path, 0700, true); }
    }
    /** Joomla file-write fixture for KML/image tests. */
    final class File {
        /**
         * Write a string to an existing fixture file.
         * @param string $path Destination path.
         * @param string $body File bytes.
         * @return bool Whether writing succeeded.
         */
        public static function write(string $path, string $body): bool { return file_put_contents($path, $body) !== false; }
    }
}
namespace Joomla\CMS\Log {
    /** Captures sanitized Joomla warnings for error assertions. */
    final class Log {
        public const WARNING = 2;
        public const ERROR = 1;
        public static array $messages = [];
        /**
         * Record a Joomla error entry without external services.
         * @param string $message Error message.
         * @param int $priority Severity.
         * @param string $category Joomla log category.
         * @return void
         */
        public static function add(string $message, int $priority, string $category): void {
            self::$messages[] = $message;
        }
    }
}
namespace Joomla\Http {
    /** Controlled HTTP transport that refuses to contact the network. */
    final class HttpFactory {
        /**
         * Return a transport which throws a simulated secret-bearing exception.
         * @param array $options Joomla HTTP settings.
         * @param array $adapters Allowed HTTP drivers.
         * @return object Simulated HTTP transport.
         */
        public function getHttp(array $options = [], array $adapters = []): object {
            return new class {
                /**
                 * Simulate transport failure without contacting public addresses.
                 * @param string $url Image URL.
                 * @param array $headers HTTP headers.
                 * @param int $timeout Request timeout.
                 * @return never Always throws the fixture exception.
                 */
                public function get(string $url, array $headers, int $timeout): never {
                    throw new \RuntimeException('password=remote-secret SELECT hidden');
                }
            };
        }
    }
}
namespace {
    define('_JEXEC', 1);
    $root = dirname(__DIR__, 2);
    $tmp = sys_get_temp_dir() . '/sm-kml-remote-' . bin2hex(random_bytes(8));
    if (!mkdir($tmp, 0700, true)) {
        throw new \RuntimeException('Unable to prepare safe fixture root.');
    }
    define('JPATH_SITE', $tmp);
    define('JPATH_ROOT', $tmp);

    require_once $root . '/site/src/Helper/ClubKmlHelper.php';
    require_once $root . '/admin/src/Helper/RemoteImageDownloadHelper.php';

    try {
        $club = \Diddipoeler\Component\SportsManagement\Site\Helper\ClubKmlHelper::class;
        $imageUrl = new \ReflectionMethod($club, 'pictureUrl');
        foreach ([
            'images/%252e%252e%252fprivate.png',
            'images/%2525252e%2525252e%2525252fprivate.png',
            '%256a%2561vascript%253aalert(1)',
            'images/%253fadmin%253d1',
            'images/%252525252525252525252e%252e%252fsecret',
        ] as $unsafe) {
            if ($imageUrl->invoke(null, $unsafe) !== '') {
                throw new \RuntimeException('Encoded club picture URL bypassed traversal checks: ' . $unsafe);
            }
        }
        if ($imageUrl->invoke(null, 'images/club.png') !== 'https://example.test/images/club.png') {
            throw new \RuntimeException('Legitimate KML club media URL was rejected.');
        }

        $download = new \Diddipoeler\Component\SportsManagement\Administrator\Helper\RemoteImageDownloadHelper();
        $target = $tmp . '/images/com_sportsmanagement/database/badge.png';
        if ($download->download('file:///etc/passwd', $target, PHP_INT_MAX)) {
            throw new \RuntimeException('Remote image downloader accepted forbidden schemes.');
        }

        $fetch = new \ReflectionMethod($download, 'fetch');
        if ($fetch->invoke($download, 'https://8.8.8.8/badge.png', 1024) !== null) {
            throw new \RuntimeException('Remote image transport exception was not handled.');
        }
        if (count(\Joomla\CMS\Log\Log::$messages) !== 1
            || !str_contains(\Joomla\CMS\Log\Log::$messages[0], 'RuntimeException')
            || str_contains(\Joomla\CMS\Log\Log::$messages[0], 'remote-secret')
            || str_contains(\Joomla\CMS\Log\Log::$messages[0], 'SELECT')) {
            throw new \RuntimeException('Transport error logging leaked remote connection information.');
        }

        $reflection = new \ReflectionClass($download);
        if ($reflection->getConstant('MAX_IMAGE_BYTES') !== 10485760) {
            throw new \RuntimeException('Remote image download maximum-size guard regressed.');
        }

        $projectMap = file_get_contents(
            $root . '/modules/mod_sportsmanagement_projectmap/src/Dispatcher/Dispatcher.php'
        );
        if (!is_string($projectMap)
            || !str_contains($projectMap, 'project map unavailable (')
            || str_contains($projectMap, "Log::add(__METHOD__ . ': ' . " . '$exception->getMessage()')) {
            throw new \RuntimeException('Project map exception details are exposed in logs.');
        }

        echo "Joomla 5/6 club icons, remote image transport and project map logs: OK\n";
    } finally {
        rmdir($tmp);
    }
}
