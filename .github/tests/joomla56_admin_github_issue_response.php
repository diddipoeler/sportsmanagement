<?php
/**
 * Joomla 5/6 administrator GitHub issue creation response regression.
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

namespace Joomla\CMS\MVC\View {
    class HtmlView {}
}

namespace Joomla\CMS\Application {
    final class AdministratorApplication
    {
        public array $messages = [];

        /**
         * Simulate the active Joomla administrator client.
         *
         * @param string $name Joomla client name.
         * @return bool Whether the requested client is administrator.
         */
        public function isClient(string $name): bool
        {
            return $name === 'administrator';
        }

        /**
         * Provide form POST input without a Joomla installation.
         *
         * @return object Request input with native getters.
         */
        public function getInput(): object
        {
            return new class {
                public object $post;

                /**
                 * Construct an empty input registry for explicit arguments.
                 */
                public function __construct()
                {
                    $this->post = new class {
                        /**
                         * Return a blank input field.
                         *
                         * @param string $name Field name.
                         * @return string Empty input.
                         */
                        public function getString(string $name): string
                        {
                            return '';
                        }

                        /**
                         * Return a blank integer input field.
                         *
                         * @param string $name Field name.
                         * @return int Unconfigured integer.
                         */
                        public function getInt(string $name): int
                        {
                            return 0;
                        }

                        /**
                         * Return the supplied default command.
                         *
                         * @param string $name Field name.
                         * @param string $default Default value.
                         * @return string Fallback command.
                         */
                        public function getCmd(string $name, string $default = ''): string
                        {
                            return $default;
                        }
                    };
                }
            };
        }

        /**
         * Record administrator feedback for response assertions.
         *
         * @param string $message Displayed administrator text.
         * @param string $type Joomla message severity.
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
         * Supply a Joomla-like administrator container.
         *
         * @return object Container returning the current application.
         */
        public static function getContainer(): object
        {
            return new class {
                /**
                 * Resolve the administrator application dependency.
                 *
                 * @param string $name Requested service identifier.
                 * @return object Test application.
                 */
                public function get(string $name): object
                {
                    if ($name !== \Joomla\CMS\Application\AdministratorApplication::class) {
                        throw new \RuntimeException('Unknown test service.');
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
        /**
         * Provide a fixed repository and configured GitHub access token.
         *
         * @param string $name Component identifier.
         * @return object Component parameter lookup.
         */
        public static function getParams(string $name): object
        {
            return new class {
                /**
                 * Return configured GitHub parameters.
                 *
                 * @param string $key Configuration key.
                 * @param mixed $default Default setting.
                 * @return mixed Configured value.
                 */
                public function get(string $key, mixed $default = null): mixed
                {
                    return match ($key) {
                        'cfg_github_username' => 'diddipoeler',
                        'cfg_github_repository' => 'sportsmanagement',
                        'gh_token' => 'ghp_TEST_TOKEN',
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
         * Capture GitHub error logs.
         *
         * @param string $message Log text.
         * @param int $level Severity.
         * @param string $category Log category.
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
        public static int $status = 201;
        public static string $body = '';
        public static array $requests = [];

        /**
         * Return an in-memory HTTP client for both creation and listing.
         *
         * @return object HTTP client observing request headers and JSON.
         */
        public static function getHttp(): object
        {
            return new class {
                /**
                 * Simulate the GitHub commit listing request.
                 *
                 * @param string $url GitHub REST list endpoint.
                 * @param array $headers HTTP headers.
                 * @param int $timeout Request deadline.
                 * @return object Configured fake JSON response.
                 */
                public function get(string $url, array $headers, int $timeout): object
                {
                    HttpFactory::$requests[] = [
                        'url' => $url, 'headers' => $headers, 'timeout' => $timeout,
                    ];

                    return new class {
                        /**
                         * @return int Fake API HTTP status.
                         */
                        public function getStatusCode(): int
                        {
                            return HttpFactory::$status;
                        }

                        /**
                         * @return string Fake JSON response body.
                         */
                        public function getBody(): string
                        {
                            return HttpFactory::$body;
                        }
                    };
                }

                /**
                 * Simulate an HTTP issue creation request.
                 *
                 * @param string $url GitHub endpoint.
                 * @param string $body Submitted JSON issue payload.
                 * @param array $headers HTTP headers.
                 * @param int $timeout Request deadline.
                 * @return object Fake GitHub API response.
                 */
                public function post(string $url, string $body, array $headers, int $timeout): object
                {
                    HttpFactory::$requests[] = compact('url', 'body', 'headers', 'timeout');

                    return new class {
                        /**
                         * Return the configured HTTP status code.
                         *
                         * @return int Simulated response status.
                         */
                        public function getStatusCode(): int
                        {
                            return HttpFactory::$status;
                        }

                        /**
                         * Return the configured JSON response body.
                         *
                         * @return string Simulated API JSON response.
                         */
                        public function getBody(): string
                        {
                            return HttpFactory::$body;
                        }
                    };
                }
            };
        }
    }
}

namespace {
    \define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/admin/src/Model/GithubModel.php';
    require_once dirname(__DIR__, 2) . '/admin/src/View/Github/HtmlView.php';

    $app = new \Joomla\CMS\Application\AdministratorApplication();
    \Joomla\CMS\Factory::$application = $app;
    $model = new \Diddipoeler\Component\SportsManagement\Administrator\Model\GithubModel();

    $cases = [
        [(object) ['number' => 42, 'html_url' => 'https://github.com/diddipoeler/sportsmanagement/issues/42'], true, true],
        [(object) ['number' => 42, 'html_url' => 'https://github.com/diddipoeler/sportsmanagement/issues/43'], true, false],
        [(object) ['number' => 42, 'html_url' => 'https://other.test/issues/42'], true, false],
        [(object) ['number' => 42], true, false],
        [(object) ['number' => '42junk'], false, false],
        [(object) ['number' => 0], false, false],
        [(object) [], false, false],
    ];

    foreach ($cases as [$payload, $success, $hasUrl]) {
        \Joomla\Http\HttpFactory::$body = (string) json_encode($payload);
        $app->messages = [];
        $result = $model->insertissue('Regression issue', 'Test details', 0, 'bug');

        if (($result !== false) !== $success || count($app->messages) !== 1) {
            throw new \RuntimeException('GitHub creation misclassified API response.');
        }

        $message = $app->messages[0][0];
        if (($app->messages[0][1] === 'message') !== $success
            || str_contains($message, 'https://github.com/') !== $hasUrl
            || str_contains($message, 'other.test')
            || str_contains($message, '/issues/43')) {
            throw new \RuntimeException('GitHub issue feedback exposed an invalid URL.');
        }
    }

    $requests = \Joomla\Http\HttpFactory::$requests;
    if (count($requests) !== count($cases)) {
        throw new \RuntimeException('GitHub response testing missed an HTTP request.');
    }

    foreach ($requests as $request) {
        $payload = json_decode($request['body']);
        if ($request['url'] !== 'https://api.github.com/repos/diddipoeler/sportsmanagement/issues'
            || ($request['headers']['Authorization'] ?? '') !== 'Bearer ghp_TEST_TOKEN'
            || $request['timeout'] !== 20
            || ($payload->title ?? '') !== 'Regression issue'
            || ($payload->body ?? '') !== 'Test details') {
            throw new \RuntimeException('GitHub native issue request contract changed.');
        }
    }

    // The native HTTP helper must not silently downgrade a mistyped
    // mutating method to GET, or accept a POST without JSON content.
    $requestMethod = new \ReflectionMethod($model, 'request');
    $requestsBeforeInvalidMethod = count(\Joomla\Http\HttpFactory::$requests);
    foreach ([
        ['PATCH', null],
        ['DELETE', null],
        ['get', null],
        ['GET', ['title' => 'unexpected']],
        ['POST', null],
    ] as [$method, $payload]) {
        try {
            $requestMethod->invoke(
                $model, $method, '/repos/diddipoeler/sportsmanagement/issues',
                $payload, 'ghp_TEST_TOKEN'
            );
            throw new \RuntimeException('Malformed GitHub API method was silently accepted.');
        } catch (\InvalidArgumentException $error) {
            if ($error->getCode() !== 400) {
                throw $error;
            }
        }
    }

    if (count(\Joomla\Http\HttpFactory::$requests) !== $requestsBeforeInvalidMethod) {
        throw new \RuntimeException('Unsupported GitHub request reached HTTP transport.');
    }

    // GitHub's per_page=30 is only a request hint. Do not render more than
    // 30 responses or records without a native commit object.
    $completeCommit = (object) [
        'sha' => str_repeat('a', 40),
        'commit' => (object) ['message' => 'Valid commit'],
    ];
    $oversizedResponse = array_fill(0, 35, $completeCommit);
    $oversizedResponse[1] = ['not a commit'];
    $oversizedResponse[2] = (object) ['sha' => 'missing-commit'];
    \Joomla\Http\HttpFactory::$status = 200;
    \Joomla\Http\HttpFactory::$body = (string) json_encode($oversizedResponse);
    $app->messages = [];
    $list = $model->getGithubList();
    if (count($list) !== 28 || !isset($list[0]->commit, $list[27]->commit)
        || $app->messages !== []) {
        throw new \RuntimeException('GitHub commit list ignored page cap or malformed entries.');
    }

    // Only the exact configured repository and matching commit SHA may
    // appear in a clickable GitHub commit link.
    $validHash = str_repeat('a', 40);
    $validCommitUrl = 'https://github.com/diddipoeler/sportsmanagement/commit/' . $validHash;
    $validateCommitUrl = new \ReflectionMethod($model, 'safeCommitUrl');
    foreach ([
        [$validCommitUrl, $validHash, $validCommitUrl],
        ['https://github.com/other/sportsmanagement/commit/' . $validHash, $validHash, ''],
        ['https://github.com/diddipoeler/other/commit/' . $validHash, $validHash, ''],
        [$validCommitUrl . '?token=secret', $validHash, ''],
        [$validCommitUrl . '#fragment', $validHash, ''],
        [$validCommitUrl, str_repeat('b', 40), ''],
        [$validCommitUrl, 'a012junk', ''],
        [$validCommitUrl, ['invalid'], ''],
        [['nested'], $validHash, ''],
    ] as [$url, $sha, $expected]) {
        if ($validateCommitUrl->invoke(null, $url, $sha, 'diddipoeler', 'sportsmanagement') !== $expected) {
            throw new \RuntimeException('Foreign or malformed GitHub commit URL was accepted.');
        }
    }

    $untrustedCommits = [
        (object) ['sha' => $validHash, 'commit' => (object) ['message' => 'valid'], 'html_url' => $validCommitUrl],
        (object) ['sha' => $validHash, 'commit' => (object) ['message' => 'foreign'],
            'html_url' => 'https://github.com/other/sportsmanagement/commit/' . $validHash],
    ];
    \Joomla\Http\HttpFactory::$body = (string) json_encode($untrustedCommits);
    $sanitizedCommits = $model->getGithubList();
    if (count($sanitizedCommits) !== 2
        || $sanitizedCommits[0]->html_url !== $validCommitUrl
        || $sanitizedCommits[1]->html_url !== '') {
        throw new \RuntimeException('Native GitHub list did not sanitize untrusted commit links.');
    }

    $lastRequest = \Joomla\Http\HttpFactory::$requests[array_key_last(\Joomla\Http\HttpFactory::$requests)];
    if ($lastRequest['url'] !== 'https://api.github.com/repos/diddipoeler/sportsmanagement/commits?per_page=30'
        || ($lastRequest['headers']['Authorization'] ?? '') !== 'Bearer ghp_TEST_TOKEN'
        || $lastRequest['timeout'] !== 20) {
        throw new \RuntimeException('GitHub commit listing transport contract changed.');
    }

    \Joomla\Http\HttpFactory::$body = (string) json_encode((object) ['message' => 'private api error']);
    if ($model->getGithubList() !== []) {
        throw new \RuntimeException('Unexpected GitHub object response appeared as commits.');
    }

    \Joomla\Http\HttpFactory::$status = 500;
    \Joomla\Http\HttpFactory::$body = (string) json_encode([$completeCommit]);
    if ($model->getGithubList() !== []) {
        throw new \RuntimeException('Failed GitHub commit API response was displayed.');
    }

    \Joomla\Http\HttpFactory::$status = 201;
    \Joomla\Http\HttpFactory::$body = (string) json_encode((object) ['number' => 42]);

    // Invalid UTF-8 issue content cannot be JSON-encoded. Reject it
    // without issuing any HTTP request or exposing submitted content.
    $requestsBeforeInvalidUtf8 = count(\Joomla\Http\HttpFactory::$requests);
    $app->messages = [];
    \Joomla\CMS\Log\Log::$entries = [];
    $invalidUtf8Title = "Private issue title: " . "\xC3\x28";
    if ($model->insertissue($invalidUtf8Title, 'Confidential issue body', 0, 'bug') !== false
        || count(\Joomla\Http\HttpFactory::$requests) !== $requestsBeforeInvalidUtf8
        || count($app->messages) !== 1
        || $app->messages[0][1] !== 'error'
        || count(\Joomla\CMS\Log\Log::$entries) !== 1) {
        throw new \RuntimeException('Malformed JSON issue input reached GitHub transport.');
    }

    foreach ([$app->messages[0][0], \Joomla\CMS\Log\Log::$entries[0][0]] as $feedback) {
        if (str_contains($feedback, 'Private issue title')
            || str_contains($feedback, 'Confidential issue body')
            || str_contains($feedback, 'ghp_TEST_TOKEN')) {
            throw new \RuntimeException('Malformed issue error leaked submitted content.');
        }
    }

    // The administrator view must not claim that malformed legacy
    // credentials can be used to create a GitHub issue.
    $tokenAvailable = new \ReflectionMethod(
        \Diddipoeler\Component\SportsManagement\Administrator\View\Github\HtmlView::class,
        'hasUsableConfiguredToken'
    );
    foreach ([
        ['ghp_VALID123', true],
        ['github_pat_abc.DEF', true],
        ['', false], [null, false], [false, false],
        [['ghp_OLD'], false], ["ghp_injected\nHeader: test", false],
        ['ghp_ space', false], [str_repeat('x', 2049), false],
    ] as [$candidate, $expected]) {
        if ($tokenAvailable->invoke(null, $candidate) !== $expected) {
            throw new \RuntimeException('GitHub administrator token indicator misclassified configuration.');
        }
    }

    echo "Joomla 5/6 GitHub issue creation response contract OK\n";
}
