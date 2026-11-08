<?php
/**
 * Native Joomla 5/6 dashboard model for SportsManagement.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Http\HttpFactory;

/**
 * Native Joomla 5/6 dashboard model.
 *
 * Database maintenance/install actions remain in the separate Databasetool model.
 */
final class CpanelModel extends SportsManagementListModel
{
    public array $_success_text = [];
    public string $storeFailedColor = 'red';
    public string $storeSuccessColor = 'green';
    public string $existingInDbColor = 'orange';

    /**
     * Provide a valid empty list query for the administrator dashboard.
     * The dashboard is not a record list; inherited list and pagination calls
     * must not enumerate or mutate any SportsManagement tables.
     *
     * @return \Joomla\Database\DatabaseQuery A query returning zero rows.
     */
    protected function getListQuery()
    {
        // The dashboard does not render a record list, but the legacy view asks
        // ListModel for Items/Pagination. Keep that call harmless on every DB.
        return $this->getDatabase()->createQuery()->select('1 AS id')->where('1 = 0');
    }

    /**
     * Read the installed SportsManagement extension version from Joomla.
     * Queries the primary Joomla extension registry and decodes manifest_cache,
     * rather than relying on an optional external sports database.
     *
     * @return string Installed version or an empty string when absent.
     */
    public function getVersion(): string
    {
        $componentElement = 'com_sportsmanagement';
        $extensionType = 'component';
        $db = $this->getJoomlaDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('manifest_cache'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = :componentElement')
            ->where($db->quoteName('type') . ' = :extensionType')
            ->bind(':componentElement', $componentElement, ParameterType::STRING)
            ->bind(':extensionType', $extensionType, ParameterType::STRING);

        $db->setQuery($query, 0, 1);
        $manifest = json_decode((string) $db->loadResult(), true);

        return is_array($manifest) ? (string) ($manifest['version'] ?? '') : '';
    }

    /**
     * Load recent GitHub commit information for the control panel.
     * Reads the configured account and repository, requests JSON from the
     * GitHub API and converts valid entries into presentation-safe objects.
     *
     * @return array Recent formatted commits, or an empty array.
     */
    public function getGithubRequests(): array
    {
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $username = trim((string) $params->get('cfg_github_username', 'diddipoeler'));
        $repo = self::toAscii((string) $params->get('cfg_github_repository', 'sportsmanagement'));

        if ($username === '' || $repo === '') {
            return [];
        }

        $payload = self::getJSON(
            'https://api.github.com/repos/' . rawurlencode($username) . '/' . rawurlencode($repo) . '/commits'
        );

        return is_array($payload) ? self::processData($payload, $params) : [];
    }

    /**
     * Normalise a configured repository slug for GitHub URLs.
     * Removes disallowed characters, folds separators and lowercases the
     * result before it is URL-encoded by its callers.
     *
     * @param mixed $repo Raw repository name.
     * @return string Normalised repository slug.
     */
    public static function toAscii($repo): string
    {
        $clean = preg_replace("/[^a-zA-Z0-9\\/_|+ '\\-]/", '', (string) $repo) ?? '';
        $clean = strtolower(trim($clean, '-'));

        return preg_replace("/[\\/_|+ '\\-]+/", '-', $clean) ?? '';
    }

    /**
     * Request and decode JSON from the GitHub REST API.
     * Restricts requests to HTTPS GitHub API paths, supplies required headers
     * and handles HTTP, transport and malformed JSON failures without output.
     *
     * @param mixed $req URL of the API resource.
     * @return array|null Decoded response, or null on failure.
     */
    public static function getJSON($req): ?array
    {
        $url = trim((string) $req);

        if (!str_starts_with($url, 'https://api.github.com/')) {
            return null;
        }

        try {
            $http = (new HttpFactory())->getHttp();
            $response = $http->get(
                $url,
                [
                    'User-Agent' => 'SportsManagement-Joomla',
                    'Accept' => 'application/vnd.github+json',
                ]
            );
            $status = $response->getStatusCode();

            if ($status < 200 || $status >= 300) {
                return null;
            }

            $decoded = json_decode((string) $response->getBody(), true);

            return is_array($decoded) ? $decoded : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Transform GitHub commit rows into dashboard presentation entries.
     * Escapes external messages and names, builds encoded issue/profile links,
     * formats dates with Joomla and caps the displayed commit count.
     *
     * @param mixed $obj Raw decoded commit records.
     * @param mixed $params Dashboard rendering preferences.
     * @return array Prepared commit entries.
     */
    public static function processData($obj, $params): array
    {
        if (!is_array($obj)) {
            return [];
        }

        $componentParams = ComponentHelper::getParams('com_sportsmanagement');
        $username = trim((string) $componentParams->get('cfg_github_username', ''));
        $repo = self::toAscii((string) $componentParams->get('cfg_github_repository', ''));
        $relativeTime = is_object($params) && method_exists($params, 'get')
            ? (string) $params->get('relativeTime', '1')
            : '1';

        $github = [];
        $i = 0;

        foreach ($obj as $commit) {
            if ($i > 15 || !is_array($commit)) {
                break;
            }

            $sha = (string) ($commit['sha'] ?? '');
            $commitData = is_array($commit['commit'] ?? null) ? $commit['commit'] : [];
            $authorData = is_array($commit['author'] ?? null) ? $commit['author'] : [];
            $committerData = is_array($commit['committer'] ?? null) ? $commit['committer'] : [];
            $authorCommit = is_array($commitData['author'] ?? null) ? $commitData['author'] : [];
            $committerCommit = is_array($commitData['committer'] ?? null) ? $commitData['committer'] : [];

            $temp = new \stdClass();
            $temp->commit = new \stdClass();

            $shortSha = htmlspecialchars(substr($sha, 0, 7), ENT_QUOTES, 'UTF-8');
            $commitUrl = 'https://github.com/' . rawurlencode($username) . '/' . rawurlencode($repo)
                . '/commit/' . rawurlencode($sha);
            $message = htmlspecialchars((string) ($commitData['message'] ?? ''), ENT_QUOTES, 'UTF-8');
            $message = preg_replace(
                '/#(\d+)/',
                '#<a href="https://github.com/' . rawurlencode($username) . '/' . rawurlencode($repo)
                    . '/issues/$1" target="_blank" rel="nofollow">$1</a>',
                $message
            ) ?? $message;

            $temp->commit->message = '<a href="' . $commitUrl
                . '" target="_blank" rel="nofollow">' . $shortSha . '</a> - ' . $message;

            $authorLogin = (string) ($authorData['login'] ?? '');
            $authorName = (string) ($authorCommit['name'] ?? $authorLogin ?: Text::_('JUNKNOWN'));
            $committerLogin = (string) ($committerData['login'] ?? '');
            $committerName = (string) ($committerCommit['name'] ?? $committerLogin ?: Text::_('JUNKNOWN'));

            $sameAccount = $authorLogin !== '' && $authorLogin === $committerLogin;
            $temp->commit->author = $sameAccount
                ? Text::_('COM_SPORTSMANAGEMENT_GITHUB_COMMITTED_BY')
                : Text::_('COM_SPORTSMANAGEMENT_GITHUB_AUTHORED_BY');
            $temp->commit->author .= self::profileLink($authorLogin, $authorName);

            if (!$sameAccount && $committerName !== '') {
                $temp->commit->committer = Text::_('COM_SPORTSMANAGEMENT_GITHUB_AND_COMMITTED_BY')
                    . self::profileLink($committerLogin, $committerName);
            }

            $date = (string) ($committerCommit['date'] ?? '');
            if ($date !== '') {
                $formatted = HTMLHelper::date($date, 'Y-m-d H:i:s');
                $temp->commit->time = $relativeTime === '1'
                    ? ' <span class="commit-time" title="' . htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8') . '">'
                        . htmlspecialchars(HTMLHelper::date($date, 'D M d H:i:s O Y'), ENT_QUOTES, 'UTF-8')
                        . '</span>'
                    : ' ' . htmlspecialchars(HTMLHelper::date($date), ENT_QUOTES, 'UTF-8');
            } else {
                $temp->commit->time = '';
            }

            $github[] = $temp;
            $i++;
        }

        return $github;
    }

    /**
     * Look up an installed plugin by its exact Joomla extension element.
     * Uses a bound database query against the primary extension registry
     * and returns zero for unknown or empty plugin identifiers.
     *
     * @param mixed $plugin Plugin element identifier.
     * @return int Installed extension ID, or zero.
     */
    public function getInstalledPlugin($plugin): int
    {
        $element = trim((string) $plugin);
        if ($element === '') {
            return 0;
        }

        $extensionType = 'plugin';
        $db = $this->getJoomlaDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = :extensionType')
            ->where($db->quoteName('element') . ' = :element')
            ->bind(':extensionType', $extensionType, ParameterType::STRING)
            ->bind(':element', $element, ParameterType::STRING);

        $db->setQuery($query, 0, 1);

        return (int) $db->loadResult();
    }

    /**
     * Return the count of countries in the configured sports database.
     * Uses the shared count query for the dashboard's country indicator.
     *
     * @return int Number of stored countries.
     */
    public function checkcountry(): int
    {
        return $this->countRows('#__sportsmanagement_countries');
    }

    /**
     * Count sport types matching a case-insensitive search fragment.
     * Normalises the search term, rejects empty input and binds the
     * LIKE pattern rather than interpolating values into SQL.
     *
     * @param mixed $type Sport-type name fragment.
     * @return int Matching sport-type record count.
     */
    public function checksporttype($type): int
    {
        $search = strtoupper(trim((string) $type));
        if ($search === '') {
            return 0;
        }

        $pattern = '%' . $search . '%';
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_sports_type'))
            ->where('UPPER(' . $db->quoteName('name') . ') LIKE :search')
            ->bind(':search', $pattern, ParameterType::STRING);

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Count entries in a trusted SportsManagement table.
     * Quotes the internal table identifier and returns an aggregate row
     * count using the database selected for the administrator component.
     *
     * @param string $table Trusted component table identifier.
     * @return int Number of table rows.
     */
    private function countRows(string $table): int
    {
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(*)')
            ->from($db->quoteName($table));

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    /**
     * Resolve Joomla's primary database interface for extension metadata.
     * Validates the administrator context and retrieves the service from
     * Joomla's dependency injection container.
     *
     * @return DatabaseInterface The primary Joomla database.
     * @throws \RuntimeException If the database service is missing.
     */
    private function getJoomlaDatabase(): DatabaseInterface
    {
        $this->administratorApplication();

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$db instanceof DatabaseInterface) {
            throw new \RuntimeException('Joomla database connection is unavailable.');
        }

        return $db;
    }

    /**
     * Render a safely escaped contributor name and optional GitHub profile link.
     * Only creates a link for a nonempty login; both the display text and
     * URL path component are encoded for HTML output.
     *
     * @param string $login Contributor account login.
     * @param string $name Contributor display name.
     * @return string Escaped HTML label or profile link.
     */
    private static function profileLink(string $login, string $name): string
    {
        $label = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

        if ($login === '') {
            return $label;
        }

        return '<a href="https://github.com/' . rawurlencode($login)
            . '" target="_blank" rel="nofollow">' . $label . '</a>';
    }
}