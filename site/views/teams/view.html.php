<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teams
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementViewTeams
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementViewTeams extends sportsmanagementView
{

	/**
	 * sportsmanagementViewTeams::init()
	 *
	 * @return void
	 */
	function init()
	{
		
		$this->division = sportsmanagementModelProject::getDivision($this->jinput->getInt("division", 0), $this->jinput->getInt('cfg_which_database', 0));
		$this->teams    = sportsmanagementModelProject::getTeams($this->jinput->getInt("division", 0), 'name', $this->jinput->getInt('cfg_which_database', 0), '', $this->config['show_club_playground']);

		$pageTitle = Text::_('COM_SPORTSMANAGEMENT_TEAMS_TITLE');

		if (isset($this->project))
		{
			$pageTitle .= " " . $this->project->name;

			if (isset($this->division))
			{
				$pageTitle .= " : " . $this->division->name;
			}
		}

		$this->document->setTitle($pageTitle);
		$this->headertitle = Text::_('COM_SPORTSMANAGEMENT_TEAMS_TITLE');

	}
}
