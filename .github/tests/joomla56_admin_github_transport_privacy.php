<?php
/**
 * Joomla 5/6 regression for GitHub API transport error confidentiality.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\MVC\Model {
    class BaseDatabaseModel {}
}

namespace Joomla\CMS\Application {
    final class AdministratorApplication
    {
        public array $messages = [];

        /**
         * Confirm the expected Joomla administrator client.
         *
         * @param string $client Joomla client name.
         * @return bool Whether the expected test client was requested.
         */
        public function isClient(string $client): bool
        {
            return $client === 'administrator';
        }

        /**
         * Capture a Joomla notification for confidentiality checks.
         *
         * @param string $message Message rendered to the administrator.
         * @param string $type Joomla severity.
         * @return void
         */
        public function enqueueMessage(string $message, string $type = 'message'): void
        {
            $this->messages[] = [$message, $type];
        }
    }
}

namespace Joomla\CMS {
    final class Factory
    {
        public static object $application;

        /**
         * Provide an isolated Joomla service container for the model.
         *
         * @return object Fake service container.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Return the configured administrator application.
                 *
                 * @param string $service Joomla container service name.
                 * @return object Application dependency.
                 */
                public function get(string $service): object
                {
                    if ($service !== \Joomla\CMS\Application\AdministratorApplication::class) {
                        throw new \RuntimeException('Unexpected container dependency.');
                    }

                    return Factory::$application;
                }
            };
        }
    }
}

namespace Joomla\CMS\Log {
    final class Log
    {
        public const ERROR = 1;
        public static array $entries = [];

        /**
         * Capture text emitted to Joomla persistent logs.
         *
         * @param string $message Log entry text.
         * @param int $level Log severity.
         * @param string $category Joomla log category.
         * @return void
         */
        public static function add(string $message, int $level, string $category): void
        {
            self::$entries[] = [$message, $level, $category];
        }
    }
}

namespace Joomla\Http {
    final class HttpFactory
    {
        /**
         * Simulate a transport exception containing a private API token.
         *
         * @return object Unreachable HTTP client.
         * @throws \RuntimeException Every time a client is requested.
         */
        public static function getHttp(): object
        {
            throw new \RuntimeException('Bearer ghp_PRIVATE_API_TOKEN and issue body');
        }
    }
}

namespace {
    \define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/GithubModel.php';

    $app = new \Joomla\CMS\Application\AdministratorApplication();
    \Joomla\CMS\Factory::$application = $app;
    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\GithubModel();
    $request = new \ReflectionMethod($model, 'request');

    if ($request->invoke($model, 'GET', '/repos/example/repository/commits') !== false) {
        throw new \RuntimeException('Failed GitHub connection did not fail closed.');
    }

    $logs = \Joomla\CMS\Log\Log::$entries;
    if (count($logs) !== 1 || count($app->messages) !== 1
        || $app->messages[0][1] !== 'error'
        || $logs[0][2] !== 'jsmerror') {
        throw new \RuntimeException('GitHub connection failure was not reported properly.');
    }

    foreach ([$logs[0][0], $app->messages[0][0]] as $message) {
        if (str_contains($message, 'ghp_PRIVATE_API_TOKEN')
            || str_contains($message, 'issue body')
            || str_contains($message, 'Bearer')) {
            throw new \RuntimeException('GitHub error message leaked credentials or issue data.');
        }
    }

    // Server-side API responses can also echo private issue content or
    // submitted credentials. Show the HTTP status, never the response body.
    $report = new \ReflectionMethod($model, 'reportApiError');
    $report->invoke(
        $model,
        422,
        (object) ['message' => 'Bearer ghp_PRIVATE_API_TOKEN and issue body'],
        'Bearer ghp_PRIVATE_API_TOKEN and issue body'
    );

    if (count(\Joomla\CMS\Log\Log::$entries) !== 2
        || count($app->messages) !== 2) {
        throw new \RuntimeException('GitHub API error was not reported.');
    }

    foreach ([\Joomla\CMS\Log\Log::$entries[1][0], $app->messages[1][0]] as $message) {
        if (!str_contains($message, '422')
            || str_contains($message, 'ghp_PRIVATE_API_TOKEN')
            || str_contains($message, 'issue body')
            || str_contains($message, 'Bearer')) {
            throw new \RuntimeException('GitHub API response leaked private data.');
        }
    }

    // GitHub's successful JSON response must not inject unrelated or
    // malformed URLs into administrator success notifications.
    $safeUrl = new \ReflectionMethod($model, 'safeIssueUrl');
    $issueCases = [
        ['https://github.com/diddipoeler/sportsmanagement/issues/123', true],
        ['https://GITHUB.COM/diddipoeler/sportsmanagement/issues/1', true],
        ['http://github.com/diddipoeler/sportsmanagement/issues/123', false],
        ['https://github.com.evil.test/diddipoeler/sportsmanagement/issues/123', false],
        ['https://evil.test/diddipoeler/sportsmanagement/issues/123', false],
        ['https://github.com/other/sportsmanagement/issues/123', false],
        ['https://github.com/diddipoeler/other/issues/123', false],
        ['https://github.com/diddipoeler/sportsmanagement/issues/0', false],
        ['https://github.com/diddipoeler/sportsmanagement/issues/12?token=private', false],
        ['https://github.com/diddipoeler/sportsmanagement/issues/12#fragment', false],
        ['https://github.com/diddipoeler/sportsmanagement/issues/12' . "\n", false],
        ['https://user:secret@github.com/diddipoeler/sportsmanagement/issues/123', false],
        [['malformed'], false],
        [null, false],
    ];

    foreach ($issueCases as [$candidate, $valid]) {
        $result = $safeUrl->invoke($model, $candidate, 'diddipoeler', 'sportsmanagement');

        if (($result !== '') !== $valid || ($valid && $result !== $candidate)) {
            throw new \RuntimeException('Untrusted GitHub issue response URL was accepted.');
        }
    }

    $repositoryPart = new \ReflectionMethod($model, 'isRepositoryPart');
    foreach ([
        ['sportsmanagement', true], ['org-name', true],
        ['module.extension', true], ['bad/owner', false],
        ['bad owner', false], ["bad\n", false], ['', false],
        [str_repeat('a', 101), false],
    ] as [$candidate, $expected]) {
        if ($repositoryPart->invoke($model, $candidate) !== $expected) {
            throw new \RuntimeException('GitHub repository coordinate was not validated.');
        }
    }

    echo "Administrator GitHub transport errors do not expose confidential data.\n";
}
