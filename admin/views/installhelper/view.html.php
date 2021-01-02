<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage installhelper
 * @file       view.html.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\String\StringHelper;
use Joomla\CMS\Application\WebApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * sportsmanagementViewinstallhelper
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2021
 * @version $Id$
 * @access public
 */
class sportsmanagementViewinstallhelper extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewinstallhelper::init()
	 * 
	 * @return void
	 */
	public function init()
	{
$this->install_step     = $this->jinput->get('step');

/** Build the html select list for sportstypes */
		$sportstypes[]  = HTMLHelper::_('select.option', '0', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'), 'id', 'name');
		$mdlSportsTypes = BaseDatabaseModel::getInstance('SportsTypes', 'sportsmanagementModel');
		$allSportstypes = $mdlSportsTypes->getSportsTypes();
		$sportstypes    = array_merge($sportstypes, $allSportstypes);

		$variable = $this->jinput->get('filter_sports_type', 0);

		$lists['sportstype']  = $sportstypes;
		$lists['sportstypes'] = HTMLHelper::_(
			'select.genericList',
			$sportstypes,
			'filter_sports_type',
			'class="inputbox" onChange="" style="width:120px"',
			'id',
			'name',
			$variable
		);
		unset($sportstypes);

		$this->lists   = $lists;
        
    $this->setLayout('install_step_'.$this->install_step);
    
    	
  }
  
  
    /**
	 * Add the page title and toolbar.
	 *
	 * @since 1.6
	 */
	protected function addToolbar()
	{
            $this->title = Text::_('COM_SPORTSMANAGEMENT_ADMIN_INSTALLHELPER_'.$this->install_step);
ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement');
		parent::addToolbar();
	}

	
  }
