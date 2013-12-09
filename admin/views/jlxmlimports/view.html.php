<?php
/**
 * @copyright	Copyright (C) 2006-2013 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view' );
jimport('joomla.html.parameter.element.timezones');

/**
 * HTML View class for the Joomleague component
 *
 * @author	diddipoeler
 * @package	SportsManagement
 * @since	1.5.0a
 */
class sportsmanagementViewJLXMLImports extends JView
{
	function display($tpl=null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        $model			= JModel::getInstance('jlxmlimport', 'sportsmanagementmodel');

		if ($this->getLayout()=='form')
		{
			$this->_displayForm($tpl);
			return;
		}
        
        if ($this->getLayout()=='update')
		{
			$this->_displayUpdate($tpl);
			return;
		}

		if ($this->getLayout()=='info')
		{
			$this->_displayInfo($tpl);
			return;
		}

		if ($this->getLayout()=='selectpage')
		{
			$this->_displaySelectpage($tpl);
			return;
		}

		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        // Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_1_3'),'xmlimport');
		sportsmanagementHelper::ToolbarButtonOnlineHelp();

		$uri = JFactory::getURI();
		$config = JComponentHelper::getParams('com_media');
		$post=JRequest::get('post');
		$files=JRequest::get('files');

		$this->assign('request_url',$uri->toString());
		$this->assignRef('config',$config);
        $this->assign('projektfussballineuropa',$model->getDataUpdateImportID() );

		parent::display($tpl);
	}

	
    private function _displayUpdate($tpl)
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       //$project_id = (int) $mainframe->getUserState($option.'project', 0);
       //$mainframe->enqueueMessage(JText::_('_displayUpdate project_id -> '.'<pre>'.print_r($project_id ,true).'</pre>' ),'');
       $model			= JModel::getInstance('jlxmlimport', 'sportsmanagementmodel');
	   $data			= $model->getData();
       $update_matches = $model->getDataUpdate(); 
       $this->assignRef('xml', $data);
       $this->assignRef('importData', $update_matches);
       $this->assignRef('projektfussballineuropa',$model->getDataUpdateImportID() );
       $this->assignRef('option',$option);
       
       // Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
       // Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_1_4'),'xmlimport');
		sportsmanagementHelper::ToolbarButtonOnlineHelp();
        JToolBarHelper::preferences(JRequest::getCmd('option'));
        
	   parent::display($tpl);
    }  
    
     
    private function _displayForm($tpl)
	{
		$mtime			= microtime();
		$mtime 			= explode(" ",$mtime);
		$mtime			= $mtime[1] + $mtime[0];
		$starttime		= $mtime;
		$option = JRequest::getCmd('option');
		$mainframe		= JFactory::getApplication();
		$document		= JFactory::getDocument();
		$db				= JFactory::getDBO();
		$uri			= JFactory::getURI();
		$model			= JModel::getInstance('jlxmlimport', 'sportsmanagementmodel');
		$data			= $model->getData();
		$uploadArray	= $mainframe->getUserState($option.'uploadArray',array());
		// TODO: import timezone
		$value  		= isset($data['project']->timezone) ? $data['project']->timezone: null;
		
        
        // Get the list of time zones from the server.
		$zones = DateTimeZone::listIdentifiers();
        
        //$mainframe->enqueueMessage(JText::_('_displayForm zones<br><pre>'.print_r($zones,true).'</pre>'),'Error');
        //$mainframe->enqueueMessage(JText::_('_displayForm groups<br><pre>'.print_r($groups,true).'</pre>'),'Error');
        //$lists['timezone']=$groups;
        $lists['timezone']=JHtml::_('select.genericList',$zones,'timezone','class="inputbox" ','value','text',$value);
        //$lists['timezone']= JHtml::_('select.genericlist', array(), 'timezone', ' class="inputbox"', 'value', 'text', $value);
		
    $whichfile = $mainframe->getUserState($option.'whichfile');
		$this->assignRef('option',$option);
        $this->assignRef('whichfile',$whichfile);
        $projectidimport = $mainframe->getUserState($option.'projectidimport');
        $this->assignRef('projectidimport',$projectidimport);
		//$countries=new Countries();
		$this->assignRef('uploadArray',$uploadArray);
		$this->assignRef('starttime',$starttime);
        // diddi
		$this->assign('countries',Countries::getCountryOptions());
        
		$this->assign('request_url',$uri->toString());
		$this->assignRef('xml', $data);
        // diddi
        $mdl = JModel::getInstance("leagues", "sportsmanagementModel");
		$this->assign('leagues',$mdl->getLeagues());
        // diddi
        $mdl = JModel::getInstance("seasons", "sportsmanagementModel");
		$this->assign('seasons',$mdl->getSeasons());
        // diddi
        $mdl = JModel::getInstance("sportstypes", "sportsmanagementModel");
		$this->assign('sportstypes',$mdl->getSportsTypes());
        
		$this->assign('admins',$model->getUserList(false));
		$this->assign('editors',$model->getUserList(false));
		$this->assign('templates',$model->getTemplateList());
        // diddi
        $mdl = JModel::getInstance("teams", "sportsmanagementModel");
		$this->assign('teams',$mdl->getTeamListSelect());
        // diddi
        $mdl = JModel::getInstance("clubs", "sportsmanagementModel");
		$this->assign('clubs',$mdl->getClubListSelect());
        // diddi
        $mdl = JModel::getInstance("eventtypes", "sportsmanagementModel");
		$this->assign('events',$mdl->getEventList());
        // diddi
        $mdl = JModel::getInstance("positions", "sportsmanagementModel");
		$this->assign('positions',$mdl->getPositionListSelect());
		$this->assign('parentpositions',$mdl->getParentsPositions());
        // diddi
        $mdl = JModel::getInstance("playgrounds", "sportsmanagementModel");
		$this->assign('playgrounds',$mdl->getPlaygroundListSelect());
        
        $mdl = JModel::getInstance('jlxmlimport', 'sportsmanagementmodel');
        // diddi
        $mdl = JModel::getInstance("persons", "sportsmanagementModel");
		$this->assign('persons',$mdl->getPersonListSelect());
        // diddi
        $mdl = JModel::getInstance("statistics", "sportsmanagementModel");
		$this->assign('statistics',$mdl->getStatisticListSelect());
        
		$this->assign('OldCountries',$model->getCountryByOldid());
		$this->assignRef('import_version',$model->import_version);
		$this->assignRef('lists',$lists);
		
    $this->assign('show_debug_info', JComponentHelper::getParams($option)->get('show_debug_info',0) );
    
		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        // Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_2_3'),'xmlimport');
		//                       task    image  mouseover_img           alt_text_for_image              check_that_standard_list_item_is_checked
		JToolBarHelper::custom('jlxmlimport.insert','upload','upload',Jtext::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_START_BUTTON'), false); // --> bij clicken op import wordt de insert view geactiveerd
		JToolBarHelper::back('JPREV','index.php?option=com_sportsmanagement&view=cpanel');
		sportsmanagementHelper::ToolbarButtonOnlineHelp();

		parent::display($tpl);
	}

	private function _displayInfo($tpl)
	{
		$option = JRequest::getCmd('option');
		$mainframe 	= JFactory::getApplication();
        $mtime 		= microtime();
		$mtime		= explode(" ",$mtime);
		$mtime		= $mtime[1] + $mtime[0];
		$starttime	= $mtime;
		$model 		= JModel::getInstance('jlxmlimport', 'sportsmanagementmodel');
		$post		= JRequest::get('post');
		
		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        // Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_3_3'),'xmlimport');
		JToolBarHelper::back('JPREV','index.php?option=com_sportsmanagement&view=projects');
		sportsmanagementHelper::ToolbarButtonOnlineHelp();

		$this->assignRef('starttime',$starttime);
		$this->assignRef('importData',$model->importData($post));
		$this->assign('postData',$post);
        $this->assignRef('option',$option);

		parent::display($tpl);
	}

	private function _displaySelectpage($tpl)
	{
		$option = JRequest::getCmd('option');
		$mainframe 	= JFactory::getApplication();
		$document 	= JFactory::getDocument();
		$db 		= JFactory::getDBO();
		$uri 		= JFactory::getURI();
		$model 		= JModel::getInstance('JLXMLImport', 'sportsmanagementmodel');
		$lists 		= array();

		$this->assignRef('request_url',$uri->toString());
		$this->assignRef('selectType',$mainframe->getUserState($option.'selectType'));
		$this->assignRef('recordID',$mainframe->getUserState($option.'recordID'));
        $this->assignRef('option',$option);

		switch ($this->selectType)
		{
			case '10':   { // Select new Club
						$this->assignRef('clubs',$model->getNewClubListSelect());
						$clublist=array();
						$clublist[]=JHtml::_('select.option',0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_CLUB'));
						$clublist=array_merge($clublist,$this->clubs);
						$lists['clubs']=JHtml::_(	'select.genericlist',$clublist,'clubID','class="inputbox select-club" onchange="javascript:insertNewClub(\''.$this->recordID.'\')" ','value','text', 0);
						unset($clubteamlist);
						}
						break;
			case '9':   { // Select Club & Team
						$this->assignRef('clubsteams',$model->getClubAndTeamListSelect());
						$clubteamlist=array();
						$clubteamlist[]=JHtml::_('select.option',0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_CLUB_AND_TEAM'));
						$clubteamlist=array_merge($clubteamlist,$this->clubsteams);
						$lists['clubsteams']=JHtml::_(	'select.genericlist',$clubteamlist,'teamID','class="inputbox select-team" onchange="javascript:insertClubAndTeam(\''.$this->recordID.'\')" ','value','text', 0);
						unset($clubteamlist);
						}
						break;
			case '8':	{ // Select Statistics
						$mdl = JModel::getInstance("statistics", "sportsmanagementModel");
                        $this->assignRef('statistics',$mdl->getStatisticListSelect());
						$statisticlist=array();
						$statisticlist[]=JHtml::_('select.option',0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_STATISTIC'));
						$statisticlist=array_merge($statisticlist,$this->statistics);
						$lists['statistics']=JHtml::_('select.genericlist',$statisticlist,'statisticID','class="inputbox select-statistic" onchange="javascript:insertStatistic(\''.$this->recordID.'\')" ');
						unset($statisticlist);
						}
						break;

			case '7':	{ // Select ParentPosition
						$mdl = JModel::getInstance("positions", "sportsmanagementModel");
                        $this->assignRef('parentpositions',$mdl->getParentsPositions());
						$parentpositionlist=array();
						$parentpositionlist[]=JHtml::_('select.option',0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_PARENT_POSITION'));
						$parentpositionlist=array_merge($parentpositionlist,$this->parentpositions);
						$lists['parentpositions']=JHtml::_('select.genericlist',$parentpositionlist,'parentPositionID','class="inputbox select-parentposition" onchange="javascript:insertParentPosition(\''.$this->recordID.'\')" ');
						unset($parentpositionlist);
						}
						break;

			case '6':	{ // Select Position
						$mdl = JModel::getInstance("positions", "sportsmanagementModel");
                        $this->assignRef('positions',$mdl->getPositionListSelect());
						$positionlist=array();
						$positionlist[]=JHtml::_('select.option',0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_POSITION'));
						$positionlist=array_merge($positionlist,$this->positions);
						$lists['positions']=JHtml::_('select.genericlist',$positionlist,'positionID','class="inputbox select-position" onchange="javascript:insertPosition(\''.$this->recordID.'\')" ');
						unset($positionlist);
						}
						break;

			case '5':	{ // Select Event
						$mdl = JModel::getInstance("eventtypes", "sportsmanagementModel");
                        $this->assignRef('events',$mdl->getEventList());
						$eventlist=array();
						$eventlist[]=JHtml::_('select.option',0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_EVENT'));
						$eventlist=array_merge($eventlist,$this->events);
						$lists['events']=JHtml::_('select.genericlist',$eventlist,'eventID','class="inputbox select-event" onchange="javascript:insertEvent(\''.$this->recordID.'\')" ');
						unset($eventlist);
						}
						break;

			case '4':	{ // Select Playground
						$mdl = JModel::getInstance("playgrounds", "sportsmanagementModel");
                        $this->assignRef('playgrounds',$mdl->getPlaygroundListSelect());
						$playgroundlist=array();
						$playgroundlist[]=JHtml::_('select.option',0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_PLAYGROUND'));
						$playgroundlist=array_merge($playgroundlist,$this->playgrounds);
						$lists['playgrounds']=JHtml::_('select.genericlist',$playgroundlist,'playgroundID','class="inputbox select-playground" onchange="javascript:insertPlayground(\''.$this->recordID.'\')" ');
						unset($playgroundlist);
						}
						break;

			case '3':	{ // Select Person
                        $mdl = JModel::getInstance("persons", "sportsmanagementModel");
						$this->assignRef('persons',$mdl->getPersonListSelect());
						$personlist=array();
						$personlist[]=JHtml::_('select.option',0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_PERSON'));
						$personlist=array_merge($personlist,$this->persons);
						$lists['persons']=JHtml::_('select.genericlist',$personlist,'personID','class="inputbox select-person" onchange="javascript:insertPerson(\''.$this->recordID.'\')" ');
						unset($personlist);
						}
						break;

			case '2':	{ // Select Club
						$mdl = JModel::getInstance("clubs", "sportsmanagementModel");
                        $this->assignRef('clubs',$mdl->getClubListSelect());
						$clublist=array();
						$clublist[]=JHtml::_('select.option',0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_CLUB'));
						$clublist=array_merge($clublist,$this->clubs);
						$lists['clubs']=JHtml::_('select.genericlist',$clublist,'clubID','class="inputbox select-club" onchange="javascript:insertClub(\''.$this->recordID.'\')" ');
						unset($clublist);
						}
						break;

			case '1':
			default:	{ // Select Team
                        $mdl = JModel::getInstance("teams", "sportsmanagementModel");
						$this->assignRef('teams',$mdl->getTeamListSelect());
                        $mdl = JModel::getInstance("clubs", "sportsmanagementModel");
						$this->assignRef('clubs',$mdl->getClubListSelect());
						$teamlist=array();
						$teamlist[]=JHtml::_('select.option',0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_TEAM'));
						$teamlist=array_merge($teamlist,$this->teams);
						$lists['teams']=JHtml::_('select.genericlist',$teamlist,'teamID','class="inputbox select-team" onchange="javascript:insertTeam(\''.$this->recordID.'\')" ','value','text',0);
						unset($teamlist);
						}
						break;
		}

		$this->assignRef('lists',$lists);
		// Set page title
		$pageTitle=JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_ASSIGN_TITLE');
		$document->setTitle($pageTitle);

		parent::display($tpl);
	}

}
?>
