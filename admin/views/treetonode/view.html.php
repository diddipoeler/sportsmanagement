<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage treetonode
 */

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

//jimport( 'joomla.application.component.view' );


/**
 * sportsmanagementViewTreetonode
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2017
 * @version $Id$
 * @access public
 */
class sportsmanagementViewTreetonode extends sportsmanagementView
{
	
    /**
     * sportsmanagementViewTreetonode::init()
     * 
     * @return
     */
    function init(  )
	{
		if ( $this->getLayout() == 'edit' || $this->getLayout() == 'edit_3' || $this->getLayout() == 'edit_4' )
		{
			$this->_displayForm(  );
			return;
		}

		//parent::display( $tpl );
	}

	/**
	 * sportsmanagementViewTreetonode::_displayForm()
	 * 
	 * @return void
	 */
	function _displayForm(  )
	{
		//$option = JFactory::getApplication()->input->getCmd('option');

		//$app	= JFactory::getApplication();
		//$project_id = $this->jinput->get('pid');
        $pid = $this->app->getUserState( $this->option . '.pid' );
        $tid = $this->app->getUserState( $this->option . '.tid' );
        
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' pid<br><pre>'.print_r($pid,true).'</pre>'),'Notice');
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' tid<br><pre>'.print_r($tid,true).'</pre>'),'Notice');
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' item<br><pre>'.print_r($this->item,true).'</pre>'),'Notice');

		//$uri 	= JFactory::getURI();
//		$user 	= JFactory::getUser();
		//$model	= $this->getModel();

		$lists = array();
		
	//	$node = $this->get('data');
		$match = $this->model->getNodeMatch();
		
        //$total = $this->get('Total');
		//$pagination = $this->get('Pagination');
		//$projectws = $this->get( 'Data', 'project' );
        $mdlProject = JModelLegacy::getInstance('Project', 'sportsmanagementModel');
		$projectws = $mdlProject->getProject($pid);
		
		$model = $this->getModel('project');
		$mdlTreetonodes = JModelLegacy::getInstance("Treetonodes", "sportsmanagementModel");
		$team_id[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT_TEAM'));
		if( $projectteams = $mdlTreetonodes->getProjectTeamsOptions($pid) )
		{
			$team_id = array_merge($team_id,$projectteams);
		}
		$lists['team'] = $team_id;
		unset($team_id);

		//$this->assignRef( 'user',		JFactory::getUser() );
		$this->projectws = $projectws;
		$this->lists = $lists;
		//$this->assignRef( 'division',		$division );
//		$this->assignRef( 'division_id',	$division_id );
		$this->node = $this->item;
		$this->match = $match;
		//$this->pagination = $this->pagination;
		//parent::display( $tpl );
	}

}
?>