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
$sportstypes[]  = HTMLHelper::_('select.option', '', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE_FILTER'));
$sportstypes[]  = HTMLHelper::_('select.option', 'soccer', Text::_('COM_SPORTSMANAGEMENT_ST_SOCCER') );
$sportstypes[]  = HTMLHelper::_('select.option', 'tablesoccer', Text::_('COM_SPORTSMANAGEMENT_ST_TABLESOCCER') );
$sportstypes[]  = HTMLHelper::_('select.option', 'hockey', Text::_('COM_SPORTSMANAGEMENT_ST_HOCKEY') );
$sportstypes[]  = HTMLHelper::_('select.option', 'floorball', Text::_('COM_SPORTSMANAGEMENT_ST_FLOORBALL') );
$sportstypes[]  = HTMLHelper::_('select.option', 'skater_hockey', Text::_('COM_SPORTSMANAGEMENT_ST_SKATER_HOCKEY') );
$sportstypes[]  = HTMLHelper::_('select.option', 'american_football', Text::_('COM_SPORTSMANAGEMENT_ST_AMERICAN_FOOTBALL') );
$sportstypes[]  = HTMLHelper::_('select.option', 'icehockey', Text::_('COM_SPORTSMANAGEMENT_ST_ICEHOCKEY') );
$sportstypes[]  = HTMLHelper::_('select.option', 'volleyball', Text::_('COM_SPORTSMANAGEMENT_ST_VOLLEYBALL') );
$sportstypes[]  = HTMLHelper::_('select.option', 'korfball', Text::_('COM_SPORTSMANAGEMENT_ST_KORFBALL') );
$sportstypes[]  = HTMLHelper::_('select.option', 'handball', Text::_('COM_SPORTSMANAGEMENT_ST_HANDBALL') );
$sportstypes[]  = HTMLHelper::_('select.option', 'tennis', Text::_('COM_SPORTSMANAGEMENT_ST_TENNIS') );
$sportstypes[]  = HTMLHelper::_('select.option', 'tabletennis', Text::_('COM_SPORTSMANAGEMENT_ST_TABLETENNIS') );
$sportstypes[]  = HTMLHelper::_('select.option', 'basketball', Text::_('COM_SPORTSMANAGEMENT_ST_BASKETBALL') );
$sportstypes[]  = HTMLHelper::_('select.option', 'australien_rules_football', Text::_('COM_SPORTSMANAGEMENT_ST_AUSTRALIEN_RULES_FOOTBALL') );
$sportstypes[]  = HTMLHelper::_('select.option', 'dart', Text::_('COM_SPORTSMANAGEMENT_ST_DART') );
$sportstypes[]  = HTMLHelper::_('select.option', 'waterpolo', Text::_('COM_SPORTSMANAGEMENT_ST_WATERPOLO') );
$sportstypes[]  = HTMLHelper::_('select.option', 'small_bore_rifle_association', Text::_('COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION') );	

        
//		$mdlSportsTypes = BaseDatabaseModel::getInstance('SportsTypes', 'sportsmanagementModel');
//		$allSportstypes = $mdlSportsTypes->getSportsTypes();
//		$sportstypes    = array_merge($sportstypes, $allSportstypes);
$variable = $this->jinput->get('filter_sports_type', 0);
		$lists['sportstype']  = $sportstypes;
		$lists['sportstypes'] = HTMLHelper::_(
			'select.genericList',
			$sportstypes,
			'filter_sports_type',
			'class="inputbox" onChange="" style="width:120px"',
			'value',
			'text',
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
