<?php
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/** Native JSON endpoints used by the Joomla 5/6 navigation menu. */
final class AjaxController extends BaseController
{
    private const ROUTE_VIEWS = [
        'calendar',
        'curve',
        'eventsranking',
        'matrix',
        'ranking',
        'rankingmatrix',
        'referees',
        'results',
        'resultsmatrix',
        'resultsranking',
        'resultsrankingmatrix',
        'roster',
        'stats',
        'statsranking',
        'teaminfo',
        'teamplan',
        'teamstats',
        'treetonode',
    ];

    public function getroute(): void
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $view = strtolower($input->getCmd('view', 'ranking'));
        $projectId = $input->getInt('p');

        if (!in_array($view, self::ROUTE_VIEWS, true)) {
            $view = 'ranking';
        }

        // The historical navigation module names the calendar target teamplan.
        if ($view === 'calendar') {
            $view = 'teamplan';
        }

        $parameters = [
            'cfg_which_database' => $input->getInt('cfg_which_database', 0),
            's' => $input->getInt('s', 0),
            'p' => $projectId,
        ];

        $divisionId = $input->getInt('division');
        $teamId = $input->getInt('tid');
        $roundId = $input->getInt('r');

        if ($divisionId > 0) {
            $parameters['division'] = $divisionId;
        }

        if ($teamId > 0) {
            $parameters['tid'] = $teamId;
        }

        if ($roundId > 0) {
            $parameters['r'] = $roundId;
        }

        $link = $projectId > 0 ? SiteRouteHelper::view($view, $parameters) : '';

        $app->getDocument()->setMimeEncoding('application/json');
        echo json_encode($link, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $app->close();
    }
}
