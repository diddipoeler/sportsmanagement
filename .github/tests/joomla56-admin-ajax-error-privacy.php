<?php
/**
 * Regression tests for the Joomla 5/6 administrator AJAX selector error boundary.
 *
 * Usage: php .github/tests/joomla56-admin-ajax-error-privacy.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
declare(strict_types=1);

namespace Joomla\CMS\Application {
    /** Minimal application contract for isolated selector tests. */
    interface CMSApplicationInterface {}
}

namespace Joomla\CMS\MVC\Model {
    /** Standalone model shim, without initialising Joomla. */
    class BaseDatabaseModel {}
}

namespace Joomla\Database {
    /** Minimal database interface used by the selector loader. */
    interface DatabaseInterface
    {
        public function setQuery(mixed $query): void;
        public function loadObjectList(): array;
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /** Return the requested translation identifier in this test. */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 'error';
        public static array $entries = [];

        /** Capture internal diagnostics without writing a real log. */
        public static function add(string $message, mixed $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Diddipoeler\Component\SportsManagement\Administrator\Helper {
    final class SportsManagementAdministratorApplicationResolver
    {
        /** Provide a test-only administrator message queue. */
        public static function resolve(): \Joomla\CMS\Application\CMSApplicationInterface
        {
            return $GLOBALS['ajaxTestApplication'];
        }
    }
}

namespace {
    defined('_JEXEC') || define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/admin/src/Model/AjaxModel.php';

    $GLOBALS['ajaxTestApplication'] = new class implements \Joomla\CMS\Application\CMSApplicationInterface {
        public array $messages = [];

        /** Record translated user-facing notifications. */
        public function enqueueMessage(string $message, string $type = 'message'): void
        {
            $this->messages[] = [$message, $type];
        }
    };

    $database = new class implements \Joomla\Database\DatabaseInterface {
        /** Simulate a database failure containing sensitive SQL details. */
        public function setQuery(mixed $query): void
        {
            throw new \RuntimeException('SQL password=not-for-ajax-output');
        }

        /** The query never reaches this method. */
        public function loadObjectList(): array
        {
            return [];
        }
    };

    $model = \Diddipoeler\Component\SportsManagement\Administrator\Model\AjaxModel::class;
    $method = new \ReflectionMethod($model, 'loadRows');
    $rows = $method->invoke(null, $database, 'SELECT secret FROM private_table');
    if ($rows !== []) {
        throw new \RuntimeException('Failed AJAX query did not return empty options.');
    }

    $messages = $GLOBALS['ajaxTestApplication']->messages;
    if (count($messages) !== 1
        || $messages[0][0] !== 'JERROR_AN_ERROR_HAS_OCCURRED'
        || $messages[0][1] !== 'error'
        || str_contains($messages[0][0], 'password')) {
        throw new \RuntimeException('AJAX selector leaked raw database diagnostics.');
    }

    $entries = \Joomla\CMS\Log\Log::$entries;
    if (count($entries) !== 1
        || !str_contains($entries[0][0], 'password=not-for-ajax-output')
        || $entries[0][2] !== 'jsmerror') {
        throw new \RuntimeException('AJAX database error was not logged privately.');
    }

    echo "Joomla 5/6 administrator AJAX error privacy checks passed.\n";
}
