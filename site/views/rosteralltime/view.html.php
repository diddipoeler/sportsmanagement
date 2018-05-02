<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage rosteralltime
 */

defined('_JEXEC') or die('Restricted access');
require_once(JPATH_SITE.DS.JSM_PATH.DS.'models'.DS.'player.php');

jimport('joomla.application.component.view');

/**
 * sportsmanagementViewRosteralltime
 * 
 * @package 
 * @author abcde
 * @copyright 2015
 * @version $Id$
 * @access public
 */
class sportsmanagementViewRosteralltime extends JViewLegacy
{

	/**
	 * sportsmanagementViewRosteralltime::display()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	function display($tpl=null)
	{
		// Get a refrence of the page instance in joomla
		$document = JFactory::getDocument();
		$model = $this->getModel();
        $user = JFactory::getUser();
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName(),$model::$cfg_which_database);
        
        $state = $this->get('State');
		$items = $this->get('Items');
        
        $pagination	= $this->get('Pagination');

		$this->config = $config;
        $this->team = $model->getTeam();
    
    $this->playerposition = $model->getPlayerPosition();
    $this->project = sportsmanagementModelProject::getProject($model::$cfg_which_database,__METHOD__);
    $this->positioneventtypes = $model->getPositionEventTypes();

	$this->rows = $model->getTeamPlayers(1,$this->positioneventtypes,$items);
    	
        $this->items = $items;
		$this->state = $state;
		$this->user = $user;
		$this->pagination = $pagination;
        
		parent::display($tpl);
	}

}
?>