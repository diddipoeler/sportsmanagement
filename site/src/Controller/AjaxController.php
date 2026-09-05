<?php
/**
 * Native JSON endpoints used by the Joomla 5/6 frontend.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\AjaxModel;
use Joomla\CMS\MVC\Controller\BaseController;

/** Native JSON endpoints used by the Joomla 5/6 frontend. */
final class AjaxController extends BaseController
{
    public function getLink(): void
    {
        $input = $this->getApplication()->getInput();
        $link = $this->ajaxModel()->getLink(
            $input->getCmd('view', 'ranking'),
            max(0, $input->getInt('project_id', $input->getInt('p', 0))),
            max(0, $input->getInt('team_id', $input->getInt('tid', 0))),
            max(0, $input->getInt('division_id', $input->getInt('division', 0))),
            $input->getString('points', '3,1,0'),
            max(0, $input->getInt('tnid', 0))
        );

        $this->sendJson([
            'linktext' => $input->getString('linktext', ''),
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
        $input = $this->getApplication()->getInput();
        $view = strtolower($input->getCmd('view', 'ranking'));

        if ($view === 'calendar') {
            $view = 'teamplan';
        }

        $link = $this->ajaxModel()->getLink(
            $view,
            max(0, $input->getInt('p', 0)),
            max(0, $input->getInt('tid', 0)),
            max(0, $input->getInt('division', 0)),
            $input->getString('points', '3,1,0'),
            max(0, $input->getInt('tnid', 0))
        );

        $this->sendJson($link);
    }

    public function getprojectsoptions(): void
    {
        $input = $this->getApplication()->getInput();
        $this->sendJson($this->ajaxModel()->getProjectsOptions(
            max(0, $input->getInt('s', 0)),
            max(0, $input->getInt('l', 0)),
            max(0, $input->getInt('o', 0))
        ));
    }

    private function ajaxModel(): AjaxModel
    {
        $model = $this->getModel('Ajax');

        if (!$model instanceof AjaxModel) {
            throw new \RuntimeException('Ajax controller requires AjaxModel.', 500);
        }

        return $model;
    }

    private function sendJson(mixed $payload): void
    {
        $app = $this->getApplication();
        $app->setHeader('Content-Type', 'application/json; charset=utf-8', true);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $app->close();
    }
}
