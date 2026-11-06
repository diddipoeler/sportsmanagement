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
     * Validate and create a GitHub issue from the administrator form.
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
     *
     * @return object|false
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

        $url = isset($decoded->html_url) ? (string) $decoded->html_url : '';
        $app->enqueueMessage(
            $url !== '' ? 'GitHub-Issue erstellt: ' . $url : 'GitHub-Issue erstellt.',
            'message'
        );

        return $decoded;
    }

    /**
     * Return the latest commits in the configured GitHub repository.
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
     * and returns false when a network or transport error occurs.
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
            Log::add($e->getMessage(), Log::ERROR, 'jsmerror');
            $this->administratorApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }
    }

    /**
     * Validate the configured GitHub account and repository names.
     * @return array{0:string,1:string} Owner and repository or empty values.
     */
    private function repositoryCoordinates(): array
    {
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $owner = trim((string) $params->get('cfg_github_username', ''));
        $repository = trim((string) $params->get('cfg_github_repository', ''));

        if (!$this->isRepositoryPart($owner) || !$this->isRepositoryPart($repository)) {
            $this->administratorApplication()->enqueueMessage('Ungültige GitHub-Repository-Konfiguration.', 'error');

            return ['', ''];
        }

        return [$owner, $repository];
    }

    /**
     * Resolve the configured or explicitly supplied GitHub API token.
     * Prefers the component's configured token over transient form input.
     * @param string $submitted Optional submitted token.
     * @return string GitHub token or an empty string.
     */
    private function githubToken(string $submitted = ''): string
    {
        $configured = trim((string) ComponentHelper::getParams('com_sportsmanagement')->get('gh_token', ''));

        if ($configured !== '') {
            return $configured;
        }

        return trim($submitted);
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
        return $value !== '' && preg_match('/^[A-Za-z0-9_.-]+$/', $value) === 1;
    }

    /**
     * Log and display a GitHub API failure to the administrator.
     * @param int $status HTTP response status.
     * @param mixed $decoded JSON-decoded error body.
     * @param string $body Raw response body.
     * @return void
     */
    private function reportApiError(int $status, $decoded, string $body): void
    {
        $message = is_object($decoded) && isset($decoded->message)
            ? (string) $decoded->message
            : trim($body);
        $message = $message !== '' ? $message : 'Unbekannter GitHub-API-Fehler';
        $safeMessage = sprintf('GitHub API %d: %s', $status, $message);

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
