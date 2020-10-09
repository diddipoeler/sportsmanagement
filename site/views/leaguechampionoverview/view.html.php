<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage leaguechampionoverview
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementViewleaguechampionoverview
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2020
 * @version $Id$
 * @access public
 */
class sportsmanagementViewleaguechampionoverview extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewleaguechampionoverview::init()
	 * 
	 * @return void
	 */
	function init()
	{
	   $mdlRankingAllTime = BaseDatabaseModel::getInstance("RankingAllTime", "sportsmanagementModel");
       
		$this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');
		$this->projectids     = $mdlRankingAllTime->getAllProject();
		$this->projectnames   = $mdlRankingAllTime->getAllProjectNames();
		$this->project_ids           = implode(",", $this->projectids);
//		$this->project_ids    = $project_ids;
		$this->teams          = $mdlRankingAllTime->getAllTeamsIndexedByPtid($this->project_ids );
		$this->matches        = $mdlRankingAllTime->getAllMatches($this->project_ids );
		$this->ranking        = $mdlRankingAllTime->getAllTimeRanking();
//		$this->tableconfig    = $mdlRankingAllTime->getAllTimeParams();
//		$this->config         = $mdlRankingAllTime->getAllTimeParams();
//		$this->currentRanking = $mdlRankingAllTime->getCurrentRanking();
//		$this->action         = $this->uri->toString();
//		$this->colors         = $mdlRankingAllTime->getColors($this->config['colors']);
		/** Set page title */
		$pageTitle = Text::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE');
		$this->document->setTitle($pageTitle);

	}

}

