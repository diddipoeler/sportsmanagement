<?php
namespace Diddipoeler\Component\SportsManagement\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Registry\Registry;

abstract class SportsManagementProjectModel extends SportsManagementModel
{
    protected int $projectId = 0;
    protected int $divisionId = 0;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);
        $input = $this->siteApplication()->getInput();
        $this->projectId = $input->getInt('p', 0);
        $this->divisionId = $input->getInt('division', 0);
    }

    public function getProjectId(): int
    {
        return $this->projectId;
    }

    public function getDivisionId(): int
    {
        return $this->divisionId;
    }

    public function getProject(): ?object
    {
        if ($this->projectId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
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
            ->where($db->quoteName('p.id') . ' = ' . $this->projectId);
        $db->setQuery($query, 0, 1);
        $project = $db->loadObject();

        if (!$project) {
            return null;
        }

        $sportName = (string) ($project->sport_type_name ?? '');
        $prefix = 'COM_SPORTSMANAGEMENT_ST_';
        $project->fs_sport_type_name = strtolower(str_starts_with($sportName, $prefix) ? substr($sportName, strlen($prefix)) : $sportName);

        $logoQuery = $db->getQuery(true)
            ->select($db->quoteName('logo_big'))
            ->from($db->quoteName('#__sportsmanagement_league_logos'))
            ->where($db->quoteName('league_id') . ' = ' . (int) $project->league_id)
            ->where($db->quoteName('season_id') . ' = ' . (int) $project->season_id);
        $db->setQuery($logoQuery, 0, 1);
        $seasonLogo = $db->loadResult();

        if ($seasonLogo) {
            $project->leaguepicture = $seasonLogo;
        }

        return $project;
    }

    public function getCurrentRound(): int
    {
        $round = $this->resolveCurrentRound();
        return $round ? (int) $round->id : 0;
    }

    public function getCurrentRoundNumber(): int
    {
        $round = $this->resolveCurrentRound();
        return $round ? (int) $round->roundcode : 0;
    }

    public function getRounds(string $ordering = 'ASC', bool $slug = true): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $direction = strtoupper($ordering) === 'DESC' ? 'DESC' : 'ASC';
        $query = $db->getQuery(true);
        if ($slug) {
            $query->select("CONCAT_WS(':', r.id, r.alias) AS id");
        } else {
            $query->select($db->quoteName('r.id'));
        }
        $query->select([
                $db->quoteName('r.round_date_first'),
                $db->quoteName('r.round_date_last'),
                "CASE LENGTH(r.name) WHEN 0 THEN r.roundcode ELSE r.name END AS name",
                $db->quoteName('r.roundcode'),
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = ' . $this->projectId)
            ->order($db->quoteName('r.roundcode') . ' ' . $direction);
        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    private function resolveCurrentRound(): ?object
    {
        $project = $this->getProject();
        if (!$project) {
            return null;
        }

        $db = $this->getDatabase();
        $mode = (int) ($project->current_round_auto ?? 0);
        $autoTime = (int) ($project->auto_time ?? 0);
        if ($autoTime <= 0) {
            $autoTime = 7200;
        }
        $currentDate = date('Y-m-d');

        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('r.id'),
                $db->quoteName('r.roundcode'),
                "CONCAT_WS(':', r.id, r.alias) AS round_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_round', 'r'))
            ->where($db->quoteName('r.project_id') . ' = ' . $this->projectId);

        switch ($mode) {
            case 0:
                if ((int) ($project->current_round ?? 0) > 0) {
                    $query->where($db->quoteName('r.id') . ' = ' . (int) $project->current_round);
                }
                break;
            case 1:
                $query->where('(r.round_date_first - INTERVAL ' . $autoTime . ' MINUTE < ' . $db->quote($currentDate) . ')')
                    ->order($db->quoteName('r.round_date_first') . ' DESC');
                break;
            case 2:
                $query->where('(r.round_date_last - INTERVAL ' . $autoTime . ' MINUTE < ' . $db->quote($currentDate) . ')')
                    ->order($db->quoteName('r.round_date_first') . ' DESC');
                break;
            case 3:
                $query->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
                    ->where('(m.match_date - INTERVAL ' . $autoTime . ' MINUTE < ' . $db->quote($currentDate) . ')')
                    ->order($db->quoteName('m.match_date') . ' DESC');
                break;
            case 4:
                $query->join('INNER', $db->quoteName('#__sportsmanagement_match', 'm') . ' ON ' . $db->quoteName('m.round_id') . ' = ' . $db->quoteName('r.id'))
                    ->where('(m.match_date + INTERVAL ' . $autoTime . ' MINUTE < ' . $db->quote($currentDate) . ')')
                    ->order($db->quoteName('m.match_date') . ' ASC');
                break;
        }

        $db->setQuery($query, 0, 1);
        $round = $db->loadObject();

        if (!$round && (int) ($project->current_round ?? 0) > 0) {
            $fallback = $db->getQuery(true)
                ->select([
                    $db->quoteName('r.id'),
                    $db->quoteName('r.roundcode'),
                    "CONCAT_WS(':', r.id, r.alias) AS round_slug",
                ])
                ->from($db->quoteName('#__sportsmanagement_round', 'r'))
                ->where($db->quoteName('r.id') . ' = ' . (int) $project->current_round)
                ->where($db->quoteName('r.project_id') . ' = ' . $this->projectId);
            $db->setQuery($fallback, 0, 1);
            $round = $db->loadObject();
        }

        if (!$round) {
            $fallback = $db->getQuery(true)
                ->select([
                    $db->quoteName('r.id'),
                    $db->quoteName('r.roundcode'),
                    "CONCAT_WS(':', r.id, r.alias) AS round_slug",
                ])
                ->from($db->quoteName('#__sportsmanagement_round', 'r'))
                ->where($db->quoteName('r.project_id') . ' = ' . $this->projectId)
                ->order($db->quoteName('r.roundcode') . (in_array($mode, [0, 2], true) ? ' DESC' : ' ASC'));
            $db->setQuery($fallback, 0, 1);
            $round = $db->loadObject();
        }

        if ($round && (int) ($project->current_round ?? 0) !== (int) $round->id) {
            $update = (object) [
                'id' => $this->projectId,
                'current_round' => (int) $round->id,
            ];
            $db->updateObject('#__sportsmanagement_project', $update, 'id');
        }

        return $round ?: null;
    }

    public function getOverallConfig(): array
    {
        return $this->getTemplateConfig('overall');
    }

    public function getTemplateConfig(string $template): array
    {
        $defaults = $this->loadDefaultTemplateConfig($template);

        if ($this->projectId <= 0) {
            return $defaults;
        }

        $params = $this->loadSavedTemplateParams($template, $this->projectId);
        if ($params === null) {
            $project = $this->getProject();
            $masterId = (int) ($project->master_template ?? 0);
            if ($masterId > 0 && $masterId !== $this->projectId) {
                $params = $this->loadSavedTemplateParams($template, $masterId);
            }
        }

        if ($params === null || $params === '') {
            return $defaults;
        }

        try {
            $registry = new Registry();
            $registry->loadString((string) $params);
            return array_merge($defaults, $registry->toArray());
        } catch (\Throwable) {
            return $defaults;
        }
    }

    public function getProjectStats($statId = 0, int $positionId = 0): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        if (!class_exists('SMStatistic')) {
            $baseFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/statistics/base.php';
            if (!is_file($baseFile)) {
                return [];
            }
            require_once $baseFile;
        }

        $statIds = [];
        foreach ((array) $statId as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $statIds[$id] = $id;
            }
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('ppos.id', 'pposid'),
                $db->quoteName('ppos.position_id', 'position_id'),
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
                . ' AND ' . $db->quoteName('ppos.project_id') . ' = ' . $this->projectId)
            ->join('INNER', $db->quoteName('#__sportsmanagement_position', 'pos') . ' ON ' . $db->quoteName('pos.id') . ' = ' . $db->quoteName('ps.position_id'))
            ->where($db->quoteName('stat.published') . ' = 1')
            ->where($db->quoteName('pos.published') . ' = 1')
            ->order([
                $db->quoteName('pos.ordering') . ' ASC',
                $db->quoteName('ps.ordering') . ' ASC',
            ]);

        if ($statIds) {
            $query->where($db->quoteName('stat.id') . ' IN (' . implode(',', array_values($statIds)) . ')');
        }

        if ($positionId > 0) {
            $query->where($db->quoteName('ppos.position_id') . ' = ' . $positionId);
        }

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
            } catch (\Throwable) {
                continue;
            }

            $rowPositionId = (int) $row->position_id;
            $rowStatId = (int) $row->id;
            if ($positionId > 0) {
                $stats[$rowStatId] = $stat;
            } else {
                $stats[$rowPositionId][$rowStatId] = $stat;
            }
        }

        return $stats;
    }

    public function getProjectTeams(?int $divisionId = null, int $playgroundId = 0): array
    {
        if ($this->projectId <= 0) {
            return [];
        }

        $divisionId ??= $this->divisionId;
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('pt.id', 'projectteamid'),
                $db->quoteName('pt.division_id'),
                $db->quoteName('pt.standard_playground'),
                $db->quoteName('pt.admin'),
                $db->quoteName('pt.start_points'),
                $db->quoteName('pt.points_finally'),
                $db->quoteName('pt.neg_points_finally'),
                $db->quoteName('pt.matches_finally'),
                $db->quoteName('pt.won_finally'),
                $db->quoteName('pt.draws_finally'),
                $db->quoteName('pt.lost_finally'),
                $db->quoteName('pt.homegoals_finally'),
                $db->quoteName('pt.guestgoals_finally'),
                $db->quoteName('pt.diffgoals_finally'),
                $db->quoteName('pt.info'),
                $db->quoteName('pt.reason'),
                $db->quoteName('pt.team_id', 'project_team_team_id'),
                $db->quoteName('pt.checked_out'),
                $db->quoteName('pt.checked_out_time'),
                $db->quoteName('pt.is_in_score'),
                $db->quoteName('pt.picture', 'projectteam_picture'),
                $db->quoteName('pt.project_id'),
                $db->quoteName('t.id'),
                $db->quoteName('t.name'),
                $db->quoteName('t.name', 'team_name'),
                $db->quoteName('t.short_name'),
                $db->quoteName('t.middle_name'),
                $db->quoteName('t.notes'),
                $db->quoteName('t.club_id'),
                $db->quoteName('t.website', 'team_www'),
                $db->quoteName('t.picture', 'team_picture'),
                $db->quoteName('u.username'),
                $db->quoteName('u.email'),
                $db->quoteName('st.team_id'),
                $db->quoteName('c.name', 'club_name'),
                $db->quoteName('c.address', 'club_address'),
                $db->quoteName('c.zipcode', 'club_zipcode'),
                $db->quoteName('c.state', 'club_state'),
                $db->quoteName('c.location', 'club_location'),
                $db->quoteName('c.unique_id'),
                $db->quoteName('c.country', 'club_country'),
                $db->quoteName('c.email', 'club_email'),
                $db->quoteName('c.phone', 'club_phone'),
                $db->quoteName('c.fax', 'club_fax'),
                $db->quoteName('c.logo_small'),
                $db->quoteName('c.logo_middle'),
                $db->quoteName('c.logo_big'),
                $db->quoteName('c.country'),
                $db->quoteName('c.website', 'club_www'),
                $db->quoteName('c.new_club_id'),
                $db->quoteName('c.facebook'),
                $db->quoteName('c.twitter'),
                $db->quoteName('c.instagram'),
                $db->quoteName('c.trikot_home'),
                $db->quoteName('c.trikot_away'),
                $db->quoteName('d.name', 'division_name'),
                $db->quoteName('d.shortname', 'division_shortname'),
                $db->quoteName('d.parent_id', 'parent_division_id'),
                $db->quoteName('plg.name', 'playground_name'),
                $db->quoteName('plg.short_name', 'playground_short_name'),
                "COALESCE(NULLIF(t.picture, ''), c.logo_small) AS picture",
                "CONCAT_WS(':', p.id, p.alias) AS project_slug",
                "CONCAT_WS(':', t.id, t.alias) AS team_slug",
                "CONCAT_WS(':', pt.id, t.alias) AS projectteam_slug",
                "CONCAT_WS(':', d.id, d.alias) AS division_slug",
                "CONCAT_WS(':', c.id, c.alias) AS club_slug",
            ])
            ->from($db->quoteName('#__sportsmanagement_project_team', 'pt'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_season_team_id', 'st') . ' ON ' . $db->quoteName('st.id') . ' = ' . $db->quoteName('pt.team_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_team', 't') . ' ON ' . $db->quoteName('st.team_id') . ' = ' . $db->quoteName('t.id'))
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('pt.admin') . ' = ' . $db->quoteName('u.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_club', 'c') . ' ON ' . $db->quoteName('t.club_id') . ' = ' . $db->quoteName('c.id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_division', 'd') . ' ON ' . $db->quoteName('d.id') . ' = ' . $db->quoteName('pt.division_id'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_playground', 'plg') . ' ON ' . $db->quoteName('plg.id') . ' = ' . $db->quoteName('pt.standard_playground'))
            ->join('LEFT', $db->quoteName('#__sportsmanagement_project', 'p') . ' ON ' . $db->quoteName('p.id') . ' = ' . $db->quoteName('pt.project_id'))
            ->where($db->quoteName('pt.project_id') . ' = ' . $this->projectId)
            ->where($db->quoteName('pt.is_in_score') . ' = 1')
            ->order($db->quoteName('t.name') . ' ASC');

        if ($divisionId > 0) {
            $divisionIds = $this->getDivisionTreeIds($divisionId);
            if (!$divisionIds) {
                return [];
            }
            $query->where($db->quoteName('pt.division_id') . ' IN (' . implode(',', array_map('intval', $divisionIds)) . ')');
        }

        if ($playgroundId > 0) {
            $query->where($db->quoteName('pt.standard_playground') . ' = ' . $playgroundId);
        }

        $db->setQuery($query);
        return $db->loadObjectList() ?: [];
    }

    public function getTeamsIndexedById(?int $divisionId = null): array
    {
        $teams = [];
        foreach ($this->getProjectTeams($divisionId) as $team) {
            $teamId = (int) ($team->id ?? 0);
            if ($teamId > 0) {
                $teams[$teamId] = $team;
            }
        }
        return $teams;
    }

    public function getFavTeams(): array
    {
        $project = $this->getProject();
        if (!$project) {
            return [];
        }

        $favorites = [];
        foreach (explode(',', (string) ($project->fav_team ?? '')) as $value) {
            $teamId = (int) trim($value);
            if ($teamId > 0) {
                $favorites[$teamId] = $teamId;
            }
        }
        return array_values($favorites);
    }

    public function getDivision(?int $divisionId = null): ?object
    {
        $divisionId ??= $this->divisionId;
        if ($divisionId <= 0) {
            return null;
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('id') . ' = ' . $divisionId);
        $db->setQuery($query, 0, 1);
        return $db->loadObject() ?: null;
    }

    protected function getDivisionTreeIds(?int $divisionId = null): array
    {
        $divisionId ??= $this->divisionId;
        if ($divisionId <= 0) {
            return [];
        }

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id'), $db->quoteName('parent_id')])
            ->from($db->quoteName('#__sportsmanagement_division'))
            ->where($db->quoteName('project_id') . ' = ' . $this->projectId);
        $db->setQuery($query);
        $divisions = $db->loadObjectList() ?: [];

        $ids = [$divisionId];
        $changed = true;
        while ($changed) {
            $changed = false;
            foreach ($divisions as $division) {
                $id = (int) $division->id;
                $parentId = (int) $division->parent_id;
                if (in_array($parentId, $ids, true) && !in_array($id, $ids, true)) {
                    $ids[] = $id;
                    $changed = true;
                }
            }
        }

        return $ids;
    }

    private function loadSavedTemplateParams(string $template, int $projectId): ?string
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('params'))
            ->from($db->quoteName('#__sportsmanagement_template_config'))
            ->where($db->quoteName('template') . ' = ' . $db->quote($template))
            ->where($db->quoteName('project_id') . ' = ' . $projectId);
        $db->setQuery($query, 0, 1);
        $value = $db->loadResult();
        return $value === null ? null : (string) $value;
    }

    private function loadDefaultTemplateConfig(string $template): array
    {
        $file = JPATH_SITE . '/components/com_sportsmanagement/settings/default/' . basename($template) . '.xml';
        if (!is_file($file)) {
            return [];
        }

        try {
            $xml = simplexml_load_file($file);
        } catch (\Throwable) {
            return [];
        }
        if ($xml === false) {
            return [];
        }

        $defaults = [];
        foreach ($xml->xpath('//field[@name]') ?: [] as $field) {
            $attributes = $field->attributes();
            if (isset($attributes['default'])) {
                $defaults[(string) $attributes['name']] = (string) $attributes['default'];
            }
        }
        return $defaults;
    }
}
