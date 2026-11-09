<?php
/**
 * Standalone Joomla 5/6 frontend error reporting regression checks.
 *
 * Run: php .github/tests/joomla56-view-error-reporter.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\Language {
    /** Stub translated generic message for public exceptions. */
    final class Text
    {
        /**
         * Return a generic public-facing error independent of model details.
         *
         * @param string $key Joomla translation identifier.
         * @return string Safe public error text.
         */
        public static function _(string $key): string { return 'An error has occurred'; }
    }
}

namespace Joomla\CMS\Log {
    /** Intercept error logs without requiring Joomla's configured logger. */
    final class Log
    {
        public const ERROR = 4;
        public static array $messages = [];

        /**
         * Record one safe server-side component diagnostic for assertions.
         *
         * @param string $message Logged diagnostic.
         * @param int $severity Joomla log severity.
         * @param string $category Component logging category.
         * @return void
         */
        public static function add(string $message, int $severity, string $category): void
        {
            self::$messages[] = [$message, $severity, $category];
        }
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);

    require_once dirname(__DIR__, 2) . '/site/src/Service/SportsManagementViewErrorReporter.php';

    use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementViewErrorReporter;
    use Joomla\CMS\Log\Log;

    /**
     * Assert that model errors are logged without disclosure in the exception.
     *
     * @param mixed $errors Original model error(s).
     * @param array<int,string> $expectedFragments Expected per-item diagnostics.
     * @return void
     */
    function smAssertViewErrors(mixed $errors, array $expectedFragments): void
    {
        Log::$messages = [];

        try {
            SportsManagementViewErrorReporter::assertNoErrors($errors, 'ranking');
        } catch (\RuntimeException $exception) {
            if ($exception->getCode() !== 500
                || $exception->getMessage() !== 'An error has occurred') {
                throw new \RuntimeException('Detailed model errors leaked to the public exception.');
            }

            if (count(Log::$messages) !== count($expectedFragments)) {
                throw new \RuntimeException('Unexpected model diagnostic count in Joomla log.');
            }

            foreach ($expectedFragments as $index => $part) {
                if (!str_contains(Log::$messages[$index][0], $part)
                    || Log::$messages[$index][1] !== Log::ERROR
                    || Log::$messages[$index][2] !== 'com_sportsmanagement') {
                    throw new \RuntimeException('Missing or miscategorized model diagnostic.');
                }
            }

            return;
        }

        throw new \RuntimeException('Expected a generic public model exception.');
    }

    foreach ([null, false, [], ''] as $empty) {
        SportsManagementViewErrorReporter::assertNoErrors($empty, 'ranking');
    }

    smAssertViewErrors(new \RuntimeException('Private database failure'), [
        'Private database failure',
    ]);
    smAssertViewErrors(new \ArrayIterator([
        new \RuntimeException('Connection detail'),
        'Another internal issue',
    ]), [
        'Connection detail', 'Another internal issue',
    ]);
    smAssertViewErrors([['nested' => 'secret'], new \stdClass()], [
        'array', 'stdClass',
    ]);

    echo "Joomla 5/6 frontend model error reporter regressions passed.\n";
}
