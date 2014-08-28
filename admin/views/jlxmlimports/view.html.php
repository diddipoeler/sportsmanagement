<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


jimport('joomla.html.parameter.element.timezones');


/**
 * sportsmanagementViewJLXMLImports
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewJLXMLImports extends sportsmanagementView
{
	
	/**
	 * sportsmanagementViewJLXMLImports::init()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	public function init ($tpl = null)
	{
		$option = JRequest::getCmd('option');
		$mainframe = JFactory::getApplication();
        // Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        $model = JModelLegacy::getInstance('jlxmlimport', 'sportsmanagementmodel');
        $document->addScript ( JUri::root(true).'/administrator/components/'.$option.'/assets/js/jlxmlimports.js' );
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getLayout <br><pre>'.print_r($this->getLayout(),true).'</pre>'),'');

		if ( $this->getLayout()=='form' || $this->getLayout()=='form_3' )
		{
			$this->_displayForm($tpl);
			return;
		}
        
        if ( $this->getLayout()=='update' || $this->getLayout()=='update_3' )
		{
			$this->_displayUpdate($tpl);
			return;
		}

		if ( $this->getLayout()=='info' || $this->getLayout()=='info_3' )
		{
			$this->_displayInfo($tpl);
			return;
		}

		if ( $this->getLayout()=='selectpage' || $this->getLayout()=='selectpage_3' )
		{
			$this->_displaySelectpage($tpl);
			return;
		}

		
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        // Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_1_3'),'xmlimports');
		

		$uri = JFactory::getURI();
		$config = JComponentHelper::getParams('com_media');
		$post=JRequest::get('post');
		$files=JRequest::get('files');

		$this->assign('request_url',$uri->toString());
		$this->assignRef('config',$config);
        $this->assign('projektfussballineuropa',$model->getDataUpdateImportID() );
        
        parent::addToolbar();

		//parent::display($tpl);
	}

	
    /**
     * sportsmanagementViewJLXMLImports::_displayUpdate()
     * 
     * @param mixed $tpl
     * @return void
     */
    private function _displayUpdate($tpl)
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       //$project_id = (int) $mainframe->getUserState($option.'project', 0);
       //$mainframe->enqueueMessage(JText::_('_displayUpdate project_id -> '.'<pre>'.print_r($project_id ,true).'</pre>' ),'');
       $model = JModelLegacy::getInstance('jlxmlimport', 'sportsmanagementmodel');
	   $data = $model->getData();
       $update_matches = $model->getDataUpdate(); 
       $this->assignRef('xml', $data);
       $this->assignRef('importData', $update_matches);
       $this->assign('projektfussballineuropa',$model->getDataUpdateImportID() );
       $this->assignRef('option',$option);
       
       // Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
       // Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_1_4'),'xmlimport');
        JToolBarHelper::back('JPREV','index.php?option=com_sportsmanagement&view=cpanel');
		
        parent::addToolbar();
        
	   //parent::display($tpl);
    }  
    
     
    /**
     * sportsmanagementViewJLXMLImports::_displayForm()
     * 
     * @param mixed $tpl
     * @return void
     */
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
		$model			= JModelLegacy::getInstance('jlxmlimport', 'sportsmanagementmodel');
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
		$this->assign('countries',JSMCountries::getCountryOptions());
        
		$this->assign('request_url',$uri->toString());
		$this->assignRef('xml', $data);
        // diddi
        $mdl = JModelLegacy::getInstance("leagues", "sportsmanagementModel");
		$this->assign('leagues',$mdl->getLeagues());
        // diddi
        $mdl = JModelLegacy::getInstance("seasons", "sportsmanagementModel");
		$this->assign('seasons',$mdl->getSeasons());
        // diddi
        $mdl = JModelLegacy::getInstance("sportstypes", "sportsmanagementModel");
		$this->assign('sportstypes',$mdl->getSportsTypes());
        
		$this->assign('admins',$model->getUserList(false));
		$this->assign('editors',$model->getUserList(false));
		$this->assign('templates',$model->getTemplateList());
        // diddi
        $mdl = JModelLegacy::getInstance("teams", "sportsmanagementModel");
		$this->assign('teams',$mdl->getTeamListSelect());
        // diddi
        $mdl = JModelLegacy::getInstance("clubs", "sportsmanagementModel");
		$this->assign('clubs',$mdl->getClubListSelect());
        // diddi
        $mdl = JModelLegacy::getInstance("eventtypes", "sportsmanagementModel");
		$this->assign('events',$mdl->getEventList());
        // diddi
        $mdl = JModelLegacy::getInstance("positions", "sportsmanagementModel");
		$this->assign('positions',$mdl->getPositionListSelect());
		$this->assign('parentpositions',$mdl->getParentsPositions());
        // diddi
        $mdl = JModelLegacy::getInstance("playgrounds", "sportsmanagementModel");
		$this->assign('playgrounds',$mdl->getPlaygroundListSelect());
        
        $mdl = JModelLegacy::getInstance('jlxmlimport', 'sportsmanagementmodel');
        // diddi
        $mdl = JModelLegacy::getInstance("persons", "sportsmanagementModel");
		$this->assign('persons',$mdl->getPersonListSelect());
        // diddi
        $mdl = JModelLegacy::getInstance("statistics", "sportsmanagementModel");
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
		
        parent::addToolbar();
        
        $this->setLayout('form');

		//parent::display($tpl);
	}

	/**
	 * sportsmanagementViewJLXMLImports::_displayInfo()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	private function _displayInfo($tpl)
	{
		$option = JRequest::getCmd('option');
		$mainframe 	= JFactory::getApplication();
        $mtime 		= microtime();
		$mtime		= explode(" ",$mtime);
		$mtime		= $mtime[1] + $mtime[0];
		$starttime	= $mtime;
		$model 		= JModelLegacy::getInstance('jlxmlimport', 'sportsmanagementmodel');
		$post		= JRequest::get('post');
		
		// Get a refrence of the page instance in joomla
		$document	= JFactory::getDocument();
        // Set toolbar items for the page
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        // Set toolbar items for the page
		JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_TITLE_3_3'),'xmlimport');
			

		$this->assignRef('starttime',$starttime);
		$this->assign('importData',$model->importData($post));
		$this->assign('postData',$post);
        $this->assignRef('option',$option);
                
        JToolBarHelper::divider();
        JToolBarHelper::back('JPREV','index.php?option=com_sportsmanagement&view=projects');
		
        parent::addToolbar();
		
        //parent::display($tpl);
	}

	/**
	 * sportsmanagementViewJLXMLImports::_displaySelectpage()
	 * 
	 * @param mixed $tpl
	 * @return void
	 */
	private function _displaySelectpage($tpl)
	{
		$option = JRequest::getCmd('option');
		$mainframe 	= JFactory::getApplication();
		$document 	= JFactory::getDocument();
		$db 		= JFactory::getDBO();
		$uri 		= JFactory::getURI();
		$model 		= JModelLegacy::getInstance('JLXMLImport', 'sportsmanagementmodel');
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
						$mdl = JModelLegacy::getInstance("statistics", "sportsmanagementModel");
                        $this->assignRef('statistics',$mdl->getStatisticListSelect());
						$statisticlist=array();
						$statisticlist[]=JHtml::_('select.option',0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_STATISTIC'));
						$statisticlist=array_merge($statisticlist,$this->statistics);
						$lists['statistics']=JHtml::_('select.genericlist',$statisticlist,'statisticID','class="inputbox select-statistic" onchange="javascript:insertStatistic(\''.$this->recordID.'\')" ');
						unset($statisticlist);
						}
						break;

			case '7':	{ // Select ParentPosition
						$mdl = JModelLegacy::getInstance("positions", "sportsmanagementModel");
                        $this->assignRef('parentpositions',$mdl->getParentsPositions());
						$parentpositionlist=array();
						$parentpositionlist[]=JHtml::_('select.option',0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_PARENT_POSITION'));
						$parentpositionlist=array_merge($parentpositionlist,$this->parentpositions);
						$lists['parentpositions']=JHtml::_('select.genericlist',$parentpositionlist,'parentPositionID','class="inputbox select-parentposition" onchange="javascript:insertParentPosition(\''.$this->recordID.'\')" ');
						unset($parentpositionlist);
						}
						break;

			case '6':	{ // Select Position
						$mdl = JModelLegacy::getInstance("positions", "sportsmanagementModel");
                        $this->assignRef('positions',$mdl->getPositionListSelect());
						$positionlist=array();
						$positionlist[]=JHtml::_('select.option',0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_POSITION'));
						$positionlist=array_merge($positionlist,$this->positions);
						$lists['positions']=JHtml::_('select.genericlist',$positionlist,'positionID','class="inputbox select-position" onchange="javascript:insertPosition(\''.$this->recordID.'\')" ');
						unset($positionlist);
						}
						break;

			case '5':	{ // Select Event
						$mdl = JModelLegacy::getInstance("eventtypes", "sportsmanagementModel");
                        $this->assignRef('events',$mdl->getEventList());
						$eventlist=array();
						$eventlist[]=JHtml::_('select.option',0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_EVENT'));
						$eventlist=array_merge($eventlist,$this->events);
						$lists['events']=JHtml::_('select.genericlist',$eventlist,'eventID','class="inputbox select-event" onchange="javascript:insertEvent(\''.$this->recordID.'\')" ');
						unset($eventlist);
						}
						break;

			case '4':	{ // Select Playground
						$mdl = JModelLegacy::getInstance("playgrounds", "sportsmanagementModel");
                        $this->assignRef('playgrounds',$mdl->getPlaygroundListSelect());
						$playgroundlist=array();
						$playgroundlist[]=JHtml::_('select.option',0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_PLAYGROUND'));
						$playgroundlist=array_merge($playgroundlist,$this->playgrounds);
						$lists['playgrounds']=JHtml::_('select.genericlist',$playgroundlist,'playgroundID','class="inputbox select-playground" onchange="javascript:insertPlayground(\''.$this->recordID.'\')" ');
						unset($playgroundlist);
						}
						break;

			case '3':	{ // Select Person
                        $mdl = JModelLegacy::getInstance("persons", "sportsmanagementModel");
						$this->assignRef('persons',$mdl->getPersonListSelect());
						$personlist=array();
						$personlist[]=JHtml::_('select.option',0,JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_IMPORT_SELECT_PERSON'));
						$personlist=array_merge($personlist,$this->persons);
						$lists['persons']=JHtml::_('select.genericlist',$personlist,'personID','class="inputbox select-person" onchange="javascript:insertPerson(\''.$this->recordID.'\')" ');
						unset($personlist);
						}
						break;

			case '2':	{ // Select Club
						$mdl = JModelLegacy::getInstance("clubs", "sportsmanagementModel");
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
                        $mdl = JModelLegacy::getInstance("teams", "sportsmanagementModel");
						$this->assignRef('teams',$mdl->getTeamListSelect());
                        $mdl = JModelLegacy::getInstance("clubs", "sportsmanagementModel");
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

		//parent::display($tpl);
	}

}
?>
