<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage treetonodes
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');


/**
 * sportsmanagementViewTreetonodes
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class sportsmanagementViewTreetonodes extends sportsmanagementView
{

	/**
	 * sportsmanagementViewTreetonodes::init()
	 * 
	 * @return
	 */
	public function init ()
	{
		//$app = JFactory::getApplication();
        
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getLayout<br><pre>'.print_r($this->getLayout(),true).'</pre>'),'Notice');
        
		if ( $this->getLayout()=='default' || $this->getLayout()=='default_3' )
		{
			$this->_displayDefault();
			return;
		}
	//	parent::display($tpl);
	}

	/**
	 * sportsmanagementViewTreetonodes::_displayDefault()
	 * 
	 * @return void
	 */
	function _displayDefault()
	{
		//$option = JFactory::getApplication()->input->getCmd('option');
//		$app = JFactory::getApplication();
//		$db = JFactory::getDbo();
//		$uri = JFactory::getURI();

		$this->node = $this->items;
		//$this->total = $this->get('Total');
//		$this->pagination = $this->get('Pagination');

		//$model = $this->getModel();
		//$projectws = $this->get('Data','project');
        $this->project_id = $this->app->getUserState( "$this->option.pid", '0' );
		$mdlProject = JModelLegacy::getInstance('Project', 'sportsmanagementModel');
		$projectws = $mdlProject->getProject($this->project_id);
        
        //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' project_id<br><pre>'.print_r($this->project_id,true).'</pre>'),'Notice');
        
		//$treetows = $this->get('Data','treeto');
        $mdltreeto = JModelLegacy::getInstance('treeto', 'sportsmanagementModel');
		$treetows = $mdltreeto->getTreeToData($this->jinput->get('tid'));

		//build the html options for teams
		$team_id[] = JHtml::_('select.option','0',JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TEAMS_LEGEND'));
		if ( $projectteams = $this->model->getProjectTeamsOptions() )
		{
			$team_id = array_merge($team_id,$projectteams);
		}
		$lists['team'] = $team_id;
		unset($team_id);
        
        //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectteams<br><pre>'.print_r($projectteams,true).'</pre>'),'Notice');

		$style  = 'style="background-color: #dddddd; ';
		$style .= 'border: 0px solid white;';
		$style .= 'font-weight: normal; ';
		$style .= 'font-size: 8pt; ';
		$style .= 'width: 150px; ';
		$style .= 'font-family: verdana; ';
		$style .= 'text-align: center;"';
		$path = 'media/com_sportsmanagement/treebracket/onwhite/';
		
		// build the html radio for adding into new round / exist round
		$createYesNo = array(0 => JText::_('JNO'),1 => JText::_('JYES'));
		$createLeftRight = array(0 => JText::_('L'),1 => JText::_('R'));
		$ynOptions = array();
		$lrOptions = array();
		foreach($createYesNo AS $key => $value){$ynOptions[]=JHtmlSelect::option($key,$value);}
		foreach($createLeftRight AS $key => $value){$lrOptions[]=JHtmlSelect::option($key,$value);}
		$lists['addToRound'] = JHtmlSelect::radiolist($ynOptions,'addToRound','class="inputbox"','value','text',1);

		// build the html radio for auto publish new matches
		$lists['autoPublish'] = JHtmlSelect::radiolist($ynOptions,'autoPublish','class="inputbox"','value','text',0);

		// build the html radio for Left or Right redepth
		$lists['LRreDepth'] = JHtmlSelect::radiolist($lrOptions,'LRreDepth','class="inputbox"','value','text',0);
		// build the html radio for create new treeto
		$lists['createNewTreeto'] = JHtmlSelect::radiolist($ynOptions,'createNewTreeto','class="inputbox"','value','text',1);

		$this->lists = $lists;
//		$this->assignRef('node',$node);
	//	$this->assignRef('roundcode',$roundcode);
		$this->style = $style;
		$this->path = $path;
		$this->projectws = $projectws;
		$this->treetows = $treetows;
        
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' lists<br><pre>'.print_r($this->lists,true).'</pre>'),'Notice');
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' style<br><pre>'.print_r($this->style,true).'</pre>'),'Notice');
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' path<br><pre>'.print_r($this->path,true).'</pre>'),'Notice');
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' projectws<br><pre>'.print_r($this->projectws,true).'</pre>'),'Notice');
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' treetows<br><pre>'.print_r($this->treetows,true).'</pre>'),'Notice');
//        $this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' node<br><pre>'.print_r($this->node,true).'</pre>'),'Notice');
        
        //$this->addToolBar();
        
        //$this->setLayout('default'); 
        
//		$this->assignRef('total',$total);
//		$this->assignRef('pagination',$pagination);
		//$this->assignRef('request_url',$uri->toString());

		//parent::display($tpl);
	}
    
    
    	/**
    	 * sportsmanagementViewTreetonodes::addToolBar()
    	 * 
    	 * @return void
    	 */
    	protected function addToolBar()
	{
	  // $istree = $this->treetows->tree_i;
//$isleafed = $this->treetows->leafed;
       $this->title = JText::_('COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_TITLE');
       switch ($this->treetows->leafed)
       {
       case 1:
       JToolbarHelper::apply('treetonode.saveshort', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_SAVE_APPLY' ), false);
	   JToolbarHelper::custom('treetonode.removenode', 'delete.png', 'delete_f2.png', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_DELETE_ALL' ), false );
       break;
       case 2:
       JToolbarHelper::apply('treetonode.saveshortleaf',JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_TEST_SHOW' ), false);
       JToolbarHelper::custom('treetonode.removenode', 'delete.png', 'delete_f2.png', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_DELETE' ), false );
       break; 
       case 3:
       JToolbarHelper::apply('treetonode.savefinishleaf', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_SAVE_LEAF' ), false);
       JToolbarHelper::custom('treetonode.removenode', 'delete.png', 'delete_f2.png', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_DELETE' ), false );
       break;  
        
       }
       parent::addToolbar();
   // if( $this->treetows->leafed == 1 )
//	{
//	JToolbarHelper::apply('treetonode.saveshort', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_SAVE_APPLY' ), false);
//	JToolbarHelper::custom('treetonode.removenode', 'delete.png', 'delete_f2.png', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_DELETE_ALL' ), false );
//	}
//	elseif( $this->treetows->leafed )
//	{
//	JToolbarHelper::apply('treetonode.saveshortleaf',JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_TEST_SHOW' ), false);
//	if( $this->treetows->leafed == 3 )
//	{
//	JToolbarHelper::apply('treetonode.savefinishleaf', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_SAVE_LEAF' ), false);
//	}
//	JToolbarHelper::custom('treetonode.removenode', 'delete.png', 'delete_f2.png', JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_TREETONODES_DELETE' ), false );
//	}
       
      } 

}
?>
