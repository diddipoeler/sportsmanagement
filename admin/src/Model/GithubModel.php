<?php
/**
 * Joomla 5/6 administrator GitHub integration model.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Http\HttpFactory;

/** Native Joomla 5/6 GitHub integration model. */
final class GithubModel extends BaseDatabaseModel
{
    private const API_ROOT = 'https://api.github.com';
    private const API_VERSION = '2022-11-28';

    /**
     * Validate GitHub issue form input and create the issue remotely.
     *
     * Uses the administrator application and native request input to read
     * the issue title, description, optional milestone and label. Requires
     * a configured or supplied API token and returns false for incomplete
     * submissions or unsuccessful API writes.
     *
     * @return bool Whether GitHub accepted the newly created issue.
     */
    public function addissue(): bool
    {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $title = trim($input->post->getString('title'));
        $message = trim($input->post->getString('message'));
        $token = $this->githubToken($input->post->getString('gh_token'));

        if ($token === '') {
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NO_TOKEN'), 'error');

            return false;
        }

        if ($title === '') {
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NO_TITLE'), 'error');

            return false;
        }

        if ($message === '') {
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_GITHUB_NO_MESSAGE'), 'error');

            return false;
        }

        return $this->insertissue(
            $title,
            $message,
            $input->post->getInt('milestones'),
            $input->post->getCmd('labels', 'bug'),
            $token
        ) !== false;
    }

    /**
     * Create an issue and return the decoded GitHub response.
     *
     * Optional arguments preserve the historic no-argument method surface.
     * Validates the returned HTML issue URL before including it in an
     * administrator success notification.
     *
     * @param string $title New issue title or empty for submitted form input.
     * @param string $message New issue body or empty for submitted form input.
     * @param int $milestone Optional positive GitHub milestone ID.
     * @param string $label Configured issue label, normalised to known values.
     * @param string $token Explicit API token or empty for configured token.
     * @return object|false Decoded issue response or false on failure.
     */
    public function insertissue(
        string $title = '',
        string $message = '',
        int $milestone = 0,
        string $label = 'bug',
        string $token = ''
    ) {
        $app = $this->administratorApplication();
        $input = $app->getInput();
        $title = trim($title !== '' ? $title : $input->post->getString('title'));
        $message = trim($message !== '' ? $message : $input->post->getString('message'));
        $milestone = $milestone > 0 ? $milestone : $input->post->getInt('milestones');
        $label = $this->normaliseLabel($label !== '' ? $label : $input->post->getCmd('labels', 'bug'));
        $token = $this->githubToken($token !== '' ? $token : $input->post->getString('gh_token'));
        [$owner, $repository] = $this->repositoryCoordinates();

        if ($owner === '' || $repository === '' || $token === '' || $title === '' || $message === '') {
            return false;
        }

        $payload = [
            'title' => $title,
            'body' => $message,
            'labels' => [$label],
        ];

        if ($milestone > 0) {
            $payload['milestone'] = $milestone;
        }

        $response = $this->request(
            'POST',
            '/repos/' . rawurlencode($owner) . '/' . rawurlencode($repository) . '/issues',
            $payload,
            $token
        );

        if ($response === false) {
            return false;
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        $decoded = json_decode($body);

        if ($status !== 201 || !is_object($decoded)) {
            $this->reportApiError($status, $decoded, $body);

            return false;
        }

        // A successful API response is still external input. Display only
        // HTTPS issue URLs belonging to the exact configured repository.
        $url = $this->safeIssueUrl($decoded->html_url ?? null, $owner, $repository);
        $app->enqueueMessage(
            $url !== '' ? 'GitHub-Issue erstellt: ' . $url : 'GitHub-Issue erstellt.',
            'message'
        );

        return $decoded;
    }

    /**
     * Validate an issue URL returned by GitHub before displaying it.
     *
     * Accepts only direct HTTPS issue links on github.com within the exact
     * repository selected in the component configuration. Discards external
     * hosts, credentialed URLs, additional query parameters, fragments and
     * malformed issue numbers so API text cannot be injected into messages.
     *
     * @param mixed $value Untrusted html_url field from the API response.
     * @param string $owner Validated configured GitHub account name.
     * @param string $repository Validated configured repository name.
     * @return string Confirmed issue URL or an empty string.
     */
    private function safeIssueUrl(mixed $value, string $owner, string $repository): string
    {
        if (!is_string($value) || $value === '' || strlen($value) > 2048) {
            return '';
        }

        $expected = '~^https://github\\.com/'
            . preg_quote($owner, '~') . '/'
            . preg_quote($repository, '~') . '/issues/[1-9][0-9]*$~iD';

        return preg_match($expected, $value) === 1 ? $value : '';
    }

    /**
     * Load the latest commits from the configured GitHub repository.
     *
     * Validates repository coordinates, requests up to 30 entries through
     * Joomla's HTTP client and reports unsuccessful API responses using
     * the administrator message queue.
     *
     * @return array<int,mixed> Decoded commit records or an empty array.
     */
    public function getGithubList(): array
    {
        [$owner, $repository] = $this->repositoryCoordinates();

        if ($owner === '' || $repository === '') {
            return [];
        }

        $response = $this->request(
            'GET',
            '/repos/' . rawurlencode($owner) . '/' . rawurlencode($repository) . '/commits?per_page=30',
            null,
            $this->githubToken()
        );

        if ($response === false) {
            return [];
        }

        $status = $response->getStatusCode();
        $body = (string) $response->getBody();
        $decoded = json_decode($body);

        if ($status !== 200 || !is_array($decoded)) {
            $this->reportApiError($status, $decoded, $body);

            return [];
        }

        return $decoded;
    }

    /**
     * Send a GitHub REST API request using Joomla's HTTP client.
     * Constructs request headers and optional bearer authentication,
     * and returns false when a network or transport error occurs. Never
     * logs or displays raw transport exception text, which can contain
     * authorization tokens and submitted issue content.
     * @param string $method HTTP method such as GET or POST.
     * @param string $path GitHub API path relative to its trusted host.
     * @param array|null $payload Optional JSON request body.
     * @param string $token Optional GitHub authentication token.
     * @return mixed Joomla HTTP response or false on failure.
     */
    private function request(string $method, string $path, ?array $payload = null, string $token = '')
    {
        $headers = [
            'Accept' => 'application/vnd.github+json',
            'X-GitHub-Api-Version' => self::API_VERSION,
            'User-Agent' => 'Joomla-SportsManagement',
        ];

        if ($token !== '') {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        if ($payload !== null) {
            $headers['Content-Type'] = 'application/json';
        }

        try {
            $http = HttpFactory::getHttp();
            $url = self::API_ROOT . $path;

            if ($method === 'POST') {
                return $http->post(
                    $url,
                    (string) json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    $headers,
                    20
                );
            }

            return $http->get($url, $headers, 20);
        } catch (\Throwable $e) {
            // Transport exceptions can include Authorization headers, tokens,
            // request bodies or credentials in their text. Do not expose them
            // in Joomla administrator messages or persistent error logs.
            Log::add('GitHub API transport failed (' . get_class($e) . ').', Log::ERROR, 'jsmerror');
            $this->administratorApplication()->enqueueMessage(
                'Die GitHub-API-Anfrage ist fehlgeschlagen. Bitte Verbindung und Konfiguration prüfen.',
                'error'
            );

            return false;
        }
    }

    /**
     * Validate the configured GitHub account and repository names.
     *
     * Rejects non-string, oversized or malformed configuration values before
     * they can become a GitHub API request path.
     *
     * @return array{0:string,1:string} Owner and repository or empty values.
     */
    private function repositoryCoordinates(): array
    {
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $rawOwner = $params->get('cfg_github_username', '');
        $rawRepository = $params->get('cfg_github_repository', '');
        $owner = is_string($rawOwner) ? trim($rawOwner) : '';
        $repository = is_string($rawRepository) ? trim($rawRepository) : '';

        if (!$this->isRepositoryPart($owner) || !$this->isRepositoryPart($repository)) {
            $this->administratorApplication()->enqueueMessage('Ungültige GitHub-Repository-Konfiguration.', 'error');

            return ['', ''];
        }

        return [$owner, $repository];
    }

    /**
     * Resolve the configured or explicitly supplied GitHub API token.
     * Prefers the component's configured token over transient form input.
     * Rejects malformed configured tokens without falling back to another
     * credential, and disallows whitespace/control characters in headers.
     *
     * @param string $submitted Optional submitted token.
     * @return string GitHub token or an empty string.
     */
    private function githubToken(string $submitted = ''): string
    {
        $configured = ComponentHelper::getParams('com_sportsmanagement')->get('gh_token', '');

        // API tokens belong in an HTTP Authorization header. Reject malformed
        // types, whitespace, control characters and unreasonably long values
        // instead of coercing arrays or accepting header-breaking newlines.
        if ($configured !== '' && $configured !== null) {
            return self::validatedGithubToken($configured);
        }

        return self::validatedGithubToken($submitted);
    }

    /**
     * Validate a GitHub credential before it enters an HTTP header.
     *
     * The administrator can configure classic or fine-grained access tokens.
     * Both are opaque printable non-whitespace ASCII strings. Invalid types,
     * line breaks, embedded whitespace and oversized values fail closed.
     *
     * @param mixed $candidate Raw configured or submitted GitHub token.
     * @return string Safe bearer-token text, or empty string if invalid.
     */
    private static function validatedGithubToken(mixed $candidate): string
    {
        return is_string($candidate)
            && $candidate !== ''
            && strlen($candidate) <= 2048
            && preg_match('/^[!-~]+$/D', $candidate) === 1
            ? $candidate : '';
    }

    /**
     * Reduce a submitted GitHub issue label to supported labels.
     * @param string $label Candidate issue label.
     * @return string Supported label or bug as a fallback.
     */
    private function normaliseLabel(string $label): string
    {
        $allowed = ['bug', 'duplicate', 'enhancement', 'invalid', 'question', 'wontfix'];

        return in_array($label, $allowed, true) ? $label : 'bug';
    }

    /**
     * Check one GitHub repository coordinate for safe characters.
     * @param string $value Owner or repository slug.
     * @return bool Whether the slug is syntactically accepted.
     */
    private function isRepositoryPart(string $value): bool
    {
        return $value !== '' && strlen($value) <= 100
            && preg_match('/^[A-Za-z0-9_.-]+$/D', $value) === 1;
    }

    /**
     * Report an unsuccessful GitHub response without exposing its body.
     *
     * API errors may echo issue descriptions, repository details or other
     * sensitive submitted values. Logs and administrator notifications
     * therefore include only the HTTP status and a generic explanation.
     *
     * @param int $status HTTP response status.
     * @param mixed $decoded Untrusted decoded API response (never displayed).
     * @param string $body Untrusted raw API response (never displayed).
     * @return void
     */
    private function reportApiError(int $status, $decoded, string $body): void
    {
        // GitHub may echo submitted issue content or authentication details
        // in error bodies. Preserve the HTTP status for diagnosis without
        // rendering or persisting untrusted API response text.
        $safeMessage = sprintf('GitHub API HTTP %d: Anfrage fehlgeschlagen.', $status);

        Log::add($safeMessage, Log::ERROR, 'jsmerror');
        $this->administratorApplication()->enqueueMessage($safeMessage, 'error');
    }

    /**
     * Resolve the active Joomla administrator application service.
     * @return AdministratorApplication Valid Joomla admin application.
     * @throws \RuntimeException If the service is unavailable.
     */
    private function administratorApplication(): AdministratorApplication
    {
        $application = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$application instanceof AdministratorApplication || !$application->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement GitHub requires the Joomla administrator application.', 500);
        }

        return $application;
    }
}
