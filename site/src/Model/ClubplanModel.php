<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

final class ClubplanModel extends SportsManagementProjectModel
{
    public static int $clubid = 0;
    public static int $project_id = 0;
    public static ?string $startdate = null;
    public static ?string $enddate = null;
    public static int $teamartsel = 0;
    public static int $type = 0;
    public static int $teamprojectssel = 0;
    public static int $teamseasonssel = 0;
    public static int $cfg_which_database = 0;

    public $club = null;
    public $awaymatches = null;
    public $homematches = null;
    public $allmatches = null;
    public $teamprojects = 0;
    public $teamseasons = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $input = Factory::getApplication()->getInput();
        self::$clubid = max(0, $input->getInt('cid', 0));
        self::$project_id = $this->projectId;
        self::$teamartsel = max(0, $input->getInt('teamartsel', 0));
        self::$type = max(0, $input->getInt('type', 0));
        self::$teamprojectssel = max(0, $input->getInt('teamprojectssel', 0));
        self::$teamseasonssel = max(0, $input->getInt('teamseasonssel', 0));
        self::$cfg_which_database = max(0, $input->getInt('cfg_which_database', 0));

        self::setStartDate($input->getString('startdate', ''));
        self::setEndDate($input->getString('enddate', ''));
    }

    public static function setStartDate($date): void
    {
        self::$startdate = self::normaliseDate($date);
    }

    public static function setEndDate($date): void
    {
        self::$enddate = self::normaliseDate($date);
    }

    public static function getClubIconHtmlSimple($logo_small, $country, $type = 1, $with_space = 0)
    {
        $type = (int) $type;

        if ($type === 1) {
            $params = [
                'align' => 'top',
                'border' => 0,
                'width' => 21,
                'height' => 'auto',
            ];

            if ((int) $with_space === 1) {
                $params['style'] = 'padding:1px;';
            }

            $logo = trim((string) $logo_small);
            if ($logo === '') {
                $logo = trim((string) ComponentHelper::getParams('com_sportsmanagement')->get('ph_logo_small', ''));
            }

            return $logo !== '' ? HTMLHelper::image($logo, '', $params) : '';
        }

        if ($type === 2 && trim((string) $country) !== '') {
            return CountryPresentationHelper::flag((string) $country);
        }

        return '';
    }

    public function getClub(): ?object
    {
        if ($this->club !== null) {
            return $this->club ?: null;
        }
        if (self::$clubid <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('c.*')
            ->select("CONCAT_WS(':', c.id, c.alias) AS slug")
            ->from($db->quoteName('#__sportsmanagement_club', 'c'))
            ->where($db->quoteName('c.id') . ' = ' . self::$clubid);
        $db->setQuery($query, 0, 1);
        $this->club = $db->loadObject() ?: false;
        return $this->club ?: null;
    }

    public function getTeamsArt(): array
    {
        if (self::$clubid <= 0) {
            return [];
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('ag.id', 'value'),
                $db->quoteName('ag.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_agegroup', 'ag') . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('t.agegroup_id'))
            ->where($db->quoteName('t.club_id') . ' = ' . self::$clubid)
            ->group([$db->quoteName('ag.id'), $db->quoteName('ag.name')])
            ->order($db->quoteName('ag.name') . ' ASC');
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getTeamsProjects(): array
    {
        if (self::$clubid <= 0) {
            return [];
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('p.id', 'value'),
                $db->quoteName('p.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'pt') . ' ON ' . $db->quoteName('pt.team_id') . ' = ' . $db->quoteName('st.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->where($db->quoteName('t.club_id') . ' = ' . self::$clubid)
            ->group([$db->quoteName('p.id'), $db->quoteName('p.name')])
            ->order($db->quoteName('p.name') . ' DESC');
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getTeamsSeasons(): array
    {
        if (self::$clubid <= 0) {
            return [];
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('s.id', 'value'),
                $db->quoteName('s.name', 'text'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team', 't'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season', 's') . ' ON ' . $db->quoteName('s.id') . ' = ' . $db->quoteName('st.season_id'))
            ->where($db->quoteName('t.club_id') . ' = ' . self::$clubid)
            ->group([$db->quoteName('s.id'), $db->quoteName('s.name')])
            ->order($db->quoteName('s.name') . ' DESC');
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getAllMatches($orderBy = 'ASC', $type = 0): array
    {
        $direction = strtoupper((string) $orderBy) === 'DESC' ? 'DESC' : 'ASC';
        $type = (int) $type;
        $project = self::$project_id > 0 ? $this->loadProject(self::$project_id) : null;
        $this->teamseasons = (int) ($project->season_id ?? self::$teamseasonssel);

        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();
        $startTimestamp = strtotime($startDate . ' 00:00:00') ?: 0;
        $endTimestamp = strtotime($endDate . ' 23:59:59') ?: PHP_INT_MAX;

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('m.id'),
                $db->quoteName('m.id', 'match_id'),
                $db->quoteName('m.match_date'),
                $db->quoteName('m.match_timestamp'),
                $db->quoteName('m.projectteam1_id'),
                $db->quoteName('m.projectteam2_id'),
                "DATE_FORMAT(m.time_present, '%H:%i') AS time_present",
                $db->quoteName('m.playground_id'),
                $db->quoteName('m.alt_decision'),
                $db->quoteName('m.team1_result'),
                $db->quoteName('m.team2_result'),
                $db->quoteName('m.team1_result_decision'),
                $db->quoteName('m.team2_result_decision'),
                $db->quoteName('m.cancel'),
                $db->quoteName('m.cancel_reason'),
                $db->quoteName('m.match_number'),
                $db->quoteName('p.name', 'project_name'),
                $db->quoteName('p.id', 'project_id'),
                $db->quoteName('p.id', 'prid'),
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                $db->quoteName('r.id', 'roundid'),
                $db->quoteName('r.roundcode'),
                $db->quoteName('r.name', 'roundname'),
                $db->quoteName('l.name', 'l_name'),
                $db->quoteName('playground.name', 'pl_name'),
                "CONCAT_WS(':', playground.id, playground.alias) AS playground_slug",
                $db->quoteName('t1.club_id', 't1club_id'),
                $db->quoteName('t1.id', 'team1_id'),
                $db->quoteName('t1.name', 'tname1'),
                $db->quoteName('t1.short_name', 'tname1_short'),
                $db->quoteName('t1.middle_name', 'tname1_middle'),
                $db->quoteName('t1.club_id', 'club1_id'),
                "CONCAT_WS(':', t1.id, t1.alias) AS team1_slug",
                $db->quoteName('t2.club_id', 't2club_id'),
                $db->quoteName('t2.id', 'team2_id'),
                $db->quoteName('t2.name', 'tname2'),
                $db->quoteName('t2.short_name', 'tname2_short'),
                $db->quoteName('t2.middle_name', 'tname2_middle'),
                $db->quoteName('t2.club_id', 'club2_id'),
                "CONCAT_WS(':', t2.id, t2.alias) AS team2_slug",
                $db->quoteName('c1.logo_small', 'home_logo_small'),
                "CONCAT_WS(':', c1.id, c1.alias) AS club1_slug",
                $db->quoteName('c1.country', 'club1_country'),
                $db->quoteName('c2.logo_small', 'away_logo_small'),
                "CONCAT_WS(':', c2.id, c2.alias) AS club2_slug",
                $db->quoteName('c2.country', 'club2_country'),
                $db->quoteName('c1.logo_big', 'home_logo_big'),
                $db->quoteName('c2.logo_big', 'away_logo_big'),
                $db->quoteName('c1.logo_middle', 'home_logo_middle'),
                $db->quoteName('c2.logo_middle', 'away_logo_middle'),
                $db->quoteName('tj1.division_id'),
                "CONCAT_WS(':', m.projectteam1_id, t1.alias) AS projectteam1_slug",
                "CONCAT_WS(':', m.projectteam2_id, t2.alias) AS projectteam2_slug",
                $db->quoteName('d.name', 'division_name'),
                $db->quoteName('d.shortname', 'division_shortname'),
                $db->quoteName('d.parent_id', 'parent_division_id'),
                "CONCAT_WS(':', d.id, d.alias) AS division_slug",
                "CONCAT_WS(':', m.id, CONCAT_WS('_', t1.alias, t2.alias)) AS match_slug",
                "CONCAT_WS(':', r.id, r.alias) AS round_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_match', 'm'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'tj1') . ' ON ' . $db->quoteName('tj1.id') . ' = ' . $db->quoteName('m.projectteam1_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project_team', 'tj2') . ' ON ' . $db->quoteName('tj2.id') . ' = ' . $db->quoteName('m.projectteam2_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st1') . ' ON ' . $db->quoteName('st1.id') . ' = ' . $db->quoteName('tj1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_season_team_id', 'st2') . ' ON ' . $db->quoteName('st2.id') . ' = ' . $db->quoteName('tj2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't1') . ' ON ' . $db->quoteName('t1.id') . ' = ' . $db->quoteName('st1.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_team', 't2') . ' ON ' . $db->quoteName('t2.id') . ' = ' . $db->quoteName('st2.team_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('tj1.project_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_league', 'l') . ' ON ' . $db->quoteName('p.league_id') . ' = ' . $db->quoteName('l.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c1') . ' ON ' . $db->quoteName('c1.id') . ' = ' . $db->quoteName('t1.club_id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_round', 'r') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
            ->join('INNER', $db->quoteName('#__sportsmanagement_club', 'c2') . ' ON ' . $db->quoteName('c2.id') . ' = ' . $db->quoteName('t2.club_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_playground', 'playground') . ' ON ' . $db->quoteName('playground.id') . ' = ' . $db->quoteName('m.playground_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd') . ' ON ' . $db->quoteName('d.id') . ' = ' . $db->quoteName('tj1.division_id'))
            ->where($db->quoteName('p.published') . ' = 1')
            ->where($db->quoteName('m.published') . ' = 1')
            ->where($db->quoteName('m.match_timestamp') . ' >= ' . (int) $startTimestamp)
            ->where($db->quoteName('m.match_timestamp') . ' <= ' . (int) $endTimestamp);

        if (self::$project_id > 0) {
            $query->where($db->quoteName('p.id') . ' = ' . self::$project_id);
        }
        if (self::$teamartsel > 0) {
            $query->where('(' . $db->quoteName('t1.agegroup_id') . ' = ' . self::$teamartsel
                . ' OR ' . $db->quoteName('t2.agegroup_id') . ' = ' . self::$teamartsel . ')');
        }
        if (self::$teamseasonssel > 0) {
            $query->where($db->quoteName('p.season_id') . ' = ' . self::$teamseasonssel);
        }

        if (self::$clubid > 0) {
            if ($type === 1) {
                $query->where($db->quoteName('t1.club_id') . ' = ' . self::$clubid);
            } elseif ($type === 2) {
                $query->where($db->quoteName('t2.club_id') . ' = ' . self::$clubid);
            } else {
                $query->where('(' . $db->quoteName('t1.club_id') . ' = ' . self::$clubid
                    . ' OR ' . $db->quoteName('t2.club_id') . ' = ' . self::$clubid . ')');
            }
        }

        $query->order($db->quoteName('m.match_date') . ' ' . $direction);
        $db->setQuery($query);
        $this->allmatches = $db->loadObjectList() ?: [];

        if (!$this->allmatches) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_MATCHES'), 'warning');
        }
        return $this->allmatches;
    }

    public function getTeams(): array
    {
        if (self::$clubid <= 0) {
            return [];
        }
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('id'),
                $db->quoteName('name', 'team_name'),
                $db->quoteName('short_name', 'team_shortcut'),
                $db->quoteName('info', 'team_description'),
            ])
            ->from($db->quoteName('#__sportsmanagement_team'))
            ->where($db->quoteName('club_id') . ' = ' . self::$clubid)
            ->order($db->quoteName('name') . ' ASC');
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getStartDate(): string
    {
        if (!empty(self::$startdate)) {
            return self::$startdate;
        }

        $config = $this->getTemplateConfig('clubplan');
        if (!empty($config['use_project_start_date']) && self::$project_id > 0) {
            $project = $this->loadProject(self::$project_id);
            $projectStart = self::normaliseDate($project->start_date ?? null);
            if ($projectStart !== null) {
                self::$startdate = $projectStart;
                return self::$startdate;
            }
        }

        $daysBefore = max(0, (int) ($config['days_before'] ?? 6));
        self::$startdate = date('Y-m-d', strtotime('-' . $daysBefore . ' days'));
        return self::$startdate;
    }

    public function getEndDate(): string
    {
        if (!empty(self::$enddate)) {
            return self::$enddate;
        }

        $config = $this->getTemplateConfig('clubplan');
        $daysAfter = max(0, (int) ($config['days_after'] ?? 6));
        self::$enddate = date('Y-m-d', strtotime('+' . $daysAfter . ' days'));
        return self::$enddate;
    }

    private function loadProject(int $projectId): ?object
    {
        if ($projectId <= 0) {
            return null;
        }
        if ($projectId === $this->projectId) {
            return $this->getProject();
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_project'))
            ->where($db->quoteName('id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }

    private static function normaliseDate($date): ?string
    {
        $value = trim((string) $date);
        if ($value === '') {
            return null;
        }
        $timestamp = strtotime($value);
        return $timestamp === false ? null : date('Y-m-d', $timestamp);
    }
}
