<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teaminfo
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementViewTeamInfo
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewTeamInfo extends sportsmanagementView
{

	/**
	 * sportsmanagementViewTeamInfo::init()
	 *
	 * @return void
	 */
	function init()
	{
    $this->warnings = array();
    $this->tips = array();
    $this->notes = array();
       
		$this->checkextrafields = sportsmanagementHelper::checkUserExtraFields('frontend', sportsmanagementModelTeamInfo::$cfg_which_database);

		if ($this->project->id)
		{
			$this->team          = sportsmanagementModelTeamInfo::getTeamByProject(1);
			$this->club          = sportsmanagementModelTeamInfo::getClub();
			//$this->seasons       = sportsmanagementModelTeamInfo::getSeasons($this->config, 0);
			$this->showediticon  = sportsmanagementModelProject::hasEditPermission('projectteam.edit');
			$this->projectteamid = sportsmanagementModelTeamInfo::$projectteamid;
			$this->teamid        = sportsmanagementModelTeamInfo::$teamid;
			$this->trainingData  = sportsmanagementModelTeamInfo::getTrainigData($this->project->id);

			if ($this->checkextrafields)
			{
				$this->extrafields = sportsmanagementHelper::getUserExtraFields(sportsmanagementModelTeamInfo::$teamid, 'frontend', sportsmanagementModelTeamInfo::$cfg_which_database);
			}

			$daysOfWeek       = array(
				1 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MONDAY'),
				2 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_TUESDAY'),
				3 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_WEDNESDAY'),
				4 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_THURSDAY'),
				5 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_FRIDAY'),
				6 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SATURDAY'),
				7 => Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SUNDAY')
			);
			$this->daysOfWeek = $daysOfWeek;

			if ($this->team->merge_clubs)
			{
				$this->merge_clubs = sportsmanagementModelTeamInfo::getMergeClubs($this->team->merge_clubs);
			}

			$this->seasons                  = sportsmanagementModelTeamInfo::getSeasons($this->config, 1);
			$this->leaguerankoverview       = sportsmanagementModelTeamInfo::getLeagueRankOverview($this->seasons);
			$this->leaguerankoverviewdetail = sportsmanagementModelTeamInfo::getLeagueRankOverviewDetail($this->seasons);

		}

		$this->extended = sportsmanagementHelper::getExtended($this->team->teamextended, 'team');

		/** Set page title */
		$pageTitle = Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_PAGE_TITLE');

		if (isset($this->team))
		{
			$pageTitle .= ': ' . $this->team->tname;
		}

		$this->document->setTitle($pageTitle);

		if (!isset($this->config['table_class']))
		{
			$this->config['table_class'] = 'table';
		}

	}
}
