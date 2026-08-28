<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Throwable;

/**
 * Native Joomla 5/6 reader for supplementary results-view data.
 *
 * Match events, substitutions and referees are loaded in batches so templates
 * do not execute database queries inside their per-match rendering loops.
 */
final class ResultsViewDataModel extends SportsManagementProjectModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
    }

    public function setProjectId(int $projectId): void
    {
        $this->projectId = max(0, $projectId);
    }

    /**
     * @param array<int, int> $matchIds
     * @return array<int, array<int, object>>
     */
    public function getMatchEvents(array $matchIds): array
    {
        $ids = $this->normaliseIds($matchIds);
        if ($ids === []) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('me.match_id'),
                $db->quoteName('me.event_type_id'),
                $db->quoteName('me.id', 'event_id'),
                $db->quoteName('me.event_time'),
                $db->quoteName('me.notice'),
                $db->quoteName('me.projectteam_id', 'ptid'),
                $db->quoteName('me.event_sum'),
                $db->quoteName('et.name', 'eventtype_name'),
                $db->quoteName('et.icon', 'eventtype_icon'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('tp.picture', 'tppicture1'),
                $db->quoteName('p.firstname', 'firstname1'),
                $db->quoteName('p.nickname', 'nickname1'),
                $db->quoteName('p.lastname', 'lastname1'),
                $db->quoteName('p.picture', 'picture1'),
                $db->quoteName('p.id', 'playerid'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_eventtype', 'et') . ' ON ' . $db->quoteName('et.id') . ' = ' . $db->quoteName('me.event_type_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.id') . ' = ' . $db->quoteName('me.projectteam_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('tp.id') . ' = ' . $db->quoteName('me.teamplayer_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('tp.person_id'))
            ->where($db->quoteName('me.match_id') . ' IN (' . implode(',', $ids) . ')')
            ->where('COALESCE(' . $db->quoteName('p.published') . ', 1) = 1')
            ->order([
                $db->quoteName('me.match_id') . ' ASC',
                '(' . $db->quoteName('me.event_time') . ' + 0) ASC',
                $db->quoteName('me.event_type_id') . ' ASC',
                $db->quoteName('me.id') . ' ASC',
            ]);

        try {
            $db->setQuery($query);
            return $this->groupByMatchId($db->loadObjectList() ?: []);
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /**
     * @param array<int, int> $matchIds
     * @return array<int, array<int, object>>
     */
    public function getMatchSubstitutions(array $matchIds): array
    {
        $ids = $this->normaliseIds($matchIds);
        if ($ids === []) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('mp.match_id'),
                $db->quoteName('mp.in_out_time'),
                $db->quoteName('pt.id', 'ptid'),
                $db->quoteName('pin.firstname', 'firstname'),
                $db->quoteName('pin.nickname', 'nickname'),
                $db->quoteName('pin.lastname', 'lastname'),
                $db->quoteName('pout.firstname', 'out_firstname'),
                $db->quoteName('pout.nickname', 'out_nickname'),
                $db->quoteName('pout.lastname', 'out_lastname'),
                $db->quoteName('posin.name', 'in_position'),
                $db->quoteName('posout.name', 'out_position'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('mp.match_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tpin') . ' ON ' . $db->quoteName('tpin.id') . ' = ' . $db->quoteName('mp.teamplayer_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'pin') . ' ON ' . $db->quoteName('pin.id') . ' = ' . $db->quoteName('tpin.person_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tpout') . ' ON ' . $db->quoteName('tpout.id') . ' = ' . $db->quoteName('mp.in_for'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_person', 'pout') . ' ON ' . $db->quoteName('pout.id') . ' = ' . $db->quoteName('tpout.person_id'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tpin.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tpin.season_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
                . ' AND ' . $db->quoteName('pt.project_id') . ' = ' . $db->quoteName('r.project_id')
            )
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'posin') . ' ON ' . $db->quoteName('posin.id') . ' = ' . $db->quoteName('mp.project_position_id'))
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_match_player', 'mpout')
                . ' ON ' . $db->quoteName('mpout.match_id') . ' = ' . $db->quoteName('mp.match_id')
                . ' AND ' . $db->quoteName('mpout.teamplayer_id') . ' = ' . $db->quoteName('mp.in_for')
            )
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'posout') . ' ON ' . $db->quoteName('posout.id') . ' = ' . $db->quoteName('mpout.project_position_id'))
            ->where($db->quoteName('mp.match_id') . ' IN (' . implode(',', $ids) . ')')
            ->where($db->quoteName('mp.came_in') . ' > 0')
            ->order([
                $db->quoteName('mp.match_id') . ' ASC',
                $db->quoteName('mp.in_out_time') . ' ASC',
            ]);

        try {
            $db->setQuery($query);
            return $this->groupByMatchId($db->loadObjectList() ?: []);
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /**
     * @param array<int, int> $matchIds
     * @return array<int, array<int, object>>
     */
    public function getMatchReferees(array $matchIds, bool $teamsAsReferees = false): array
    {
        $ids = $this->normaliseIds($matchIds);
        if ($ids === []) {
            return [];
        }

        return $teamsAsReferees
            ? $this->getMatchRefereeTeams($ids)
            : $this->getMatchRefereePersons($ids);
    }

    /** @return array<int, array<int, object>> */
    private function getMatchRefereePersons(array $ids): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('mr.match_id'),
                $db->quoteName('p.id'),
                $db->quoteName('p.firstname'),
                $db->quoteName('p.nickname'),
                $db->quoteName('p.lastname'),
                $db->quoteName('pos.name', 'position_name'),
                $db->quoteName('mr.project_position_id'),
                $db->quoteName('pref.picture'),
                "CONCAT_WS(':', p.id, p.alias) AS person_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_match_referee', 'mr'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_referee', 'pref') . ' ON ' . $db->quoteName('pref.id') . ' = ' . $db->quoteName('mr.project_referee_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_person_id', 'spi') . ' ON ' . $db->quoteName('spi.id') . ' = ' . $db->quoteName('pref.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('spi.person_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('mr.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('mr.match_id') . ' IN (' . implode(',', $ids) . ')')
            ->where($db->quoteName('p.published') . ' = 1')
            ->order([
                $db->quoteName('mr.match_id') . ' ASC',
                $db->quoteName('pos.name') . ' ASC',
                $db->quoteName('mr.ordering') . ' ASC',
            ]);

        try {
            $db->setQuery($query);
            return $this->groupByMatchId($db->loadObjectList() ?: []);
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /** @return array<int, array<int, object>> */
    private function getMatchRefereeTeams(array $ids): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('mr.match_id'),
                $db->quoteName('mr.project_referee_id', 'value'),
                $db->quoteName('t.name', 'teamname'),
                $db->quoteName('pos.name', 'position_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_referee', 'mr'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.id') . ' = ' . $db->quoteName('mr.project_referee_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('mr.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('mr.match_id') . ' IN (' . implode(',', $ids) . ')')
            ->order([
                $db->quoteName('mr.match_id') . ' ASC',
                $db->quoteName('pos.name') . ' ASC',
                $db->quoteName('mr.ordering') . ' ASC',
            ]);

        try {
            $db->setQuery($query);
            return $this->groupByMatchId($db->loadObjectList() ?: []);
        } catch (Throwable $e) {
            $this->reportDatabaseError($e);
            return [];
        }
    }

    /** @param array<int, object> $rows */
    private function groupByMatchId(array $rows): array
    {
        $grouped = [];
        foreach ($rows as $row) {
            $matchId = (int) ($row->match_id ?? 0);
            if ($matchId > 0) {
                $grouped[$matchId][] = $row;
            }
        }

        return $grouped;
    }

    /** @return array<int, int> */
    private function normaliseIds(array $values): array
    {
        $ids = [];
        foreach ($values as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    private function reportDatabaseError(Throwable $e): void
    {
        Factory::getApplication()->enqueueMessage(
            Text::sprintf(
                'COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED',
                $e->getCode(),
                $e->getMessage()
            ),
            'error'
        );
    }
}
