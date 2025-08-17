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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;

require_once JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'matrix' . DIRECTORY_SEPARATOR . 'view.html.php';

jimport('joomla.filesystem.file');

/**
 * sportsmanagementViewResultsmatrix
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewRankingmatrix extends sportsmanagementView
{

	/**
	 * sportsmanagementViewRakingmatrix::init()
	 *
	 * @return void
	 */
	function init()
	{

		$params = $this->app->getParams();
        $this->mdlClub = BaseDatabaseModel::getInstance("club", "sportsmanagementModel");
		$this->mdlLeague = BaseDatabaseModel::getInstance("league", "sportsmanagementModel");
//        $this->jsmapp->enqueueMessage('params<pre>'.print_r($params,true).'</pre>', 'notice');

		$this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');
        $this->pagination = $this->get('Pagination');

        /** add the ranking model */
		$rankingmodel = new sportsmanagementModelRanking;
		$project      = sportsmanagementModelProject::getProject($this->jinput->getInt('cfg_which_database', 0), __METHOD__, 1);
		/** add the ranking config file */
		$rankingconfig       = sportsmanagementModelProject::getTemplateConfig('ranking', $this->jinput->getInt('cfg_which_database', 0));
		$rankingmodel::$from = 0;
		$rankingmodel::$to   = 0;
		$rankingmodel->computeRanking($this->jinput->getInt('cfg_which_database', 0), 0, $project->sport_type_name);


		// Add the matrix model
		$matrixmodel = new sportsmanagementModelMatrix;

		// Add the matrix config file
		$matrixconfig = sportsmanagementModelProject::getTemplateConfig('matrix', $this->jinput->getInt('cfg_which_database', 0));

        /**
		// Add the results model
		$resultsmodel           = new sportsmanagementModelResults;
		$project                = sportsmanagementModelProject::getProject($this->jinput->getInt('cfg_which_database', 0));
		$resultsmodel::$roundid = $this->jinput->getInt('r', 0);

		// Add the results config file
		//$resultsconfig = sportsmanagementModelProject::getTemplateConfig('results', $this->jinput->getInt('cfg_which_database', 0));

		$mdlRound = BaseDatabaseModel::getInstance("Round", "sportsmanagementModel");

		if ($resultsmodel::$roundid)
		{
			$roundcode = $mdlRound::getRoundcode($this->jinput->getInt('r', 0), $this->jinput->getInt('cfg_which_database', 0) );
		}
		else
		{
			$roundcode = '';
		}

		$rounds = sportsmanagementModelProject::getRoundOptions('ASC', $this->jinput->getInt('cfg_which_database', 0) );
           */

/**
		if (!isset($resultsconfig['switch_home_guest']))
		{
			$resultsconfig['switch_home_guest'] = 0;
		}

		if (!isset($resultsconfig['show_dnp_teams_icons']))
		{
			$resultsconfig['show_dnp_teams_icons'] = 0;
		}

		if (!isset($resultsconfig['show_results_ranking']))
		{
			$resultsconfig['show_results_ranking'] = 0;
		}

		$resultsconfig['show_matchday_dropdown'] = 0;

		// Merge the 2 config files
		$config = array_merge($matrixconfig, $resultsconfig);
        */

        /** merge the 2 config files */
		$config = array_merge($rankingconfig, $matrixconfig);

		$this->config       = array_merge($this->overallconfig, $config);
		$this->tableconfig  = $rankingconfig;
		$this->params       = $params;
		//$this->showediticon = $resultsmodel->getShowEditIcon();
//		$this->division     = $resultsmodel->getDivision();
		$this->divisionid   = $matrixmodel::$divisionid;
        $this->division     = $matrixmodel->getDivision();
		$this->divisions    = sportsmanagementModelProject::getDivisions(0, $this->jinput->getInt('cfg_which_database', 0));
		$this->teams        = sportsmanagementModelProject::getTeamsIndexedByPtid($matrixmodel::$divisionid, 'name', $this->jinput->getInt('cfg_which_database', 0));
		$this->results      = $matrixmodel->getMatrixResults($project->id);
		$this->favteams     = sportsmanagementModelProject::getFavTeams($this->jinput->getInt('cfg_which_database', 0));
//		$this->matches      = $resultsmodel->getMatches($this->jinput->getInt('cfg_which_database', 0));
//		$this->round        = $resultsmodel::$roundid;
//		$this->roundid      = $resultsmodel::$roundid;
		$this->roundcode    = $roundcode;

		//$options = self::getRoundSelectNavigation($rounds);

		$this->matchdaysoptions               = $options;
		$routeparameter                       = array();
		$routeparameter['cfg_which_database'] = $this->jinput->getInt('cfg_which_database', 0);
		$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
		$routeparameter['p']                  = $project->slug;
        $routeparameter['division']           = 0;
		$routeparameter['r']                  = sportsmanagementModelProject::$roundslug;

//		$routeparameter['mode']               = 0;
//		$routeparameter['order']              = 0;
//		$routeparameter['layout']             = 0;
		$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('rankingmatrix', $routeparameter);
		$this->currenturl                     = $link;
		$this->rounds                         = sportsmanagementModelProject::getRounds('ASC', $this->jinput->getInt('cfg_which_database', 0));
		$this->favteams                       = sportsmanagementModelProject::getFavTeams($project->id);
		$this->projectevents                  = sportsmanagementModelProject::getProjectEvents(0, $this->jinput->getInt('cfg_which_database', 0));
		//$this->model                          = $resultsmodel;
		//$this->isAllowed                      = $resultsmodel->isAllowed();

		$this->action = $this->uri->toString();

        $this->previousRanking = $rankingmodel::$previousRanking;
		$this->currentRanking  = $rankingmodel::$currentRanking;
        $this->current_round = $rankingmodel::$current_round;
		$this->teams         = sportsmanagementModelProject::getTeamsIndexedByPtid(0, 'name', $this->jinput->getInt('cfg_which_database', 0));
		$this->previousgames = $rankingmodel->getPreviousGames($this->jinput->getInt('cfg_which_database', 0));

		if (!isset($this->config['teamnames']))
		{
			$this->config['teamnames'] = 'name';
		}

		if (!isset($this->config['image_placeholder']))
		{
			$this->config['image_placeholder'] = '';
		}

		// Set page title
		$pageTitle = ($this->params->get('what_to_show_first', 0) == 0)
			? Text::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE') . ' & ' . Text::_('COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE')
			: Text::_('COM_SPORTSMANAGEMENT_MATRIX_PAGE_TITLE') . ' & ' . Text::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE');

		if (isset($this->project->name))
		{
			$pageTitle .= ' - ' . $this->project->name;
		}

		$this->document->setTitle($pageTitle);

		$stylelink = '<link rel="stylesheet" href="' . Uri::root() . 'components/' . $this->option . '/assets/css/' . $this->view . '.css' . '" type="text/css" />' . "\n";
		$this->document->addCustomTag($stylelink);

		sportsmanagementHelperHtml::$project = $project;
		sportsmanagementHelperHtml::$teams   = $this->teams;

		if ($this->params->get('show_map', 0))
		{
			/**
			 * diddipoeler
			 */
			$mdlProjectteams = BaseDatabaseModel::getInstance("Projectteams", "sportsmanagementModel");
			$this->allteams  = $mdlProjectteams->getAllProjectTeams($project->id, 0, null, $this->jinput->getInt('cfg_which_database', 0));
			$this->mapconfig = sportsmanagementModelProject::getTemplateConfig('map', $this->jinput->getInt('cfg_which_database', 0));

			//	  $this->geo = new JSMsimpleGMapGeocoder();
			//	  $this->geo->genkml3($project->id,$this->allteams);

			foreach ($this->allteams as $row)
			{
				$address_parts = array();

				if (!empty($row->club_address))
				{
					$address_parts[] = $row->club_address;
				}

				if (!empty($row->club_state))
				{
					$address_parts[] = $row->club_state;
				}

				if (!empty($row->club_location))
				{
					if (!empty($row->club_zipcode))
					{
						$address_parts[] = $row->club_zipcode . ' ' . $row->club_location;
					}
					else
					{
						$address_parts[] = $row->club_location;
					}
				}

				if (!empty($row->club_country))
				{
					$address_parts[] = JSMCountries::getShortCountryName($row->club_country);
				}

				$row->address_string = implode(', ', $address_parts);
			}
		}

	}

	/**
	 * sportsmanagementViewResultsmatrix::getRoundSelectNavigation()
	 *
	 * @param   mixed  $rounds
	 *
	 * @return
	 */
     /**
	function getRoundSelectNavigation(&$rounds)
	{
		// Get a refrence of the page instance in joomla
		$document = Factory::getDocument();

		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;

		$options = array();

		foreach ($rounds as $r)
		{
			$routeparameter                       = array();
			$routeparameter['cfg_which_database'] = $jinput->getInt('cfg_which_database', 0);
			$routeparameter['s']                  = Factory::getApplication()->input->getInt('s', 0);
			$routeparameter['p']                  = $this->project->slug;
			$routeparameter['r']                  = $r->slug;
			$routeparameter['division']           = 0;
			$routeparameter['mode']               = 0;
			$routeparameter['order']              = 0;
			$routeparameter['layout']             = 0;
			$link                                 = sportsmanagementHelperRoute::getSportsmanagementRoute('resultsmatrix', $routeparameter);

			$options[] = HTMLHelper::_('select.option', $link, $r->text);
		}

		return $options;
	}
    */

}
