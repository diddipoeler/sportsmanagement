<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Pagination\Pagination;

final class RosteralltimeModel extends SportsManagementProjectModel
{
    public static int $projectid = 0;
    public static int $projectteamid = 0;
    public static $projectteam = 0;
    public static int $teamid = 0;
    public static int $cfg_which_database = 0;
    public static array $_tips = [];
    public static array $_warnings = [];
    public static array $_notes = [];

    public $team = null;
    public $_teaminout = null;
    public array $_players = [];
    public array $_all_time_players = [];
    public string $_identifier = 'rosteralltime';
    public int $limitstart = 0;
    public int $limit = 0;

    private ?array $itemsCache = null;
    private ?int $totalCache = null;
    private ?Pagination $paginationCache = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $app = Factory::getApplication();
        $input = $app->getInput();
        self::$projectid = $this->projectId;
        self::$teamid = max(0, $input->getInt('tid', 0));
        self::$projectteamid = max(0, $input->getInt('ttid', 0));
        self::$cfg_which_database = max(0, $input->getInt('cfg_which_database', 0));
        self::$_tips = [];
        self::$_warnings = [];
        self::$_notes = [];

        $defaultLimit = max(1, (int) $app->get('list_limit', 20));
        $this->limit = max(0, $input->getInt('limit', $defaultLimit));
        $this->limitstart = max(0, $input->getInt('limitstart', $input->getInt('start', 0)));
        $search = trim((string) $app->getUserStateFromRequest(
            'com_sportsmanagement.rosteralltime.filter.search',
            'filter_search',
            '',
            'string'
        ));

        $this->setState('list.limit', $this->limit);
        $this->setState('list.start', $this->limitstart);
        $this->setState('filter.search', $search);
        $this->setState('filter_order', 'pr.lastname');
        $this->setState('filter_order_Dir', 'ASC');
    }

    public function getStart(): int
    {
        $start = max(0, (int) $this->getState('list.start', $this->limitstart));
        $limit = max(0, (int) $this->getState('list.limit', $this->limit));
        $total = $this->getTotal();

        if ($limit <= 0) {
            return $start;
        }

        if ($start > $total - $limit) {
            $start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
        }

        return $start;
    }

    public function getItems(): array
    {
        if ($this->itemsCache !== null) {
            return $this->itemsCache;
        }
        if (self::$teamid <= 0) {
            return $this->itemsCache = [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('tp.person_id', 'person_id'))
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'pr') . ' ON ' . $db->quoteName('pr.id') . ' = ' . $db->quoteName('tp.person_id'))
            ->where($db->quoteName('tp.team_id') . ' = ' . self::$teamid)
            ->where($db->quoteName('pr.published') . ' = 1')
            ->where($db->quoteName('tp.published') . ' = 1')
            ->order([
                $db->quoteName('pr.lastname') . ' ASC',
                $db->quoteName('pr.firstname') . ' ASC',
            ]);

        $search = trim((string) $this->getState('filter.search', ''));
        if ($search !== '') {
            $query->where('LOWER(' . $db->quoteName('pr.lastname') . ') LIKE ' . $db->quote('%' . strtolower($search) . '%'));
        }

        $limit = max(0, (int) $this->getState('list.limit', $this->limit));
        $start = $this->getStart();
        $db->setQuery($query, $start, $limit > 0 ? $limit : 0);
        return $this->itemsCache = ($db->loadObjectList() ?: []);
    }

    public function getTotal(): int
    {
        if ($this->totalCache !== null) {
            return $this->totalCache;
        }
        if (self::$teamid <= 0) {
            return $this->totalCache = 0;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(DISTINCT ' . $db->quoteName('tp.person_id') . ')')
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'pr') . ' ON ' . $db->quoteName('pr.id') . ' = ' . $db->quoteName('tp.person_id'))
            ->where($db->quoteName('tp.team_id') . ' = ' . self::$teamid)
            ->where($db->quoteName('pr.published') . ' = 1')
            ->where($db->quoteName('tp.published') . ' = 1');

        $search = trim((string) $this->getState('filter.search', ''));
        if ($search !== '') {
            $query->where('LOWER(' . $db->quoteName('pr.lastname') . ') LIKE ' . $db->quote('%' . strtolower($search) . '%'));
        }

        $db->setQuery($query);
        return $this->totalCache = (int) ($db->loadResult() ?: 0);
    }

    public function getPagination(): Pagination
    {
        if (!$this->paginationCache instanceof Pagination) {
            $this->paginationCache = new Pagination(
                $this->getTotal(),
                $this->getStart(),
                max(0, (int) $this->getState('list.limit', $this->limit))
            );
        }
        return $this->paginationCache;
    }

    public function getPlayerPosition($sports_type_id = 1): array
    {
        $sportsTypeId = max(0, (int) $sports_type_id);
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('po') . '.*')
            ->from($db->quoteName('#__sportsmanagement_position', 'po'))
            ->where($db->quoteName('po.parent_id') . ' <> 0')
            ->where($db->quoteName('po.persontype') . ' = 1')
            ->order($db->quoteName('po.ordering') . ' ASC');

        if ($sportsTypeId > 0) {
            $query->where($db->quoteName('po.sports_type_id') . ' = ' . $sportsTypeId);
        }

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getPositionEventTypes($positionId = 0): array
    {
        $positionId = max(0, (int) $positionId);
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('pet.*')
            ->select([
                $db->quoteName('et.name', 'name'),
                $db->quoteName('et.icon', 'icon'),
            ])
            ->from($db->quoteName('#__sportsmanagement_position_eventtype', 'pet'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_eventtype', 'et') . ' ON ' . $db->quoteName('et.id') . ' = ' . $db->quoteName('pet.eventtype_id'))
            ->where($db->quoteName('et.published') . ' = 1')
            ->order([
                $db->quoteName('pet.ordering') . ' ASC',
                $db->quoteName('et.ordering') . ' ASC',
            ]);

        if ($positionId > 0) {
            $query->where($db->quoteName('pet.position_id') . ' = ' . $positionId);
        }

        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];
        if ($positionId > 0) {
            return $rows;
        }

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[(int) $row->position_id][] = $row;
        }
        return $grouped;
    }

    public function getTeam()
    {
        if ($this->team !== null) {
            return $this->team;
        }
        if (self::$teamid <= 0) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_ERROR'), Log::WARNING, 'jsmerror');
            return false;
        }
        if (self::$projectid <= 0) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_RANKING_ERROR_PROJECTID_REQUIRED'), Log::WARNING, 'jsmerror');
            return false;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('t.*')
            ->select("CONCAT_WS(':', t.id, t.alias) AS team_slug")
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->where($db->quoteName('t.id') . ' = ' . self::$teamid);
        $db->setQuery($query, 0, 1);
        $this->team = $db->loadObject() ?: null;
        return $this->team ?: false;
    }

    public function getTeamPlayers($persontype = 1, $positioneventtypes = [], $items = [])
    {
        $personType = in_array((int) $persontype, [1, 2], true) ? (int) $persontype : 1;
        $personIds = [];
        foreach ((array) $items as $item) {
            $id = (int) ($item->person_id ?? 0);
            if ($id > 0) {
                $personIds[$id] = $id;
            }
        }

        if (!$personIds) {
            self::$_notes[] = Text::_('COM_SPORTSMANAGEMENT_ROSTERALLTIME_NO_PERSON');
            return false;
        }

        $db = $this->getDatabase();
        $personList = implode(',', array_values($personIds));
        $latestTp = $db->getQuery(true)
            ->select('MAX(tp0.id)')
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp0'))
            ->where($db->quoteName('tp0.person_id') . ' = ' . $db->quoteName('pr.id'))
            ->where($db->quoteName('tp0.team_id') . ' = ' . self::$teamid)
            ->where($db->quoteName('tp0.persontype') . ' = ' . $personType)
            ->where($db->quoteName('tp0.published') . ' = 1');

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pr.firstname'),
                $db->quoteName('pr.nickname'),
                $db->quoteName('pr.lastname'),
                $db->quoteName('pr.country'),
                $db->quoteName('pr.birthday'),
                $db->quoteName('pr.deathday'),
                $db->quoteName('pr.id', 'pid'),
                $db->quoteName('pr.id', 'person_id'),
                $db->quoteName('pr.picture', 'ppic'),
                $db->quoteName('pr.suspension'),
                $db->quoteName('pr.away'),
                $db->quoteName('pr.injury'),
                "CONCAT_WS(':', pr.id, pr.alias) AS person_slug",
                '(' . $latestTp . ') AS playerid',
            ])
            ->from($db->quoteName('#__sportsmanagement_person', 'pr'))
            ->where($db->quoteName('pr.id') . ' IN (' . $personList . ')')
            ->where($db->quoteName('pr.published') . ' = 1')
            ->order([
                $db->quoteName('pr.lastname') . ' ASC',
                $db->quoteName('pr.firstname') . ' ASC',
            ]);
        $db->setQuery($query);
        $players = $db->loadObjectList('pid') ?: [];

        if (!$players) {
            return [];
        }

        $latestIds = [];
        foreach ($players as $player) {
            $id = (int) ($player->playerid ?? 0);
            if ($id > 0) {
                $latestIds[$id] = $id;
            }
        }

        $team = $this->getTeam();
        $project = $this->getProject();
        $teamSlug = $team && isset($team->team_slug) ? (string) $team->team_slug : '';
        $projectSlug = $project && isset($project->slug) ? (string) $project->slug : '';

        $details = [];
        if ($latestIds) {
            $detailQuery = $db->getQuery(true)
                ->select([
                    $db->quoteName('tp.id'),
                    $db->quoteName('tp.jerseynumber', 'position_number'),
                    $db->quoteName('tp.notes', 'description'),
                    $db->quoteName('tp.market_value'),
                    $db->quoteName('tp.market_text'),
                    $db->quoteName('tp.picture'),
                    $db->quoteName('tp.season_id'),
                ])
                ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
                ->where($db->quoteName('tp.id') . ' IN (' . implode(',', $latestIds) . ')');
            $db->setQuery($detailQuery);
            $details = $db->loadObjectList('id') ?: [];
        }

        $positionMap = $this->loadPersonPositions(array_values($personIds));
        $inOutMap = $this->loadAllTimeInOutStats(array_values($personIds));
        $eventMap = $this->loadAllTimeEventStats(array_values($personIds));

        foreach ($players as $pid => $player) {
            $detail = $details[(int) ($player->playerid ?? 0)] ?? null;
            $position = $positionMap[(int) $pid] ?? null;
            $inOut = $inOutMap[(int) $pid] ?? (object) [
                'played' => 0,
                'started' => 0,
                'sub_in' => 0,
                'sub_out' => 0,
            ];

            $player->season_team_person_id = (int) ($player->playerid ?? 0);
            $player->position_number = $detail->position_number ?? '';
            $player->description = $detail->description ?? '';
            $player->market_value = $detail->market_value ?? null;
            $player->market_text = $detail->market_text ?? '';
            $player->picture = $detail->picture ?? '';
            $player->season_team_id = 0;
            $player->project_id = self::$projectid;
            $player->project_slug = $projectSlug;
            $player->team_slug = $teamSlug;
            $player->position_id = (int) ($position->position_id ?? 0);
            $player->pposid = (int) ($position->pposid ?? 0);
            $player->position = (string) ($position->position ?? '');
            $player->played = (int) $inOut->played;
            $player->start = (int) $inOut->started;
            $player->came_in = (int) $inOut->sub_in;
            $player->out = (int) $inOut->sub_out;

            $allowedEventIds = [];
            foreach ((array) ($positioneventtypes[$player->position_id] ?? []) as $eventType) {
                $eventId = (int) ($eventType->eventtype_id ?? 0);
                if ($eventId > 0) {
                    $allowedEventIds[$eventId] = true;
                }
            }
            foreach (($eventMap[(int) $pid] ?? []) as $eventId => $total) {
                if ($allowedEventIds && !isset($allowedEventIds[(int) $eventId])) {
                    continue;
                }
                $property = 'event_type_id_' . (int) $eventId;
                $player->{$property} = (int) $total;
            }
        }

        $this->_players = array_values($players);
        $this->_all_time_players = $players;
        return $this->_all_time_players;
    }

    private function loadPersonPositions(array $personIds): array
    {
        if (!$personIds) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('perpos.person_id'),
                $db->quoteName('ppos.position_id'),
                $db->quoteName('ppos.id', 'pposid'),
                $db->quoteName('pos.name', 'position'),
                $db->quoteName('perpos.project_id'),
                $db->quoteName('perpos.id', 'mapping_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_person_project_position', 'perpos'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('perpos.project_position_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('perpos.person_id') . ' IN (' . implode(',', array_map('intval', $personIds)) . ')')
            ->order([
                'CASE WHEN ' . $db->quoteName('perpos.project_id') . ' = ' . self::$projectid . ' THEN 0 ELSE 1 END ASC',
                $db->quoteName('perpos.id') . ' DESC',
            ]);
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        $positions = [];
        foreach ($rows as $row) {
            $pid = (int) $row->person_id;
            if (!isset($positions[$pid])) {
                $positions[$pid] = $row;
            }
        }
        return $positions;
    }

    private function loadAllTimeInOutStats(array $personIds): array
    {
        $stats = [];
        foreach ($personIds as $personId) {
            $stats[(int) $personId] = [
                'played' => [],
                'started' => [],
                'sub_in' => [],
                'sub_out' => [],
            ];
        }
        if (!$personIds || self::$teamid <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $list = implode(',', array_map('intval', $personIds));
        $teamId = self::$teamid;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id', 'match_id'),
                $db->quoteName('mp.came_in'),
                $db->quoteName('mp.out'),
                $db->quoteName('tp_in.person_id', 'incoming_person_id'),
                $db->quoteName('tp_out.person_id', 'outgoing_person_id'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match_player', 'mp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('mp.match_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp_in') . ' ON ' . $db->quoteName('tp_in.id') . ' = ' . $db->quoteName('mp.teamplayer_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp_out') . ' ON ' . $db->quoteName('tp_out.id') . ' = ' . $db->quoteName('mp.in_for'))
            ->where($db->quoteName('m.published') . ' = 1')
            ->where('((' . $db->quoteName('tp_in.team_id') . ' = ' . $teamId
                . ' AND ' . $db->quoteName('tp_in.person_id') . ' IN (' . $list . '))'
                . ' OR (' . $db->quoteName('tp_out.team_id') . ' = ' . $teamId
                . ' AND ' . $db->quoteName('tp_out.person_id') . ' IN (' . $list . ')))');
        $db->setQuery($query);

        foreach ($db->loadObjectList() ?: [] as $row) {
            $matchId = (int) $row->match_id;
            $incoming = (int) ($row->incoming_person_id ?? 0);
            $outgoing = (int) ($row->outgoing_person_id ?? 0);

            if ($incoming > 0 && isset($stats[$incoming])) {
                if ((int) $row->came_in === 0) {
                    $stats[$incoming]['played'][$matchId] = true;
                    $stats[$incoming]['started'][$matchId] = true;
                } else {
                    $stats[$incoming]['played'][$matchId] = true;
                    $stats[$incoming]['sub_in'][$matchId] = true;
                }
                if ((int) $row->out === 1) {
                    $stats[$incoming]['sub_out'][$matchId] = true;
                }
            }
            if ($outgoing > 0 && isset($stats[$outgoing])) {
                $stats[$outgoing]['sub_out'][$matchId] = true;
                $stats[$outgoing]['played'][$matchId] = true;
            }
        }

        $objects = [];
        foreach ($stats as $pid => $values) {
            $objects[$pid] = (object) [
                'played' => count($values['played']),
                'started' => count($values['started']),
                'sub_in' => count($values['sub_in']),
                'sub_out' => count($values['sub_out']),
            ];
        }
        return $objects;
    }

    private function loadAllTimeEventStats(array $personIds): array
    {
        if (!$personIds || self::$teamid <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('tp.person_id'),
                $db->quoteName('me.event_type_id'),
                'COUNT(' . $db->quoteName('me.event_sum') . ') AS total',
            ])
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('me.match_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('tp.id') . ' = ' . $db->quoteName('me.teamplayer_id'))
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('tp.team_id') . ' = ' . self::$teamid)
            ->where($db->quoteName('tp.person_id') . ' IN (' . implode(',', array_map('intval', $personIds)) . ')')
            ->group([
                $db->quoteName('tp.person_id'),
                $db->quoteName('me.event_type_id'),
            ]);
        $db->setQuery($query);

        $stats = [];
        foreach ($db->loadObjectList() ?: [] as $row) {
            $stats[(int) $row->person_id][(int) $row->event_type_id] = (int) $row->total;
        }
        return $stats;
    }
}
