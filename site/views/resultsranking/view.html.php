<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage resultsranking
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Model\ResultsrankingDataModel;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

require_once JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'results' . DIRECTORY_SEPARATOR . 'view.html.php';

if (!class_exists(ResultsrankingDataModel::class)) {
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
    require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsrankingDataModel.php';
}

/**
 * sportsmanagementViewResultsranking
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewResultsranking extends sportsmanagementView
{
    /**
     * sportsmanagementViewResultsranking::init()
     *
     * @return void
     */
    public function init()
    {
        $this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');
        $this->pagination = $this->get('Pagination');

        $cfgWhichDatabase = $this->jinput->getInt('cfg_which_database', 0);
        $dataModel = new ResultsrankingDataModel();
        $dataModel->setDatabaseSelector($cfgWhichDatabase);

        /** Ranking calculation remains in the legacy model until RankingModel is migrated. */
        $rankingmodel = new sportsmanagementModelRanking;
        $project = $dataModel->getProject();
        $this->project = $project;

        if (!$project) {
            return;
        }

        $rankingconfig = $dataModel->getTemplateConfig('ranking');
        $rankingmodel::$from = 0;
        $rankingmodel::$to = 0;
        $rankingmodel->computeRanking($cfgWhichDatabase, 0, $project->sport_type_name);

        /** Results match/edit/ACL logic remains in the legacy model until ResultsModel is migrated. */
        $resultsmodel = new sportsmanagementModelResults;
        $roundcode = $dataModel->getRoundCode((int) $rankingmodel::$round);
        $roundSlug = $dataModel->getRoundSlug((int) $rankingmodel::$round);
        if ($roundSlug === '') {
            $roundSlug = (string) ($project->round_slug ?? '');
        }

        $this->paramconfig = $rankingmodel::$paramconfig;
        $this->paramconfig['p'] = $project->slug;

        $resultsconfig = $dataModel->getTemplateConfig('results');

        if (!isset($resultsconfig['switch_home_guest'])) {
            $resultsconfig['switch_home_guest'] = 0;
        }

        if (!isset($resultsconfig['show_dnp_teams_icons'])) {
            $resultsconfig['show_dnp_teams_icons'] = 0;
        }

        if (!isset($resultsconfig['show_results_ranking'])) {
            $resultsconfig['show_results_ranking'] = 0;
        }

        /** Merge the two config files. */
        $config = array_merge($rankingconfig, $resultsconfig);
        $this->config = array_merge($this->overallconfig, $config);

        $this->tableconfig = $rankingconfig;
        $this->showediticon = $resultsmodel->getShowEditIcon();
        $this->division = $resultsmodel->getDivision();
        $this->divisions = $dataModel->getDivisions();
        $this->divLevel = $rankingmodel::$divLevel;
        $this->matches = $resultsmodel->getMatches($cfgWhichDatabase);
        $this->round = $resultsmodel::$roundid;
        $this->roundid = $resultsmodel::$roundid;
        $this->roundcode = $roundcode;

        $rounds = $dataModel->getRoundOptions('ASC');
        $this->matchdaysoptions = $this->getRoundSelectNavigation($rounds, $cfgWhichDatabase);

        $routeparameter = [];
        $routeparameter['cfg_which_database'] = $cfgWhichDatabase;
        $routeparameter['s'] = $this->jinput->getInt('s', 0);
        $routeparameter['p'] = $project->slug;
        $routeparameter['r'] = $roundSlug;
        $routeparameter['division'] = 0;
        $routeparameter['mode'] = 0;
        $routeparameter['order'] = 0;
        $routeparameter['layout'] = 0;
        $routeparameter['to'] = $roundSlug;
        $this->currenturl = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsranking', $routeparameter);

        $this->rounds = $dataModel->getRounds('ASC');
        $this->favteams = $dataModel->getFavTeams();
        $this->projectevents = $dataModel->getProjectEvents();
        $this->model = $resultsmodel;
        $this->isAllowed = $resultsmodel->isAllowed();
        $this->type = $rankingmodel::$type;
        $this->from = $rankingmodel::$from;
        $this->to = $rankingmodel::$to;

        if ($this->params->get('show_allranking', 0)) {
            $this->previousRanking = $rankingmodel::$previousRanking;

            if ($this->config['show_table_1']) {
                $this->currentRanking = $rankingmodel::$currentRanking;
            }

            if ($this->config['show_table_2']) {
                $this->homeRank = $rankingmodel::$homeRank;
            }

            if ($this->config['show_table_3']) {
                $this->awayRank = $rankingmodel::$awayRank;
            }

            if ($this->config['show_table_4']) {
                $rankingmodel::$part = 1;
                $rankingmodel::computeRanking($cfgWhichDatabase, 0, $project->sport_type_name);
                $this->firstRank = $rankingmodel::$currentRanking;
            }

            if ($this->config['show_table_5']) {
                $rankingmodel::$part = 2;
                $rankingmodel::computeRanking($cfgWhichDatabase, 0, $project->sport_type_name);
                $this->secondRank = $rankingmodel::$currentRanking;
            }
        } else {
            $this->previousRanking = $rankingmodel::$previousRanking;
            $this->currentRanking = $rankingmodel::$currentRanking;
        }

        $this->current_round = $rankingmodel::$current_round;
        $this->teams = $dataModel->getProjectTeamsIndexed(0);
        $this->previousgames = $rankingmodel->getPreviousGames($cfgWhichDatabase);

        /** Ranking colors. */
        if (!isset($this->config['colors'])) {
            $this->config['colors'] = '';
        }
        $this->colors = $dataModel->parseColors((string) $this->config['colors']);

        /** Set page title. */
        $pageTitle = ($this->params->get('what_to_show_first', 0) == 0)
            ? Text::_('COM_SPORTSMANAGEMENT_RESULTS_PAGE_TITLE') . ' & ' . Text::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE')
            : Text::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE') . ' & ' . Text::_('COM_SPORTSMANAGEMENT_RESULTS_PAGE_TITLE');

        if (isset($this->project->name)) {
            $pageTitle .= ' - ' . $this->project->name;
        }

        $this->document->setTitle($pageTitle);

        $stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'components/' . $this->option . '/assets/css/' . $this->view . '.css' . '" type="text/css" />' . "\n";
        $this->document->addCustomTag($stylelink);

        $this->allteams = $dataModel->getProjectTeams(0);

        if ($this->params->get('show_map', 0)) {
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
                    if (!empty($row->club_zipcode)) {
                        $addressParts[] = $row->club_zipcode . ' ' . $row->club_location;
                    } else {
                        $addressParts[] = $row->club_location;
                    }
                }

                if (!empty($row->club_country)) {
                    $addressParts[] = JSMCountries::getShortCountryName($row->club_country);
                }

                $row->address_string = implode(', ', $addressParts);
            }
        }
    }

    /**
     * sportsmanagementViewResultsranking::getRoundSelectNavigation()
     *
     * @param mixed $rounds
     * @param int   $cfg_which_database
     *
     * @return array
     */
    public function getRoundSelectNavigation(&$rounds, $cfg_which_database = 0)
    {
        $options = [];

        foreach ($rounds as $r) {
            $routeparameter = [];
            $routeparameter['cfg_which_database'] = $cfg_which_database;
            $routeparameter['s'] = Factory::getApplication()->input->getInt('s', 0);
            $routeparameter['p'] = $this->project->slug;
            $routeparameter['r'] = $r->slug;
            $routeparameter['division'] = 0;
            $routeparameter['mode'] = 0;
            $routeparameter['order'] = 0;
            $routeparameter['layout'] = 0;
            $routeparameter['to'] = $r->slug;
            $link = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsranking', $routeparameter);

            $options[] = HTMLHelper::_('select.option', $link, $r->text);
        }

        return $options;
    }
}
