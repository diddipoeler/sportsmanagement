<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

/**
 * Native Joomla 5/6 sports-types list and administrator statistics model.
 */
final class SportstypesModel extends SportsManagementListModel
{
    public static string $setError = '';
    public string $_identifier = 'sportstypes';
    public int $count_result = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        $config['filter_fields'] = $config['filter_fields'] ?? [
            's.name', 'name',
            's.icon', 'icon',
            's.sportsart', 'sportsart',
            's.id', 'id',
            's.ordering', 'ordering',
            's.published', 'published', 'state',
            's.modified', 'modified',
            's.modified_by', 'modified_by',
            's.checked_out', 'checked_out',
            's.checked_out_time', 'checked_out_time',
        ];

        parent::__construct($config, $factory);
    }

    protected function populateState($ordering = 's.name', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);

        $app = Factory::getApplication();
        $this->setState(
            'filter.search',
            $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string')
        );
        $this->setState(
            'filter.state',
            $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string')
        );
    }

    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('s.id'),
                $db->quoteName('s.name'),
                $db->quoteName('s.icon'),
                $db->quoteName('s.sportsart'),
                $db->quoteName('s.eventtime'),
                $db->quoteName('s.published'),
                $db->quoteName('s.ordering'),
                $db->quoteName('s.modified'),
                $db->quoteName('s.modified_by'),
                $db->quoteName('s.checked_out'),
                $db->quoteName('s.checked_out_time'),
                $db->quoteName('uc.name', 'editor'),
            ])
            ->from($db->quoteName('#__sportsmanagement_sports_type', 's'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'uc')
                . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('s.checked_out')
            );

        $search = trim((string) $this->getState('filter.search'));
        if ($search !== '') {
            $token = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('LOWER(' . $db->quoteName('s.name') . ') LIKE LOWER(' . $token . ')');
        }

        $state = $this->getState('filter.state');
        if ($state !== '' && is_numeric($state)) {
            $query->where($db->quoteName('s.published') . ' = ' . (int) $state);
        }

        $map = [
            's.name' => $db->quoteName('s.name'),
            'name' => $db->quoteName('s.name'),
            's.icon' => $db->quoteName('s.icon'),
            'icon' => $db->quoteName('s.icon'),
            's.sportsart' => $db->quoteName('s.sportsart'),
            'sportsart' => $db->quoteName('s.sportsart'),
            's.published' => $db->quoteName('s.published'),
            'published' => $db->quoteName('s.published'),
            'state' => $db->quoteName('s.published'),
            's.ordering' => $db->quoteName('s.ordering'),
            'ordering' => $db->quoteName('s.ordering'),
            's.id' => $db->quoteName('s.id'),
            'id' => $db->quoteName('s.id'),
            's.modified' => $db->quoteName('s.modified'),
            'modified' => $db->quoteName('s.modified'),
            's.modified_by' => $db->quoteName('s.modified_by'),
            'modified_by' => $db->quoteName('s.modified_by'),
            's.checked_out' => $db->quoteName('s.checked_out'),
            'checked_out' => $db->quoteName('s.checked_out'),
            's.checked_out_time' => $db->quoteName('s.checked_out_time'),
            'checked_out_time' => $db->quoteName('s.checked_out_time'),
        ];

        $ordering = (string) $this->getState('list.ordering', 's.name');
        $direction = strtoupper((string) $this->getState('list.direction', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';
        $query->order(($map[$ordering] ?? $map['s.name']) . ' ' . $direction);

        return $query;
    }

    public function getSportsTypes(): array
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name'),
                $db->quoteName('name', 'text'),
                $db->quoteName('icon'),
            ])
            ->from($db->quoteName('#__sportsmanagement_sports_type'))
            ->order($db->quoteName('name') . ' ASC');

        $db->setQuery($query);
        $result = $db->loadObjectList() ?: [];

        foreach ($result as $sportstype) {
            $sportstype->name = Text::_((string) $sportstype->name);
        }

        return $result;
    }

    public function getProjectsCount($sporttypeid = 0): int
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.sports_type_id') . ' = ' . $sporttypeId);

        return $this->loadCount($query);
    }

    public function getPlaygroundsOnlyCount($sporttypeid = 0): int
    {
        return $this->countTable('#__sportsmanagement_playground');
    }

    public function getLeaguesOnlyCount($sporttypeid = 0): int
    {
        return $this->countTable('#__sportsmanagement_league');
    }

    public function getPersonsOnlyCount($sporttypeid = 0): int
    {
        return $this->countTable('#__sportsmanagement_person');
    }

    public function getClubsOnlyCount($sporttypeid = 0): int
    {
        return $this->countTable('#__sportsmanagement_club');
    }

    public function getLeaguesCount($sporttypeid = 0): int
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(DISTINCT ' . $db->quoteName('p.league_id') . ')')
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.sports_type_id') . ' = ' . $sporttypeId)
            ->where($db->quoteName('p.league_id') . ' > 0');

        return $this->loadCount($query);
    }

    public function getSeasonsOnlyCount($sporttypeid = 0): int
    {
        return $this->countTable('#__sportsmanagement_season');
    }

    public function getSeasonsCount($sporttypeid = 0): int
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(DISTINCT ' . $db->quoteName('p.season_id') . ')')
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->where($db->quoteName('p.sports_type_id') . ' = ' . $sporttypeId)
            ->where($db->quoteName('p.season_id') . ' > 0');

        return $this->loadCount($query);
    }

    public function getProjectTeamsCount($sporttypeid = 0): int
    {
        return $this->countProjectRelation(
            '#__sportsmanagement_project_team',
            'ptt',
            'ptt.project_id = p.id',
            $sporttypeid
        );
    }

    public function getProjectTeamsPlayersCount($sporttypeid = 0): int
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project_team', 'ptt')
                . ' ON ' . $db->quoteName('ptt.project_id') . ' = ' . $db->quoteName('p.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_id', 'st2')
                . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('ptt.team_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp1')
                . ' ON ' . $db->quoteName('tp1.team_id') . ' = ' . $db->quoteName('st2.team_id')
            )
            ->where($db->quoteName('p.sports_type_id') . ' = ' . $sporttypeId);

        return $this->loadCount($query);
    }

    public function getProjectDivisionsCount($sporttypeid = 0): int
    {
        return $this->countProjectRelation(
            '#__sportsmanagement_division',
            'd',
            'd.project_id = p.id',
            $sporttypeid
        );
    }

    public function getProjectRoundsCount($sporttypeid = 0): int
    {
        return $this->countProjectRelation(
            '#__sportsmanagement_round',
            'r',
            'r.project_id = p.id',
            $sporttypeid
        );
    }

    public function getProjectMatchesCount($sporttypeid = 0): int
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id')
            )
            ->where($db->quoteName('p.sports_type_id') . ' = ' . $sporttypeId);

        return $this->loadCount($query);
    }

    public function getProjectMatchesEventsNameCount($sporttypeid = 0): array
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                'COUNT(' . $db->quoteName('me.id') . ') AS ' . $db->quoteName('total'),
                $db->quoteName('me.event_type_id'),
                $db->quoteName('p.sports_type_id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match', 'm')
                . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('me.match_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_eventtype', 'et')
                . ' ON ' . $db->quoteName('et.id') . ' = ' . $db->quoteName('me.event_type_id')
            )
            ->where($db->quoteName('p.sports_type_id') . ' = ' . $sporttypeId)
            ->group([
                $db->quoteName('me.event_type_id'),
                $db->quoteName('p.sports_type_id'),
                $db->quoteName('et.name'),
                $db->quoteName('et.icon'),
            ])
            ->order($db->quoteName('et.name') . ' ASC');

        try {
            $db->setQuery($query);
            return $db->loadObjectList() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function getProjectMatchesEventsCount($sporttypeid = 0): int
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match', 'm')
                . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('me.match_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id')
            )
            ->where($db->quoteName('p.sports_type_id') . ' = ' . $sporttypeId);

        return $this->loadCount($query);
    }

    public function getProjectMatchesStatsCount($sporttypeid = 0): int
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_match_statistic', 'ms'))
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_match', 'm')
                . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('ms.match_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('m.round_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id')
            )
            ->where($db->quoteName('p.sports_type_id') . ' = ' . $sporttypeId);

        return $this->loadCount($query);
    }

    private function countTable(string $table): int
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName($table));

        return $this->loadCount($query);
    }

    private function countProjectRelation(string $table, string $alias, string $join, $sporttypeid): int
    {
        $sporttypeId = max(0, (int) $sporttypeid);
        if ($sporttypeId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__sportsmanagement_project', 'p'))
            ->join(
                'INNER',
                $db->quoteName($table, $alias) . ' ON ' . $join
            )
            ->where($db->quoteName('p.sports_type_id') . ' = ' . $sporttypeId);

        return $this->loadCount($query);
    }

    private function loadCount($query): int
    {
        try {
            $db = $this->getDatabase();
            $db->setQuery($query);
            $this->count_result = (int) $db->loadResult();
        } catch (\Throwable $e) {
            $this->count_result = 0;
        }

        return $this->count_result;
    }
}
