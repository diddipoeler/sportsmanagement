<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage eventtype
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

/**
 * sportsmanagementVieweventtype
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementVieweventtype extends sportsmanagementView
{
	
	/**
	 * sportsmanagementVieweventtype::init()
	 * 
	 * @return
	 */
	public function init ()
	{
	
		$this->cfg_which_media_tool	= ComponentHelper::getParams('com_sportsmanagement')->get('cfg_which_media_tool', 0);

	}
 
	
	/**
	 * sportsmanagementVieweventtype::addToolBar()
	 * 
	 * @return void
	 */
	protected function addToolBar() 
	{
	$this->jinput->set('hidemainmenu', true);
	$isNew = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_EVENTTYPE_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_EVENTTYPE_NEW');
        $this->icon = 'quote';
        parent::addToolbar();
	}
    
	
}
