<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage resultsmatrix
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\MatrixModel;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

require_once JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'results' . DIRECTORY_SEPARATOR . 'view.html.php';

if (!class_exists(MatrixModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/MatrixModel.php';
}

/**
 * Results and matrix combined view.
 */
class sportsmanagementViewResultsmatrix extends sportsmanagementView
{
    public function init()
    {
        $this->params = $this->app->getParams();
        $databaseSelector = $this->jinput->getInt('cfg_which_database', 0);

        $this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');
        $this->pagination = $this->get('Pagination');

        $matrixModel = new MatrixModel();
        $matrixModel->setDatabaseSelector($databaseSelector);
        $project = $matrixModel->getProject();

        if (!$project) {
            $this->document->setTitle(
                Text::_('COM_SPORTSMANAGEMENT_RESULTS_PAGE_TITLE') . ' & ' . Text::_('COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE')
            );
            return;
        }

        $this->project = $project;
        $this->overallconfig = $matrixModel->getOverallConfig();
        $matrixconfig = $matrixModel->getTemplateConfig('matrix');
        $resultsconfig = $matrixModel->getTemplateConfig('results');

        if (!isset($resultsconfig['switch_home_guest'])) {
            $resultsconfig['switch_home_guest'] = 0;
        }
        if (!isset($resultsconfig['show_dnp_teams_icons'])) {
            $resultsconfig['show_dnp_teams_icons'] = 0;
        }
        if (!isset($resultsconfig['show_results_ranking'])) {
            $resultsconfig['show_results_ranking'] = 0;
        }
        $resultsconfig['show_matchday_dropdown'] = 0;

        $this->config = array_merge($this->overallconfig, $matrixconfig, $resultsconfig);
        $this->tableconfig = $matrixconfig;

        /**
         * Results is still the deliberate legacy boundary. Match preparation,
         * edit permissions and result-specific behaviour remain there until the
         * large site ResultsModel is migrated separately.
         */
        $resultsmodel = new sportsmanagementModelResults();
        $resultsmodel::$roundid = $this->jinput->getInt('r', 0);

        $selectedRound = $matrixModel->getRound();
        $this->roundcode = $selectedRound ? (string) ($selectedRound->roundcode ?? '') : '';
        $rounds = $matrixModel->getRounds('ASC');

        $this->showediticon = $resultsmodel->getShowEditIcon();
        $this->divisionid = MatrixModel::$divisionid;
        $this->division = $matrixModel->getDivision();
        $this->teams = $matrixModel->getProjectTeamsIndexed(MatrixModel::$divisionid);
        $this->results = $matrixModel->getMatrixResults((int) $project->id);
        $this->favteams = $matrixModel->getFavTeams();
        $this->matches = $resultsmodel->getMatches($databaseSelector);
        $this->round = $resultsmodel::$roundid;
        $this->roundid = $resultsmodel::$roundid;
        $this->matchdaysoptions = $this->getRoundSelectNavigation($rounds);

        $routeparameter = [];
        $routeparameter['cfg_which_database'] = $databaseSelector;
        $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
        $routeparameter['p'] = $project->slug;
        if ($selectedRound) {
            $routeparameter['r'] = (int) $selectedRound->id . ':' . (string) ($selectedRound->alias ?? '');
        } else {
            $routeparameter['r'] = (string) ($project->round_slug ?? '');
        }
        $routeparameter['division'] = 0;
        $routeparameter['mode'] = 0;
        $routeparameter['order'] = 0;
        $routeparameter['layout'] = 0;
        $this->currenturl = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsmatrix', $routeparameter);

        $this->rounds = $rounds;
        $this->projectevents = $matrixModel->getProjectEvents();
        $this->model = $resultsmodel;
        $this->isAllowed = $resultsmodel->isAllowed();
        $this->action = $this->uri->toString();

        if (!isset($this->config['teamnames'])) {
            $this->config['teamnames'] = 'name';
        }
        if (!isset($this->config['image_placeholder'])) {
            $this->config['image_placeholder'] = '';
        }

        $pageTitle = ($this->params->get('what_to_show_first', 0) == 0)
            ? Text::_('COM_SPORTSMANAGEMENT_RESULTS_PAGE_TITLE') . ' & ' . Text::_('COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE')
            : Text::_('COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE') . ' & ' . Text::_('COM_SPORTSMANAGEMENT_RESULTS_PAGE_TITLE');

        if (isset($this->project->name)) {
            $pageTitle .= ' - ' . $this->project->name;
        }

        $this->document->setTitle($pageTitle);
        $stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'components/' . $this->option . '/assets/css/' . $this->view . '.css' . '" type="text/css" />' . "\n";
        $this->document->addCustomTag($stylelink);

        sportsmanagementHelperHtml::$project = $project;
        sportsmanagementHelperHtml::$teams = $this->teams;

        if ($this->params->get('show_map', 0)) {
            $this->allteams = $matrixModel->getProjectTeams(0);
            $this->mapconfig = $matrixModel->getTemplateConfig('map');

            foreach ($this->allteams as $row) {
                $addressParts = [];
                if (!empty($row->club_address)) {
                    $addressParts[] = $row->club_address;
                }
                if (!empty($row->club_state)) {
                    $addressParts[] = $row->club_state;
                }
                if (!empty($row->club_location)) {
                    $addressParts[] = !empty($row->club_zipcode)
                        ? $row->club_zipcode . ' ' . $row->club_location
                        : $row->club_location;
                }
                if (!empty($row->club_country)) {
                    $addressParts[] = JSMCountries::getShortCountryName($row->club_country);
                }
                $row->address_string = implode(', ', $addressParts);
            }
        }
    }

    public function getRoundSelectNavigation(&$rounds)
    {
        $jinput = Factory::getApplication()->input;
        $options = [];

        foreach ($rounds as $round) {
            $routeparameter = [];
            $routeparameter['cfg_which_database'] = $jinput->getInt('cfg_which_database', 0);
            $routeparameter['s'] = $jinput->getInt('s', 0);
            $routeparameter['p'] = $this->project->slug;
            $routeparameter['r'] = $round->id;
            $routeparameter['division'] = 0;
            $routeparameter['mode'] = 0;
            $routeparameter['order'] = 0;
            $routeparameter['layout'] = 0;
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsmatrix', $routeparameter);
            $options[] = HTMLHelper::_('select.option', $link, $round->name);
        }

        return $options;
    }
}
