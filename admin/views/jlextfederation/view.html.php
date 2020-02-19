<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage jlextfederation
 */


defined('_JEXEC') or die('Restricted access');
 
/**
 * sportsmanagementViewJlextfederation
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewJlextfederation extends sportsmanagementView
{
	
	
	/**
	 * sportsmanagementViewJlextfederation::init()
	 * 
	 * @return
	 */
	public function init ()
	{
       
	}
 
	
	/**
	 * sportsmanagementViewJlextfederation::addToolBar()
	 * 
	 * @return void
	 */
	protected function addToolBar() 
	{
		$this->jinput->set('hidemainmenu', true);
        parent::addToolbar();
	}
	
}
