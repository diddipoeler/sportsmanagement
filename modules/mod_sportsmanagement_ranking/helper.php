<?php
/**
 * SportsManagement legacy Ranking helper facade for Joomla 5/6 compatibility.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\RankingEngine;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Module\SportsManagementRanking\Site\Helper\RankingHelper as NativeRankingHelper;
use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

$nativeDependencies = [
    SiteRouteHelper::class => JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php',
    RankingEngine::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/RankingEngine.php',
    SportsManagementDatabaseResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementDatabaseResolver.php',
    SportsManagementSiteApplicationResolver::class => JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php',
    NativeRankingHelper::class => __DIR__ . '/src/Helper/RankingHelper.php',
];

foreach ($nativeDependencies as $class => $file) {
    if (!class_exists($class, false) && is_file($file)) {
        require_once $file;
    }
}

if (!class_exists(NativeRankingHelper::class)) {
    throw new \RuntimeException('SportsManagement native Ranking module helper could not be loaded.', 500);
}

/**
 * Legacy ranking helper facade kept for template overrides and third-party code.
 */
class modJSMRankingHelper extends stdClass
{
    /**
     * Preserve the historical return shape while using the native Joomla 5/6 helper.
     *
     * @param mixed $params
     * @return array{project:?object,ranking:array,colors:array}
     */
    public static function getData(&$params): array
    {
        $registry = $params instanceof Registry ? $params : new Registry((array) $params);
        $app = SportsManagementSiteApplicationResolver::resolve();

        $data = (new NativeRankingHelper())->getData($registry, (object) ['id' => 0], $app);

        return [
            'project' => $data['project'] ?? null,
            'ranking' => $data['ranking'] ?? [],
            'colors' => $data['colors'] ?? [],
        ];
    }

    /**
     * Keep the historic shrink helper callable by old template overrides.
     */
    public static function getShrinkedDataAroundOneTeam($completeRankingList, $alwaysVisibleTeamId, $paramRowLimit)
    {
        $rank = $completeRankingList;
        $i = 0;

        foreach ($rank as $item) {
            $isFav = $item->team->id == $alwaysVisibleTeamId;

            if ($isFav) {
                $limit = $paramRowLimit - 1;
                $startOffset = $i - floor($limit / 2);

                if ($limit % 2 > 0) {
                    $startOffset -= $limit % 2;
                }

                if ($startOffset < 0) {
                    $startOffset = 0;
                }

                return array_slice($rank, $startOffset, $paramRowLimit);
            }

            $i++;
        }

        return $rank;
    }

    /**
     * Count unfinished games old enough to qualify for the inline-hockey update.
     */
    public static function getCountGames($projectid, $ishd_update_hour)
    {
        $app = SportsManagementSiteApplicationResolver::resolve();

        /** @var DatabaseInterface $db */
        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->createQuery();
        $matchestoupdate = 0;
        $projectId = (int) $projectid;
        $matchTimestamp = time() - ((int) $ishd_update_hour * 60 * 60);

        $query->select('COUNT(*) AS ' . $db->quoteName('count'))
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
            ->where($db->quoteName('p.id') . ' = :projectId')
            ->where($db->quoteName('m.team1_result') . ' IS NULL')
            ->where($db->quoteName('m.match_timestamp') . ' < :matchTimestamp')
            ->bind(':projectId', $projectId, ParameterType::INTEGER)
            ->bind(':matchTimestamp', $matchTimestamp, ParameterType::INTEGER);

        try {
            $db->setQuery($query);
            $matchestoupdate = (int) $db->loadResult();
        } catch (\Throwable $e) {
            $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'error');
            $app->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'error');
        }

        return $matchestoupdate;
    }

    /**
     * Return the value corresponding to a ranking column.
     */
    public static function getColValue($column, $item)
    {
        $column = strtolower(ucfirst(str_replace('jl_', '', strtolower(trim($column)))));

        switch ($column) {
            case 'points':
                return $item->getPoints();
            case 'played':
                return $item->cnt_matches;
            case 'wins':
                return $item->cnt_won;
            case 'ties':
                return $item->cnt_draw;
            case 'losses':
                return $item->cnt_lost;
            case 'wot':
                return $item->cnt_wot;
            case 'wso':
                return $item->cnt_wso;
            case 'lot':
                return $item->cnt_lot;
            case 'lso':
                return $item->cnt_lso;
            case 'scorefor':
                return $item->sum_team1_result;
            case 'scoreagainst':
                return $item->sum_team2_result;
            case 'results':
                return $item->sum_team1_result . ':' . $item->sum_team2_result;
            case 'diff':
            case 'scorediff':
                return $item->diff_team_results;
            case 'scorepct':
                return round($item->scorePct(), 2);
            case 'bonus':
                return $item->bonus_points;
            case 'start':
                return $item->cnt_lost;
            case 'winpct':
                return round($item->winpct(), 2);
            case 'legs':
                return $item->sum_team1_legs . ':' . $item->sum_team2_legs;
            case 'legsdiff':
                return $item->diff_team_legs;
            case 'legsratio':
                return round($item->legsRatio(), 2);
            case 'negpoints':
                return $item->neg_points;
            case 'oldnegpoints':
                return $item->getPoints() . ':' . $item->neg_points;
            case 'pointsratio':
                return round($item->pointsRatio(), 2);
            case 'gfa':
                return round($item->getGFA(), 2);
            case 'gaa':
                return round($item->getGAA(), 2);
            case 'ppg':
                return round($item->getPPG(), 2);
            case 'ppp':
                return round($item->getPPP(), 2);
            default:
                if (isset($item->$column)) {
                    return $item->$column;
                }
        }

        return '?';
    }

    /**
     * Build the historic team-logo markup with Joomla's namespaced media helper.
     */
    public static function getLogo($item, $type = 1): string
    {
        $logo = '';

        if ($type == 1 && !empty($item->team->logo_small)) {
            $logo = $item->team->logo_small;
        } elseif ($type == 2 && !empty($item->team->country)) {
            return CountryPresentationHelper::flag((string) $item->team->country, 'class="teamcountry"');
        } elseif ($type == 3 && !empty($item->team->logo_middle)) {
            $logo = $item->team->logo_middle;
        } elseif ($type == 4 && !empty($item->team->logo_big)) {
            $logo = $item->team->logo_big;
        } elseif ($type == 5 && !empty($item->team->trikot_home)) {
            $logo = $item->team->trikot_home;
        } elseif ($type == 6 && !empty($item->team->trikot_away)) {
            $logo = $item->team->trikot_away;
        }

        $logo = MediaHelper::getCleanMediaFieldValue((string) $logo);

        if ($logo !== '') {
            return HTMLHelper::image($logo, (string) $item->team->short_name, 'class="teamlogo" width="20"');
        }

        return '';
    }

    /**
     * Preserve the legacy public method while routing through the native helper.
     */
    public static function getTeamLink($item, $params, $project)
    {
        if (!class_exists(SiteRouteHelper::class)) {
            $routeHelperFile = JPATH_SITE . '/components/com_sportsmanagement/src/Helper/SiteRouteHelper.php';

            if (is_file($routeHelperFile)) {
                require_once $routeHelperFile;
            }
        }

        if (!class_exists(SiteRouteHelper::class)) {
            throw new \RuntimeException('SportsManagement SiteRouteHelper could not be loaded.', 500);
        }

        $routeparameter = [
            'cfg_which_database' => $params->get('cfg_which_database'),
            's' => $params->get('s'),
            'p' => $project->slug,
        ];

        switch ($params->get('teamlink')) {
            case 'teaminfo':
                return SiteRouteHelper::view('teaminfo', $routeparameter + [
                    'tid' => $item->team->team_slug,
                    'ptid' => $item->team->projectteamid,
                ]);

            case 'roster':
                return SiteRouteHelper::view('roster', $routeparameter + [
                    'tid' => $item->team->team_slug,
                    'ptid' => $item->team->projectteamid,
                ]);

            case 'teamplan':
                return SiteRouteHelper::view('teamplan', $routeparameter + [
                    'tid' => $item->team->team_slug,
                    'division' => $item->team->division_slug,
                    'mode' => 0,
                    'ptid' => $item->team->projectteamid,
                ]);

            case 'clubinfo':
                return SiteRouteHelper::view('clubinfo', $routeparameter + [
                    'cid' => $item->team->club_slug,
                ]);
        }

        return null;
    }
}
