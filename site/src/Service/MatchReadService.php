<?php
namespace Diddipoeler\Component\SportsManagement\Site\Service;

\defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;

final class MatchReadService
{
    public function __construct(private DatabaseInterface $db)
    {
    }

    /**
     * @return array<int,object>
     */
    public function getSingleMatches(int $matchId, ?string $matchNumber = null): array
    {
        if ($matchId <= 0) {
            return [];
        }

        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('m') . '.*')
            ->from($this->db->quoteName('#__sportsmanagement_match_single', 'm'))
            ->where($this->db->quoteName('m.match_id') . ' = ' . $matchId);

        if ($matchNumber !== null) {
            $query->where($this->db->quoteName('m.match_number') . ' = ' . $this->db->quote($matchNumber));
        }

        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    public function getMatch(int $matchId): ?object
    {
        if ($matchId <= 0) {
            return null;
        }

        $query = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('m') . '.*',
                $this->db->quoteName('t1.name', 'hometeam'),
                $this->db->quoteName('t2.name', 'awayteam'),
                $this->db->quoteName('pt1.project_id'),
                $this->db->quoteName('p.season_id'),
                $this->db->quoteName('p.timezone'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $this->db->quoteName('pt1.id') . ' = ' . $this->db->quoteName('m.projectteam1_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $this->db->quoteName('pt2.id') . ' = ' . $this->db->quoteName('m.projectteam2_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $this->db->quoteName('st1.id') . ' = ' . $this->db->quoteName('pt1.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $this->db->quoteName('st2.id') . ' = ' . $this->db->quoteName('pt2.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $this->db->quoteName('t1.id') . ' = ' . $this->db->quoteName('st1.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $this->db->quoteName('t2.id') . ' = ' . $this->db->quoteName('st2.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $this->db->quoteName('p.id') . ' = ' . $this->db->quoteName('pt1.project_id'))
            ->where($this->db->quoteName('m.id') . ' = ' . $matchId);

        $this->db->setQuery($query, 0, 1);
        $row = $this->db->loadObject();

        return $row ?: null;
    }

    /**
     * @param array<int,int> $excludeIds
     * @return array<int,object>
     */
    public function getMatchRelations(int $projectId, array $excludeIds = []): array
    {
        if ($projectId <= 0) {
            return [];
        }

        $query = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('m.id', 'value'),
                $this->db->quoteName('m.match_date'),
                $this->db->quoteName('p.timezone'),
                $this->db->quoteName('t1.name', 't1_name'),
                $this->db->quoteName('t2.name', 't2_name'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $this->db->quoteName('pt1.id') . ' = ' . $this->db->quoteName('m.projectteam1_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $this->db->quoteName('pt2.id') . ' = ' . $this->db->quoteName('m.projectteam2_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $this->db->quoteName('st1.id') . ' = ' . $this->db->quoteName('pt1.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $this->db->quoteName('st2.id') . ' = ' . $this->db->quoteName('pt2.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $this->db->quoteName('t1.id') . ' = ' . $this->db->quoteName('st1.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $this->db->quoteName('t2.id') . ' = ' . $this->db->quoteName('st2.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $this->db->quoteName('p.id') . ' = ' . $this->db->quoteName('pt1.project_id'))
            ->where($this->db->quoteName('pt1.project_id') . ' = ' . $projectId)
            ->where($this->db->quoteName('m.published') . ' = 1')
            ->order($this->db->quoteName('m.match_date') . ' DESC')
            ->order($this->db->quoteName('t1.short_name') . ' ASC');

        $excludeIds = array_values(array_unique(array_filter(array_map('intval', $excludeIds))));
        if ($excludeIds) {
            $query->where($this->db->quoteName('m.id') . ' NOT IN (' . implode(',', $excludeIds) . ')');
        }

        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    /**
     * @return array<int,object>
     */
    public function getEvents(int $matchId): array
    {
        if ($matchId <= 0) {
            return [];
        }

        $query = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('me') . '.*',
                $this->db->quoteName('t.name', 'team'),
                $this->db->quoteName('et.name', 'event'),
                $this->db->quoteName('p.firstname'),
                $this->db->quoteName('p.nickname'),
                $this->db->quoteName('p.lastname'),
            ])
            ->from($this->db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $this->db->quoteName('tp.id') . ' = ' . $this->db->quoteName('me.teamplayer_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $this->db->quoteName('st.team_id') . ' = ' . $this->db->quoteName('tp.team_id') . ' AND ' . $this->db->quoteName('st.season_id') . ' = ' . $this->db->quoteName('tp.season_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $this->db->quoteName('p.id') . ' = ' . $this->db->quoteName('tp.person_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $this->db->quoteName('t.id') . ' = ' . $this->db->quoteName('st.team_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_eventtype', 'et') . ' ON ' . $this->db->quoteName('et.id') . ' = ' . $this->db->quoteName('me.event_type_id'))
            ->where($this->db->quoteName('me.match_id') . ' = ' . $matchId)
            ->order($this->db->quoteName('me.event_time') . ' ASC');

        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }

    /**
     * @return array<int,object>
     */
    public function getReferees(int $matchId): array
    {
        if ($matchId <= 0) {
            return [];
        }

        $query = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('p.id'),
                $this->db->quoteName('pref.id', 'project_referee_id'),
                $this->db->quoteName('p.firstname'),
                $this->db->quoteName('p.nickname'),
                $this->db->quoteName('p.lastname'),
                $this->db->quoteName('pos.name', 'position_name'),
                $this->db->quoteName('mr.project_position_id'),
                $this->db->quoteName('pref.picture'),
                "CONCAT_WS(':', p.id, p.alias) AS person_slug",
            ])
            ->from($this->db->quoteName('#__sportsmanagement_match_referee', 'mr'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project_referee', 'pref') . ' ON ' . $this->db->quoteName('pref.id') . ' = ' . $this->db->quoteName('mr.project_referee_id'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_season_person_id', 'spi') . ' ON ' . $this->db->quoteName('spi.id') . ' = ' . $this->db->quoteName('pref.person_id'))
            ->join('INNER', $this->db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $this->db->quoteName('p.id') . ' = ' . $this->db->quoteName('spi.person_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $this->db->quoteName('ppos.id') . ' = ' . $this->db->quoteName('mr.project_position_id'))
            ->join('LEFT', $this->db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $this->db->quoteName('pos.id') . ' = ' . $this->db->quoteName('ppos.position_id'))
            ->where($this->db->quoteName('mr.match_id') . ' = ' . $matchId)
            ->where($this->db->quoteName('p.published') . ' = 1')
            ->order($this->db->quoteName('pos.name') . ' ASC')
            ->order($this->db->quoteName('mr.ordering') . ' ASC');

        $this->db->setQuery($query);

        return $this->db->loadObjectList() ?: [];
    }
}
