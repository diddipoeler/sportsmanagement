<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Log\Log;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/** Import Inline Hockey clubs and teams without the legacy extension model. */
final class InlineHockeyClubTeamImportService
{
    private const SPORTS_TYPE = 'COM_SPORTSMANAGEMENT_ST_SKATER_HOCKEY';
    private const CLUBS_URL = 'https://www.ishd.de/vereine.json';

    public function __construct(
        private readonly DatabaseInterface $db,
        private readonly InlineHockeyApiClient $api
    ) {
    }

    public function importClubs(string $username = '', string $password = ''): int
    {
        $firstPage = $this->api->fetchJson(self::CLUBS_URL, $username, $password);
        $pages = max(1, (int) ($firstPage->pages ?? 1));
        $imported = 0;

        for ($page = 1; $page <= $pages; $page++) {
            $payload = $page === 1
                ? $firstPage
                : $this->api->fetchJson($this->api->pageUrl(self::CLUBS_URL, $page), $username, $password);
            $clubs = $payload->_embedded->clubs ?? [];

            if (!is_iterable($clubs)) {
                continue;
            }

            foreach ($clubs as $club) {
                if (!is_object($club)) {
                    continue;
                }

                $clubId = (int) ($club->id ?? 0);
                $name = trim((string) ($club->name ?? ''));

                if ($clubId <= 0 || $name === '' || $this->clubExists($clubId)) {
                    continue;
                }

                $record = (object) [
                    'id' => $clubId,
                    'name' => $name,
                    'country' => 'DEU',
                    'alias' => OutputFilter::stringURLSafe($name),
                ];
                $this->db->insertObject('#__sportsmanagement_club', $record);
                $imported++;
            }
        }

        return $imported;
    }

    public function importTeams(string $username = '', string $password = ''): int
    {
        $sportsTypeId = $this->ensureSportsType();

        if ($sportsTypeId <= 0) {
            return 0;
        }

        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_club'))
            ->order($this->db->quoteName('id') . ' ASC');
        $this->db->setQuery($query);
        $clubIds = array_map('intval', (array) $this->db->loadColumn());
        $changed = 0;

        foreach ($clubIds as $clubId) {
            if ($clubId <= 0) {
                continue;
            }

            $url = 'https://www.ishd.de/api/licenses/clubs/' . $clubId . '/teams.json';

            try {
                $payload = $this->api->fetchJson($url, $username, $password);
            } catch (\Throwable $exception) {
                Log::add($exception->getMessage(), Log::WARNING, 'jsmerror');
                continue;
            }

            $teams = $payload->teams ?? [];

            if (!is_iterable($teams)) {
                continue;
            }

            foreach ($teams as $team) {
                if (!is_object($team)) {
                    continue;
                }

                $teamId = (int) ($team->team_id ?? 0);
                $name = trim((string) ($team->team ?? ''));

                if ($teamId <= 0 || $name === '') {
                    continue;
                }

                $record = (object) [
                    'id' => $teamId,
                    'club_id' => $clubId,
                    'name' => $name,
                    'short_name' => $name,
                    'middle_name' => $name,
                    'info' => trim((string) ($team->team_name ?? '')),
                    'sports_type_id' => $sportsTypeId,
                    'alias' => OutputFilter::stringURLSafe($name),
                ];

                if ($this->teamExists($teamId)) {
                    $this->db->updateObject('#__sportsmanagement_team', $record, 'id');
                } else {
                    $this->db->insertObject('#__sportsmanagement_team', $record);
                }

                $changed++;
            }
        }

        return $changed;
    }

    private function ensureSportsType(): int
    {
        $name = self::SPORTS_TYPE;
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_sports_type'))
            ->where($this->db->quoteName('name') . ' = :name')
            ->bind(':name', $name, ParameterType::STRING);
        $this->db->setQuery($query, 0, 1);
        $id = (int) ($this->db->loadResult() ?: 0);

        if ($id > 0) {
            return $id;
        }

        $record = (object) ['name' => $name];
        $this->db->insertObject('#__sportsmanagement_sports_type', $record);

        return (int) $this->db->insertid();
    }

    private function clubExists(int $clubId): bool
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_club'))
            ->where($this->db->quoteName('id') . ' = :clubId')
            ->bind(':clubId', $clubId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);

        return (int) $this->db->loadResult() > 0;
    }

    private function teamExists(int $teamId): bool
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__sportsmanagement_team'))
            ->where($this->db->quoteName('id') . ' = :teamId')
            ->bind(':teamId', $teamId, ParameterType::INTEGER);
        $this->db->setQuery($query, 0, 1);

        return (int) $this->db->loadResult() > 0;
    }
}
