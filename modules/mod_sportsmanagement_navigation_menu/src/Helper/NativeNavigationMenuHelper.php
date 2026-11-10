<?php
/**
 * Joomla 5/6 route-aware navigation helper for mod_sportsmanagement_navigation_menu.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementNavigationMenu\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Joomla 5/6 route-aware navigation helper.
 *
 * The data/select logic remains in NavigationMenuHelper while frontend URLs are
 * built exclusively through the component's native route helper.
 */
final class NativeNavigationMenuHelper extends NavigationMenuHelper
{
    private ?CMSApplicationInterface $application = null;

    /**
     * Prepare navigation menu data using the supplied Joomla site context.
     * Stores the site application for route-aware menu link generation
     * and delegates project/team selection to the base navigation helper.
     * @param Registry $params Module navigation configuration.
     * @param CMSApplicationInterface $app Joomla site application.
     * @param DatabaseInterface $joomlaDatabase Primary Joomla connection.
     * @return array Prepared navigation menu data.
     */
    public function getData(
        Registry $params,
        CMSApplicationInterface $app,
        DatabaseInterface $joomlaDatabase
    ): array {
        $this->application = $app;

        return parent::getData($params, $app, $joomlaDatabase);
    }

    /**
     * Build a supported SportsManagement project navigation URL.
     * Rejects empty, padded or unsupported view names and routes team-specific
     * views only when a valid team identifier is selected. Links to supported
     * native Joomla 5/6 project, roster, team, statistic, tournament and
     * export views (including the restored jltournamenttree route) use
     * SiteRouteHelper rather than the legacy
     * URL builder. Both saved and raw URL database selectors must be valid;
     * invalid values are never silently mapped to another connection by
     * Joomla's integer filter. Negative round/season selectors are bounded.
     *
     * @param mixed $view Configured navigation view name.
     * @return string|false Routed Joomla link or false when unavailable.
     */
    public function getLink($view): string|false
    {
        // Stored configuration must specify a non-empty, unpadded view name.
        if (!is_string($view) || $view === '' || trim($view) !== $view) {
            return false;
        }

        $project = $this->getProject();
        $projectId = $project ? (int) $project->id : 0;

        if ($projectId <= 0 || $view === 'separator') {
            return false;
        }

        $app = $this->application;

        if ($app === null) {
            $resolvedApp = SportsManagementSiteApplicationResolver::resolve();

            if (!$resolvedApp->isClient('site')) {
                throw new \RuntimeException('SportsManagement site application is unavailable.');
            }

            $app = $resolvedApp;
        }

        $input = $app->getInput();
        $teamId = max(0, $this->getTeamId());
        $divisionId = max(0, $this->getDivisionId());
        $roundId = max(0, $input->getInt('r', 0));
        $storedSelector = self::navigationDatabaseSelector($this->getParam('cfg_which_database', 0));

        if ($storedSelector === null) {
            return false;
        }

        // Joomla's getInt() would silently map invalid URL values such as
        // "1garbage" or nested arrays onto another database selector.
        // Inspect raw input and fail closed before project navigation.
        $rawSelector = $input->get('cfg_which_database', null, 'raw');
        $requestedSelector = $rawSelector === null
            ? $storedSelector
            : self::navigationDatabaseSelector($rawSelector);

        if ($requestedSelector === null) {
            return false;
        }

        $base = [
            'cfg_which_database' => $requestedSelector,
            's' => max(0, $input->getInt('s', 0)),
            'p' => $projectId,
        ];

        switch ($view) {
            case 'calendar':
                return SiteRouteHelper::view('teamplan', $base + [
                    'tid' => $teamId,
                    'division' => $divisionId,
                    'mode' => 0,
                    'ptid' => 0,
                ]);

            case 'curve':
                return SiteRouteHelper::view('curve', $base + [
                    'tid1' => $teamId,
                    'tid2' => 0,
                    'division' => $divisionId,
                ]);

            case 'eventsranking':
                return SiteRouteHelper::view('eventsranking', $base + [
                    'division' => $divisionId,
                    'tid' => $teamId,
                    'evid' => 0,
                    'mid' => 0,
                ]);

            case 'matrix':
                return SiteRouteHelper::view('matrix', $base + [
                    'division' => $divisionId,
                    'r' => 0,
                ]);

            case 'referees':
                return SiteRouteHelper::view('referees', $base);

            case 'results':
            case 'resultsmatrix':
            case 'resultsranking':
                return SiteRouteHelper::view((string) $view, $base + [
                    'r' => $roundId,
                    'division' => $divisionId,
                    'mode' => 0,
                    'order' => '',
                    'layout' => '',
                ]);

            case 'resultsrankingmatrix':
                return SiteRouteHelper::view('resultsrankingmatrix', $base + [
                    'r' => $roundId,
                    'division' => $divisionId,
                ]);

            case 'roster':
            case 'rosteralltime':
                if ($teamId <= 0) {
                    return false;
                }

                return SiteRouteHelper::view((string) $view, $base + [
                    'tid' => $teamId,
                    'ptid' => 0,
                ]);

            case 'stats':
                return SiteRouteHelper::view('stats', $base + [
                    'division' => $divisionId,
                ]);

            case 'statsranking':
            case 'statsrankingteams':
                return SiteRouteHelper::view((string) $view, $base + [
                    'division' => $divisionId,
                    'tid' => 0,
                    'sid' => 0,
                    'order' => '',
                ]);

            case 'teaminfo':
                if ($teamId <= 0) {
                    return false;
                }

                return SiteRouteHelper::view('teaminfo', $base + [
                    'tid' => $teamId,
                    'ptid' => 0,
                ]);

            case 'teamplan':
                if ($teamId <= 0) {
                    return false;
                }

                return SiteRouteHelper::view('teamplan', $base + [
                    'tid' => $teamId,
                    'division' => $divisionId,
                    'mode' => 0,
                    'ptid' => 0,
                ]);

            case 'teamstats':
                if ($teamId <= 0) {
                    return false;
                }

                return SiteRouteHelper::view('teamstats', $base + [
                    'tid' => $teamId,
                ]);

            case 'treetonode':
                return SiteRouteHelper::view('treetonode', [
                    'cfg_which_database' => 0,
                    'p' => $projectId,
                ]);

            // These selections are already advertised by the Joomla admin
            // JSMMenuItems form field and have native frontend views. The
            // old navigation switch silently suppressed them.
            case 'teams':
            case 'teamstree':
                return SiteRouteHelper::view((string) $view, $base + [
                    'division' => $divisionId,
                ]);

            case 'allprojectrounds':
            case 'jltournamenttree':
            case 'jlxmlexports':
            case 'leaguechampionoverview':
                return SiteRouteHelper::view((string) $view, $base);

            case 'ranking':
                return SiteRouteHelper::view('ranking', $base + [
                    'type' => 0,
                    'r' => $roundId,
                    'from' => 0,
                    'to' => 0,
                    'division' => $divisionId,
                ]);

            default:
                // An unknown navigation target must not silently become a ranking link.
                return false;
        }
    }

    /**
     * Validate a legacy navigation module database selector before routing.
     *
     * The navigation model supports Joomla's primary (0) and additional
     * SportsManagement (1) databases. Accept only these exact values and
     * zero-padded decimal representations; do not cast malformed arrays,
     * boolean settings, floats, negative numbers or arbitrary strings into
     * database zero. An invalid setting prevents the module link from being
     * generated instead of silently routing to the wrong project.
     *
     * @param mixed $value Saved cfg_which_database module option.
     * @return int|null Valid selector (0 or 1), or null when malformed.
     */
    private static function navigationDatabaseSelector(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value === 0 || $value === 1 ? $value : null;
        }

        if (!is_string($value) || strlen($value) > 64
            || preg_match('/^0*([01])$/D', $value, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }
}
