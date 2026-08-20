<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/** Native Joomla 5/6 administrator list model for matches. */
final class MatchesModel extends SportsManagementListModel
{
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            'mc.match_number', 'match_number',
            'mc.match_date', 'match_date',
            'mc.id', 'id',
            'mc.ordering', 'ordering',
            'mc.published', 'published', 'state',
        ];
        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 'mc.match_date', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = Factory::getApplication();
        $input = $app->getInput();
        $option = 'com_sportsmanagement';

        $projectId = $input->getInt('pid') ?: (int) $app->getUserState($option . '.pid', 0);
        $roundId = $input->getInt('rid') ?: (int) $app->getUserState($option . '.rid', 0);
        $projectTeamId = $input->getInt('projectteam');
        $seasonId = $input->getInt('season_id') ?: (int) $app->getUserState($option . '.season_id', 0);

        if ($projectTeamId > 0) {
            $roundId = 0;
        }

        $this->setState('context.project_id', $projectId);
        $this->setState('context.round_id', $roundId);
        $this->setState('context.project_team_id', $projectTeamId);
        $this->setState('context.season_id', $seasonId);
        $this->setState(
            'filter.division',
            $this->getUserStateFromRequest($this->context . '.filter.division', 'filter_division', 0, 'int')
        );

        if ($projectId > 0) {
            $app->setUserState($option . '.pid', $projectId);
        }
        if ($roundId > 0) {
            $app->setUserState($option . '.rid', $roundId);
        }
        if ($seasonId > 0) {
            $app->setUserState($option . '.season_id', $seasonId);
        }
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $roundId = (int) $this->getState('context.round_id');
        $projectTeamId = (int) $this->getState('context.project_team_id');
        $divisionId = (int) $this->getState('filter.division');

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('mc') . '.*',
                $db->quoteName('u.name', 'editor'),
                $db->quoteName('divhome.id', 'divhomeid'),
                $db->quoteName('divaway.id', 'divawayid'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'mc'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'u')
                . ' ON ' . $db->quoteName('u.id') . ' = ' . $db->quoteName('mc.checked_out')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'pthome')
                . ' ON ' . $db->quoteName('pthome.id') . ' = ' . $db->quoteName('mc.projectteam1_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_project_team', 'ptaway')
                . ' ON ' . $db->quoteName('ptaway.id') . ' = ' . $db->quoteName('mc.projectteam2_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_division', 'divhome')
                . ' ON ' . $db->quoteName('divhome.id') . ' = ' . $db->quoteName('pthome.division_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__sportsmanagement_division', 'divaway')
                . ' ON ' . $db->quoteName('divaway.id') . ' = ' . $db->quoteName('ptaway.division_id')
            );

        if ($roundId > 0) {
            $query->where($db->quoteName('mc.round_id') . ' = ' . $roundId);
        }

        if ($projectTeamId > 0) {
            $query->where(
                '(' . $db->quoteName('mc.projectteam1_id') . ' = ' . $projectTeamId
                . ' OR ' . $db->quoteName('mc.projectteam2_id') . ' = ' . $projectTeamId . ')'
            );
        }

        if ($divisionId > 0) {
            $query->where(
                '(' . $db->quoteName('divhome.id') . ' = ' . $divisionId
                . ' OR ' . $db->quoteName('divaway.id') . ' = ' . $divisionId . ')'
            );
        }

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            if (ctype_digit($search)) {
                $query->where(
                    '(' . $db->quoteName('mc.id') . ' = ' . (int) $search
                    . ' OR ' . $db->quoteName('mc.match_number') . ' = ' . (int) $search . ')'
                );
            } else {
                $needle = $db->quote('%' . $db->escape($search, true) . '%', false);
                $query->where(
                    '(' . $db->quoteName('mc.notes') . ' LIKE ' . $needle
                    . ' OR ' . $db->quoteName('mc.matchcode') . ' LIKE ' . $needle . ')'
                );
            }
        }

        $state = $this->getState('filter.state');
        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('mc.published') . ' = ' . (int) $state);
        }

        $orderMap = [
            'mc.match_number' => $db->quoteName('mc.match_number'),
            'match_number' => $db->quoteName('mc.match_number'),
            'mc.match_date' => $db->quoteName('mc.match_date'),
            'match_date' => $db->quoteName('mc.match_date'),
            'mc.id' => $db->quoteName('mc.id'),
            'id' => $db->quoteName('mc.id'),
            'mc.ordering' => $db->quoteName('mc.ordering'),
            'ordering' => $db->quoteName('mc.ordering'),
            'mc.published' => $db->quoteName('mc.published'),
            'published' => $db->quoteName('mc.published'),
            'state' => $db->quoteName('mc.published'),
        ];
        $ordering = (string) $this->getState('list.ordering', 'mc.match_date');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($orderMap[$ordering] ?? $orderMap['mc.match_date']) . ' ' . $direction);

        return $query;
    }

    /** Add roster/referee counters used by the administrator match list. */
    public function prepareItems($items)
    {
        $seasonId = (int) $this->getState('context.season_id');

        foreach ((array) $items as $item) {
            $matchId = (int) ($item->id ?? 0);
            $homeProjectTeamId = (int) ($item->projectteam1_id ?? 0);
            $awayProjectTeamId = (int) ($item->projectteam2_id ?? 0);

            $item->homeplayers_count = $this->countRosterRows(
                '#__sportsmanagement_match_player',
                'teamplayer_id',
                $matchId,
                $homeProjectTeamId,
                $seasonId,
                1,
                true
            );
            $item->homestaff_count = $this->countRosterRows(
                '#__sportsmanagement_match_staff',
                'team_staff_id',
                $matchId,
                $homeProjectTeamId,
                $seasonId,
                2
            );
            $item->awayplayers_count = $this->countRosterRows(
                '#__sportsmanagement_match_player',
                'teamplayer_id',
                $matchId,
                $awayProjectTeamId,
                $seasonId,
                1,
                true
            );
            $item->awaystaff_count = $this->countRosterRows(
                '#__sportsmanagement_match_staff',
                'team_staff_id',
                $matchId,
                $awayProjectTeamId,
                $seasonId,
                2
            );

            $db = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select([
                    'COUNT(' . $db->quoteName('id') . ') AS ' . $db->quoteName('total'),
                    'MIN(' . $db->quoteName('project_referee_id') . ') AS ' . $db->quoteName('referee_id'),
                ])
                ->from($db->quoteName('#__sportsmanagement_match_referee'))
                ->where($db->quoteName('match_id') . ' = ' . $matchId);
            $db->setQuery($query);
            $referee = $db->loadObject();
            $item->referees_count = (int) ($referee->total ?? 0);
            $item->referee_id = (int) ($referee->referee_id ?? 0);
        }

        return $items;
    }

    public function checkMatchPicturePath($matchId): bool
    {
        $matchId = (int) $matchId;
        if ($matchId <= 0) {
            return false;
        }

        $folder = 'matchreport/' . $matchId;
        $destination = JPATH_ROOT . '/images/com_sportsmanagement/database/' . $folder;
        $this->setState('folder', $folder);

        return is_dir($destination) || @mkdir($destination, 0775, true) || is_dir($destination);
    }

    public function getMatchesCount($projectId = 0, $projectTeamId = 0)
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(' . $db->quoteName('m.id') . ')')
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->where($db->quoteName('r.project_id') . ' = ' . (int) $projectId);

        if ((int) $projectTeamId > 0) {
            $query->where(
                '(' . $db->quoteName('m.projectteam1_id') . ' = ' . (int) $projectTeamId
                . ' OR ' . $db->quoteName('m.projectteam2_id') . ' = ' . (int) $projectTeamId . ')'
            );
        }

        $db->setQuery($query);
        return (int) $db->loadResult();
    }

    public function getMatchesByRound($roundId)
    {
        $roundId = (int) $roundId;
        if ($roundId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('m') . '.*')
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->where($db->quoteName('m.round_id') . ' = ' . $roundId)
            ->order($db->quoteName('m.match_date') . ' ASC');
        $db->setQuery($query);

        return $db->loadObjectList() ?: [];
    }

    public function getContextParams(): array
    {
        return [
            'pid' => (int) $this->getState('context.project_id'),
            'rid' => (int) $this->getState('context.round_id'),
            'projectteam' => (int) $this->getState('context.project_team_id'),
            'season_id' => (int) $this->getState('context.season_id'),
        ];
    }

    private function countRosterRows(
        string $matchTable,
        string $relationField,
        int $matchId,
        int $projectTeamId,
        int $seasonId,
        int $personType,
        bool $playersOnly = false
    ): int {
        if ($matchId <= 0 || $projectTeamId <= 0 || $seasonId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(' . $db->quoteName('mr.id') . ')')
            ->from($db->quoteName($matchTable, 'mr'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp')
                . ' ON ' . $db->quoteName('tp.id') . ' = ' . $db->quoteName('mr.' . $relationField)
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
            )
            ->where($db->quoteName('mr.match_id') . ' = ' . $matchId)
            ->where($db->quoteName('pt.id') . ' = ' . $projectTeamId)
            ->where($db->quoteName('tp.season_id') . ' = ' . $seasonId)
            ->where($db->quoteName('tp.persontype') . ' = ' . $personType);

        if ($playersOnly) {
            $query->where(
                '(' . $db->quoteName('mr.came_in') . ' = 0 OR ' . $db->quoteName('mr.came_in') . ' = 1)'
            );
        }

        $db->setQuery($query);
        return (int) $db->loadResult();
    }
}
