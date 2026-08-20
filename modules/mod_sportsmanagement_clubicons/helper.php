<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_clubicons
 * @file       helper.php
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class modJSMClubiconsHelper
{
    public $project = null;
    public $ranking = [];
    public $teams = [];
    public $params;
    public $module;
    public $placeholders = [
        'logo_big' => 'images/com_sportsmanagement/database/placeholders/placeholder_150.png',
        'projectteam_picture' => 'images/com_sportsmanagement/database/placeholders/placeholder_450_2.png',
        'team_picture' => 'images/com_sportsmanagement/database/placeholders/placeholder_450_2.png',
    ];

    public function __construct($params, $module)
    {
        $this->params = $params;
        $this->module = $module;
        $this->loadData();
    }

    private function loadData(): void
    {
        $input = Factory::getApplication()->getInput();
        $projectId = (
            $input->getCmd('option', '') === 'com_sportsmanagement'
            && $input->getInt('p', 0) > 0
            && (int) $this->params->get('usepfromcomponent', 0) === 1
        ) ? $input->getInt('p') : $this->params->get('project_ids');

        if (is_array($projectId)) {
            $projectId = reset($projectId);
        }

        $projectId = (int) $projectId;

        if ($projectId <= 0) {
            return;
        }

        sportsmanagementModelProject::$projectid = $projectId;
        sportsmanagementModelProject::$cfg_which_database = $this->params->get('cfg_which_database');
        $this->project = sportsmanagementModelProject::getProject($this->params->get('cfg_which_database'));

        if (!$this->project) {
            return;
        }

        $rankingEngine = JSMRanking::getInstance($this->project, $this->params->get('cfg_which_database'));
        sportsmanagementModelRanking::$projectid = $projectId;
        $divisionId = (int) explode(':', (string) $this->params->get('division_id', 0))[0];
        $this->ranking = $rankingEngine->getRanking(
            null,
            null,
            $divisionId,
            $this->params->get('cfg_which_database')
        ) ?: [];

        if ($this->params->get('logotype') === 'logo_small') {
            $teams = sportsmanagementModelProject::getTeamsIndexedByPtid(
                $divisionId,
                'name',
                $this->params->get('cfg_which_database')
            );
        } else {
            $teams = sportsmanagementModelProject::getTeams(
                $divisionId,
                'name',
                $this->params->get('cfg_which_database')
            );
        }

        $this->buildData((array) $teams);
    }

    public function buildData($result): void
    {
        foreach ((array) $result as $team) {
            if (!isset($team->projectteamid)) {
                continue;
            }

            $projectTeamId = (int) $team->projectteamid;
            $this->teams[$projectTeamId] = [
                'link' => $this->getLink($team),
                'logo' => $this->getLogo($team, 'img-zoom img-height'),
            ];
        }
    }

    public function getLink($item)
    {
        if (!$this->project) {
            return '';
        }

        $routeParameter = [
            'cfg_which_database' => $this->params->get('cfg_which_database'),
            's' => $this->params->get('s'),
            'p' => $this->project->slug,
        ];

        switch ((int) $this->params->get('teamlink')) {
            case 0:
                return '';
            case 1:
                $routeParameter['tid'] = $item->team_slug;
                $routeParameter['ptid'] = 0;
                return sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeParameter);
            case 2:
                $routeParameter['tid'] = $item->team_slug;
                $routeParameter['ptid'] = 0;
                return sportsmanagementHelperRoute::getSportsmanagementRoute('roster', $routeParameter);
            case 3:
                $routeParameter['tid'] = $item->team_slug;
                $routeParameter['division'] = 0;
                $routeParameter['mode'] = 0;
                $routeParameter['ptid'] = 0;
                return sportsmanagementHelperRoute::getSportsmanagementRoute('teamplan', $routeParameter);
            case 4:
                return sportsmanagementHelperRoute::getClubInfoRoute($this->project->slug, $item->club_slug);
            case 5:
                return (string) ($item->club_www ?? $item->website ?? '');
        }

        return '';
    }

    public function getLogo($item, $class)
    {
        $imageType = (string) $this->params->get('logotype', 'logo_big');
        $logoUrl = (string) ($item->{$imageType} ?? '');

        if ($logoUrl === '') {
            $logoUrl = $this->placeholders[$imageType] ?? $this->placeholders['logo_big'];
        }

        if ((int) $this->params->get('cfg_which_database')) {
            $componentParams = ComponentHelper::getParams('com_sportsmanagement');
            $logoUrl = (string) $componentParams->get('cfg_which_database_server') . $logoUrl;
        }

        $name = (string) ($item->name ?? '');
        $title = Text::_('JGLOBAL_VIEW') . ' ' . $name;
        $height = max(1, (int) $this->params->get('picture_height', 50));

        return HTMLHelper::image($logoUrl, $name, [
            'style' => 'width:auto;height:' . $height . 'px',
            'title' => $title,
            'class' => $class,
        ]);
    }
}
