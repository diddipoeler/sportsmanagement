<?php
/**
 * Native Joomla 5/6 frontend roster model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Database\DatabaseInterface;

final class RosterModel extends SportsManagementProjectModel
{
    public static int $projectid = 0;
    public static int $projectteamid = 0;
    public static int $teamid = 0;
    public static int $seasonid = 0;
    public static $projectteam = null;
    public static $team = null;
    public static array $_players = [];
    public static int $cfg_which_database = 0;

    public $_teaminout = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$projectid = $this->projectId;
        self::$teamid = $input->getInt('tid', 0);
        self::$projectteamid = $input->getInt('ptid', 0);
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0);
        self::$projectteam = null;
        self::$team = null;
        self::$_players = [];

        self::getProjectTeam();

        if (self::$projectid > 0 && self::$projectid !== $this->projectId) {
            $this->projectId = self::$projectid;
        }
        $project = $this->getProject();
        self::$seasonid = (int) ($project->season_id ?? 0);
    }

    public static function getProjectTeam($team_picture_which = 'pt')
    {
        if (self::$projectteam !== null) {
            return self::$projectteam;
        }

        $pictureAlias = strtolower((string) $team_picture_which) === 't' ? 't' : 'pt';
        $db = self::database();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pt.project_id'),
                $db->quoteName('pt.id'),
                $db->quoteName('st.team_id', 'season_team_id'),
                $db->quoteName('pt.notes'),
                $db->quoteName($pictureAlias . '.picture', 'picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'pro')
                . ' ON ' . $db->quoteName('pro.id') . ' = ' . $db->quoteName('pt.project_id')
                . ' AND ' . $db->quoteName('pro.season_id') . ' = ' . $db->quoteName('st.season_id'))
            ->where($db->quoteName('pt.published') . ' = 1')
            ->where($db->quoteName('t.published') . ' = 1')
            ->where($db->quoteName('pro.published') . ' = 1');

        if (self::$projectteamid > 0) {
            $query->where($db->quoteName('pt.id') . ' = ' . self::$projectteamid);
        } else {
            if (self::$teamid <= 0) {
                Log::add(Text::_('COM_SPORTSMANAGEMENT_ROSTER_ERROR_TEAM_ID'), Log::WARNING, 'jsmerror');
                return false;
            }
            if (self::$projectid <= 0) {
                Log::add(Text::_('COM_SPORTSMANAGEMENT_ROSTER_ERROR_PROJECT_ID'), Log::WARNING, 'jsmerror');
                return false;
            }
            $query->where($db->quoteName('st.team_id') . ' = ' . self::$teamid)
                ->where($db->quoteName('pt.project_id') . ' = ' . self::$projectid);
        }

        $db->setQuery($query, 0, 1);
        self::$projectteam = $db->loadObject() ?: null;

        if (self::$projectteam && self::$projectteamid > 0) {
            self::$projectid = (int) self::$projectteam->project_id;
            self::$teamid = (int) self::$projectteam->season_team_id;
        }

        return self::$projectteam ?: false;
    }

    public static function getTeam()
    {
        if (self::$team !== null) {
            return self::$team;
        }
        if (self::$teamid <= 0 || self::$projectid <= 0) {
            Log::add(Text::_('COM_SPORTSMANAGEMENT_ROSTER_ERROR_TEAM_ID'), Log::WARNING, 'jsmerror');
            return false;
        }

        $db = self::database();
        $query = $db->createQuery()
            ->select(['t.*', $db->quoteName('c.logo_big'), "CONCAT_WS(':', t.id, t.alias) AS slug"])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('t.club_id'))
            ->where($db->quoteName('t.id') . ' = ' . self::$teamid)
            ->where($db->quoteName('t.published') . ' = 1');
        $db->setQuery($query, 0, 1);
        self::$team = $db->loadObject() ?: null;

        return self::$team ?: false;
    }

    public function getProjectPositions(): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pos.id'),
                $db->quoteName('pos.persontype'),
                $db->quoteName('pos.name'),
                $db->quoteName('pos.ordering'),
                $db->quoteName('pos.published'),
                $db->quoteName('ppos.id', 'pposid'),
            ])
            ->from($db->quoteName('#__sportsmanagement_project_position', 'ppos'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('ppos.project_id') . ' = ' . $this->projectId)
            ->where($db->quoteName('pos.published') . ' = 1')
            ->order([
                $db->quoteName('pos.persontype') . ' ASC',
                $db->quoteName('pos.ordering') . ' ASC',
            ]);
        $db->setQuery($query);
        return $db->loadObjectList('id') ?: [];
    }

    public static function getPlayerEventStats($dart = false, $sumeventid = false): array
    {
        $playerStats = [];
        $rows = self::getTeamPlayers(1);
        if (!$rows) {
            return $playerStats;
        }

        foreach ($rows as $players) {
            foreach ((array) $players as $player) {
                $playerStats[(int) $player->pid] = [];
            }
        }

        foreach (self::getPositionEventTypes() as $position => $eventTypes) {
            foreach ((array) $eventTypes as $eventType) {
                $eventTypeId = (int) ($eventType->eventtype_id ?? 0);
                if ($eventTypeId <= 0) {
                    continue;
                }
                $teamStats = self::getTeamEventStat($eventTypeId, (bool) $dart, (bool) $sumeventid);
                if (!isset($rows[$position])) {
                    continue;
                }
                foreach ((array) $rows[$position] as $player) {
                    $personId = (int) $player->pid;
                    if ($dart && !$sumeventid) {
                        $playerStats[$eventTypeId] = $teamStats;
                    } else {
                        $playerStats[$personId][$eventTypeId] = isset($teamStats[$personId])
                            ? (float) ($teamStats[$personId]->total ?? 0)
                            : 0;
                    }
                }
            }
        }

        return $playerStats;
    }

    public static function getTeamPlayers($persontype = 1)
    {
        $personType = in_array((int) $persontype, [1, 2], true) ? (int) $persontype : 1;
        $projectTeam = self::getProjectTeam();
        if (!$projectTeam || self::$seasonid <= 0) {
            return [];
        }

        $db = self::database();
        $query = $db->createQuery()
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
                $db->quoteName('ppos.position_id', 'position_id'),
                $db->quoteName('ppos.id', 'pposid'),
                $db->quoteName('pos.name', 'position'),
                $db->quoteName('st.id', 'season_team_id'),
                $db->quoteName('pt.project_id', 'project_id'),
                $db->quoteName('pt.id', 'projectteam_id'),
                $db->quoteName('tp.id', 'playerid'),
                $db->quoteName('tp.id', 'season_team_person_id'),
                $db->quoteName('tp.jerseynumber', 'position_number'),
                $db->quoteName('tp.notes', 'description'),
                $db->quoteName('tp.market_value'),
                $db->quoteName('tp.market_text'),
                $db->quoteName('tp.picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_season_team_person_id', 'tp'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'pr') . ' ON ' . $db->quoteName('tp.person_id') . ' = ' . $db->quoteName('pr.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'pro')
                . ' ON ' . $db->quoteName('pro.id') . ' = ' . $db->quoteName('pt.project_id')
                . ' AND ' . $db->quoteName('pro.season_id') . ' = ' . $db->quoteName('tp.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_person_project_position', 'perpos')
                . ' ON ' . $db->quoteName('perpos.project_id') . ' = ' . $db->quoteName('pro.id')
                . ' AND ' . $db->quoteName('perpos.person_id') . ' = ' . $db->quoteName('pr.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('perpos.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('tp.persontype') . ' = ' . $personType)
            ->where($db->quoteName('tp.season_id') . ' = ' . self::$seasonid)
            ->where($db->quoteName('tp.team_id') . ' = ' . (int) $projectTeam->season_team_id)
            ->where($db->quoteName('pr.published') . ' = 1')
            ->where($db->quoteName('pr.show_on_frontend') . ' = 1')
            ->where($db->quoteName('tp.published') . ' = 1')
            ->where($db->quoteName('pt.published') . ' = 1')
            ->where($db->quoteName('t.published') . ' = 1')
            ->where($db->quoteName('pro.published') . ' = 1')
            ->where($db->quoteName('pro.id') . ' = ' . self::$projectid)
            ->order([
                $db->quoteName('pos.ordering') . ' ASC',
                $db->quoteName('ppos.position_id') . ' ASC',
                $db->quoteName('tp.ordering') . ' ASC',
                $db->quoteName('tp.jerseynumber') . ' ASC',
                $db->quoteName('pr.lastname') . ' ASC',
                $db->quoteName('pr.firstname') . ' ASC',
            ]);

        if ($personType === 2) {
            $query->select($db->quoteName('posparent.name', 'parentname'))
                ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'posparent') . ' ON ' . $db->quoteName('posparent.id') . ' = ' . $db->quoteName('pos.parent_id'));
        }

        $db->setQuery($query);
        self::$_players = $db->loadObjectList() ?: [];

        if ($personType === 2) {
            return self::$_players;
        }

        $byPosition = [];
        foreach (self::$_players as $player) {
            $positionId = (int) ($player->position_id ?? 0);
            $byPosition[$positionId][] = $player;
        }
        return $byPosition;
    }

    public static function getPositionEventTypes($positionId = 0): array
    {
        $db = self::database();
        $query = $db->createQuery()
            ->select(['pet.*', $db->quoteName('ppos.id', 'pposid'), $db->quoteName('ppos.position_id'), $db->quoteName('et.name'), $db->quoteName('et.icon')])
            ->from($db->quoteName('#__sportsmanagement_position_eventtype', 'pet'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_eventtype', 'et') . ' ON ' . $db->quoteName('et.id') . ' = ' . $db->quoteName('pet.eventtype_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pet.position_id'))
            ->where($db->quoteName('ppos.project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('et.published') . ' = 1')
            ->order([$db->quoteName('pet.ordering') . ' ASC', $db->quoteName('et.ordering') . ' ASC']);

        $positionId = max(0, (int) $positionId);
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
            $grouped[(int) $row->position_id][(int) $row->eventtype_id] = $row;
        }
        return $grouped;
    }

    public static function getTeamEventStat($eventtype_id = 0, $dart = false, $sumeventid = false)
    {
        $eventTypeId = max(0, (int) $eventtype_id);
        $projectTeam = self::getProjectTeam();
        if ($eventTypeId <= 0 || !$projectTeam) {
            return [];
        }

        $db = self::database();
        $query = $db->createQuery();
        if ($dart) {
            $query->select($sumeventid ? 'COUNT(me.event_type_id) AS total' : 'me.event_sum AS total, me.event_type_id AS event_type_id');
        } else {
            $query->select('SUM(me.event_sum) AS total');
        }

        $query->select($db->quoteName('tp.person_id'))
            ->from($db->quoteName('#__sportsmanagement_match_event', 'me'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'tp') . ' ON ' . $db->quoteName('me.teamplayer_id') . ' = ' . $db->quoteName('tp.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('tp.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('tp.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'ma') . ' ON ' . $db->quoteName('ma.id') . ' = ' . $db->quoteName('me.match_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.id') . ' = ' . $db->quoteName('ma.round_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt')
                . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id')
                . ' AND ' . $db->quoteName('pt.project_id') . ' = ' . $db->quoteName('r.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'pro')
                . ' ON ' . $db->quoteName('pro.id') . ' = ' . $db->quoteName('pt.project_id')
                . ' AND ' . $db->quoteName('pro.season_id') . ' = ' . $db->quoteName('st.season_id'))
            ->where($db->quoteName('me.event_type_id') . ' = ' . $eventTypeId)
            ->where($db->quoteName('pt.id') . ' = ' . (int) $projectTeam->id)
            ->where($db->quoteName('pt.project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('r.project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('tp.published') . ' = 1')
            ->where($db->quoteName('pt.published') . ' = 1')
            ->where($db->quoteName('pro.published') . ' = 1')
            ->where($db->quoteName('ma.published') . ' = 1')
            ->where($db->quoteName('pro.id') . ' = ' . self::$projectid);

        if (!$dart || $sumeventid) {
            $query->group($db->quoteName('tp.person_id'));
        }

        $db->setQuery($query);
        return $dart && !$sumeventid
            ? ($db->loadObjectList() ?: [])
            : ($db->loadObjectList('person_id') ?: []);
    }

    public static function getRosterStats(): array
    {
        $projectTeam = self::getProjectTeam();
        if (!$projectTeam) {
            return [];
        }

        $result = [];
        foreach (self::loadProjectStats() as $positionId => $stats) {
            foreach ((array) $stats as $stat) {
                if (!is_object($stat) || !method_exists($stat, 'getRosterStats')) {
                    continue;
                }
                $statId = (int) ($stat->id ?? 0);
                if ($statId <= 0) {
                    continue;
                }
                $result[$positionId][$statId] = $stat->getRosterStats(
                    (int) $projectTeam->season_team_id,
                    (int) $projectTeam->project_id,
                    (int) $positionId
                );
            }
        }
        return $result;
    }

    public static function getLastSeasonDate(): string
    {
        if (self::$projectid <= 0) {
            return '0000-00-00';
        }
        $db = self::database();
        $query = $db->createQuery()
            ->select(['MAX(' . $db->quoteName('round_date_first') . ') AS firstday', 'MAX(' . $db->quoteName('round_date_last') . ') AS lastday'])
            ->from($db->quoteName('#__sportsmanagement_round'))
            ->where($db->quoteName('project_id') . ' = ' . self::$projectid);
        $db->setQuery($query, 0, 1);
        $roundDate = $db->loadObject();
        if (!$roundDate || (!$roundDate->firstday && !$roundDate->lastday)) {
            return '0000-00-00';
        }
        if (!$roundDate->lastday || $roundDate->lastday === '0000-00-00' || $roundDate->firstday > $roundDate->lastday) {
            return (string) $roundDate->firstday;
        }
        return (string) $roundDate->lastday;
    }

    public function getTeamPlayer($round_id, $player_id): array
    {
        $roundId = max(0, (int) $round_id);
        $playerId = max(0, (int) $player_id);
        if ($roundId <= 0 || $playerId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('pr.injury'),
                $db->quoteName('pr.suspension'),
                $db->quoteName('pr.away'),
                $db->quoteName('ppos.id', 'pposid'),
                $db->quoteName('pos.id', 'position_id'),
                $db->quoteName('stp.picture'),
            ])
            ->from($db->quoteName('#__sportsmanagement_person', 'pr'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_person_id', 'stp') . ' ON ' . $db->quoteName('stp.person_id') . ' = ' . $db->quoteName('pr.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st')
                . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('stp.team_id')
                . ' AND ' . $db->quoteName('st.season_id') . ' = ' . $db->quoteName('stp.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'pro')
                . ' ON ' . $db->quoteName('pro.id') . ' = ' . $db->quoteName('pt.project_id')
                . ' AND ' . $db->quoteName('pro.season_id') . ' = ' . $db->quoteName('stp.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('t.id') . ' = ' . $db->quoteName('st.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('r.project_id') . ' = ' . $db->quoteName('pt.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('stp.project_position_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('r.id') . ' = ' . $roundId)
            ->where($db->quoteName('stp.id') . ' = ' . $playerId)
            ->where($db->quoteName('pr.published') . ' = 1')
            ->where($db->quoteName('pr.show_on_frontend') . ' = 1')
            ->where($db->quoteName('stp.published') . ' = 1')
            ->where($db->quoteName('pt.published') . ' = 1')
            ->where($db->quoteName('pro.published') . ' = 1')
            ->where($db->quoteName('t.published') . ' = 1');
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    private static function loadProjectStats(): array
    {
        if (self::$projectid <= 0) {
            return [];
        }
        if (!class_exists('SMStatistic')) {
            $baseFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/statistics/base.php';
            if (!is_file($baseFile)) {
                return [];
            }
            require_once $baseFile;
        }

        $db = self::database();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('ppos.position_id'),
                $db->quoteName('stat.id'),
                $db->quoteName('stat.name'),
                $db->quoteName('stat.short'),
                $db->quoteName('stat.class'),
                $db->quoteName('stat.icon'),
                $db->quoteName('stat.calculated'),
                $db->quoteName('stat.params'),
                $db->quoteName('stat.baseparams'),
                $db->quoteName('stat.ordering'),
            ])
            ->from($db->quoteName('#__sportsmanagement_statistic', 'stat'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_position_statistic', 'ps') . ' ON ' . $db->quoteName('ps.statistic_id') . ' = ' . $db->quoteName('stat.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_position', 'ppos')
                . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('ps.position_id')
                . ' AND ' . $db->quoteName('ppos.project_id') . ' = ' . self::$projectid)
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ps.position_id'))
            ->where($db->quoteName('stat.published') . ' = 1')
            ->where($db->quoteName('pos.published') . ' = 1')
            ->order([$db->quoteName('pos.ordering') . ' ASC', $db->quoteName('ps.ordering') . ' ASC']);
        $db->setQuery($query);
        $rows = $db->loadObjectList() ?: [];

        $stats = [];
        foreach ($rows as $row) {
            try {
                $stat = \SMStatistic::getInstance((string) $row->class);
                if (!$stat) {
                    continue;
                }
                $stat->bind($row);
                $stat->set('position_id', (int) $row->position_id);
                $stats[(int) $row->position_id][(int) $row->id] = $stat;
            } catch (\Throwable) {
                continue;
            }
        }
        return $stats;
    }

    private static function database(): DatabaseInterface
    {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);

        return SportsManagementDatabaseResolver::resolve($joomlaDatabase, self::$cfg_which_database);
    }
}
