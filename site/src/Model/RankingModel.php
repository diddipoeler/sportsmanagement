<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
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

        $teams = [];
        foreach ($this->getProjectTeams(0) as $team) {
            $projectTeamId = (int) ($team->projectteamid ?? 0);
            if ($projectTeamId > 0) {
                $teams[$projectTeamId] = $team;
            }
        }

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
        Factory::getApplication()->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
            'error'
        );
    }
}
