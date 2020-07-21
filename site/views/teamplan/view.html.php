<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teamplan
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

/**
 * sportsmanagementViewTeamPlan
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewTeamPlan extends sportsmanagementView
{

	/**
	 * sportsmanagementViewTeamPlan::init()
	 *
	 * @return void
	 */
	function init()
	{

		$this->document->addScript(Uri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');
		$this->document->addStyleSheet(Uri::base() . 'components/' . $this->option . '/assets/css/modalwithoutjs.css');

		sportsmanagementHelperHtml::$project = $this->project;

		if ($this->config['show_date_image'])
		{
			$this->document->addStyleSheet(Uri::base() . 'components/' . $this->option . '/assets/css/calendar.css');
		}

		if (isset($this->project))
		{
			$this->rounds           = sportsmanagementModelProject::getRounds($this->config['plan_order'], sportsmanagementModelTeamPlan::$cfg_which_database);
			$this->teams            = sportsmanagementModelProject::getTeamsIndexedByPtid(0, 'name', sportsmanagementModelTeamPlan::$cfg_which_database);
			$this->favteams         = sportsmanagementModelProject::getFavTeams(sportsmanagementModelTeamPlan::$cfg_which_database);
			$this->division         = sportsmanagementModelTeamPlan::getDivision();
			$this->ptid             = sportsmanagementModelTeamPlan::getProjectTeamId();
			$this->projectevents    = sportsmanagementModelProject::getProjectEvents(0, sportsmanagementModelTeamPlan::$cfg_which_database);
			$this->matches          = sportsmanagementModelTeamPlan::getMatches($this->config);
			$this->matches_refering = sportsmanagementModelTeamPlan::getMatchesRefering($this->config);
			$this->matchesperround  = sportsmanagementModelTeamPlan::getMatchesPerRound($this->config, $this->rounds);
		}

		/** Set page title */
		if (empty($this->ptid))
		{
			$pageTitle = (!empty($this->project->id)) ? $this->project->name : '';
		}
		else
		{
			if (isset($this->project) && $this->ptid)
			{
				$pageTitle = $this->teams[$this->ptid]->name;
			}
			else
			{
				$pageTitle = '';
			}
		}

		$this->document->setTitle(Text::sprintf('COM_SPORTSMANAGEMENT_TEAMPLAN_PAGE_TITLE', $pageTitle));

		if (!isset($this->config['table_class']))
		{
			$this->config['table_class'] = 'table';
		}

	}

}
