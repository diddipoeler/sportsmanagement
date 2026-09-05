<?php
/**
 * Joomla 5/6 data reader for the frontend tournament bracket.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Throwable;

/**
 * Joomla 5/6 data reader for the frontend tournament bracket.
 *
 * Keeps database access separate from the legacy bracket-building algorithm so
 * the latter can be migrated without changing its result semantics at once.
 */
final class TournamentbracketDataModel extends SportsManagementModel
{
    public function getProjectCountry(int $projectId): string
    {
        if ($projectId <= 0) {
            return '';
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('l.country'))
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_league', 'l')
                    . ' ON ' . $db->quoteName('p.league_id') . ' = ' . $db->quoteName('l.id')
            )
            ->where($db->quoteName('p.id') . ' = ' . $projectId);

        try {
            $db->setQuery($query, 0, 1);
            return (string) ($db->loadResult() ?: '');
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return '';
        }
    }

    public function getTournamentRounds(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('r') . '.*')
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('r.tournement') . ' = 1')
            ->order($db->quoteName('r.roundcode') . ' DESC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /**
     * Return all published tournament matches keyed by round id.
     */
    public function getPublishedMatchesByRound(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m') . '.*',
                $db->quoteName('r.roundcode'),
                $db->quoteName('r.name', 'round_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                    . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->where($db->quoteName('r.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('r.tournement') . ' = 1')
            ->where($db->quoteName('m.published') . ' = 1')
            ->order([
                $db->quoteName('r.roundcode') . ' DESC',
                $db->quoteName('m.id') . ' ASC',
            ]);

        try {
            $db->setQuery($query);
            $matches = $db->loadObjectList() ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }

        $grouped = [];
        foreach ($matches as $match) {
            $grouped[(int) $match->round_id][] = $match;
        }

        return $grouped;
    }

    /**
     * Return all project-team presentation data keyed by projectteam id.
     */
    public function getProjectTeamInfo(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt.id', 'projectteamid'),
                $db->quoteName('t.name'),
                $db->quoteName('c.logo_big'),
                $db->quoteName('c.country'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                    . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_team', 't')
                    . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_club', 'c')
                    . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id')
            )
            ->where($db->quoteName('pt.project_id') . ' = ' . $projectId);

        try {
            $db->setQuery($query);
            return $db->loadObjectList('projectteamid') ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    private function reportDatabaseError(Throwable $e): void
    {
        $this->siteApplication()->enqueueMessage(
            Text::sprintf(
                'COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED',
                $e->getCode(),
                $e->getMessage()
            ),
            'error'
        );
    }
}
