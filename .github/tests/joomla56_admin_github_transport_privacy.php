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

namespace Joomla\CMS\Component {
    final class ComponentHelper
    {
        public static mixed $token = '';
        public static mixed $owner = 'diddipoeler';
        public static mixed $repository = 'sportsmanagement';

        /**
         * Return isolated GitHub module configuration for credential checks.
         *
         * @param string $component Joomla component option name.
         * @return object Minimal parameter registry with test token.
         */
        public static function getParams(string $component): object
        {
            return new class {
                /**
                 * Retrieve the stored credential without coercing its type.
                 *
                 * @param string $key Component configuration key.
                 * @param mixed $default Fallback for missing settings.
                 * @return mixed Raw test credential.
                 */
                public function get(string $key, mixed $default = null): mixed
                {
                    return match ($key) {
                        'gh_token' => ComponentHelper::$token,
                        'cfg_github_username' => ComponentHelper::$owner,
                        'cfg_github_repository' => ComponentHelper::$repository,
                        default => $default,
                    };
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
        ['https://github.com/diddipoeler/sportsmanagement/issues/9999999999999999999999999999999', false],
        ['https://github.com/diddipoeler/sportsmanagement/issues/0000000000000000000000000000', false],
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

    // A valid repository URL must also identify the issue from the same
    // creation response; a different number must not be displayed.
    $createdIssueUrl = 'https://github.com/diddipoeler/sportsmanagement/issues/123';
    if ($safeUrl->invoke($model, $createdIssueUrl, 'diddipoeler', 'sportsmanagement', 123)
        !== $createdIssueUrl
        || $safeUrl->invoke($model, $createdIssueUrl, 'diddipoeler', 'sportsmanagement', 124)
        !== ''
        || $safeUrl->invoke(
            $model,
            'https://github.com/diddipoeler/sportsmanagement/issues/9999999999999999999999999',
            'diddipoeler',
            'sportsmanagement',
            123
        ) !== '') {
        throw new \RuntimeException('GitHub success link pointed to an unrelated issue number.');
    }

    // A 201 response must contain a complete, positive issue number;
    // otherwise an empty API object could produce a false success message.
    $issueNumber = new \ReflectionMethod($model, 'positiveIssueNumber');
    foreach ([
        [1, 1], ['00023', 23], [0, 0], [-1, 0], ['0', 0],
        [true, 0], [['1'], 0], ['1junk', 0],
        ['1.5', 0], [' 1', 0], ['99999999999999999999999', 0],
    ] as [$candidate, $expected]) {
        if ($issueNumber->invoke(null, $candidate) !== $expected) {
            throw new \RuntimeException('Malformed GitHub issue number passed validation.');
        }
    }

    // Dot-segment repository configuration must be rejected before the
    // transport is ever created; a request to getGithubList would otherwise
    // trigger the simulated private-credential transport exception.
    foreach ([
        ['.', 'sportsmanagement'],
        ['..', 'sportsmanagement'],
        ['diddipoeler', '.'],
        ['diddipoeler', '..'],
    ] as [$owner, $repository]) {
        \Joomla\CMS\Component\ComponentHelper::$owner = $owner;
        \Joomla\CMS\Component\ComponentHelper::$repository = $repository;
        $logsBefore = count(\Joomla\CMS\Log\Log::$entries);
        $app->messages = [];

        if ($model->getGithubList() !== []
            || count(\Joomla\CMS\Log\Log::$entries) !== $logsBefore
            || count($app->messages) !== 1
            || $app->messages[0][1] !== 'error'
            || !str_contains($app->messages[0][0], 'Repository')) {
            throw new \RuntimeException('Dot-segment GitHub repository reached HTTP transport.');
        }
    }
    \Joomla\CMS\Component\ComponentHelper::$owner = 'diddipoeler';
    \Joomla\CMS\Component\ComponentHelper::$repository = 'sportsmanagement';

    $repositoryPart = new \ReflectionMethod($model, 'isRepositoryPart');
    foreach ([
        ['sportsmanagement', true], ['org-name', true],
        ['module.extension', true], ['.github', true],
        ['.', false], ['..', false], ['bad/owner', false],
        ['bad owner', false], ["bad\n", false], ['', false],
        [str_repeat('a', 101), false],
    ] as [$candidate, $expected]) {
        if ($repositoryPart->invoke($model, $candidate) !== $expected) {
            throw new \RuntimeException('GitHub repository coordinate was not validated.');
        }
    }

    // Credentials stored by older extensions may be malformed arrays or
    // strings with embedded control characters. Never coerce these into
    // an Authorization bearer value or fall back after a bad saved token.
    $validateToken = new \ReflectionMethod($model, 'validatedGithubToken');
    foreach ([
        ['ghp_validTOKEN123', 'ghp_validTOKEN123'],
        ['github_pat_123.ABC', 'github_pat_123.ABC'],
        ['', ''], [null, ''], [true, ''], [['ghp_secret'], ''],
        ['Bearer ghp_secret', ''], ["ghp_value\nOther: header", ''],
        ["ghp_value\r", ''], ['ghp_ value', ''],
        [str_repeat('x', 2049), ''],
    ] as [$raw, $expected]) {
        if ($validateToken->invoke(null, $raw) !== $expected) {
            throw new \RuntimeException('Untrusted GitHub bearer token was accepted.');
        }
    }

    $tokenMethod = new \ReflectionMethod($model, 'githubToken');
    \Joomla\CMS\Component\ComponentHelper::$token = ['invalid'];
    if ($tokenMethod->invoke($model, 'ghp_fallback123') !== '') {
        throw new \RuntimeException('Malformed saved token silently enabled submitted credentials.');
    }

    \Joomla\CMS\Component\ComponentHelper::$token = '';
    if ($tokenMethod->invoke($model, 'ghp_fallback123') !== 'ghp_fallback123') {
        throw new \RuntimeException('Missing saved token did not allow valid form credential.');
    }

    \Joomla\CMS\Component\ComponentHelper::$token = 'ghp_configured123';
    if ($tokenMethod->invoke($model, 'ghp_fallback123') !== 'ghp_configured123') {
        throw new \RuntimeException('Configured GitHub credential lost precedence.');
    }

    echo "Administrator GitHub transport errors do not expose confidential data.\n";
}
