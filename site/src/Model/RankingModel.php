<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Feed\FeedFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Throwable;

/**
 * Native Joomla 5/6 ranking data model.
 *
 * The ranking calculation itself is still performed by the legacy
 * sportsmanagementModelRanking class. Pure database reads are migrated here
 * incrementally so callers no longer depend on legacy database helpers.
 */
final class RankingModel extends SportsManagementProjectModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
    }

    /**
     * Return the previous matches grouped by project team and division.
     * The shape intentionally matches sportsmanagementModelRanking::getPreviousGames().
     *
     * @return array|false
     */
    public function getPreviousGames(int $roundId = 0)
    {
        if ($this->projectId <= 0) {
            return false;
        }

        $round = $this->resolveRound($roundId);
        if (!$round) {
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'm.*',
                $db->quoteName('r.roundcode'),
                "CASE WHEN CHAR_LENGTH(t1.alias) AND CHAR_LENGTH(t2.alias) THEN CONCAT_WS(':', m.id, CONCAT_WS('_', t1.alias, t2.alias)) ELSE m.id END AS slug",
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt1.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt2.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->where($db->quoteName('r.project_id') . ' = ' . $this->projectId)
            ->where($db->quoteName('r.roundcode') . ' <= ' . (int) $round->roundcode)
            ->where($db->quoteName('m.team1_result') . ' IS NOT NULL')
            ->order($db->quoteName('r.roundcode') . ' ASC');

        try {
            $db->setQuery($query);
            $games = $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }

        $teams = $this->getProjectTeamsIndexed(0);
        $config = $this->getTemplateConfig('ranking');
        $numberOfGames = max(0, (int) ($config['nb_previous'] ?? 0));
        $result = [];

        foreach ($teams as $projectTeamId => $team) {
            $teamGames = [];

            foreach ($games as $game) {
                if (
                    (int) $game->projectteam1_id !== (int) $team->projectteamid
                    && (int) $game->projectteam2_id !== (int) $team->projectteamid
                ) {
                    continue;
                }

                $divisionId = (int) ($game->division_id ?? 0);
                $teamGames[$divisionId][] = $game;
            }

            if (!$teamGames) {
                $result[$projectTeamId] = [];
                continue;
            }

            foreach ($teamGames as $divisionId => $divisionGames) {
                $result[$projectTeamId][$divisionId] = $numberOfGames > 0
                    ? array_slice($divisionGames, -$numberOfGames)
                    : $divisionGames;
            }
        }

        return $result;
    }

    /** Preserve the legacy round-option contract used by ranking forms. */
    public function getRoundOptions(string $ordering = 'ASC'): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $direction = strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';
        $db = $this->getDatabase();
        $matchdayName = Text::_('COM_SPORTSMANAGEMENT_MATCHDAY_NAME');
        $query = $db->getQuery(true)
            ->select([
                "CONCAT_WS(':', id, alias) AS slug",
                $db->quoteName('id', 'value'),
                "CASE LENGTH(name) WHEN 0 THEN CONCAT(" . $db->quote($matchdayName) . ", ' ', id) ELSE CONCAT(name, ' (', round_date_first, ')') END AS text",
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $this->projectId)
            ->order($db->quoteName('roundcode') . ' ' . $direction);

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /** Return the previous round id without Joomla 2.5/3 compatibility branches. */
    public function getPreviousRoundId(int $roundId): int
    {
        if ($roundId <= 0 || $this->projectId <= 0) {
            return $roundId;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . $this->projectId)
            ->order($db->quoteName('roundcode') . ' ASC');

        try {
            $db->setQuery($query);
            $roundIds = array_map('intval', $db->loadColumn() ?: []);
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return $roundId;
        }

        $index = array_search($roundId, $roundIds, true);

        return is_int($index) && $index > 0
            ? (int) $roundIds[$index - 1]
            : $roundId;
    }

    /**
     * Load one or more RSS/Atom feeds through Joomla's namespaced FeedFactory.
     * Invalid feeds are skipped and reported using the existing frontend notice.
     */
    public function getRssFeeds(string $rssFeedLink, int $rssItems = 0): array
    {
        $feedFactory = new FeedFactory();
        $feeds = [];
        $limit = max(0, $rssItems);

        foreach (array_filter(array_map('trim', explode(',', $rssFeedLink))) as $rssId) {
            try {
                $feed = $feedFactory->getFeed($rssId);
                if ($limit > 0 && isset($feed->entries) && is_array($feed->entries)) {
                    $feed->entries = array_slice($feed->entries, 0, $limit);
                }
                $feeds[] = $feed;
            } catch (Throwable $e) {
                $this->siteApplication()->enqueueMessage(
                    Text::_('COM_NEWSFEEDS_ERRORS_FEED_NOT_RETRIEVED'),
                    'notice'
                );
            }
        }

        return $feeds;
    }

    /** Preserve the legacy id-keyed division list. */
    public function getDivisions(int $divisionLevel = 0): array
    {
        $project = $this->getProject();
        if (!$project || ($project->project_type ?? '') !== 'DIVISIONS_LEAGUE') {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('project_id') . ' = ' . $this->projectId)
            ->where($db->quoteName('published') . ' = 1')
            ->order($db->quoteName('ordering') . ' ASC');

        if ($divisionLevel === 1) {
            $query->where('(' . $db->quoteName('parent_id') . ' = 0 OR ' . $db->quoteName('parent_id') . ' IS NULL)');
        } elseif ($divisionLevel === 2) {
            $query->where($db->quoteName('parent_id') . ' > 0');
        }

        try {
            $db->setQuery($query);
            return $db->loadObjectList('id') ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    public function getProjectTeamsIndexed(int $divisionId = 0): array
    {
        $teams = [];
        foreach ($this->getProjectTeams($divisionId) as $team) {
            $projectTeamId = (int) ($team->projectteamid ?? 0);
            if ($projectTeamId > 0) {
                $teams[$projectTeamId] = $team;
            }
        }
        return $teams;
    }

    public function getLogoHistory(int $seasonId, int $teamId): array
    {
        if ($seasonId <= 0 || $teamId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'cl.*',
                $db->quoteName('se.name', 'seasonname'),
            ])
            ->from($db->quoteName('#__sportsmanagement_club_logos', 'cl'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season', 'se') . ' ON ' . $db->quoteName('se.id') . ' = ' . $db->quoteName('cl.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('cl.club_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id'))
            ->where($db->quoteName('t.id') . ' = ' . $teamId)
            ->where($db->quoteName('se.id') . ' = ' . $seasonId)
            ->order($db->quoteName('se.name') . ' DESC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /** Preserve the legacy ranking color configuration structure. */
    public function parseColors(string $configColors = ''): array
    {
        $colors = [[
            'from' => '',
            'to' => '',
            'color' => '',
            'description' => '',
        ]];

        if (trim($configColors) === '') {
            return $colors;
        }

        foreach (explode(';', $configColors) as $index => $entry) {
            $parts = explode(',', $entry);
            if (count($parts) !== 4) {
                break;
            }

            $colors[$index] = [
                'from' => $parts[0],
                'to' => $parts[1],
                'color' => $parts[2],
                'description' => $parts[3],
            ];
        }

        return $colors;
    }

    private function resolveRound(int $roundId): ?object
    {
        if ($roundId <= 0) {
            $roundId = $this->getCurrentRound();
        }

        if ($roundId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('roundcode'),
                "CONCAT_WS(':', id, alias) AS round_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = ' . $roundId)
            ->where($db->quoteName('project_id') . ' = ' . $this->projectId);

        try {
            $db->setQuery($query, 0, 1);
            $round = $db->loadObject();
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return null;
        }

        if ($round) {
            return $round;
        }

        $currentRoundId = $this->getCurrentRound();
        if ($currentRoundId <= 0 || $currentRoundId === $roundId) {
            return null;
        }

        $query->clear()
            ->select([
                $db->quoteName('id'),
                $db->quoteName('roundcode'),
                "CONCAT_WS(':', id, alias) AS round_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = ' . $currentRoundId)
            ->where($db->quoteName('project_id') . ' = ' . $this->projectId);

        try {
            $db->setQuery($query, 0, 1);
            return $db->loadObject() ?: null;
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return null;
        }
    }

    private function reportDatabaseError(Throwable $e): void
    {
        $this->siteApplication()->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
            'error'
        );
    }
}
