<?php
/**
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementRanking\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\RankingEngine;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

final class RankingHelper
{
    public function getData(Registry $params, object $module, CMSApplicationInterface $app): array
    {
        $projectId = max(0, (int) $params->get('p', 0));

        if ($projectId <= 0) {
            return [];
        }

        $cfg = (int) $params->get('cfg_which_database', 0);
        $divisionId = max(0, (int) $params->get('division_id', 0));
        $engine = new RankingEngine($this->database($params, $app));
        $rankingResult = $engine->calculate($projectId, $divisionId);
        $project = $rankingResult['project'];

        if (empty($project->id)) {
            return [];
        }

        $list = array_values($rankingResult['ranking']);
        $visibleTeamId = $this->firstId($params->get('visible_team'));
        $limit = max(1, min(100, (int) $params->get('limit', 5)));

        if ($visibleTeamId > 0) {
            $list = $this->shrinkAroundTeam($list, $visibleTeamId, $limit);
        }

        $columns = array_values(array_filter(array_map('trim', explode(',', (string) $params->get('columns', 'PLAYED, POINTS')))));
        $columnNames = array_values(array_map('trim', explode(',', (string) $params->get('column_names', 'MP, PTS'))));

        if (count($columns) !== count($columnNames)) {
            $columns = [];
            $columnNames = [];
        }

        $flagMap = $this->countryFlags($params, $list, $app);
        $nameType = (string) $params->get('nametype', 'short_name');

        if (!in_array($nameType, ['name', 'short_name', 'middle_name'], true)) {
            $nameType = 'short_name';
        }

        $logoType = (int) $params->get('show_logo', 0);

        foreach ($list as $row) {
            if (!$row->team) {
                continue;
            }

            $row->display_team_name = (string) ($row->team->{$nameType} ?: $row->team->name);
            $row->logo_url = $this->logoUrl($row->team, $logoType, $flagMap);
            $row->team_url = $this->teamUrl($params, $project, $row->team);
            $row->column_values = [];

            foreach ($columns as $column) {
                $row->column_values[$column] = $this->columnValue($column, $row);
            }
        }

        $colors = (int) $params->get('show_rank_colors', 0) ? $rankingResult['colors'] : [];
        $fullTableUrl = $this->route('ranking', [
            'cfg_which_database' => $cfg,
            's' => (int) $params->get('s', 0),
            'p' => (string) ($project->slug ?? $projectId),
            'type' => 0,
            'r' => (string) ($project->round_slug ?? ''),
            'from' => 0,
            'to' => 0,
            'division' => $divisionId,
        ]);

        $canRefresh = (bool) $params->get('ishd_update', 0)
            && $app->getIdentity()->authorise('core.manage', 'com_sportsmanagement');

        return [
            'project' => $project,
            'ranking' => array_slice($list, 0, $limit),
            'colors' => $colors,
            'columns' => $columns,
            'column_names' => $columnNames,
            'full_table_url' => $fullTableUrl,
            'can_refresh' => $canRefresh,
            'refresh_url' => rtrim((string) Uri::root(), '/') . '/index.php?option=com_ajax&module=sportsmanagement_ranking&method=refresh&format=json',
            'module_id' => (int) ($module->id ?? 0),
        ];
    }

    public function refreshAjax(): array
    {
        $container = Factory::getContainer();
        /** @var SiteApplication $app */
        $app = $container->get(SiteApplication::class);

        if (!Session::checkToken('post')) {
            throw new \RuntimeException('Invalid CSRF token.', 403);
        }

        if (!$app->getIdentity()->authorise('core.manage', 'com_sportsmanagement')) {
            throw new \RuntimeException('Not authorised to refresh SportsManagement match data.', 403);
        }

        $moduleId = $app->getInput()->post->getInt('module_id', 0);

        if ($moduleId <= 0) {
            throw new \RuntimeException('Invalid ranking module.', 400);
        }

        /** @var DatabaseInterface $joomlaDb */
        $joomlaDb = $container->get(DatabaseInterface::class);
        $query = $joomlaDb->getQuery(true)
            ->select([$joomlaDb->quoteName('params'), $joomlaDb->quoteName('published')])
            ->from($joomlaDb->quoteName('#__modules'))
            ->where($joomlaDb->quoteName('id') . ' = ' . $moduleId)
            ->where($joomlaDb->quoteName('module') . ' = ' . $joomlaDb->quote('mod_sportsmanagement_ranking'))
            ->where($joomlaDb->quoteName('client_id') . ' = 0');
        $joomlaDb->setQuery($query, 0, 1);
        $module = $joomlaDb->loadObject();

        if (!$module || (int) $module->published !== 1) {
            throw new \RuntimeException('Ranking module is not published.', 404);
        }

        $params = new Registry((string) $module->params);

        if (!(int) $params->get('ishd_update', 0)) {
            throw new \RuntimeException('Inline-hockey refresh is disabled for this module.', 403);
        }

        if ((int) $params->get('cfg_which_database', 0) !== 0) {
            throw new \RuntimeException('Inline-hockey refresh is restricted to the Joomla database.', 409);
        }

        $projectId = max(0, (int) $params->get('p', 0));

        if ($projectId <= 0) {
            throw new \RuntimeException('Ranking module has no valid project.', 400);
        }

        $hours = max(1, min(168, (int) $params->get('ishd_update_hour', 4)));
        $cutoff = time() - ($hours * 3600);
        $query = $joomlaDb->getQuery(true)
            ->select('COUNT(*)')
            ->from($joomlaDb->quoteName('#__sportsmanagement_match', 'm'))
            ->join(
                'INNER',
                $joomlaDb->quoteName('#__sportsmanagement_round', 'r')
                . ' ON ' . $joomlaDb->quoteName('r.id') . ' = ' . $joomlaDb->quoteName('m.round_id')
            )
            ->where($joomlaDb->quoteName('r.project_id') . ' = ' . $projectId)
            ->where($joomlaDb->quoteName('m.team1_result') . ' IS NULL')
            ->where($joomlaDb->quoteName('m.match_timestamp') . ' < ' . $cutoff);
        $joomlaDb->setQuery($query);
        $pending = (int) $joomlaDb->loadResult();

        if ($pending <= 0) {
            return ['updated' => false, 'pending' => 0, 'project_id' => $projectId];
        }

        $modelFile = JPATH_SITE . '/components/com_sportsmanagement/extensions/jsminlinehockey/admin/models/jsminlinehockey.php';

        if (!is_file($modelFile)) {
            throw new \RuntimeException('Inline-hockey importer is not installed.', 500);
        }

        require_once $modelFile;

        if (!class_exists('sportsmanagementModeljsminlinehockey')) {
            throw new \RuntimeException('Inline-hockey importer could not be loaded.', 500);
        }

        $model = new \sportsmanagementModeljsminlinehockey();
        $model->getmatches($projectId);

        return ['updated' => true, 'pending' => $pending, 'project_id' => $projectId];
    }

    private function countryFlags(Registry $params, array $rows, CMSApplicationInterface $app): array
    {
        $countries = [];

        foreach ($rows as $row) {
            $country = strtoupper(trim((string) ($row->team->country ?? '')));

            if ($country !== '') {
                $countries[$country] = $country;
            }
        }

        if (!$countries) {
            return [];
        }

        $db = $this->database($params, $app);
        $quoted = array_map([$db, 'quote'], array_values($countries));
        $query = $db->getQuery(true)
            ->select([$db->quoteName('alpha3'), $db->quoteName('picture')])
            ->from($db->quoteName('#__sportsmanagement_countries'))
            ->where($db->quoteName('alpha3') . ' IN (' . implode(',', $quoted) . ')');
        $db->setQuery($query);
        $map = [];

        foreach ($db->loadObjectList() ?: [] as $country) {
            $map[strtoupper((string) $country->alpha3)] = $this->mediaUrl((string) $country->picture);
        }

        return $map;
    }

    private function teamUrl(Registry $params, object $project, object $team): string
    {
        $base = [
            'cfg_which_database' => (int) $params->get('cfg_which_database', 0),
            's' => (int) $params->get('s', 0),
            'p' => (string) ($project->slug ?? $project->id),
        ];

        return match ((string) $params->get('teamlink', 'none')) {
            'teaminfo' => $this->route('teaminfo', $base + ['tid' => $team->team_slug, 'ptid' => (int) $team->projectteamid]),
            'roster' => $this->route('roster', $base + ['tid' => $team->team_slug, 'ptid' => (int) $team->projectteamid]),
            'teamplan' => $this->route('teamplan', $base + ['tid' => $team->team_slug, 'ptid' => (int) $team->projectteamid, 'division' => (string) ($team->division_slug ?? 0), 'mode' => 0]),
            'clubinfo' => $this->route('clubinfo', $base + ['cid' => (string) ($team->club_slug ?? '')]),
            default => '',
        };
    }

    private function logoUrl(object $team, int $type, array $flags): string
    {
        if ($type === 2) {
            return $flags[strtoupper((string) ($team->country ?? ''))] ?? '';
        }

        $path = match ($type) {
            1 => (string) ($team->logo_small ?? ''),
            3 => (string) ($team->logo_middle ?? ''),
            4 => (string) ($team->logo_big ?? ''),
            5 => (string) ($team->trikot_home ?? ''),
            6 => (string) ($team->trikot_away ?? ''),
            default => '',
        };

        return $this->mediaUrl($path);
    }

    private function columnValue(string $column, object $item): mixed
    {
        $key = strtolower(str_replace('jl_', '', trim($column)));

        return match ($key) {
            'points' => $item->getPoints(),
            'played' => $item->cnt_matches,
            'wins' => $item->cnt_won,
            'ties' => $item->cnt_draw,
            'losses' => $item->cnt_lost,
            'wot' => $item->cnt_wot,
            'wso' => $item->cnt_wso,
            'lot' => $item->cnt_lot,
            'lso' => $item->cnt_lso,
            'scorefor' => $item->sum_team1_result,
            'scoreagainst' => $item->sum_team2_result,
            'results' => $item->sum_team1_result . ':' . $item->sum_team2_result,
            'diff', 'scorediff' => $item->diff_team_results,
            'scorepct' => round($item->scorePct(), 2),
            'bonus' => $item->bonus_points,
            'start' => $item->cnt_lost,
            'winpct' => round($item->winpct(), 2),
            'legs' => $item->sum_team1_legs . ':' . $item->sum_team2_legs,
            'legsdiff' => $item->diff_team_legs,
            'legsratio' => round($item->legsRatio(), 2),
            'negpoints' => $item->neg_points,
            'oldnegpoints' => $item->getPoints() . ':' . $item->neg_points,
            'pointsratio' => round($item->pointsRatio(), 2),
            'gfa' => round($item->getGFA(), 2),
            'gaa' => round($item->getGAA(), 2),
            'ppg' => round($item->getPPG(), 2),
            'ppp' => round($item->getPPP(), 2),
            default => $item->{$key} ?? '?',
        };
    }

    private function shrinkAroundTeam(array $ranking, int $teamId, int $limit): array
    {
        foreach ($ranking as $index => $item) {
            if ((int) ($item->team->id ?? 0) !== $teamId) {
                continue;
            }

            $other = max(0, $limit - 1);
            $start = $index - intdiv($other, 2) - ($other % 2);

            return array_slice($ranking, max(0, $start), $limit);
        }

        return $ranking;
    }

    private function firstId(mixed $value): int
    {
        if (is_array($value)) {
            $value = reset($value);
        }

        return preg_match('/^\s*(\d+)/', (string) $value, $match) ? (int) $match[1] : 0;
    }

    private function route(string $view, array $parameters): string
    {
        return SiteRouteHelper::view($view, $parameters);
    }

    private function mediaUrl(string $path): string
    {
        if ($path === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        return rtrim((string) Uri::root(), '/') . '/' . ltrim($path, '/');
    }

    private function database(Registry $params, CMSApplicationInterface $app): DatabaseInterface
    {
        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);

        return SportsManagementDatabaseResolver::resolve(
            $joomlaDatabase,
            (int) $params->get('cfg_which_database', 0)
        );
    }
}
