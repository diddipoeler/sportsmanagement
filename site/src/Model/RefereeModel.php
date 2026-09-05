<?php
/**
 * Native Joomla 5/6 frontend referee model.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class RefereeModel extends SportsManagementProjectModel
{
    public static int $projectid = 0;
    public static int $personid = 0;
    public static int $cfg_which_database = 0;
    public static $_history = null;

    public $_data = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = $this->siteApplication()->getInput();
        self::$projectid = $this->projectId;
        self::$personid = $input->getInt('pid', 0);
        self::$cfg_which_database = $input->getInt('cfg_which_database', 0);

        PersonModel::$projectid = self::$projectid;
        PersonModel::$personid = self::$personid;
        PersonModel::$cfg_which_database = self::$cfg_which_database;
    }

    public function getPerson(): ?object
    {
        if (self::$personid <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                'p.*',
                "CONCAT_WS(':', p.id, p.alias) AS slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_person', 'p'))
            ->where($db->quoteName('p.id') . ' = ' . self::$personid);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    public function getReferee(): ?object
    {
        if (self::$projectid <= 0 || self::$personid <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                'p.*',
                $db->quoteName('pr.id'),
                $db->quoteName('pr.notes', 'prnotes'),
                $db->quoteName('pr.picture'),
                $db->quoteName('pos.name', 'position_name'),
                "CONCAT_WS(':', p.id, p.alias) AS slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project_referee', 'pr'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_person_id', 'o') . ' ON ' . $db->quoteName('o.id') . ' = ' . $db->quoteName('pr.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_person', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('o.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'pj')
                . ' ON ' . $db->quoteName('pj.id') . ' = ' . $db->quoteName('pr.project_id')
                . ' AND ' . $db->quoteName('pj.season_id') . ' = ' . $db->quoteName('o.season_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('ppos.id') . ' = ' . $db->quoteName('pr.project_position_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ppos.position_id'))
            ->where($db->quoteName('pr.project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('pr.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('pj.published') . ' = 1')
            ->where($db->quoteName('o.person_id') . ' = ' . self::$personid);
        $db->setQuery($query, 0, 1);

        return $db->loadObject() ?: null;
    }

    public function getTeamsIndexedByProjectTeamId(): array
    {
        $teams = [];

        foreach ($this->getProjectTeams(0) as $team) {
            $projectTeamId = (int) ($team->projectteamid ?? 0);

            if ($projectTeamId > 0) {
                $teams[$projectTeamId] = $team;
            }
        }

        return $teams;
    }

    public function getHistory($order = 'ASC')
    {
        if (self::$personid <= 0) {
            return [];
        }

        if (self::$_history !== null) {
            return self::$_history;
        }

        $direction = strtoupper((string) $order) === 'DESC' ? 'DESC' : 'ASC';
        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('per.id', 'pid'),
                $db->quoteName('per.firstname'),
                $db->quoteName('per.lastname'),
                "CONCAT_WS(':', per.id, per.alias) AS person_slug",
                $db->quoteName('pr.person_id'),
                $db->quoteName('pr.project_id'),
                $db->quoteName('pos.name', 'position_name'),
                $db->quoteName('p.name', 'project_name'),
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                $db->quoteName('s.name', 'season_name'),
            ])
            ->from($db->quoteName('#__sportsmanagement_person', 'per'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_person_id', 'o') . ' ON ' . $db->quoteName('per.id') . ' = ' . $db->quoteName('o.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_referee', 'pr') . ' ON ' . $db->quoteName('pr.person_id') . ' = ' . $db->quoteName('o.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pr.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('p.season_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('l.id') . ' = ' . $db->quoteName('p.league_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project_position', 'ppos') . ' ON ' . $db->quoteName('pr.project_position_id') . ' = ' . $db->quoteName('ppos.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('ppos.position_id') . ' = ' . $db->quoteName('pos.id'))
            ->where($db->quoteName('per.id') . ' = ' . self::$personid)
            ->where($db->quoteName('per.published') . ' = 1')
            ->where($db->quoteName('pr.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->order([
                $db->quoteName('s.ordering') . ' ASC',
                $db->quoteName('l.ordering') . ' ASC',
                $db->quoteName('p.name') . ' ' . $direction,
            ]);

        $db->setQuery($query);
        self::$_history = $db->loadObjectList() ?: [];

        return self::$_history;
    }

    public function getPresenceStats($project_id, $person_id)
    {
        $projectId = max(0, (int) $project_id);
        $personId = max(0, (int) $person_id);

        if ($projectId <= 0 || $personId <= 0) {
            return 0;
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(' . $db->quoteName('mr.id') . ')')
            ->from($db->quoteName('#__sportsmanagement_match_referee', 'mr'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('mr.match_id') . ' = ' . $db->quoteName('m.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_referee', 'pr') . ' ON ' . $db->quoteName('pr.id') . ' = ' . $db->quoteName('mr.project_referee_id'))
            ->where($db->quoteName('pr.person_id') . ' = ' . $personId)
            ->where($db->quoteName('pr.project_id') . ' = ' . $projectId)
            ->where($db->quoteName('pr.published') . ' = 1');
        $db->setQuery($query, 0, 1);

        return (int) $db->loadResult();
    }

    public function getGames()
    {
        if (self::$personid <= 0 || self::$projectid <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->createQuery()
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.match_date'),
                $db->quoteName('m.projectteam1_id'),
                $db->quoteName('m.projectteam2_id'),
                $db->quoteName('m.team1_result'),
                $db->quoteName('m.team2_result'),
                $db->quoteName('t1.id', 'team1'),
                $db->quoteName('t1.name', 'home_name'),
                $db->quoteName('t2.id', 'team2'),
                $db->quoteName('t2.name', 'away_name'),
                $db->quoteName('r.roundcode'),
                $db->quoteName('r.project_id'),
                $db->quoteName('c1.logo_big', 'home_logo'),
                $db->quoteName('c2.logo_big', 'away_logo'),
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_match_referee', 'mr') . ' ON ' . $db->quoteName('mr.match_id') . ' = ' . $db->quoteName('m.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_referee', 'pr') . ' ON ' . $db->quoteName('pr.id') . ' = ' . $db->quoteName('mr.project_referee_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_person_id', 'o') . ' ON ' . $db->quoteName('o.id') . ' = ' . $db->quoteName('pr.person_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt1') . ' ON ' . $db->quoteName('m.projectteam1_id') . ' = ' . $db->quoteName('pt1.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('pt1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c1') . ' ON ' . $db->quoteName('t1.club_id') . ' = ' . $db->quoteName('c1.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt2') . ' ON ' . $db->quoteName('m.projectteam2_id') . ' = ' . $db->quoteName('pt2.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('pt2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c2') . ' ON ' . $db->quoteName('t2.club_id') . ' = ' . $db->quoteName('c2.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('r.project_id'))
            ->where($db->quoteName('o.person_id') . ' = ' . self::$personid)
            ->where($db->quoteName('r.project_id') . ' = ' . self::$projectid)
            ->where($db->quoteName('pr.published') . ' = 1')
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('m.published') . ' = 1')
            ->order($db->quoteName('m.match_date') . ' ASC');

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }
}
