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
use Joomla\Database\ParameterType;
use Throwable;

/**
 * Joomla 5/6 data reader for the frontend tournament bracket.
 *
 * Keeps database access separate from the legacy bracket-building algorithm so
 * the latter can be migrated without changing its result semantics at once.
 */
final class TournamentbracketDataModel extends SportsManagementModel
{
    /**
     * Find the league country associated with the selected tournament project.
     * @param int $projectId Selected project identifier.
     * @return string League country code or an empty string.
     */
    public function getProjectCountry(int $projectId): string
    {
        if ($projectId <= 0) {
            return '';
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('l.country'))
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_league', 'l')
                    . ' ON ' . $db->quoteName('p.league_id') . ' = ' . $db->quoteName('l.id')
            )
            ->where($db->quoteName('p.id') . ' = :countryProjectId')
            ->bind(':countryProjectId', $projectId, ParameterType::INTEGER);

        try {
            $db->setQuery($query, 0, 1);
            return (string) ($db->loadResult() ?: '');
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return '';
        }
    }

    /**
     * Load tournament rounds for the selected project in descending order.
     * @param int $projectId Selected project identifier.
     * @return array<int,object> Tournament round records.
     */
    public function getTournamentRounds(int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('r') . '.*')
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = :roundProjectId')
            ->bind(':roundProjectId', $projectId, ParameterType::INTEGER)
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
        $query = $db->createQuery()
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
            ->where($db->quoteName('r.project_id') . ' = :matchProjectId')
            ->bind(':matchProjectId', $projectId, ParameterType::INTEGER)
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
        $query = $db->createQuery()
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
            ->where($db->quoteName('pt.project_id') . ' = :teamProjectId')
            ->bind(':teamProjectId', $projectId, ParameterType::INTEGER);

        try {
            $db->setQuery($query);
            return $db->loadObjectList('projectteamid') ?: [];
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /**
     * Log tournament-bracket query failures without exposing SQL in the frontend.
     * @param Throwable $e Database or query exception.
     * @return void
     */
    private function reportDatabaseError(Throwable $e): void
    {
        \Joomla\CMS\Log\Log::add(
            'SportsManagement tournament bracket query failed: ' . $e->getMessage(),
            \Joomla\CMS\Log\Log::ERROR,
            'com_sportsmanagement'
        );
        $this->siteApplication()->enqueueMessage(
            Text::_('JERROR_AN_ERROR_HAS_OCCURRED'),
            'error'
        );
    }
}
