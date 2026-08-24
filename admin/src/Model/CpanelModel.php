<?php
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

    protected function getListQuery()
    {
        // The dashboard does not render a record list, but the legacy view asks
        // ListModel for Items/Pagination. Keep that call harmless on every DB.
        return $this->getDatabase()->getQuery(true)->select('1 AS id')->where('1 = 0');
    }

    public function getVersion(): string
    {
        $db = $this->getJoomlaDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('manifest_cache'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('com_sportsmanagement'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('component'));

        $db->setQuery($query, 0, 1);
        $manifest = json_decode((string) $db->loadResult(), true);

        return is_array($manifest) ? (string) ($manifest['version'] ?? '') : '';
    }

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

    public static function toAscii($repo): string
    {
        $clean = preg_replace("/[^a-zA-Z0-9\\/_|+ '\\-]/", '', (string) $repo) ?? '';
        $clean = strtolower(trim($clean, '-'));

        return preg_replace("/[\\/_|+ '\\-]+/", '-', $clean) ?? '';
    }

    public static function getJSON($req): ?array
    {
        $url = trim((string) $req);

        if (!str_starts_with($url, 'https://api.github.com/')) {
            return null;
        }

        try {
            $response = HttpFactory::getHttp()->get(
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

    public function getInstalledPlugin($plugin): int
    {
        $element = trim((string) $plugin);
        if ($element === '') {
            return 0;
        }

        $extensionType = 'plugin';
        $db = $this->getJoomlaDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = :extensionType')
            ->where($db->quoteName('element') . ' = :element')
            ->bind(':extensionType', $extensionType, ParameterType::STRING)
            ->bind(':element', $element, ParameterType::STRING);

        $db->setQuery($query, 0, 1);

        return (int) $db->loadResult();
    }

    public function checkcountry(): int
    {
        return $this->countRows('#__sportsmanagement_countries');
    }

    public function checksporttype($type): int
    {
        $search = strtoupper(trim((string) $type));
        if ($search === '') {
            return 0;
        }

        $pattern = '%' . $search . '%';
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_sports_type'))
            ->where('UPPER(' . $db->quoteName('name') . ') LIKE :search')
            ->bind(':search', $pattern, ParameterType::STRING);

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    private function countRows(string $table): int
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($table));

        $db->setQuery($query);

        return (int) $db->loadResult();
    }

    private function getJoomlaDatabase(): DatabaseInterface
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        if (!$db instanceof DatabaseInterface) {
            throw new \RuntimeException('Joomla database connection is unavailable.');
        }

        return $db;
    }

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
