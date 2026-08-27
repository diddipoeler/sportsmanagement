<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rankingmatrix
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\MatrixModel;
use Diddipoeler\Component\SportsManagement\Site\Model\RankingmatrixDataModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

require_once JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'matrix' . DIRECTORY_SEPARATOR . 'view.html.php';

if (!class_exists(MatrixModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/MatrixModel.php';
}

if (!class_exists(RankingmatrixDataModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/RankingmatrixDataModel.php';
}

/**
 * Ranking and matrix combined view.
 */
class sportsmanagementViewRankingmatrix extends sportsmanagementView
{
    public function init()
    {
        $this->jinput->set('r', 0);
        $this->params = $this->app->getParams();
        $this->roundcode = '';
        $this->matchdaysoptions = [];
        $databaseSelector = $this->jinput->getInt('cfg_which_database', 0);

        $this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');
        $this->pagination = $this->get('Pagination');

        $dataModel = new RankingmatrixDataModel();
        $dataModel->setDatabaseSelector($databaseSelector);
        $matrixModel = new MatrixModel();
        $matrixModel->setDatabaseSelector($databaseSelector);

        $project = $dataModel->getProject();
        if (!$project) {
            $this->document->setTitle(
                Text::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE') . ' & ' . Text::_('COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE')
            );
            return;
        }

        $this->project = $project;
        $this->overallconfig = $dataModel->getOverallConfig();
        $rankingconfig = $dataModel->getTemplateConfig('ranking');
        $rankingmatrixconfig = $dataModel->getTemplateConfig('rankingmatrix');
        $this->matrixconfig = $dataModel->getTemplateConfig('matrix');
        $this->config = array_merge($this->overallconfig, $rankingconfig, $rankingmatrixconfig);
        $this->tableconfig = $rankingconfig;

        /**
         * Ranking is still a deliberate legacy boundary. It owns the current
         * ranking calculation and previous-game cache until RankingModel is
         * migrated separately.
         */
        $rankingmodel = new sportsmanagementModelRanking();
        $rankingmodel::$from = 0;
        $rankingmodel::$to = 0;
        $rankingmodel->computeRanking($databaseSelector, 0, (string) $project->sport_type_name);

        $this->divisionid = MatrixModel::$divisionid;
        $this->division = $matrixModel->getDivision();
        $this->divisions = $dataModel->getDivisions();
        $this->teams = $dataModel->getProjectTeamsIndexed(MatrixModel::$divisionid);
        $this->results = $matrixModel->getMatrixResults((int) $project->id);
        $this->favteams = $dataModel->getFavTeams();

        $routeparameter = [];
        $routeparameter['cfg_which_database'] = $databaseSelector;
        $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
        $routeparameter['p'] = $project->slug;
        $routeparameter['division'] = 0;
        $routeparameter['r'] = (string) ($project->round_slug ?? '');
        $this->currenturl = sportsmanagementHelperRoute::getSportsmanagementRoute('rankingmatrix', $routeparameter);

        $this->rounds = $dataModel->getRounds('ASC');
        $this->projectevents = $dataModel->getProjectEvents();
        $this->action = $this->uri->toString();

        $this->previousRanking = $rankingmodel::$previousRanking;
        $this->currentRanking = $rankingmodel::$currentRanking;
        $this->current_round = $rankingmodel::$current_round;
        $this->teams = $dataModel->getProjectTeamsIndexed();
        $this->previousgames = $rankingmodel->getPreviousGames($databaseSelector);

        if (!isset($this->config['teamnames'])) {
            $this->config['teamnames'] = 'name';
        }

        if (!isset($this->config['image_placeholder'])) {
            $this->config['image_placeholder'] = '';
        }

        $pageTitle = ($this->params->get('what_to_show_first', 0) == 0)
            ? Text::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE') . ' & ' . Text::_('COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE')
            : Text::_('COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE') . ' & ' . Text::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE');

        if (isset($this->project->name)) {
            $pageTitle .= ' - ' . $this->project->name;
        }

        $this->document->setTitle($pageTitle);

        $stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'components/' . $this->option . '/assets/css/' . $this->view . '.css' . '" type="text/css" />' . "\n";
        $this->document->addCustomTag($stylelink);

        sportsmanagementHelperHtml::$project = $project;
        sportsmanagementHelperHtml::$teams = $this->teams;

        if ($this->params->get('show_map', 0)) {
            // SportsManagementProjectModel already returns the club/address data
            // required by the map, so no legacy Projectteams model is needed.
            $this->allteams = $dataModel->getProjectTeams();
            $this->mapconfig = $dataModel->getTemplateConfig('map');

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
}
