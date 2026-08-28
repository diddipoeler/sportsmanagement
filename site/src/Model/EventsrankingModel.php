<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;

final class EventsrankingModel extends SportsManagementProjectModel
{
    public static $projectid = 0;
    public static int $divisionid = 0;
    public static $teamid = 0;
    public static $eventid = 0;
    public static int $matchid = 0;
    public static int $limit = 20;
    public static int $limitstart = 0;
    public static int $cfg_which_database = 0;

    public $_total = null;
    public $_pagination = null;
    public ?string $order = 'desc';

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = Factory::getApplication()->getInput();
        self::$projectid = $this->projectId;
        self::$divisionid = $this->divisionId;
        self::$teamid = $input->getInt('tid', 0);
        self::$matchid = $input->getInt('mid', 0);
        self::$eventid = implode(',', self::normaliseIds($input->get('evid', 0, 'raw')));
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0);

        $configValues = $this->getTemplateConfig('eventsranking');
        $defaultLimit = self::normaliseIds(self::$eventid)
            ? (int) ($configValues['max_events'] ?? 20)
            : (int) ($configValues['count_events'] ?? 5);
        self::$limit = max(0, $input->getInt('limit', $defaultLimit));
        self::$limitstart = max(0, $input->getInt('start', 0));
        $this->setOrder($input->getCmd('order', 'desc'));
    }

    public function setOrder($order)
    {
        $value = strtolower((string) $order);
        if (in_array($value, ['asc', 'desc'], true)) {
            $this->order = $value;
        }
        return $this->order;
    }

    public function getTeamId()
    {
        $ids = self::normaliseIds(self::$teamid);
        return count($ids) === 1 ? $ids[0] : 0;
    }

    public function getPagination(): Pagination
    {
        if (!$this->_pagination instanceof Pagination) {
            $this->_pagination = new Pagination($this->getTotal(), self::$limitstart, self::$limit);
        }
        return $this->_pagination;
    }

    public function getTotal(): int
    {
        if ($this->_total !== null) {
            return (int) $this->_total;
        }

        $eventIds = self::normaliseIds(self::$eventid);
        if (!$eventIds) {
            $this->_total = 0;
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(DISTINCT ' . $db->quoteName('me.teamplayer_id') . ')')
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('me.teamplayer_id') . ' = ' . $db->quoteName('tp.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id')
                . ' AND ' . $db->quoteName('p.season_id') . ' = ' . $db->quoteName('st.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'pl') . ' ON ' . $db->quoteName('tp.person_id') . ' = ' . $db->quoteName('pl.id'))
            ->where($db->quoteName('me.event_type_id') . ' IN (' . implode(',', $eventIds) . ')')
            ->where($db->quoteName('pl.published') . ' = 1');

        self::applyRankingFilters($query, $db, false);
        $db->setQuery($query, 0, 1);
        $this->_total = (int) ($db->loadResult() ?: 0);
        return $this->_total;
    }

    public static function getLimitStart(): int
    {
        return self::$limitstart;
    }

    public static function getLimit(): int
    {
        return self::$limit;
    }

    public function getEventRankings($limit = 0, $limitstart = 0, $order = null, $dart = false, $sports_type_id = 0)
    {
        $eventTypes = self::getEventTypes((int) $sports_type_id);
        if (!$eventTypes) {
            return null;
        }

        $rankingOrder = $order ?: $this->order;
        $rankings = [];
        foreach ($eventTypes as $eventId => $eventType) {
            $rankings[$eventId] = self::_getEventsRanking(
                (int) $eventId,
                $rankingOrder,
                (int) $limit,
                (int) $limitstart,
                (bool) $dart,
                (string) ($eventType->directionspoint ?? 'DESC'),
                (string) ($eventType->directionscounter ?? 'DESC'),
                (int) ($eventType->directionspointpos ?? 1)
            );
        }
        return $rankings;
    }

    public static function getEventTypes($sports_type_id = 0): array
    {
        $db = self::database();
        $query = $db->getQuery(true)
            ->select('DISTINCT et.*')
            ->select($db->quoteName('et.id', 'etid'))
            ->select("CONCAT_WS(':', et.id, et.alias) AS event_slug")
            ->from($db->quoteName('#__sportsmanagement_eventtype', 'et'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match_event', 'me') . ' ON ' . $db->quoteName('et.id') . ' = ' . $db->quoteName('me.event_type_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('me.match_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
            ->order($db->quoteName('et.ordering') . ' ASC');

        $projectIds = self::normaliseIds(self::$projectid);
        if ($projectIds) {
            $query->where($db->quoteName('r.project_id') . ' IN (' . implode(',', $projectIds) . ')');
        }
        $eventIds = self::normaliseIds(self::$eventid);
        if ($eventIds) {
            $query->where($db->quoteName('me.event_type_id') . ' IN (' . implode(',', $eventIds) . ')');
        }
        if (self::$matchid > 0) {
            $query->where($db->quoteName('me.match_id') . ' = ' . self::$matchid);
        }
        $sportsTypeId = max(0, (int) $sports_type_id);
        if ($sportsTypeId > 0) {
            $query->where($db->quoteName('et.sports_type_id') . ' = ' . $sportsTypeId);
        }

        $db->setQuery($query);
        return $db->loadObjectList('etid') ?: [];
    }

    public static function _getEventsRanking($eventtype_id, $order = 'DESC', $limit = 10, $limitstart = 0, $dart = false, $directionspoint = 'DESC', $directionscounter = 'DESC', $directionspointpos = 1): array
    {
        $eventIds = self::normaliseIds($eventtype_id);
        if (!$eventIds) {
            return [];
        }

        $pointDirection = strtoupper((string) $directionspoint) === 'ASC' ? 'ASC' : 'DESC';
        $counterDirection = strtoupper((string) $directionscounter) === 'ASC' ? 'ASC' : 'DESC';
        $directionPosition = (int) $directionspointpos === 2 ? 2 : 1;
        $db = self::database();
        $query = $db->getQuery(true);

        if ($dart) {
            if ($directionPosition === 2) {
                $query->select('me.event_sum AS zaehler, COUNT(me.event_sum) AS p');
            } else {
                $query->select('me.event_sum AS p, COUNT(me.event_sum) AS zaehler');
            }
        } else {
            $query->select('SUM(me.event_sum) AS p');
        }

        $query->select([
                $db->quoteName('pl.firstname', 'fname'),
                $db->quoteName('pl.nickname', 'nname'),
                $db->quoteName('pl.lastname', 'lname'),
                $db->quoteName('pl.country'),
                $db->quoteName('pl.id', 'pid'),
                $db->quoteName('pl.picture'),
                $db->quoteName('tp.picture', 'teamplayerpic'),
                $db->quoteName('t.id', 'tid'),
                $db->quoteName('t.name', 'tname'),
                "CONCAT_WS(':', pl.id, pl.alias) AS person_slug",
                "CONCAT_WS(':', t.id, t.alias) AS team_slug",
                "CONCAT_WS(':', pt.id, t.alias) AS projectteam_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('me.teamplayer_id') . ' = ' . $db->quoteName('tp.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p')
                . ' ON ' . $db->quoteName('pt.project_id') . ' = ' . $db->quoteName('p.id')
                . ' AND ' . $db->quoteName('p.season_id') . ' = ' . $db->quoteName('st.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'pl') . ' ON ' . $db->quoteName('tp.person_id') . ' = ' . $db->quoteName('pl.id'))
            ->where($db->quoteName('me.event_type_id') . ' IN (' . implode(',', $eventIds) . ')')
            ->where($db->quoteName('pl.published') . ' = 1');

        self::applyRankingFilters($query, $db, true);

        $group = [
            $db->quoteName('me.teamplayer_id'),
            $db->quoteName('pl.firstname'),
            $db->quoteName('pl.nickname'),
            $db->quoteName('pl.lastname'),
            $db->quoteName('pl.country'),
            $db->quoteName('pl.id'),
            $db->quoteName('pl.picture'),
            $db->quoteName('pl.alias'),
            $db->quoteName('tp.picture'),
            $db->quoteName('t.id'),
            $db->quoteName('t.name'),
            $db->quoteName('t.alias'),
            $db->quoteName('pt.id'),
        ];
        if ($dart) {
            $group[] = $db->quoteName('me.event_sum');
        }
        $query->group($group);
        $query->order($dart
            ? ['p ' . $pointDirection, 'zaehler ' . $counterDirection]
            : 'p ' . $pointDirection);

        $rowLimit = (int) $limit > 0 ? (int) $limit : self::$limit;
        $rowStart = (int) $limitstart > 0 ? (int) $limitstart : self::$limitstart;
        $db->setQuery($query, max(0, $rowStart), max(0, $rowLimit));
        $rows = $db->loadObjectList() ?: [];

        $previousValue = null;
        $currentRank = 1 + max(0, $rowStart);
        foreach ($rows as $index => $row) {
            $row->rank = $previousValue !== null && $row->p == $previousValue
                ? $currentRank
                : $index + 1 + max(0, $rowStart);
            $previousValue = $row->p;
            $currentRank = $row->rank;
        }
        return $rows;
    }

    private static function applyRankingFilters($query, DatabaseInterface $db, bool $withMatchJoins): void
    {
        $projectIds = self::normaliseIds(self::$projectid);
        if ($projectIds) {
            $query->where($db->quoteName('pt.project_id') . ' IN (' . implode(',', $projectIds) . ')');
            $query->where($db->quoteName('p.id') . ' IN (' . implode(',', $projectIds) . ')');
            if ($withMatchJoins) {
                $query->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('me.match_id') . ' = ' . $db->quoteName('m.id'));
                $query->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'));
                $query->where($db->quoteName('r.project_id') . ' IN (' . implode(',', $projectIds) . ')');
            }
        }

        if (self::$divisionid > 0) {
            $query->where($db->quoteName('pt.division_id') . ' = ' . self::$divisionid);
        }
        $teamIds = self::normaliseIds(self::$teamid);
        if ($teamIds) {
            $teamList = implode(',', $teamIds);
            $query->where($db->quoteName('st.team_id') . ' IN (' . $teamList . ')');
            if ($withMatchJoins) {
                $query->where($db->quoteName('tp.team_id') . ' IN (' . $teamList . ')');
            }
        }
        if (self::$matchid > 0) {
            $query->where($db->quoteName('me.match_id') . ' = ' . self::$matchid);
        }
    }

    private static function normaliseIds($value): array
    {
        $parts = is_array($value) ? $value : preg_split('/[|,]+/', (string) $value, -1, PREG_SPLIT_NO_EMPTY);
        $ids = [];
        foreach ($parts ?: [] as $part) {
            $id = (int) $part;
            if ($id > 0) {
                $ids[$id] = $id;
            }
        }
        return array_values($ids);
    }

    private static function database(): DatabaseInterface
    {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);

        return SportsManagementDatabaseResolver::resolve(
            $joomlaDatabase,
            self::$cfg_which_database === 1 ? 1 : 0
        );
    }
}
