<?php
namespace Diddipoeler\Module\SportsManagementNavigationMenu\Site\Helper;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
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

    public function getData(
        Registry $params,
        CMSApplicationInterface $app,
        DatabaseInterface $joomlaDatabase
    ): array {
        $this->application = $app;

        return parent::getData($params, $app, $joomlaDatabase);
    }

    public function getLink($view): string|false
    {
        $project = $this->getProject();
        $projectId = $project ? (int) $project->id : 0;

        if ($projectId <= 0 || $view === 'separator') {
            return false;
        }

        $app = $this->application;

        if ($app === null) {
            $app = Factory::getApplication();

            if (!$app instanceof SiteApplication) {
                throw new \RuntimeException('SportsManagement site application is unavailable.');
            }
        }

        $input = $app->getInput();
        $teamId = $this->getTeamId();
        $divisionId = $this->getDivisionId();
        $roundId = $input->getInt('r', 0);
        $base = [
            'cfg_which_database' => $input->getInt(
                'cfg_which_database',
                (int) $this->getParam('cfg_which_database', 0)
            ),
            's' => $input->getInt('s', 0),
            'p' => $projectId,
        ];

        switch ((string) $view) {
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
                if ($teamId <= 0) {
                    return false;
                }

                return SiteRouteHelper::view('roster', $base + [
                    'tid' => $teamId,
                    'ptid' => 0,
                ]);

            case 'stats':
                return SiteRouteHelper::view('stats', $base + [
                    'division' => $divisionId,
                ]);

            case 'statsranking':
                return SiteRouteHelper::view('statsranking', $base + [
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

            case 'ranking':
            default:
                return SiteRouteHelper::view('ranking', $base + [
                    'type' => 0,
                    'r' => $roundId,
                    'from' => 0,
                    'to' => 0,
                    'division' => $divisionId,
                ]);
        }
    }
}
