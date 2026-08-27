<?php
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Diddipoeler\Component\SportsManagement\Site\Model\AjaxModel;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/** Native JSON endpoints used by the Joomla 5/6 frontend. */
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
        'teams',
        'teamstats',
        'teamstree',
        'treetonode',
    ];

    public function getLink(): void
    {
        $input = $this->getApplication()->getInput();
        $view = strtolower($input->getCmd('view', 'ranking'));
        $projectId = max(0, $input->getInt('project_id', $input->getInt('p', 0)));
        $seasonId = max(0, $input->getInt('season_id', $input->getInt('s', 0)));
        $roundId = max(0, $input->getInt('round_id', $input->getInt('r', 0)));
        $divisionId = max(0, $input->getInt('division_id', $input->getInt('division', 0)));
        $linkText = $input->getString('linktext', '');
        $link = '';

        if ($projectId > 0 && in_array($view, ['ranking', 'results', 'resultsranking', 'teams', 'teamstree'], true)) {
            $parameters = [
                'cfg_which_database' => $input->getInt('cfg_which_database', 0),
                's' => $seasonId,
                'p' => $projectId,
            ];

            if ($view === 'ranking') {
                $parameters += [
                    'type' => 0,
                    'r' => $roundId,
                    'from' => 0,
                    'to' => 0,
                    'division' => $divisionId,
                ];
            } elseif ($view === 'results' || $view === 'resultsranking') {
                $parameters += [
                    'r' => $roundId,
                    'division' => $divisionId,
                    'mode' => 0,
                    'order' => '',
                    'layout' => '',
                ];
            } else {
                $parameters['division'] = $divisionId;
            }

            $link = SiteRouteHelper::view($view, $parameters);
        }

        $this->sendJson([
            'linktext' => $linkText,
            'link' => $link,
        ]);
    }

    public function getProjectTeams(): void
    {
        $input = $this->getApplication()->getInput();
        $this->sendJson($this->ajaxModel()->getProjectTeams(max(0, $input->getInt('project_id', 0))));
    }

    public function getProjectSelect(): void
    {
        $input = $this->getApplication()->getInput();
        $this->sendJson($this->ajaxModel()->getProjectSelect(max(0, $input->getInt('league_id', 0))));
    }

    public function getAssocLeagueSelect(): void
    {
        $input = $this->getApplication()->getInput();
        $this->sendJson($this->ajaxModel()->getAssocLeagueSelect(
            $input->getString('country', ''),
            max(0, $input->getInt('assoc_id', 0))
        ));
    }

    public function getCountrySubSubAssocSelect(): void
    {
        $input = $this->getApplication()->getInput();
        $this->sendJson($this->ajaxModel()->getCountrySubSubAssocSelect(
            max(0, $input->getInt('subassoc_id', 0))
        ));
    }

    public function getCountrySubAssocSelect(): void
    {
        $input = $this->getApplication()->getInput();
        $this->sendJson($this->ajaxModel()->getCountrySubAssocSelect(
            max(0, $input->getInt('assoc_id', 0))
        ));
    }

    public function getcountryassoc(): void
    {
        $input = $this->getApplication()->getInput();
        $this->sendJson($this->ajaxModel()->getCountryAssocSelect($input->getString('country', '')));
    }

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

        $this->sendJson($link);
    }

    public function getprojectsoptions(): void
    {
        $input = $this->getApplication()->getInput();
        $options = $this->ajaxModel()->getProjectsOptions(
            max(0, $input->getInt('s', 0)),
            max(0, $input->getInt('l', 0)),
            max(0, $input->getInt('o', 0))
        );

        $this->sendJson($options);
    }

    private function ajaxModel(): AjaxModel
    {
        $model = $this->getModel('Ajax');

        if (!$model instanceof AjaxModel) {
            throw new \RuntimeException('Ajax controller requires AjaxModel.', 500);
        }

        return $model;
    }

    private function sendJson($payload): void
    {
        $app = $this->getApplication();
        $app->getDocument()->setMimeEncoding('application/json');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $app->close();
    }
}
