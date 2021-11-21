<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage allprojectrounds
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

/**
 * sportsmanagementViewallprojectrounds
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementViewallprojectrounds extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewallprojectrounds::init()
	 * 
	 * @return void
	 */
	function init()
	{
		
		$this->tableclass = $this->jinput->request->get('table_class', 'table', 'STR');
       	$this->show_favteaminfo  = $this->jinput->request->get('show_favteaminfo', 0, 'INT');
		$this->projectid      = $this->project->id;
		$this->projectmatches = $this->model->getProjectMatches();
		$this->rounds         = sportsmanagementModelProject::getRounds();
		$this->overallconfig  = sportsmanagementModelProject::getOverallConfig();
		$this->config         = array_merge($this->overallconfig, $this->model->_params);
		$this->favteams       = sportsmanagementModelProject::getFavTeams($this->projectid);
		$this->projectteamid  = $this->model->getProjectTeamID($this->favteams);
		$this->content        = $this->model->getRoundsColumn($this->rounds, $this->config);
		$this->headertitle    = Text::sprintf('COM_SPORTSMANAGEMENT_RESULTS_ROUND_RESULTS2', $this->project->name);
	}

}
