<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage editclub
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementViewEditteam
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2019
 * @version $Id$
 * @access public
 */
class sportsmanagementViewEditteam extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewEditClub::init()
	 * 
	 * @return void
	 */
	function init()
	{
$this->item = $this->model->getData();
		$lists = array();
		$this->form = $this->get('Form');	
		$extended = sportsmanagementHelper::getExtended($this->item->extended, 'team');
		$this->extended = $extended;
        $this->lists = $lists;
        $this->cfg_which_media_tool = ComponentHelper::getParams($this->option)->get('cfg_which_media_tool',0);
	}
	
}
?>

