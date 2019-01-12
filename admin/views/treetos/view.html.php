<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage treetos
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementViewTreetos
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementViewTreetos extends sportsmanagementView
{

	/**
	 * sportsmanagementViewTreetos::init()
	 * 
	 * @return void
	 */
	public function init ()
	{
        
        $this->project_id = $this->app->getUserState( "$this->option.pid", '0' );
        $mdlProject = BaseDatabaseModel::getInstance('Project', 'sportsmanagementModel');
	    $projectws = $mdlProject->getProject($this->project_id);
        
		$division = $this->app->getUserStateFromRequest($this->option.'tt_division', 'division', '', 'string');

		//build the html options for divisions
		$divisions[] = JHtmlSelect::option('0',Text::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_DIVISION'));
		$mdlDivisions = BaseDatabaseModel::getInstance("divisions", "sportsmanagementModel");
		if ($res = $mdlDivisions->getDivisions($this->project_id))
        {
			$divisions = array_merge($divisions,$res);
		}
		$lists['divisions'] = $divisions;
		unset($divisions);
	
		//$this->user = $user;
		$this->lists = $lists;
		//$this->items = $items;
		$this->projectws = $projectws;
		$this->division = $division;
		//$this->total = $total;
		//$this->pagination = $pagination;
		//$this->request_url = $uri;
        
        //$this->setLayout('default');

		//$this->addToolbar();
//		parent::display($tpl);
	}

	/**
	 * sportsmanagementViewTreetos::addToolbar()
	 * 
	 * @return void
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_TITLE'),'Tree');

		JToolbarHelper::apply('treeto.saveshort');
		JToolbarHelper::publishList('treetos.publish');
		JToolbarHelper::unpublishList('treetos.unpublish');
		JToolbarHelper::divider();

		JToolbarHelper::addNew('treetos.save');
		JToolbarHelper::deleteList(Text::_('COM_SPORTSMANAGEMENT_ADMIN_TREETOS_WARNING'), 'treeto.remove');
		JToolbarHelper::divider();
        
        parent::addToolbar();

		
	}
}
?>
