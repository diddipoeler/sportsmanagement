<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

/**
 * Native Joomla 5/6 read service for the frontend edit-match view.
 *
 * The historical edit view mixed the component-selected SportsManagement DB
 * for project context with Joomla's default DB for administrator Match reads.
 * Both are injected explicitly so the migration preserves that behaviour.
 */
final class EditmatchViewDataService
{
    public function __construct(
        private readonly DatabaseInterface $joomlaDatabase,
        private readonly DatabaseInterface $sportsDatabase
    ) {
    }

    public function getProjectContext(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }

        $db = $this->sportsDatabase;
        $query = $db->getQuery(true)
            ->select([
                'p.*',
                $db->quoteName('l.country'),
                $db->quoteName('st.id', 'sport_type_id'),
                $db->quoteName('st.name', 'sport_type_name'),
                $db->quoteName('st.icon', 'sport_type_picture'),
                $db->quoteName('st.eventtime', 'useeventtime'),
                $db->quoteName('l.picture', 'leaguepicture'),
                $db->quoteName('l.name', 'league_name'),
                $db->quoteName('s.name', 'season_name'),
                $db->quoteName('r.name', 'round_name'),
                $db->quoteName('l.cr_picture', 'cr_leaguepicture'),
                $db->quoteName('l.champions_complete'),
                $db->quoteName('asso.name', 'assoname'),
                "CONCAT_WS(':', p.id, p.alias) AS slug",
                "CONCAT_WS(':', l.id, l.alias) AS league_slug",
                "CONCAT_WS(':', s.id, s.alias) AS season_slug",
                "CONCAT_WS(':', r.id, r.alias) AS round_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_sports_type', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('p.sports_type_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('p.current_round'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_associations', 'asso') . ' ON ' . $db->quoteName('asso.id') . ' = ' . $db->quoteName('l.associations'))
            ->where($db->quoteName('p.id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);

        $project = $db->loadObject();

        if (!$project) {
            return null;
        }

        $sportName = (string) ($project->sport_type_name ?? '');
        $prefix = 'COM_SPORTSMANAGEMENT_ST_';
        $project->fs_sport_type_name = strtolower(str_starts_with($sportName, $prefix) ? substr($sportName, strlen($prefix)) : $sportName);

        return $project;
    }

    public function getRound(int $roundId): ?object
    {
        if ($roundId <= 0) {
            return null;
        }

        $db = $this->sportsDatabase;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('roundcode'),
                $db->quoteName('project_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('id') . ' = ' . $roundId);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    /** @return array<int,object> */
    public function getMatchRelationsOptions(int $projectId, array $excludeMatchIds = []): array
    {
        if ($projectId <= 0) {
            return [];
        }

        // Preserve sportsmanagementModelMatch::getMatchRelationsOptions(): it
        // explicitly used Joomla's default DB rather than getDBConnection().
        $db = $this->joomlaDatabase;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id', 'value'),
                $db->quoteName('m.match_date'),
                $db->quoteName('p.timezone'),
                $db->quoteName('t1.name', 't1_name'),
                $db->quoteName('t2.name', 't2_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt1.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt2.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt1.project_id'))
            ->where($db->quoteName('pt1.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('m.published') . ' = 1')
            ->order($db->quoteName('m.match_date') . ' DESC')
            ->order($db->quoteName('t1.short_name') . ' ASC');

        $excludeMatchIds = array_values(array_filter(array_map('intval', $excludeMatchIds)));

        if ($excludeMatchIds !== []) {
            $query->where($db->quoteName('m.id') . ' NOT IN (' . implode(',', $excludeMatchIds) . ')');
        }

        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    /**
     * Preserve sportsmanagementModelMatch::getMatchPersons() for the remaining
     * Golf/Billard edit template without loading the administrator Match model.
     *
     * @return array<int,object>
     */
    public function getMatchPersons(int $projectTeamId, int $matchId): array
    {
        if ($projectTeamId <= 0 || $matchId <= 0) {
            return [];
        }

        $db = $this->joomlaDatabase;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('mp.teamplayer_id', 'tpid'),
                $db->quoteName('mp.teamplayer_id'),
                $db->quoteName('mp.project_position_id'),
                $db->quoteName('mp.match_id'),
                $db->quoteName('mp.id', 'update_id'),
                $db->quoteName('mp.trikot_number'),
                $db->quoteName('pt.id', 'projectteam_id'),
                $db->quoteName('sp.id', 'value'),
                $db->quoteName('pl.firstname'),
                $db->quoteName('pl.nickname'),
                $db->quoteName('pl.lastname'),
                $db->quoteName('pl.knvbnr'),
                $db->quoteName('pos.name', 'positionname'),
                $db->quoteName('ppos.position_id'),
                $db->quoteName('ppos.id', 'pposid'),
                $db->quoteName('mp.ordering', 'playerordering'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'sp') . ' ON ' . $db->quoteName('mp.teamplayer_id') . ' = ' . $db->quoteName('sp.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('sp.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_person_project_position', 'ppp')
                . ' ON ' . $db->quoteName('ppp.person_id') . ' = ' . $db->quoteName('sp.person_id')
                . ' AND ' . $db->quoteName('ppp.persontype') . ' = ' . $db->quoteName('sp.persontype')
                . ' AND ' . $db->quoteName('ppp.project_id') . ' = ' . $db->quoteName('pt.project_id')
            )
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('ppp.project_position_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'pl') . ' ON ' . $db->quoteName('pl.id') . ' = ' . $db->quoteName('sp.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('mp.match_id') . ' = ' . $matchId)
            ->where($db->quoteName('pl.published') . ' = 1')
            ->where($db->quoteName('pt.id') . ' = ' . $projectTeamId)
            ->order($db->quoteName('mp.project_position_id') . ' ASC')
            ->order($db->quoteName('mp.ordering') . ' ASC')
            ->order($db->quoteName('pl.lastname') . ' ASC')
            ->order($db->quoteName('pl.firstname') . ' ASC');
        $db->setQuery($query);
        $rows = $db->loadObjectList('teamplayer_id') ?: [];

        foreach ($rows as $row) {
            $row->text = trim((string) ($row->firstname ?? '') . ':' . (string) ($row->lastname ?? ''));
        }

        return $rows;
    }

    /** @return array<int,object> */
    public function getProjectPositionsOptions(int $projectId, int $personType): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->joomlaDatabase;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('ppos.id', 'value'),
                $db->quoteName('pos.name', 'text'),
                $db->quoteName('pos.id', 'posid'),
                $db->quoteName('pos.id', 'pposid'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position', 'pos'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pos.id'))
            ->where($db->quoteName('ppos.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('pos.persontype') . ' = ' . $personType)
            ->order($db->quoteName('pos.ordering') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList('value') ?: [];
    }

    /** @return array<int,object> */
    public function getProjectReferees(array $alreadySelected, int $projectId): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $db = $this->joomlaDatabase;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pref.id', 'value'),
                $db->quoteName('pl.firstname'),
                $db->quoteName('pl.nickname'),
                $db->quoteName('pl.lastname'),
                $db->quoteName('pl.info'),
                $db->quoteName('pos.name', 'positionname'),
            ])
            ->from($db->quoteName('#__sportsmanagement_person', 'pl'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_person_id', 'spi') . ' ON ' . $db->quoteName('spi.person_id') . ' = ' . $db->quoteName('pl.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_referee', 'pref') . ' ON ' . $db->quoteName('pref.person_id') . ' = ' . $db->quoteName('spi.id') . ' AND ' . $db->quoteName('pref.published') . ' = 1')
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('pref.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('pref.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('pl.published') . ' = 1')
            ->order($db->quoteName('pl.lastname') . ' ASC');

        $alreadySelected = array_values(array_filter(array_map('intval', $alreadySelected)));

        if ($alreadySelected !== []) {
            $query->where($db->quoteName('pref.id') . ' NOT IN (' . implode(',', $alreadySelected) . ')');
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList('value') ?: [];

        foreach ($rows as $row) {
            $row->text = trim((string) ($row->firstname ?? '') . ' - ' . (string) ($row->lastname ?? ''));
        }

        return $rows;
    }

    /** @return array<int,object> */
    public function getRefereeRoster(int $projectPositionId, int $matchId): array
    {
        if ($matchId <= 0) {
            return [];
        }

        $db = $this->joomlaDatabase;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pref.id', 'value'),
                $db->quoteName('pref.id', 'project_referee_id'),
                $db->quoteName('mr.project_position_id'),
                $db->quoteName('pl.firstname'),
                $db->quoteName('pl.nickname'),
                $db->quoteName('pl.lastname'),
                $db->quoteName('pos.name', 'positionname'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_referee', 'mr'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_referee', 'pref') . ' ON ' . $db->quoteName('pref.id') . ' = ' . $db->quoteName('mr.project_referee_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_person_id', 'spi') . ' ON ' . $db->quoteName('spi.id') . ' = ' . $db->quoteName('pref.person_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_person', 'pl') . ' ON ' . $db->quoteName('pl.id') . ' = ' . $db->quoteName('spi.person_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('mr.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('mr.match_id') . ' = ' . $matchId)
            ->order($db->quoteName('mr.ordering') . ' ASC');

        if ($projectPositionId > 0) {
            $query->where($db->quoteName('mr.project_position_id') . ' = ' . $projectPositionId);
        }

        $db->setQuery($query);

        return $db->loadObjectList('value') ?: [];
    }
}
