<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage sportstype
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text; 

/**
 * sportsmanagementViewSportsType
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewSportsType extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewSportsType::init()
	 * 
	 * @return
	 */
	public function init ()
	{
      
	}
 
	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		$this->jinput->set('hidemainmenu', true);
        $isNew = $this->item->id ? $this->title = Text::_('COM_SPORTSMANAGEMENT_SPORTSTYPE_EDIT') : $this->title = Text::_('COM_SPORTSMANAGEMENT_SPORTSTYPE_NEW');
        $this->icon = 'sportstype';
	parent::addToolbar();
	}

}
