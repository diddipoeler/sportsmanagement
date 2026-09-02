<?php
/**
 * Native Joomla 5/6 link builder trait for the matches module.
 *
 * @version   5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementMatches\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

trait NativeLinkTrait
{
    /** @return array<int,array{label:string,url:string,external:bool}> */
    private function teamLinks(object $match, string $n, Registry $params): array
    {
        $mode = (int) $params->get('link_teams', 0);
        if ($mode <= 0) {
            return [];
        }
        $teamId = (int) $match->{'team' . $n . '_id'};
        $clubId = (int) $match->{'club' . $n . '_id'};
        $base = ['p' => (string) $match->project_slug, 'tid' => $teamId, 'cfg_which_database' => (int) $params->get('cfg_which_database', 0)];
        $links = [];
        foreach ([
            'plan' => ['teamplan', 'link_team_plan_text', 'Schedule'],
            'curve' => ['curve', 'link_team_curve_text', 'Fever Chart'],
            'teaminfo' => ['teaminfo', 'link_team_teaminfo_text', 'Team Info'],
            'roster' => ['roster', 'link_team_roster_text', 'Roster'],
            'club' => ['clubinfo', 'link_team_club_text', 'Club'],
        ] as $key => [$view, $textKey, $fallback]) {
            if ((int) $params->get('link_team_' . $key, 0) !== 1) {
                continue;
            }
            $query = $base;
            if ($key === 'club') {
                unset($query['tid']);
                $query['cid'] = $clubId;
            }
            $links[] = ['label' => (string) $params->get($textKey, $fallback), 'url' => $this->route($view, $query), 'external' => false];
        }
        $website = trim((string) $match->{'club' . $n . '_website'});
        if ((int) $params->get('link_team_www', 0) === 1 && $website !== '') {
            $links[] = ['label' => (string) $params->get('link_team_www_text', 'WWW'), 'url' => $website, 'external' => true];
        }
        return $links;
    }

    /** @return array<int,array{label:string,url:string}> */
    private function matchLinks(object $match, Registry $params): array
    {
        $links = [];
        $common = ['p' => (string) $match->project_slug, 'cfg_which_database' => (int) $params->get('cfg_which_database', 0)];
        if ((int) $params->get('show_act_report_link', 0) === 1 && (int) $match->show_report === 1) {
            $links[] = ['label' => (string) $params->get('show_act_report_text', 'Show Report'), 'url' => $this->route('matchreport', $common + ['mid' => (string) $match->match_slug])];
        }
        if ((int) $params->get('show_statistic_link', 0) === 1) {
            $links[] = ['label' => (string) $params->get('statistic_link_text', 'Season statistics'), 'url' => $this->route('stats', $common)];
        }
        if ((int) $params->get('show_nextmatch_link', 0) === 1) {
            $links[] = ['label' => (string) $params->get('nextmatch_link_text', 'Nextmatch'), 'url' => $this->route('nextmatch', $common + ['mid' => (string) $match->match_slug])];
        }
        return $links;
    }

    /** @param array<int,int> $projectIds @return array{previous:?array{label:string,url:string},next:?array{label:string,url:string}} */
    private function neighborLinks(DatabaseInterface $db, object $match, Registry $params, array $projectIds): array
    {
        $out = ['previous' => null, 'next' => null];
        foreach (['previous' => ['<', 'DESC', (string) $params->get('last_text', '<<')], 'next' => ['>', 'ASC', (string) $params->get('next_text', '>>')]] as $key => [$operator, $direction, $label]) {
            $q = $db->getQuery(true)
                ->select(['m.id', 'p.id AS project_id', 'p.alias AS project_alias', 't1.alias AS team1_alias', 't2.alias AS team2_alias'])
                ->from('#__sportsmanagement_match AS m')
                ->join('INNER', '#__sportsmanagement_round AS r ON r.id = m.round_id')
                ->join('INNER', '#__sportsmanagement_project AS p ON p.id = r.project_id')
                ->join('LEFT', '#__sportsmanagement_project_team AS pt1 ON pt1.id = m.projectteam1_id')
                ->join('LEFT', '#__sportsmanagement_project_team AS pt2 ON pt2.id = m.projectteam2_id')
                ->join('LEFT', '#__sportsmanagement_season_team_id AS st1 ON st1.id = pt1.team_id')
                ->join('LEFT', '#__sportsmanagement_season_team_id AS st2 ON st2.id = pt2.team_id')
                ->join('LEFT', '#__sportsmanagement_team AS t1 ON t1.id = st1.team_id')
                ->join('LEFT', '#__sportsmanagement_team AS t2 ON t2.id = st2.team_id')
                ->where(((int) $params->get('nextlast_from_same_project', 1) === 1 ? 'r.project_id = ' . (int) $match->project_id : 'r.project_id IN (' . implode(',', $projectIds) . ')'))
                ->where('m.published = 1')->where('m.match_timestamp ' . $operator . ' ' . (int) $match->match_timestamp)
                ->order('m.match_timestamp ' . $direction);
            $db->setQuery($q, 0, 1);
            $neighbor = $db->loadObject();
            if ($neighbor) {
                $p = $neighbor->project_id . ':' . $neighbor->project_alias;
                $mid = $neighbor->id . ':' . trim($neighbor->team1_alias . '_' . $neighbor->team2_alias, '_');
                $out[$key] = ['label' => $label, 'url' => $this->route('matchreport', ['p' => $p, 'mid' => $mid, 'cfg_which_database' => (int) $params->get('cfg_which_database', 0)])];
            }
        }
        return $out;
    }

    private function componentRoute(string $view, object $match, array $extra, Registry $params): string
    {
        return $this->route($view, ['p' => (string) $match->project_slug, 'cfg_which_database' => (int) $params->get('cfg_which_database', 0)] + $extra);
    }

    private function route(string $view, array $query): string
    {
        return SiteRouteHelper::view($view, $query);
    }
}
