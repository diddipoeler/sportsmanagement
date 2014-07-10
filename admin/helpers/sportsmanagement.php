<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/
// No direct access to this file
defined('_JEXEC') or die;
 

/**
 * sportsmanagementHelper
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
abstract class sportsmanagementHelper
{
	
    /**
	 * Add data to the xml
	 *
	 * @param array $data data what we want to add in the xml
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return void
	 */
	 function _addToXml($data)
	{
		if (is_array($data) && count($data) > 0)
		{
			$object = $data[0]['object'];
			$output = '';
			foreach ($data as $name => $value)
			{
				$output .= "<record object=\"" . self::stripInvalidXml($object) . "\">\n";
				foreach ($value as $key => $data)
				{
					if (!is_null($data) && !(substr($key, 0, 1) == "_") && $key != "object")
					{
						$output .= "  <$key><![CDATA[" . self::stripInvalidXml(trim($data)) . "]]></$key>\n";
					}
				}
				$output .= "</record>\n";
			}
			return $output;
		}
		return false;
	}    
    
/**
	 * _setJoomLeagueVersion
	 *
	 * set the version data and actual date, time and
	 * Joomla systemName from the joomleague_version table
	 *
	 * @access private
	 * @since  2010-08-26
	 *
	 * @return array
	 */
	 function _setJoomLeagueVersion()
	{
		$exportRoutine='2010-09-23 15:00:00';
			$result[0]['exportRoutine']=$exportRoutine;
			$result[0]['exportDate']=date('Y-m-d');
			$result[0]['exportTime']=date('H:i:s');
			$result[0]['exportSystem']=JFactory::getConfig()->getValue('config.sitename');
			$result[0]['object']='JoomLeagueVersion';
			return $result;
	}    
    
    
/**
	 * _setLeagueData
	 *
	 * set the league data from the joomleague_league table
	 *
	 * @access private
	 * @since  1.5.5241
	 *
	 * @return array
	 */
	 function _setLeagueData($league)
	{
		
        if ( $league )
        {
            $result[] = JArrayHelper::fromObject($league);
			$result[0]['object'] = 'League';
			return $result;
		}
		return false;
        		
	}    

/**
	 * _setProjectData
	 *
	 * set the project data from the joomleague table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	 function _setProjectData($project)
	{
		if ( $project )
        {
            $result[] = JArrayHelper::fromObject($project);
			$result[0]['object'] = 'JoomLeague20';
			return $result;
		}
		return false;
	}    

/**
	 * _setSeasonData
	 *
	 * set the season data from the joomleague_season table
	 *
	 * @access private
	 * @since  1.5.5241
	 *
	 * @return array
	 */
	 function _setSeasonData($season)
	{
		if ( $season )
        {
            $result[] = JArrayHelper::fromObject($season);
			$result[0]['object'] = 'Season';
			return $result;
		}
		return false;
	}
    
    
    /**
	 * _setSportsType
	 *
	 * set the SportsType
	 *
	 * @access private
	 * @since  1.5.5241
	 *
	 * @return array
	 */
	 function _setSportsType($sportstype)
	{

		if ( $sportstype )
		{
			$result[] = JArrayHelper::fromObject($sportstype);
			$result[0]['object'] = 'SportsType';
			return $result;
		}
		return false;

	}        
    
/**
	 * _setXMLData
	 *
	 * 
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return void
	 */
	 function _setXMLData($data, $object)
	{
	if ( $data )
        {
            foreach ( $data as $row )
            {
                $result[] = JArrayHelper::fromObject($row);
            }
			$result[0]['object'] = $object;
			return $result;
		}
		return false;
	}
    

    
    
    
	/**
	 * sportsmanagementHelper::addSubmenu()
	 * 
	 * @param mixed $submenu
	 * @return void
	 */
	public static function addSubmenu($submenu) 
	{
	   $mainframe	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
        $document=JFactory::getDocument();
        $show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        // retrieve the value of the state variable. If no value is specified,
        // the specified default value will be returned.
        // function syntax is getUserState( $key, $default );
        $project_id = $mainframe->getUserState( "$option.pid", '0' );
        $project_team_id = $mainframe->getUserState( "$option.project_team_id", '0' );
        $team_id = $mainframe->getUserState( "$option.team_id", '0' );
        $club_id = $mainframe->getUserState( "$option.club_id", '0' );
      
        
        
        if ( $show_debug_info )
        {
            $mainframe->enqueueMessage(JText::_('addSubmenu post<br><pre>'.print_r(JRequest::get('post'),true).'</pre>'),'');
            $mainframe->enqueueMessage(JText::_('addSubmenu project_id<br><pre>'.print_r($project_id,true).'</pre>'),'');
            $mainframe->enqueueMessage(JText::_('addSubmenu project_team_id<br><pre>'.print_r($project_team_id,true).'</pre>'),'');
            $mainframe->enqueueMessage(JText::_('addSubmenu team_id<br><pre>'.print_r($team_id,true).'</pre>'),'');
            $mainframe->enqueueMessage(JText::_('addSubmenu club_id<br><pre>'.print_r($club_id,true).'</pre>'),'');
        }
        //$mainframe->enqueueMessage(JText::_('addSubmenu project_id<br><pre>'.print_r($project_id,true).'</pre>'),'');
		
        JSubMenuHelper::addEntry(JText::_('COM_SPORTSMANAGEMENT_MENU'), 'index.php?option=com_sportsmanagement', $submenu == 'menu');
		
        JSubMenuHelper::addEntry(JText::_('COM_SPORTSMANAGEMENT_SUBMENU_EXTENSIONS'), 'index.php?option=com_sportsmanagement&view=extensions', $submenu == 'extensions');
        
        JSubMenuHelper::addEntry(JText::_('COM_SPORTSMANAGEMENT_SUBMENU_PROJECTS'), 'index.php?option=com_sportsmanagement&view=projects', $submenu == 'projects');
        
        if ( $project_id != 0 )
        {
        JSubMenuHelper::addEntry(JText::_('COM_SPORTSMANAGEMENT_SUBMENU_PROJECTS_DETAILS'), 'index.php?option=com_sportsmanagement&view=project&layout=panel&id='.$project_id, $submenu == 'project');
        }
        else
        {
//         $menu = JToolBar::getInstance('submenu');
//         $menu->appendButton(JText::_('COM_SPORTSMANAGEMENT_SUBMENU_PROJECTS_DETAILS'), '', false);    
        //JSubMenuHelper::addEntry(JText::_('COM_SPORTSMANAGEMENT_SUBMENU_PROJECTS_DETAILS'), 'index.php?option=com_sportsmanagement&view=project&layout=panel&id=', false );
        }
        
        JSubMenuHelper::addEntry(JText::_('COM_SPORTSMANAGEMENT_SUBMENU_PREDICTIONS'), 'index.php?option=com_sportsmanagement&view=predictions', $submenu == 'predictions');
        
        JSubMenuHelper::addEntry(JText::_('COM_SPORTSMANAGEMENT_SUBMENU_CURRENT_SEASONS'), 'index.php?option=com_sportsmanagement&view=currentseasons', $submenu == 'currentseasons');
        
        JSubMenuHelper::addEntry(JText::_('COM_SPORTSMANAGEMENT_SUBMENU_GOOGLE_CALENDAR'), 'index.php?option=com_sportsmanagement&view=jsmgooglecalendar', $submenu == 'googlecalendar');
        
        JSubMenuHelper::addEntry(JText::_('COM_SPORTSMANAGEMENT_SUBMENU_SPECIAL_EXTENSIONS'), 'index.php?option=com_sportsmanagement&view=specialextensions', $submenu == 'specialextensions');
        
		// set some global property
		$document = JFactory::getDocument();
		$document->addStyleDeclaration('.icon-48-helloworld {background-image: url(../media/com_sportsmanagement/images/tux-48x48.png);}');
		if ($submenu == 'extensions') 
		{
			$document->setTitle(JText::_('COM_SPORTSMANAGEMENT_ADMINISTRATION_EXTENSIONS'));
		}
	}
    
	/**
	 * Get the actions
	 */
	public static function getActions($messageId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;
 
		if (empty($messageId)) {
			$assetName = 'com_sportsmanagement';
		}
		else {
			$assetName = 'com_sportsmanagement.message.'.(int) $messageId;
		}
 
		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.delete'
		);
 
		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}
 
		return $result;
	}
    
    /**
	 * 
	 * @param string $data
	 * @param string $file
	 * @return object
	 */
	static function getExtendedStatistic($data='', $file, $format='ini') 
    {
        $mainframe = JFactory::getApplication();
		//$xmlfile = JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'extended'.DS.$file.'.xml';
		$templatepath = JPATH_COMPONENT_ADMINISTRATOR.DS.'statistics';
        $xmlfile = $templatepath.DS.$file.'.xml';
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementHelper data<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        //$mainframe->enqueueMessage(JText::_('sportsmanagementHelper getExtendedStatistic<br><pre>'.print_r($jRegistry,true).'</pre>'),'Notice');
        
		$extended = JForm::getInstance('params', $xmlfile,
				array('control'=> 'params'),
				false, '/config');
		$extended->bind($data);
		return $extended;
	}
    
  
  	/**
	 * support for extensions which can overload extended data
	 * @param string $data
	 * @param string $file
	 * @return object
	 */
	static function getExtended($data='', $file, $format='ini') 
    {
        $mainframe = JFactory::getApplication();
		$xmlfile = JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'extended'.DS.$file.'.xml';
		
    /*
    //extension management
		$extensions = sportsmanagementHelper::getExtensions(JRequest::getInt('p'));
		foreach ($extensions as $e => $extension) {
			$JLGPATH_EXTENSION = JPATH_COMPONENT_SITE.DS.'extensions'.DS.$extension.DS.'admin';
			//General extension extended xml 
			$file = $JLGPATH_EXTENSION.DS.'assets'.DS.'extended'.DS.$file.'.xml';
			if(file_exists(JPath::clean($file))) {
				$xmlfile = $file;
				break; //first extension file will win
			}
		}
		*/
		
		/*
		 * extended data
		*/
		
        $jRegistry = new JRegistry;
		//$jRegistry->loadString($data, $format);
        $jRegistry->loadString($data);
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementHelper data<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        //$mainframe->enqueueMessage(JText::_('sportsmanagementHelper getExtended<br><pre>'.print_r($jRegistry,true).'</pre>'),'Notice');
        
		$extended = JForm::getInstance('extended', $xmlfile,
				array('control'=> 'extended'),
				false, '/config');
		$extended->bind($jRegistry);
		return $extended;
	}
    
    
    /**
     * sportsmanagementHelper::getExtendedUser()
     * 
     * @param string $data
     * @param mixed $file
     * @param string $format
     * @return
     */
    static function getExtendedUser($data='', $file, $format='ini') 
    {
        $mainframe = JFactory::getApplication();
		$xmlfile = JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'extendeduser'.DS.$file.'.xml';
		
    /*
    //extension management
		$extensions = sportsmanagementHelper::getExtensions(JRequest::getInt('p'));
		foreach ($extensions as $e => $extension) {
			$JLGPATH_EXTENSION = JPATH_COMPONENT_SITE.DS.'extensions'.DS.$extension.DS.'admin';
			//General extension extended xml 
			$file = $JLGPATH_EXTENSION.DS.'assets'.DS.'extended'.DS.$file.'.xml';
			if(file_exists(JPath::clean($file))) {
				$xmlfile = $file;
				break; //first extension file will win
			}
		}
		*/
		
		/*
		 * extended data
		*/
		
        if (JFile::exists($xmlfile))
        {
        $jRegistry = new JRegistry;
		//$jRegistry->loadString($data, $format);
        $jRegistry->loadString($data);
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementHelper data<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        //$mainframe->enqueueMessage(JText::_('sportsmanagementHelper getExtended<br><pre>'.print_r($jRegistry,true).'</pre>'),'Notice');
        
		$extended = JForm::getInstance('extendeduser', $xmlfile,
				array('control'=> 'extendeduser'),
				false, '/config');
		$extended->bind($jRegistry);
		return $extended;
        }
        else
        {
            return false;
        }
	}
    
	/**
	 * Method to return a project array (id,name)
	 *
	 * @access	public
	 * @return	array project
	 * @since	1.5
	 */
	function getProjects()
	{
		$db = JFactory::getDBO();

		$query='	SELECT	id,
							name

					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project
					ORDER BY ordering, name ASC';

		$db->setQuery($query);

		if (!$result=$db->loadObjectList())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * Method to return the project teams array (id,name)
	 *
	 * @access	public
	 * @return	array
	 * @since	0.1
	 */
	function getProjectteams($project_id)
	{
		$db = JFactory::getDBO();
		$query='	SELECT	pt.id AS value,
							t.name AS text,
							t.notes

					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id=t.id
					WHERE pt.project_id='.$project_id.'
					ORDER BY name ASC ';

		$db->setQuery($query);
		if (!$result=$db->loadObjectList())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * Method to return the project teams array (id,name)
	 *
	 * @access	public
	 * @return	array
	 * @since	1.5.03a
	 */
	function getProjectteamsNew($project_id)
	{
		$db = JFactory::getDBO();

		$query='	SELECT	pt.team_id AS value,
							t.name AS text,
							t.notes

					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id=t.id
					WHERE pt.project_id='.(int) $project_id.'
					ORDER BY name ASC ';

		$db->setQuery($query);
		if (!$result=$db->loadObjectList())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}

	function getProjectFavTeams($project_id)
	{
		$db = JFactory::getDBO();

		$query='	SELECT fav_team,
							fav_team_color,
							fav_team_text_color,
							fav_team_highlight_type,
							fav_team_text_bold

					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project
					WHERE id='.(int) $project_id;

		$db->setQuery($query);
		if (!$result=$db->loadObject())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}

	
    /**
	 * Method to return the project
	 *
	 * @access	public
	 * @return	array project
	 * @since	1.5
	 */
	function getTeamplayerProject($projectteam_id)
	{
		$db = JFactory::getDBO();
		$query='SELECT project_id FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team WHERE id='.(int) $projectteam_id;
		$db->setQuery($query);
		if (!$result=$db->loadResult())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		return $result;
	}
    
    /**
	 * Method to return a SportsType name
	 *
	 * @access	public
	 * @return	array project
	 * @since	1.5
	 */
	function getSportsTypeName($sportsType)
	{
		$db = JFactory::getDBO();
		$query='SELECT name FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type WHERE id='.(int) $sportsType;
		$db->setQuery($query);
		if (!$result=$db->loadResult())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		return JText::_($result);
	}

	/**
	 * Method to return a sportsTypees array (id,name)
	 *
	 * @access	public
	 * @return	array seasons
	 * @since	1.5.0a
	 */
	function getSportsTypes()
	{
		$db = JFactory::getDBO();
		$query='SELECT id, name FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type ORDER BY name ASC ';
		$db->setQuery($query);
		if (!$result=$db->loadObjectList())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		foreach ($result as $sportstype){
			$sportstype->name=JText::_($sportstype->name);
		}
		return $result;
	}

	/**
	 * Method to return a SportsType name
	 *
	 * @access	public
	 * @return	array project
	 * @since	1.5
	 */
	function getPosPersonTypeName($personType)
	{
		switch ($personType)
		{
			case 2	:	$result =	JText::_('COM_SPORTSMANAGEMENT_F_TEAM_STAFF');
			break;
			case 3	:	$result =	JText::_('COM_SPORTSMANAGEMENT_F_REFEREES');
			break;
			case 4	:	$result =	JText::_('COM_SPORTSMANAGEMENT_F_CLUB_STAFF');
			break;
			default	:
			case 1	:	$result =	JText::_('COM_SPORTSMANAGEMENT_F_PLAYERS');
			break;
		}
		return $result;
	}

	/**
	 * return name of extension assigned to current project.
	 * @param int project_id
	 * @return string or false
	 */
	function getExtension($project_id=0)
	{
		$option='com_sportsmanagement';
		if (!$project_id)
		{
			$app=&JFactory::getApplication();
			$project_id=$app->getUserState($option.'project',0);
		}
		if (!$project_id){
			return false;
		}

		$db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('extension');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project');
        $query->where('id ='. $db->Quote((int)$project_id) );
        
//		$query='SELECT extension FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project WHERE id='. $db->Quote((int)$project_id);
		$db->setQuery($query);
		$res = $db->loadResult();

		return (!empty($res) ? $res : false);
	}

	/**
	 * sportsmanagementHelper::getExtensions()
	 * 
	 * @return
	 */
	public static function getExtensions()
	{
		$option='com_sportsmanagement';
		$arrExtensions = array();
		$excludeExtension = array();
		
		if(JFolder::exists(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'extensions')) {
			$folderExtensions  = JFolder::folders(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'extensions',
													'.', false, false, $excludeExtension);
			if($folderExtensions !== false) {
				foreach ($folderExtensions as $ext)
				{
					$arrExtensions[] = $ext;
				}
			}
		}

		return $arrExtensions;
	}
    
//	public static function getExtensions($project_id)
//	{
//		$option='com_sportsmanagement';
//		$arrExtensions = array();
//		$excludeExtension = array();
//		if ($project_id) {
//			$db= JFactory::getDBO();
//			$query='SELECT extension FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project WHERE id='. $db->Quote((int)$project_id);
//
//			$db->setQuery($query);
//			$res=$db->loadObject();
//			if(!empty($res)) {
//				$excludeExtension = explode(",", $res->extension);
//			}
//		}
//		if(JFolder::exists(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'extensions')) {
//			$folderExtensions  = JFolder::folders(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'extensions',
//													'.', false, false, $excludeExtension);
//			if($folderExtensions !== false) {
//				foreach ($folderExtensions as $ext)
//				{
//					$arrExtensions[] = $ext;
//				}
//			}
//		}
//
//		return $arrExtensions;
//	}
	
		public static function getExtensionsOverlay($project_id)
	{
		$option='com_sportsmanagement';
		$arrExtensions = array();
		$excludeExtension = array();
		if ($project_id) {
			$db= JFactory::getDBO();
			$query='SELECT extension FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project WHERE id='. $db->Quote((int)$project_id);

			$db->setQuery($query);
			$res=$db->loadObject();
			if(!empty($res)) {
				$excludeExtension = explode(",", $res->extension);
			}
		}
		if(JFolder::exists(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'extensions-overlay')) {
			$folderExtensions  = JFolder::folders(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'extensions-overlay',
													'.', false, false, $excludeExtension);
			if($folderExtensions !== false) {
				foreach ($folderExtensions as $ext)
				{
					$arrExtensions[] = $ext;
				}
			}
		}

		return $arrExtensions;
	}

	/**
	 * returns number of years between 2 dates
	 *
	 * @param string $birthday date in YYYY-mm-dd format
	 * @param string $current_date date in YYYY-mm-dd format,default to today
	 * @return int age
	 */
	function getAge($date, $seconddate)
	{

		if ( ($date != "0000-00-00") &&
		(preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/',$date,$regs) ) &&
		($seconddate == "0000-00-00") )
		{
			$intAge=date('Y') - $regs[1];
			if($regs[2] > date('m'))
			{
				$intAge--;
			}
			else
			{
				if($regs[2] == date('m'))
				{
					if($regs[3] > date('d')) $intAge--;
				}
			}
			return $intAge;
		}

		if ( ($date != "0000-00-00") &&
		( preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/',$date,$regs) ) &&
		($seconddate != "0000-00-00") &&
		( preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})/',$seconddate,$regs2) ) )
		{
			$intAge=$regs2[1] - $regs[1];
			if($regs[2] > $regs2[2])
			{
				$intAge--;
			}
			else
			{
				if($regs[2] == $regs2[2])
				{
					if($regs[3] > $regs2[3] ) $intAge--;
				}
			}
			return $intAge;
		}

		return '-';
	}

	/**
	 * returns the default placeholder
	 *
	 * @param string $type ,default is player
	 * @return string placeholder (path)
	 */
	public static function getDefaultPlaceholder($type="player")
	{	
		$params		 	=	JComponentHelper::getParams('com_sportsmanagement');
		$ph_player		=	$params->get('ph_player',0);
		$ph_logo_big	=	$params->get('ph_logo_big',0);	
		$ph_logo_medium	=	$params->get('ph_logo_medium',0);		
		$ph_logo_small	=	$params->get('ph_logo_small',0);
		$ph_icon		=	$params->get('ph_icon', 'images/com_sportsmanagement/database/placeholders/placeholder_21.png');			
		$ph_team		=	$params->get('ph_team',0);
		
			//setup the different placeholders
			switch ($type) {
				case "player": //player
					return $ph_player;
					break;
				case "clublogobig": //club logo big
					return $ph_logo_big;
					break;
				case "clublogomedium": //club logo medium
					return $ph_logo_medium;
					break;
				case "clublogosmall": //club logo small
					return $ph_logo_small;
					break;
				case "icon": //icon
					return $ph_icon;
					break;
				case "team": //team picture
					return $ph_team;;
					break;					
				default:
					$picture=null;
				break;
			}	
	}
	
	/**
	 *
	 * static method which return a <img> tag with the given picture
	 * @param string $picture
	 * @param string $alttext
	 * @param int $width=40, if set to 0 the original picture width will be used
	 * @param int $height=40, if set to 0 the original picture height will be used
	 * @param int $type=0, 0=player, 1=club logo big, 2=club logo medium, 3=club logo small
	 * @return string
	 */
	public static function getPictureThumb($picture, $alttext, $width=40, $height=40, $type=0)
	{
		$ret = "";
		$picturepath 	= 	JPath::clean(JPATH_SITE.DS.str_replace(JPATH_SITE.DS, '', $picture));
		$params		 	=	JComponentHelper::getParams('com_sportsmanagement');
		$ph_player		=	$params->get('ph_player',0);
		$ph_logo_big	=	$params->get('ph_logo_big',0);	
		$ph_logo_medium	=	$params->get('ph_logo_medium',0);		
		$ph_logo_small	=	$params->get('ph_logo_small',0);
		$ph_icon		=	$params->get('ph_icon',0);			
		$ph_team		=	$params->get('ph_team',0);
		
		if (!file_exists($picturepath) || $picturepath == JPATH_SITE.DS)
		{
			//setup the different placeholders
			switch ($type) {
				case 0: //player
					$picture=JPATH_SITE.DS.$ph_player;
					break;
				case 1: //club logo big
					$picture=JPATH_SITE.DS.$ph_logo_big;
					break;
				case 2: //club logo medium
					$picture=JPATH_SITE.DS.$ph_logo_medium;
					break;
				case 3: //club logo small
					$picture=JPATH_SITE.DS.$ph_logo_small;
					break;
				case 4: //icon
					$picture=JPATH_SITE.DS.$ph_icon;
					break;
				case 5: //team picture
					$picture=JPATH_SITE.DS.$ph_team;
					break;					
				default:
					$picture=null;
				break;
			}
		}
		if (!empty($picture))
		{
			$params = JComponentHelper::getParams('com_sportsmanagement');
			$format = "JPG"; //PNG is not working in IE8
			$format = $params->get('thumbformat', 'PNG');
			$bUseThumbLib = $params->get('usethumblib', false);
			if($bUseThumbLib) {
				if (file_exists($picturepath)) {
					$picture = $picturepath;
				}
				$thumb=PhpThumbFactory::create($picture);
				$thumb->setFormat($format);

				//height and width set, resize it with the thumblib
				if($height>0 && $width>0) {
					$thumb->setMaxHeight($height);
					$thumb->adaptiveResizeQuadrant ($width, $height, $quadrant = 'C');
					$pic=$thumb->getImageAsString();
					$ret .= '<img src="data:image/'.$format.';base64,'. base64_encode($pic);
					$ret .='" alt="'.$alttext.'" title="'.$alttext.'"/>';
				}
				//height==0 and width set, let the browser resize it
				if($height==0 && $width>0) {
					$thumb->setMaxWidth($width);
					$pic=$thumb->getImageAsString();
					$ret .= '<img src="data:image/'.$format.';base64,'. base64_encode($pic);
					$ret .='" width="'.$width.'" alt="'.$alttext.'" title="'.$alttext.'"/>';
				}
				//width==0 and height set, let the browser resize it
				if($height>0 && $width==0) {
					$thumb->setMaxHeight($height);
					$pic=$thumb->getImageAsString();
					$ret .= '<img src="data:image/'.$format.';base64,'. base64_encode($pic);
					$ret .='" height="'.$height.'" alt="'.$alttext.'" title="'.$alttext.'"/>';
				}
				//width==0 and height==0, use original picture size
				if($height==0 && $width==0) {
					$thumb->setMaxHeight($height);
					$pic=$thumb->getImageAsString();
					$ret .= '<img src="data:image/'.$format.';base64,'. base64_encode($pic);
					$ret .='" alt="'.$alttext.'" title="'.$alttext.'"/>';
				}
			} else {
				$picture = JUri::root(true).'/'.str_replace(JPATH_SITE.DS, "", $picture);
				$title = $alttext;
				//height and width set, let the browser resize it
				$bUseHighslide = $params->get('use_highslide', false);
				if($bUseHighslide && $type != 4) {
					$title .= ' (' . JText::_('COM_SPORTSMANAGEMENT_GLOBAL_CLICK_TO_ENLARGE') . ')';
					$ret .= '<a href="'.$picture.'" class="highslide">';
				}
				$ret .= '<img ';
				$ret .= ' ';
				if($height>0 && $width>0) {
					$ret .= ' src="'.$picture;
					$ret .='" width="'.$width.'" height="'.$height.'"
							alt="'.$alttext.'" title="'.$title.'"';
				}
				//height==0 and width set, let the browser resize it
				if($height==0 && $width>0) {
					$ret .= ' src="'.$picture;
					$ret .='" width="'.$width.'" alt="'.$alttext.'" title="'.$title.'"';
				}
				//width==0 and height set, let the browser resize it
				if($height>0 && $width==0) {
					$ret .= ' src="'.$picture;
					$ret .='" height="'.$height.'" alt="'.$alttext.'" title="'.$title.'"';
				}
				//width==0 and height==0, use original picture size
				if($height==0 && $width==0) {
					$ret .= ' src="'.$picture;
					$ret .='" alt="'.$alttext.'" title="'.$title.'"';
				}
				$ret .= '/>';
				if($bUseHighslide) {
					$ret .= '</a>';
				}
			}
				
		}
			
		return $ret;
	}

	/**
	 * static method which extends template path for given view names
	 * Can be used by views to search for extensions that implement parts of common views
	 * and add their path to the template search path.
	 * (e.g. 'projectheading', 'backbutton', 'footer')
	 * @param array(string) $viewnames, names of views for which templates need to be loaded,
	 *                      so that extensions are used when available
	 * @param JLGView       $view to which the template paths should be added
	 */
	public static function addTemplatePaths($templatesToLoad, &$view)
	{
		$extensions = sportsmanagementHelper::getExtensions(JRequest::getInt('p'));
		foreach ($templatesToLoad as $template)
		{
			$view->addTemplatePath(JPATH_COMPONENT . DS . 'views' . DS . $template . DS . 'tmpl');
			if (is_array($extensions) && count($extensions) > 0)
			{
				foreach ($extensions as $e => $extension) 
				{
					$extension_views = JPATH_COMPONENT_SITE . DS . 'extensions' . DS . $extension . DS . 'views';
					$tmpl_path = $extension_views . DS . $template . DS . 'tmpl';
					if (JFolder::exists($tmpl_path))
					{
						$view->addTemplatePath($tmpl_path);
					}
				}
			}
		}
	}

	/**
	 * get unix timestamp of specified date
	 *
	 * @param string $date now if null
	 * @param boolean $use_offset use time offset
	 * @param int $project_serveroffset custom time offset in hours. Use default joomla timeoffset if null
	 */
	function getTimestamp($date = null, $use_offset = 0, $offset = null)
	{
		$date = $date ? $date : 'now';
		$app = Jfactory::getApplication();
		$res = JFactory::getDate(strtotime($date));

		if ($use_offset)
		{
			if ($offset)
			{
				$serveroffset=explode(':', $offset);
				$res->setOffset($serveroffset[0]);
			}
			else
			{
				$res->setOffset($app->getCfg('offset'));
			}
		}
		return $res->toUnix('true');
	}

	/**
	 * Method to convert a date from 0000-00-00 to 00-00-0000 or back
	 * return a date string
	 * $direction == 1 means from convert from 0000-00-00 to 00-00-0000
	 * $direction != 1 means from convert from 00-00-0000 to 0000-00-00
	 * call by sportsmanagementHelper::convertDate($date) inside the script
	 *
	 * When no "-" are given in $date two short date formats (DDMMYYYY and DDMMYY) are supported
	 * for example "31122011" or "311211" for 31 december 2011
	 * 
	 * @access	public
	 * @return	array
	 *
	 */
	static function convertDate($DummyDate,$direction=1)
	{
		if(!strpos($DummyDate,"-")!==false)
		{
			// for example 31122011 is used for 31 december 2011
			if (strlen($DummyDate) == 8 )
			{
				$result  = substr($DummyDate,4,4);
				$result .= '-';
				$result .= substr($DummyDate,2,2);
				$result .= '-';
				$result .= substr($DummyDate,0,2);
			}
			// for example 311211 is used for 31 december 2011
			elseif (strlen($DummyDate) == 6 )
 			{
 				$result  = substr(date("Y"),0,2);
				$result .= substr($DummyDate,4,4);
				$result .= '-';
				$result .= substr($DummyDate,2,2);
				$result .= '-';
				$result .= substr($DummyDate,0,2);
			}
		}
		else
		{

			if ($direction == 1)
			{
				$result  = substr($DummyDate,8);
				$result .= '-';
				$result .= substr($DummyDate,5,2);
				$result .= '-';
				$result .= substr($DummyDate,0,4);
			}
			else
			{
				$result  = substr($DummyDate,6,4);
				$result .= '-';
				$result .= substr($DummyDate,3,2);
				$result .= '-';
				$result .= substr($DummyDate,0,2);
			}
		}

		return $result;
	}

	/**
	 * sportsmanagementHelper::showTeamIcons()
	 * 
	 * @param mixed $team
	 * @param mixed $config
	 * @return
	 */
	function showTeamIcons(&$team,&$config)
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($team,true).'</pre>'),'');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
            {
                $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.'<br><pre>'.print_r($team,true).'</pre>'),'');
            }
            
        if(!isset($team->projectteamid)) return "";
		$projectteamid = $team->projectteamid;
		$teamname      = $team->name;
		$teamid        = $team->team_id;
		$teamSlug      = (isset($team->team_slug) ? $team->team_slug : $teamid);
		$clubSlug      = (isset($team->club_slug) ? $team->club_slug : $team->club_id);
		$division_slug = (isset($team->division_slug) ? $team->division_slug : $team->division_id);
		$projectSlug   = (isset($team->project_slug) ? $team->project_slug : $team->project_id);
		$output        = '';

		if ($config['show_team_link'])
		{
			$link = sportsmanagementHelperRoute::getPlayersRoute($projectSlug,$teamSlug,NULL,$projectteamid);
			$title = JText::_('COM_SPORTSMANAGEMENT_TEAMICONS_ROSTER_LINK').'&nbsp;'.$teamname;
			$picture = 'media/com_sportsmanagement/jl_images/team_icon.png';
			$desc = self::getPictureThumb($picture, $title, 0, 0, 4);
			$output .= JHtml::link($link,$desc);
		}

		if (((!isset($team_plan)) || ($teamid!=$team_plan->id)) && ($config['show_plan_link']))
		{
			$link =sportsmanagementHelperRoute::getTeamPlanRoute($projectSlug,$teamSlug,$division_slug,NULL,$projectteamid);
			$title=JText::_('COM_SPORTSMANAGEMENT_TEAMICONS_TEAMPLAN_LINK').'&nbsp;'.$teamname;
			$picture = 'media/com_sportsmanagement/jl_images/calendar_icon.gif';
			$desc = self::getPictureThumb($picture, $title, 0, 0, 4);
			$output .= JHtml::link($link,$desc);
		}

		if ($config['show_curve_link'])
		{
			$link =sportsmanagementHelperRoute::getCurveRoute($projectSlug,$teamSlug,0,$division_slug);
			$title=JText::_('COM_SPORTSMANAGEMENT_TEAMICONS_CURVE_LINK').'&nbsp;'.$teamname;
			$picture = 'media/com_sportsmanagement/jl_images/curve_icon.gif';
			$desc = self::getPictureThumb($picture, $title, 0, 0, 4);
			$output .= JHtml::link($link,$desc);
		}

		if ($config['show_teaminfo_link'])
		{
//    		$link =JoomleagueHelperRoute::getProjectTeamInfoRoute($projectSlug,$projectteamid);
			$link = sportsmanagementHelperRoute::getTeamInfoRoute($projectSlug,$teamSlug,$projectteamid);
//            $link = sportsmanagementHelperRoute::getTeamInfoRoute($projectSlug,$projectteamid);
            $title = JText::_('COM_SPORTSMANAGEMENT_TEAMICONS_TEAMINFO_LINK').'&nbsp;'.$teamname;
			$picture = 'media/com_sportsmanagement/jl_images/teaminfo_icon.png';
			$desc = self::getPictureThumb($picture, $title, 0, 0, 4);
			$output .= JHtml::link($link,$desc);
		}

		if ($config['show_club_link'])
		{
			$link =sportsmanagementHelperRoute::getClubInfoRoute($projectSlug,$clubSlug);
			$title=JText::_('COM_SPORTSMANAGEMENT_TEAMICONS_CLUBINFO_LINK').'&nbsp;'.$teamname;
			$picture = 'media/com_sportsmanagement/jl_images/mail.gif';
			$desc = self::getPictureThumb($picture, $title, 0, 0, 4);
			$output .= JHtml::link($link,$desc);
		}

		if ($config['show_teamstats_link'])
		{
			$link =sportsmanagementHelperRoute::getTeamStatsRoute($projectSlug,$teamSlug);
			$title=JText::_('COM_SPORTSMANAGEMENT_TEAMICONS_TEAMSTATS_LINK').'&nbsp;'.$teamname;
			$picture = 'media/com_sportsmanagement/jl_images/teamstats_icon.png';
			$desc = self::getPictureThumb($picture, $title, 0, 0, 4);
			$output .= JHtml::link($link,$desc);
		}

		if ($config['show_clubplan_link'])
		{
			$link =sportsmanagementHelperRoute::getClubPlanRoute($projectSlug,$clubSlug);
			$title=JText::_('COM_SPORTSMANAGEMENT_TEAMICONS_CLUBPLAN_LINK').'&nbsp;'.$teamname;
			$picture = 'media/com_sportsmanagement/jl_images/clubplan_icon.png';
			$desc = self::getPictureThumb($picture, $title, 0, 0, 4);
			$output .= JHtml::link($link,$desc);
		}

		return $output;
	}

	/**
	 * sportsmanagementHelper::formatTeamName()
	 * 
	 * @param mixed $team
	 * @param mixed $containerprefix
	 * @param mixed $config
	 * @param integer $isfav
	 * @param mixed $link
	 * @return
	 */
	function formatTeamName($team,$containerprefix,&$config,$isfav=0,$link=null)
	{
		$output			= '';
		$desc			= '';

		if ((isset($config['results_below'])) && ($config['results_below']) && ($config['show_logo_small']))
		{
			$js_func		= 'visibleMenu';
			$style_append	= 'visibility:hidden';
			$container		= 'span';
		}
		else
		{
			$js_func		= 'switchMenu';
			$style_append	= 'display:none';
			$container		= 'div';
		}
		
		$showIcons=	(
						($config['show_info_link']==2) && ($isfav)
					) ||
					(
						($config['show_info_link']==1) &&
						(
							$config['show_club_link'] ||
							$config['show_team_link'] ||
							$config['show_curve_link'] ||
							$config['show_plan_link'] ||
							$config['show_teaminfo_link'] ||
							$config['show_teamstats_link'] ||
							$config['show_clubplan_link']
						)
					);
		$containerId = $containerprefix.'t'.$team->id.'p'.$team->project_id;
		if ($showIcons)
		{
			$onclick	= $js_func.'(\''.$containerId.'\');return false;';
			$params		= array('onclick' => $onclick);
		}

		$style = 'padding:2px;';
		if ($config['highlight_fav'] && $isfav)
		{
			$favs = self::getProjectFavTeams($team->project_id);
			$style .= ($favs->fav_team_text_bold != '') ? 'font-weight:bold;' : '';
			$style .= (trim($favs->fav_team_text_color) != '') ? 'color:'.trim($favs->fav_team_text_color).';' : '';
			$style .= (trim($favs->fav_team_color) != '') ? 'background-color:'.trim($favs->fav_team_color).';' : '';
		}

		$desc .= '<span style="'.$style.'">';
		
		$formattedTeamName = "";
		if ($config['team_name_format']== 0)
		{
			$formattedTeamName = $team->short_name;
		}
		else if ($config['team_name_format']== 1)
		{
			$formattedTeamName = $team->middle_name;
		}
		if (empty($formattedTeamName))
		{
			$formattedTeamName = $team->name;
		}

		if (($config['team_name_format']== 0) && (!empty($team->short_name))) 
		{
			$desc .=  '<acronym title="'.$team->name.'">'.$team->short_name.'</acronym>';
		}
		else
		{
			$desc .= $formattedTeamName;
		}

		$desc .=  '</span>';

		if ($showIcons)
		{
			$output .= JHtml::link('javascript:void(0);',$desc,$params);
            //$output .= '<ul id="submenu"><li><a id="'.$containerId.'" >'.$formattedTeamName.'</a></li></ul>';
			$output .= '<'.$container.' id="page-'.$containerId.'" style="'.$style_append.';" class="rankingteam">';
            //$output .= '<div id="config-document">';
            //$output .= '<'.$container.' id="page-'.$containerId.'" >';
			$output .= self::showTeamIcons ($team,$config);
			$output .= '</'.$container.'>';
            //$output .= '</div>';
		}
		else
		{
			$output = $desc;
		}
		
		if ($link != null)
		{
			$output = JHtml::link($link, $output);
		}

		return $output;
	}

	/**
	 * sportsmanagementHelper::showClubIcon()
	 * 
	 * @param mixed $team
	 * @param integer $type
	 * @param integer $with_space
	 * @return void
	 */
	function showClubIcon(&$team,$type=1,$with_space=0)
	{
		if (($type==1) && (isset($team->country)))
		{
			if ($team->logo_small!='')
			{
				echo JHtml::image($team->logo_small,'');
				if ($with_space==1){
					echo ' style="padding:1px;"';
				}
			}
			else
			{
				echo '&nbsp;';
			}
		}
		elseif (($type==2) && (isset($team->country)))
		{
			echo JSMCountries::getCountryFlag($team->country);
		}
	}

	/**
	 * sportsmanagementHelper::showColorsLegend()
	 * 
	 * @param mixed $colors
	 * @return void
	 */
	function showColorsLegend($colors)
	{
		$favshow=JRequest::getVar('func','');
		if (($favshow!='showCurve') && ($this->project->fav_team))
		{
			$fav=array('color'=>$this->project->fav_team_color,'description'=> JText::_('COM_SPORTSMANAGEMENT_RANKING_FAVTEAM'));
			array_push($colors,$fav);
		}
		foreach($colors as $color)
		{
			if (trim($color['description'])!='')
			{
				echo '<td align="center" style="background-color:'.$color['color'].';"><b>'.$color['description'].'</b>&nbsp;</td>';
			}

		}
	}


	/**
	 * Removes invalid XML
	 *
	 * @access public
	 * @param string $value
	 * @return string
	 */
	public function stripInvalidXml($value)
	{
		$ret='';
		$current='';
		if (is_null($value)){
			return $ret;
		}

		$length=strlen($value);
		for ($i=0; $i < $length; $i++)
		{
			$current=ord($value{$i});
			if (($current == 0x9) ||
			($current == 0xA) ||
			($current == 0xD) ||
			(($current >= 0x20) && ($current <= 0xD7FF)) ||
			(($current >= 0xE000) && ($current <= 0xFFFD)) ||
			(($current >= 0x10000) && ($current <= 0x10FFFF)))
			{
				$ret .= chr($current);
			}
			else
			{
				$ret .= ' ';
			}
		}
		return $ret;
	}

	public function getVersion() 
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       $db = JFactory::getDBO();
	   $db->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = "com_sportsmanagement"');
       $manifest_cache = json_decode( $db->loadResult(), true );
	   //$mainframe->enqueueMessage(JText::_('manifest_cache<br><pre>'.print_r($manifest_cache,true).'</pre>'   ),'');
       return $manifest_cache['version'];	
	}



	/**
	 * returns formatName
	 *
	 * @param prefix
	 * @param firstName
	 * @param nickName
	 * @param lastName
	 * @param format
	 */
	static function formatName($prefix, $firstName, $nickName, $lastName, $format)
	{
		$name = array();
		if ($prefix)
		{
			$name[] = $prefix;
		}
		switch ($format)
		{
			case 0: //Firstname 'Nickname' Lastname
				if ($firstName != "") {
					$name[] = $firstName;
				}
				if ($nickName != "") {
					$name[] = "'" . $nickName . "'";
				}
				if ($lastName != "") {
					$name[] = $lastName;
				}
				break;
			case 1: //Lastname, 'Nickname' Firstname
				if ($lastName != "") {
					$name[] = $lastName . ",";
				}
				if ($nickName != "") {
					$name[] = "'" . $nickName . "'";
				}
				if ($firstName != "") {
					$name[] = $firstName;
				}
				break;
			case 2: //Lastname, Firstname 'Nickname'
				if ($lastName != "") {
					$name[] = $lastName . ",";
				}
				if ($firstName != "") {
					$name[] = $firstName;
				}
				if ($nickName != "") {
					$name[] = "'" . $nickName . "'";
				}
				break;
			case 3: //Firstname Lastname
				if ($firstName != "") {
					$name[] = $firstName;
				}
				if ($lastName != "") {
					$name[] = $lastName;
				}
				break;
			case 4: //Lastname, Firstname
				if ($lastName != "") {
					$name[] = $lastName . ",";
				}
				if ($firstName != "") {
					$name[] = $firstName;
				}
				break;
			case 5: //'Nickname' - Firstname Lastname
				if ($nickName != "") {
					$name[] = "'" . $nickName . "' - ";
				}
				if ($firstName != "") {
					$name[] = $firstName;
				}
				if ($lastName != "") {
					$name[] = $lastName;
				}
				break;
			case 6: //'Nickname' - Lastname, Firstname
				if ($nickName != "") {
					$name[] = "'" . $nickName . "' - ";
				}
				if ($lastName != "") {
					$name[] = $lastName . ",";
				}
				if ($firstName != "") {
					$name[] = $firstName;
				}
				break;
			case 7: //Firstname Lastname (Nickname)
				if ($firstName != "") {
					$name[] = $firstName;
				}
				if ($lastName != "") {
					$name[] = $lastName ;
				}
				if ($nickName != "") {
					$name[] = "(" . $nickName . ")";
				}
				break;
			case 8: //F. Lastname
				if ($firstName != "") {
					$name[] = $firstName[0] . ".";
				}
				if ($lastName != "") {
					$name[] = $lastName;
				}
				break;
			case 9: //Lastname, F.
				if ($lastName != "") {
					$name[] = $lastName.",";
				}
				if ($firstName != "") {
					$name[] = $firstName[0] . ".";
				}
				break;
			case 10: //Lastname
				if ($lastName != "") {
					$name[] = $lastName;
				}
				break;
			case 11: //Firstname 'Nickname' L.
				if ($firstName != "") {
					$name[] = $firstName;
				}
				if ($nickName != "") {
					$name[] = "'" . $nickName . "'";
				}
				if ($lastName != "") {
					$name[] = $lastName[0]. ".";
				}
				break;
			case 12: //Nickname
				if ($nickName != "") {
					$name[] = $nickName;
				}
				break;
			case 13: //Firstname L.
				if ($firstName != "") {
					$name[] = $firstName;
				}
				if ($lastName != "") {
					$name[] = $lastName[0]. ".";
				}
				break;
			case 14: //Lastname Firstname
				if ($lastName != "") {
					$name[] = $lastName;
				}
				if ($firstName != "") {
					$name[] = $firstName;
				}
				break;
			case 15: //Lastname newline Firstname
				if ($lastName != "") {
					$name[] = $lastName;
					$name[] = '<br \>';
				}
				if ($firstName != "") {
					$name[] = $firstName;
				}
				break;
			case 16: //Firstname newline Lastname
				if ($lastName != "") {
					$name[] = $lastName;
					$name[] = '<br \>';
				}
				if ($firstName != "") {
					$name[] = $firstName;
				}
				break;
		}

		return implode(" ", $name);
	}

	/**
	 * Creates the print button
	 *
	 * @param string $print_link
	 * @param array $config
	 * @since 1.5.2
	 */
	public static function printbutton($print_link, &$config)
	{
		if ($config['show_print_button'] == 1) {
			JHtml::_('behavior.tooltip');
			$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=800,height=600,directories=no,location=no';
			// checks template image directory for image, if non found default are loaded
			if ($config['show_icons'] == 1 ) {
				$image = JHtml::_('image.site', 'printButton.png', 'media/com_sportsmanagement/jl_images/', NULL, NULL, JText::_( 'Print' ));
			} else {
				$image = JText::_( 'Print' );
			}
			if (JRequest::getInt('pop')) {
				//button in popup
				$output = '<a href="javascript: void(0)" onclick="window.print();return false;">'.$image.'</a>';
			} else {
				//button in view
				$overlib = JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_PRINT_TIP' );
				$text = JText::_( 'COM_SPORTSMANAGEMENT_GLOBAL_PRINT' );
				$sef = JFactory::getConfig()->getValue('config.sef', false);
				$print_urlparams = ($sef ? "?tmpl=component&print=1" : "&tmpl=component&print=1");

				if(is_null($print_link)) {
				    $output	= '<a href="javascript: void(0)" class="editlinktip hasTip" onclick="window.open(window.location.href + \''.$print_urlparams.'\',\'win2\',\''.$status.'\'); return false;" rel="nofollow" title="'.$text.'::'.$overlib.'">'.$image.'</a>';
				} else {
				    $output	= '<a href="'. JRoute::_($print_link) .'" class="editlinktip hasTip" onclick="window.open(window.location.href + \''.$print_urlparams.'\',\'win2\',\''.$status.'\'); return false;" rel="nofollow" title="'.$text.'::'.$overlib.'">'.$image.'</a>';
				}
			}
			return $output;
		}
		return;
	}

	/**
	 * return true if mootools upgrade is enabled
	 *
	 * @return boolean
	 */
	function isMootools12()
	{
		$version = new JVersion();
		if ($version->RELEASE == '1.5' && $version->DEV_LEVEL >= 19 && JPluginHelper::isEnabled( 'system', 'mtupgrade' ) ) {
			return true;
		}
		else {
			return false;
		}
	}
    
    
    static function ToolbarButton($layout = Null,$icon_image = 'upload',$alt_text = 'My Label',$view = '',$type=0)
	{
	$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
    
    //$mainframe->enqueueMessage(JText::_('ToolbarButton layout<br><pre>'.print_r(JRequest::getVar('layout'),true).'</pre>'),'Notice');
    //$mainframe->enqueueMessage(JText::_('ToolbarButton get<br><pre>'.print_r($_GET,true).'</pre>'),'Notice');
    
    if ( !$view )
    {
    $view = JRequest::getVar( "view") ;
    }
    $modal_popup_width = JComponentHelper::getParams($option)->get('modal_popup_width',0) ;
    $modal_popup_height = JComponentHelper::getParams($option)->get('modal_popup_height',0) ;
    $bar = JToolBar::getInstance('toolbar');
    $page_url = JFilterOutput::ampReplace('index.php?option=com_sportsmanagement&view='.$view.'&tmpl=component&layout='.$layout.'&type='.$type );
    
    $bar->appendButton('Popup', $icon_image, $alt_text, $page_url, $modal_popup_width, $modal_popup_height);
    
    
    
    }
    
    
    static function ToolbarButtonOnlineHelp()
	{
	$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
	$document = JFactory::getDocument();
    $view = JRequest::getVar( "view") ;
    $layout= JRequest::getVar( "layout") ;
    $view = ucfirst(strtolower($view));
    $layout = ucfirst(strtolower($layout));
    $document->addScript(JUri::root(true).'/administrator/components/com_sportsmanagement/assets/js/sm_functions.js');
    $window_width = '<script>alert($(window).width()); </script>';
    $window_height = '<script>alert(window.screen.height); </script>';
    
    //$mainframe->enqueueMessage(JText::_('ToolbarButtonOnlineHelp width<br><pre>'.print_r($window_width,true).'</pre>'),'Notice');
    //$mainframe->enqueueMessage(JText::_('ToolbarButtonOnlineHelp width<br><pre>'.print_r($_SESSION,true).'</pre>'),'Notice');
    
    switch ($view)
    {
    case 'Template':
    case 'Predictiontemplate':
    $template_help = $mainframe->getUserState($option.'template_help');
    $view = $view.'_'.$template_help;
    break;
    default:
    break;
    }
    $cfg_help_server = JComponentHelper::getParams($option)->get('cfg_help_server','') ;
    $modal_popup_width = JComponentHelper::getParams($option)->get('modal_popup_width',0) ;
    $modal_popup_height = JComponentHelper::getParams($option)->get('modal_popup_height',0) ;
    $bar = JToolBar::getInstance('toolbar');
    
    if ( $layout )
    {
    $send = '<a class="modal" rel="{handler: \'iframe\', size: {x: '.$modal_popup_width.', y: '.$modal_popup_height.'}}" '.
         ' href="'.$cfg_help_server.'SM-Backend:'.$view.'-'.$layout   .'"><span title="send" class="icon-32-help"></span>'.JText::_('Onlinehilfe').'</a>';    
    }
    else
    {
    $send = '<a class="modal" rel="{handler: \'iframe\', size: {x: '.$modal_popup_width.', y: '.$modal_popup_height.'}}" '.
         ' href="'.$cfg_help_server.'SM-Backend:'.$view.'"><span title="send" class="icon-32-help"></span>'.JText::_('Onlinehilfe').'</a>';
	}
    /*
    $send = '<a class="modal" rel="{handler: \'iframe\', size: {x: '.'<script>width; </script>'.', y: '.$modal_popup_height.'}}" '.
         ' href="'.$cfg_help_server.'SM-Backend:'.$view.'"><span title="send" class="icon-32-help"></span>'.JText::_('Onlinehilfe').'</a>';	
    */
        
        // Add a help button.
		$bar->appendButton('Custom',$send);	
		//$bar->appendButton('Help', $ref, $com, $override, $component);
	}
    
		
	/**
	* return project rounds as array of objects(roundid as value, name as text)
	*
	* @param string $ordering
	* @return array
	*/
	function getRoundsOptions($project_id, $ordering='ASC', $required = false, $round_ids = NULL)
	{
		$mainframe = JFactory::getApplication();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('id as value');
        $query->select('name AS text');
        $query->select('id, name, round_date_first, round_date_last, roundcode');
        
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
        
        $query->where('project_id = '.$project_id);
        $query->where('published = 1');
        
        if ( $round_ids )
    {
    $query->where('id IN (' . implode(',', $round_ids) . ')');   
    }
        
        $query->order('roundcode '.$ordering);  
        
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' project_id'.'<pre>'.print_r($project_id,true).'</pre>' ),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' ordering'.'<pre>'.print_r($ordering,true).'</pre>' ),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' required'.'<pre>'.print_r($required,true).'</pre>' ),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' round_ids'.'<pre>'.print_r($round_ids,true).'</pre>' ),'');
//        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' '.'<pre>'.print_r($query->dump(),true).'</pre>' ),'');

		$db->setQuery($query);
		if(!$required) {
			$mitems = array(JHtml::_('select.option', '', JText::_('COM_SPORTSMANAGEMENT_GLOBAL_SELECT')));
			return array_merge($mitems, $db->loadObjectList());
		} else {
			return $db->loadObjectList();
		}
	}
	
	/**
	 * returns -1/0/1 if the team lost/drew/won in specified game, or false if not played/cancelled
	 *  
	 * @param object $game date from match table
	 * @param int $ptid project team id
	 * @return false|int
	 */
	function getTeamMatchResult($game, $ptid)
	{
		if (!isset($game->team1_result)) {
			return false;
		}
		if ($game->cancel) {
			return false;
		}
		
		if (!$game->alt_decision)
		{
			$result1 = $game->team1_result;
			$result2 = $game->team2_result;
		}
		else
		{
			$result1 = $game->team1_result_decision;
			$result2 = $game->team2_result_decision;
		}
		if ($result1 == $result2) {
			return 0;
		}
		
		if ($ptid == $game->projectteam1_id) {
			return ($result1 > $result2) ? 1 : -1;
		}
		else {
			return ($result1 > $result2) ? -1 : 1;
		}
	}
    



/**
 * sportsmanagementHelper::getExtraSelectOptions()
 * 
 * @param string $view
 * @param string $field
 * @param bool $template
 * @param integer $fieldtyp
 * @return
 */
function getExtraSelectOptions($view='', $field='', $template = FALSE, $fieldtyp = 0  )	
{
$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option'); 
        $select_columns = array();
        $select_values = array();
        $select_options = array();    
    // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($view,true).'</pre>'),'Notice');    
//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($field,true).'</pre>'),'Notice');

        // Select some fields
        if ( $template )
        {
        $query->select('select_columns,select_values');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_user_extra_fields');
        $query->where('template_backend LIKE '. $db->Quote(''.$view.'') );
        $query->where('name LIKE ' . $db->Quote(''.$field.'') );    
        }
        else
        {
        $query->select('select_columns,select_values');
		// From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_user_extra_fields');
        $query->where('views_backend LIKE '. $db->Quote(''.$view.'') );
        $query->where('views_backend_field LIKE ' . $db->Quote(''.$field.'') );    
        }
    
		
        $query->where('fieldtyp = ' . $fieldtyp );
        
        
        //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        
    $db->setQuery($query);
    $result = $db->loadObject();
    if ( $result)
		{
		  $select_columns = explode(",",$result->select_columns);
          //$select_values = explode(",",$result->select_values);
          
          if ( $result->select_values )
          {
          $select_values = explode(",",$result->select_values);  
          }
          else
          {
          $select_values = explode(",",$result->select_columns);  
          }
          //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($select_columns,true).'</pre>'),'Notice');
          
          foreach($select_columns as $key => $value )
          {
          $temp = new stdClass();
          $temp->value = $value;
          $temp->text = $select_values[$key];
          $select_options[] = $temp;  
          }
          
          return $select_options;
          
			
		}
		else
        {
//            JError::raiseError(0, $db->getErrorMsg());
            return false;
        }
}    
    
    
    /**
	 * Method to check extra fields
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
    static function checkUserExtraFields()
    {
         $mainframe	= JFactory::getApplication();
		$option = JRequest::getCmd('option');
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        
        $query->select('ef.id');
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_user_extra_fields as ef ');
        $query->where('ef.template_backend LIKE ' . $db->Quote(''.JRequest::getVar('view').'') );
    //$query="SELECT id FROM #__".COM_SPORTSMANAGEMENT_TABLE."_user_extra_fields WHERE template_backend LIKE '".JRequest::getVar('view')."' ";
			//echo '<pre>'.print_r($query,true).'</pre>';
			$db->setQuery($query);
			if ($db->loadResult())
			{
				return true;
			}
            else
            {
                return false;
            }    
        
    }
    
    /**
	 * Method to get extra fields
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
    static function getUserExtraFields($jlid)
    {
        $mainframe = JFactory::getApplication();
    	$db = JFactory::getDBO();
        $query = $db->getQuery(true);
        
        $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' db-id<br><pre>'.print_r($jlid,true).'</pre>'),'Notice');
        
        $query->select('ef.*,ev.fieldvalue as fvalue,ev.id as value_id ');
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_user_extra_fields as ef ');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_user_extra_fields_values as ev ON ( ef.id = ev.field_id AND ev.jl_id = '.$jlid .')' );
        $query->where('ef.template_backend LIKE ' . $db->Quote(''.JRequest::getVar('view').'') );
        //$query->where('ev.jl_id = '.$jlid );
        $query->order('ef.ordering');
        
        $db->setQuery($query);
		if (!$result = $db->loadObjectList())
		{
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
            $mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
			return false;
		}
		return $result;
    
    }
    
    
    /**
     * sportsmanagementHelper::saveExtraFields()
     * 
     * @param mixed $post
     * @param mixed $pid
     * @return void
     */
    function saveExtraFields($post,$pid)
  {
    $mainframe = JFactory::getApplication();
       $address_parts = array();
    //$mainframe->enqueueMessage(JText::_('sportsmanagementHelper saveExtraFields<br><pre>'.print_r($post,true).'</pre>'),'Notice');
    
    $db = JFactory::getDBO();
  //-------extra fields-----------//
		if(isset($post['extraf']) && count($post['extraf']))
    {
        //$mainframe->enqueueMessage(JText::_('sportsmanagementHelper saveExtraFields<br><pre>'.print_r($post,true).'</pre>'),'Notice');
        //$mainframe->enqueueMessage(JText::_('sportsmanagementHelper saveExtraFields pid<br><pre>'.print_r($pid,true).'</pre>'),'Notice');
			for($p=0;$p<count($post['extraf']);$p++)
            {
                // Create a new query object.
                $query = $db->getQuery(true);
// delete all
$conditions = array(
    $db->quoteName('field_id') . '='.$post['extra_id'][$p],
    $db->quoteName('jl_id') . '='.$pid
);
 
$query->delete($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_user_extra_fields_values'));
$query->where($conditions);
 
$db->setQuery($query);  

if (!$db->query())
		{
			
            $mainframe->enqueueMessage(JText::_('sportsmanagementHelper saveExtraFields delete<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
		}
        
// Create a new query object.
        $query = $db->getQuery(true);
        // Insert columns.
        $columns = array('field_id','jl_id','fieldvalue');
        // Insert values.
        $values = array($post['extra_id'][$p],$pid,'\''.$post['extraf'][$p].'\'');
        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_user_extra_fields_values'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        // Set the query using our newly populated query object and execute it.
        $db->setQuery($query);

if (!$db->query())
		{
			
            $mainframe->enqueueMessage(JText::_('sportsmanagementHelper saveExtraFields insert<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
		}

			}
		}
  
  }
    
    /**
	* Fetch google map data refere to
	* http://code.google.com/apis/maps/documentation/geocoding/#Geocoding	 
	*/	 	
	public function getAddressData($address)
	{
	   $mainframe = JFactory::getApplication();

		$url = 'http://maps.google.com/maps/api/geocode/json?' . 'address='.urlencode($address) .'&sensor=false&language=de';
//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($url,true).'</pre>'),'');        

		$content = self::getContent($url);
//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($content,true).'</pre>'),'');   		

		$status = null;	
		if(!empty($content))
		{
			$json = new Services_JSON();
			$status = $json->decode($content);
		}

//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($status,true).'</pre>'),'');   

		return $status;
	}
    
    /**
* @function     getOSMGeoCoords
* @param        $address : string
* @returns      -
* @description  Gets GeoCoords by calling the OpenStreetMap geoencoding API
*/
public function getOSMGeoCoords($address)
{
    $mainframe = JFactory::getApplication();
    $coords = array();
        
    //$address = utf8_encode($address);
    
    // call OSM geoencoding api
    // limit to one result (limit=1) without address details (addressdetails=0)
    // output in JSON
    $geoCodeURL = "http://nominatim.openstreetmap.org/search?format=json&limit=1&addressdetails=1&q=".
                  urlencode($address);

//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($geoCodeURL,true).'</pre>'),'');   
    
    $result = json_decode(file_get_contents($geoCodeURL), true);
    
    


/*
[COM_SPORTSMANAGEMENT_SUBLOCALITY_LONG_NAME] => D�rpum
[COM_SPORTSMANAGEMENT_LOCALITY_LONG_NAME] => Bordelum
[COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME] => Schleswig-Holstein
[COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_SHORT_NAME] => SH
*/    
    if ( isset($result[0]) )
    {        
    $coords['latitude'] = $result[0]["lat"];
    $coords['longitude'] = $result[0]["lon"];
    $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME'] = $result[0]["address"]["state"];
    
    $coords['COM_SPORTSMANAGEMENT_LOCALITY_LONG_NAME'] = $result[0]["address"]["city"];
    $coords['COM_SPORTSMANAGEMENT_SUBLOCALITY_LONG_NAME'] = $result[0]["address"]["residential"];
    $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME'] = $result[0]["address"]["county"];
    
    }
    
//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($result,true).'</pre>'),'');

    return $coords;
}

    /**
     * sportsmanagementHelper::resolveLocation()
     * 
     * @param mixed $address
     * @return
     */
    public function resolveLocation($address)
	{
		$mainframe = JFactory::getApplication();
    $coords = array();
		$data = self::getAddressData($address);
        
//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($address,true).'</pre>'),'');
//$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($data->status,true).'</pre>'),'');
//$osm = self::getOSMGeoCoords($address);  
        
		if($data)
        {
			if($data->status == 'OK')
			{
				$this->latitude  = $data->results[0]->geometry->location->lat;
				$coords['latitude'] = $data->results[0]->geometry->location->lat; 
				$this->longitude = $data->results[0]->geometry->location->lng;
				$coords['longitude'] = $data->results[0]->geometry->location->lng;
				
				for ($a=0; $a < sizeof($data->results[0]->address_components); $a++ )
				{
        switch($data->results[0]->address_components[$a]->types[0])
        {
        case 'administrative_area_level_1':
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_SHORT_NAME'] = $data->results[0]->address_components[$a]->short_name;
        break;
        
        case 'administrative_area_level_2':
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_SHORT_NAME'] = $data->results[0]->address_components[$a]->short_name;
        break;
        
        case 'administrative_area_level_3':
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
        $coords['COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_SHORT_NAME'] = $data->results[0]->address_components[$a]->short_name;
        break;

        case 'locality':
        $coords['COM_SPORTSMANAGEMENT_LOCALITY_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
        break;
        
        case 'sublocality':
        $coords['COM_SPORTSMANAGEMENT_SUBLOCALITY_LONG_NAME'] = $data->results[0]->address_components[$a]->long_name;
        break;
                        
        }
                
        
        }
				
				return $coords;
			}
//            else
//            {
//                $osm = self::getOSMGeoCoords($address);
//            }
          
		}
        
	}
    

	/**
	 * sportsmanagementHelper::getContent()
	 * Return content of the given url
	 * @param mixed $url
	 * @param bool $raw
	 * @param bool $headerOnly
	 * @return
	 */
	static public function getContent($url , $raw = false , $headerOnly = false)
	{
		if (!$url)
			return false;
		
		if (function_exists('curl_init'))
		{
			$ch			= curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, true );
			
			if($raw){
				curl_setopt($ch, CURLOPT_BINARYTRANSFER, true );
			}

			$response	= curl_exec($ch);
			
			$curl_errno	= curl_errno($ch);
			$curl_error	= curl_error($ch);
			
			if ($curl_errno!=0)
			{
				$mainframe	= JFactory::getApplication();
				$err		= 'CURL error : '.$curl_errno.' '.$curl_error;
				$mainframe->enqueueMessage($err, 'error');
			}
			
			$code		= curl_getinfo( $ch , CURLINFO_HTTP_CODE );

			// For redirects, we need to handle this properly instead of using CURLOPT_FOLLOWLOCATION
			// as it doesn't work with safe_mode or openbase_dir set.
			if( $code == 301 || $code == 302 )
			{
				list( $headers , $body ) = explode( "\r\n\r\n" , $response , 2 );
				
				preg_match( "/(Location:|URI:)(.*?)\n/" , $headers , $matches );
				
				if( !empty( $matches ) && isset( $matches[2] ) )
				{
					$url	= JString::trim( $matches[2] );
					curl_setopt( $ch , CURLOPT_URL , $url );
					curl_setopt( $ch , CURLOPT_RETURNTRANSFER, 1);
					curl_setopt( $ch , CURLOPT_HEADER, true );
					$response	= curl_exec( $ch );
				}
			}
			
			
			if(!$raw){
				list( $headers , $body )	= explode( "\r\n\r\n" , $response , 2 );
			}
			
			$ret	= $raw ? $response : $body;
			$ret	= $headerOnly ? $headers : $ret;
			
			curl_close($ch);
			return $ret;
		}
	
		// CURL unavailable on this install
		return false;
	}
    
    /**
     * sportsmanagementHelper::getPictureClub()
     * 
     * @param mixed $id
     * @return
     */
    static function getPictureClub($id)
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $db = JFactory::getDBO();
    // Create a new query object.
        $query = $db->getQuery(true);
        $query->select(array('logo_big'))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_club')
        ->where('id = '.$id);    
        $db->setQuery($query);
        $picture = $db->loadResult();
        if (JFile::exists(JPATH_SITE.DS.$picture))
        {
            // alles ok
        }
        else
        {
            $picture = JComponentHelper::getParams($option)->get('ph_logo_big','');
        }
		return $picture;
    }
    
    /**
     * sportsmanagementHelper::getPicturePlayground()
     * 
     * @param mixed $id
     * @return
     */
    static function getPicturePlayground($id)
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $db = JFactory::getDBO();
    // Create a new query object.
        $query = $db->getQuery(true);
        $query->select(array('picture'))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_playground')
        ->where('id = '.$id);    
        $db->setQuery($query);
        $picture = $db->loadResult();
        if (JFile::exists(JPATH_SITE.DS.$picture))
        {
            // alles ok
        }
        else
        {
            $picture = JComponentHelper::getParams($option)->get('ph_team','');
        }
		return $picture;    
        
    }
    
    /**
     * sportsmanagementHelper::getArticleList()
     * 
     * @param mixed $project_category_id
     * @return
     */
    function getArticleList($project_category_id)
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $db = JFactory::getDBO();
    // Create a new query object.
        $query = $db->getQuery(true);
        
        $query->select('c.id as value,c.title as text');
       
        switch ( JComponentHelper::getParams($option)->get('which_article_component') )
    {
        case 'com_content':
        $query->from('#__content as c');
        break;
        case 'com_k2':
        $query->from('#__k2_items as c');
        break;
    }
    $query->where('catid ='. $project_category_id );
       $db->setQuery($query); 
        $result = $db->loadObjectList();
        return $result;
    }
        
}
