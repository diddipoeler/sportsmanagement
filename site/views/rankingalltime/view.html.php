<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rankingalltime
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
 * sportsmanagementViewRankingAllTime
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewRankingAllTime extends sportsmanagementView
{

	/**
	 * sportsmanagementViewRankingAllTime::init()
	 *
	 * @return void
	 */
	function init()
	{
	   $mdlRankingAllTime = BaseDatabaseModel::getInstance("RankingAllTime", "sportsmanagementModel");
		$this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');
		$this->projectids     = $this->model->getAllProject();
		$this->projectnames   = $this->model->getAllProjectNames();
		$this->project_ids          = implode(",", $this->projectids);
		$this->teams          = $this->model->getAllTeamsIndexedByPtid($this->project_ids);
		$this->matches        = $this->model->getAllMatches($this->project_ids);
		$this->ranking        = $this->model->getAllTimeRanking();
		$this->tableconfig    = $this->model->getAllTimeParams();
		$this->config         = $this->model->getAllTimeParams();
		$this->currentRanking = $this->model->getCurrentRanking();
		$this->action         = $this->uri->toString();
		$this->colors         = $this->model->getColors($this->config['colors']);
		/** Set page title */
		$pageTitle = Text::_('COM_SPORTSMANAGEMENT_RANKING_PAGE_TITLE');
		$this->document->setTitle($pageTitle);
        
        $this->warnings = $mdlRankingAllTime::$rankingalltimewarnings;
        $this->tips = $mdlRankingAllTime::$rankingalltimetips;
        $this->notes = $mdlRankingAllTime::$rankingalltimenotes;
        
        $this->warnings = array_merge($this->warnings, sportsmanagementModelProject::$warnings);
        $this->tips = array_merge($this->tips, sportsmanagementModelProject::$tips);
        $this->notes = array_merge($this->notes, sportsmanagementModelProject::$notes);
        

	}

}

