<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage controllers
 * @file       projectteam.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementControllerprojectteam
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementControllerprojectteam extends JSMControllerForm
{

	/**
	 * sportsmanagementControllerprojectteam::storechangeteams()
	 *
	 * @return void
	 */
	function storechangeteams()
	{
		$model = $this->getModel('projectteams');
		$model->setNewTeamID();
		$msg = '';
		$this->setRedirect('index.php?option=com_sportsmanagement&view=close&tmpl=component', $msg);
	}

}
