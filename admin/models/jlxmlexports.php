<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
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

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');
jimport('joomla.utilities.array');
jimport('joomla.utilities.arrayhelper');


/**
 * sportsmanagementModelJLXMLExports
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2016
 * @version $Id$
 * @access public
 */
class sportsmanagementModelJLXMLExports extends JModelLegacy
{
	/**
	 * @var int
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_project_id = 0;
    
    /**
	 * @var int
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_update = 0;

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_project = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_projectteam = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.5253
	 */
	private $_projectreferee = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.5253
	 */
	private $_projectposition = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_team = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_teamplayer = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_teamstaff = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_teamtrainingdata = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_match = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_club = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_playground = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_matchplayer = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_matchstaff = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_matchreferee = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_person = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_matchevent = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_eventtype = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.0a
	 */
	private $_position = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.5262
	 */
	private $_parentposition = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.5283
	 */
	private $_matchstaffstatistic = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.5283
	 */
	private $_matchstatistic = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.5283
	 */
	private $_positionstatistic = array();

	/**
	 * @var array
	 *
	 * @access private
	 * @since  1.5.5283
	 */
	private $_statistic = array();

	
    
    
    /**
     * sportsmanagementModelJLXMLExports::__construct()
     * 
     * @param mixed $config
     * @return void
     */
    public function __construct($config = array())
        {   

                parent::__construct($config);
                $getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($getDBConnection);
        // Reference global application object
        $this->app = JFactory::getApplication();
        $this->user	= JFactory::getUser();     
        // JInput object
        $this->jinput = $this->app->input;
        $this->option = $this->jinput->getCmd('option');
        $this->jsmdb = $this->getDbo();
        $this->query = $this->jsmdb->getQuery(true);
        }
        
    /**
	 * exportData
	 *
	 * Export the active project data to xml
	 *
	 * @access public
	 * @since  1.5.0a
	 *
	 * @return null
	 */
	public function exportData()
	{
//	   // Reference global application object
//        $app = JFactory::getApplication();
//        // JInput object
//        $jinput = $app->input;
//        $option = $jinput->getCmd('option');
//        $db	= $this->getDbo();
//        $query = $db->getQuery(true);

//		$user = JFactory::getUser();

		//$this->_project_id = $app->getUserState($option.'project');
        //$this->_project_id = JFactory::getApplication()->input->getInt('p');
        $this->_project_id = $this->jinput->getVar('pid');
        $this->_update = $this->jinput->getVar('update');
		//$this->_project_id = $app->getUserState('project');
		if (empty($this->_project_id) || $this->_project_id == 0)
		{
			JError::raiseWarning('ERROR_CODE',JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_EXPORT_MODEL_SELECT_PROJECT'));
		}
		else
		{

//  das ist neu
    $filename = $this->_getIdFromData('name', $this->_project);
		
		if(empty($filename)) 
    {
			//$this->_project_id = $app->getUserState($option.'project');
            //$this->_project_id = JFactory::getApplication()->input->getInt('p');
			if (empty($this->_project_id) || $this->_project_id == 0)
			{
				JError::raiseWarning('ERROR_CODE',JText::_('COM_SPORTSMANAGEMENT_ADMIN_XML_EXPORT_MODEL_SELECT_PROJECT'));
			}
			else 
      {
				// get the project datas
				$this->_getProjectData();
				$filename = $this->_getIdFromData('name', $this->_project);
				$filename[0] = $filename[0]."-".$table;
			}
		}			
    $l98filename = JFilterOutput::stringURLSafe($filename[0])."-".date("ymd-His");
    //$file = JPATH_SITE.DS.'tmp'.DS.$l98filename.'.jlg';
    $file = JPATH_SITE.DS.'tmp'.DS.$this->user->username.DS.JFilterOutput::stringURLSafe($filename[0]).'.jlg';   
    
    $userpath = JPATH_SITE.DS.'tmp'.DS.$this->user->username;
    if ( JFolder::exists($userpath) )
    {
    }
    else
    {
    JFolder::create($userpath);
    }  
      
      $output = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
      
      
			// open the project
			$output .= "<project>\n";
			

			if ( $this->_update )
            {
            // get the matches data
			$output .= $this->_addToXml($this->_getMatchData());    
            }
            else
            {
            // get the version of JoomLeague
			$output .= $this->_addToXml($this->_getJoomLeagueVersion());

			// get the project datas
			$output .= $this->_addToXml($this->_getProjectData());

			// get sportstype data of project
			$output .= $this->_addToXml($this->_getSportsTypeData());

			// get league data of project
			$output .= $this->_addToXml($this->_getLeagueData());

			// get season data of project
			$output .= $this->_addToXml($this->_getSeasonData());

			// get the template data
			$output .= $this->_addToXml($this->_getTemplateData());

			// get divisions data
			$output .= $this->_addToXml($this->_getDivisionData());

			// get the projectteams data
			$output .= $this->_addToXml($this->_getProjectTeamData());

			// get referee data of project
			$output .= $this->_addToXml($this->_getProjectRefereeData());

			// get position data of project
			$output .= $this->_addToXml($this->_getProjectPositionData());

			// get the teams data
			$output .= $this->_addToXml($this->_getTeamData());

			// get the clubs data
			$output .= $this->_addToXml($this->_getClubData());

			// get the rounds data
			$output .= $this->_addToXml($this->_getRoundData());

			// get the matches data
			$output .= $this->_addToXml($this->_getMatchData());

			// get the playground data
			$output .= $this->_addToXml($this->_getPlaygroundData());

			// get the team player data
			$output .= $this->_addToXml($this->_getTeamPlayerData());

			// get the team staff data
			$output .= $this->_addToXml($this->_getTeamStaffData());

			// get the team training data
			$output .= $this->_addToXml($this->_getTeamTrainingData());

/*
			// get the match player data
			$output .= $this->_addToXml($this->_getMatchPlayerData());
*/

/*
			// get the match staff data
			$output .= $this->_addToXml($this->_getMatchStaffData());
*/

/*
			// get the match referee data
			$output .= $this->_addToXml($this->_getMatchRefereeData());
*/

			// get the positions data
			$output .= $this->_addToXml($this->_getPositionData());

			// get the positions parent data
			$output .= $this->_addToXml($this->_getParentPositionData());

			// get ALL persons data for Export
			$output .= $this->_addToXml($this->_getPersonData());

			// get the match events data
			$output .= $this->_addToXml($this->_getMatchEvent());

			// get the event types data
			$output .= $this->_addToXml($this->_getEventType());

			// get the position eventtypes data
			$output .= $this->_addToXml($this->_getPositionEventType());

			// get the match_statistic data
			$output .= $this->_addToXml($this->_getMatchStatistic());

			// get the match_staff_statistic data
			$output .= $this->_addToXml($this->_getMatchStaffStatistic());

			// get the position_statistic data
			$output .= $this->_addToXml($this->_getPositionStatistic());

			// get the statistic data
			$output .= $this->_addToXml($this->_getStatistic());
            }

			// close the project
			$output .= '</project>';

// mal als test
$xmlfile = $xmlfile.$output;
//JFile::write($file, $xmlfile);
    
			// download the generated xml
			$this->downloadXml($output,"");

			// close the application
			$app = JFactory::getApplication();
			$app->close();
		}
	}

	/**
	 * downloadXml
	 *
	 * Pop-up the browser's download window with the generated xml file
	 *
	 * @param string $data generated xml data
	 *
	 * @since  1.5.0a
	 *
	 * @return null
	 */
	function downloadXml($data, $table)
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db	= $this->getDbo();
        $query = $db->getQuery(true);
        
		jimport('joomla.filter.output');
		$filename = $this->_getIdFromData('name', $this->_project);
		if(empty($filename)) 
        {
			$this->_project_id = $app->getUserState($option.'project');
			if (empty($this->_project_id) || $this->_project_id == 0)
			{
				JError::raiseWarning('ERROR_CODE',JText::_('JL_ADMIN_XML_EXPORT_MODEL_SELECT_PROJECT'));
			}
			else {
				// get the project datas
				$this->_getProjectData();
				$filename = $this->_getIdFromData('name', $this->_project);
				$filename[0] = $filename[0]."-".$table;
			}
		}
		/**/
		header('Content-type: "text/xml"; charset="utf-8"');
		header("Content-Disposition: attachment; filename=\"" . JFilterOutput::stringURLSafe($filename[0])."-".date("ymd-His"). ".jlg\"");
		header("Expires: " . gmdate("D, d M Y H:i:s", mktime(date("H") + 2, date("i"), date("s"), date("m"), date("d"), date("Y"))) . " GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		/**/
		ob_clean(); 
		echo $data;
	}

	/**
	* Removes invalid XML
	*
	* @access public
	* @param string $value
	* @return string
	*/
	private function stripInvalidXml($value)
	{
		$ret='';
		$current;
		if (is_null($value)){return $ret;}

		$length = strlen($value);
		for ($i=0; $i < $length; $i++)
		{
			$current = ord($value{$i});
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
	private function _addToXml($data)
	{
		if (is_array($data) && count($data) > 0)
		{
			$object = $data[0]['object'];
			$output = '';
			foreach ($data as $name => $value)
			{
				$output .= "<record object=\"" . $this->stripInvalidXml($object) . "\">\n";
				foreach ($value as $key => $data)
				{
					if (!is_null($data) && !(substr($key, 0, 1) == "_") && $key != "object")
					{
						$output .= "  <$key><![CDATA[" . $this->stripInvalidXml(trim($data)) . "]]></$key>\n";
					}
				}
				$output .= "</record>\n";
			}
			return $output;
		}
		return false;
	}

	/**
	 * _getIdFromData
	 *
	 * Get only the ids array from the full array
	 *
	 * @param string $id	field name what we find in the array
	 * @param array  $array the array where we find the field
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return void
	 */
	private function _getIdFromData($id,$array)
	{
		if (is_array($array) && count($array) > 0)
		{
			$ids = array();
			foreach ($array as $key => $value)
			{
				if (array_key_exists($id, $value) && $value[$id] != '')
				{
					$ids[] = $value[$id];
				}
			}
			return $ids;
		}
		return false;
	}

	/**
	 * _getJoomLeagueVersion
	 *
	 * Get the version data and actual date, time and
	 * Joomla systemName from the joomleague_version table
	 *
	 * @access private
	 * @since  2010-08-26
	 *
	 * @return array
	 */
	private function _getJoomLeagueVersion()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db	= $this->getDbo();
        $query = $db->getQuery(true);
        
		$exportRoutine = '2010-09-23 15:00:00';
//		$query = "SELECT CONCAT(major,'.',minor,'.',build,'.',revision) AS version FROM #__joomleague_version ORDER BY date DESC LIMIT 1";
//		$this->_db->setQuery($query);
//		$this->_db->execute();
        
        // Select some fields
        $query->select('manifest_cache');
		// From the table
		$query->from('#__extensions');
        $query->where('name LIKE '.JFactory::getDbo()->Quote(''.'com_sportsmanagement'.'') );
        JFactory::getDbo()->setQuery( $query );
       $manifest_cache = json_decode( JFactory::getDbo()->loadResult(), true );
       
		if ( $manifest_cache['version'] )
		{
			$result[0]['version'] = $manifest_cache['version'];
			$result[0]['exportRoutine'] = $exportRoutine;
			$result[0]['exportDate'] = date('Y-m-d');
			$result[0]['exportTime'] = date('H:i:s');
if(version_compare(JVERSION,'3.0.0','ge')) 
{
			$result[0]['exportSystem'] = JFactory::getConfig()->get('config.sitename');
}
else
{
			$result[0]['exportSystem'] = JFactory::getConfig()->getValue('config.sitename');    
}            

			$result[0]['object'] = 'JoomLeagueVersion';
			return $result;
		}
		return false;
	}

	/**
	 * _getProjectData
	 *
	 * Get the project data from the joomleague table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getProjectData()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db	= $this->getDbo();
        $query = $db->getQuery(true);
        
        $query->select('*');
        $query->from('#__sportsmanagement_project');
        $query->where('id = ' . $this->_project_id );
        
		//$query = "SELECT * FROM #__sportsmanagement_project WHERE id=$this->_project_id";
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows() > 0)
		{
			$result = $db->loadAssocList();
			$result[0]['object'] = 'JoomLeague15';
			$this->_project = $result;
			return $result;
		}
		return false;
	}

	/**
	 * _getTemplateData
	 *
	 * Get the template data from the joomleague table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getTemplateData()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db	= $this->getDbo();
        $query = $db->getQuery(true);
        
		// this is the master template
		if ( $this->_project[0]['master_template'] == 0 )
		{
			$master_template_id = $this->_project_id;
		}
		else
		{
			$master_template_id = $this->_project[0]['master_template'];
		}
        
        $query->select('*');
        $query->from('#__sportsmanagement_template_config');
        $query->where('project_id = ' . $master_template_id );

		//$query = "SELECT * FROM #__sportsmanagement_template_config WHERE project_id=$master_template_id";
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows() > 0)
		{
			$result = $db->loadAssocList();
			$result[0]['object'] = 'Template';

			return $result;
		}
		return false;
	}

	/**
	 * _getLeagueData
	 *
	 * Get the league data from the joomleague_league table
	 *
	 * @access private
	 * @since  1.5.5241
	 *
	 * @return array
	 */
	private function _getLeagueData()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db	= $this->getDbo();
        $query = $db->getQuery(true);
        
        $query->select('*');
        $query->from('#__sportsmanagement_league');
        $query->where('id = ' . $this->_project[0]['league_id'] );
        
		//$query = "SELECT * FROM #__sportsmanagement_league WHERE id=".$this->_project[0]['league_id'];
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows() > 0)
		{
			$result = $db->loadAssocList();
			$result[0]['object'] = 'League';

			return $result;
		}
		return false;
	}

	/**
	 * _getSportsTypeData
	 *
	 * Get the sportstype data from the joomleague_sports_type table
	 *
	 * @access private
	 * @since  1.5.5263
	 *
	 * @return array
	 */
	private function _getSportsTypeData()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db	= $this->getDbo();
        $query = $db->getQuery(true);
        
        $query->select('*');
        $query->from('#__sportsmanagement_sports_type');
        $query->where('id = ' . $this->_project[0]['sports_type_id'] );
        
		//$query = "SELECT * FROM #__sportsmanagement_sports_type WHERE id=".$this->_project[0]['sports_type_id'];
		$db->setQuery($query);
		$db->execute();
		if ($this->_db->getNumRows() > 0)
		{
			$result = $db->loadAssocList();
			$result[0]['object'] = 'SportsType';

			return $result;
		}
		return false;
	}

	/**
	 * _getSeasonData
	 *
	 * Get the season data from the joomleague_season table
	 *
	 * @access private
	 * @since  1.5.5241
	 *
	 * @return array
	 */
	private function _getSeasonData()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db	= $this->getDbo();
        $query = $db->getQuery(true);
        
        $query->select('*');
        $query->from('#__sportsmanagement_season');
        $query->where('id = ' . $this->_project[0]['season_id'] );
        
		//$query = "SELECT * FROM #__sportsmanagement_season WHERE id=".$this->_project[0]['season_id'];
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows() > 0)
		{
			$result = $db->loadAssocList();
			$result[0]['object'] = 'Season';

			return $result;
		}
		return false;
	}

	/**
	 * _getDivisionData
	 *
	 * Get the division data from the joomleague_divisions table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getDivisionData()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db	= $this->getDbo();
        $query = $db->getQuery(true);
        
        $query->select('*');
        $query->from('#__sportsmanagement_division');
        $query->where('project_id = ' . $this->_project_id );
        
		//$query = "SELECT * FROM #__sportsmanagement_division WHERE project_id=$this->_project_id";
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows() > 0)
		{
			$result = $db->loadAssocList();
			$result[0]['object'] = 'LeagueDivision';

			return $result;
		}
		return false;
	}

	/**
	 * _getprojectteamData
	 *
	 * Get the projectteam data from the joomleague_team_joomleague table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getProjectTeamData()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db	= $this->getDbo();
        $query = $db->getQuery(true);
        
        $query->select('pt.id,
        pt.project_id,
        st.team_id,
        st.id as season_team_id,
        pt.start_points,
        pt.points_finally,
        pt.neg_points_finally,
        pt.matches_finally,
        pt.won_finally,
  pt.draws_finally,
  pt.lost_finally,
  pt.homegoals_finally,
  pt.guestgoals_finally,
  pt.diffgoals_finally,
  pt.is_in_score,
  pt.use_finally,
  pt.admin,
  pt.info,
  pt.notes,
  pt.reason,
  pt.checked_out,
  pt.checked_out_time');
        $query->from('#__sportsmanagement_project_team as pt');
        $query->join('INNER','#__sportsmanagement_season_team_id AS st on st.id = pt.team_id');
        $query->where('pt.project_id = ' . $this->_project_id );
        
		//$query = "SELECT * FROM #__sportsmanagement_project_team WHERE project_id=$this->_project_id";
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows() > 0)
		{
			$result = $db->loadAssocList();
/*
            foreach( $result as $row ) 
            { 

      
      $query->clear();
      $query->select('team_id'); 
      $query->from('#__sportsmanagement_season_team_id');
      $query->where('id = ' . (int)$row['team_id'] );
      $db->setQuery($query);
	  $db->execute();
      $teamresult = $db->loadResult();
      if( $teamresult )
      {
      $row['team_id'] = $teamresult;  
      }
       
 }
*/  
			$result[0]['object'] = 'ProjectTeam';
			$this->_projectteam =& $result;
			return $result;
		}
		return false;
	}

	/**
	 * _getProjectPositionData
	 *
	 * Get the season data from the joomleague_season table
	 *
	 * @access private
	 * @since  1.5.5241
	 *
	 * @return array
	 */
	private function _getProjectPositionData()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db	= $this->getDbo();
        $query = $db->getQuery(true);
        
        $query->select('*');
        $query->from('#__sportsmanagement_project_position');
        $query->where('project_id = ' . $this->_project_id );
        
		//$query = "SELECT * FROM #__sportsmanagement_project_position WHERE project_id=$this->_project_id";
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows() > 0)
		{
			$result = $db->loadAssocList();
			$result[0]['object'] = 'ProjectPosition';
			$this->_projectposition =& $result;
			return $result;
		}
		return false;
	}

	/**
	 * _getProjectRefereeData
	 *
	 * Get the season data from the joomleague_season table
	 *
	 * @access private
	 * @since  1.5.5241
	 *
	 * @return array
	 */
	private function _getProjectRefereeData()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db	= $this->getDbo();
        $query = $db->getQuery(true);
        
        $query->select('*');
        $query->from('#__sportsmanagement_project_referee');
        $query->where('project_id = ' . $this->_project_id );
        
		//$query = "SELECT * FROM #__sportsmanagement_project_referee WHERE project_id=$this->_project_id";
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows() > 0)
		{
			$result = $db->loadAssocList();
			$result[0]['object'] = 'ProjectReferee';
			$this->_projectreferee =& $result;
			return $result;
		}
		return false;
	}

	/**
	 * _getTeamData
	 *
	 * Get the team data from the joomleague_teams table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return void
	 */
	private function _getTeamData()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db	= $this->getDbo();
        $query = $db->getQuery(true);
        
		$team_ids = $this->_getIdFromData('season_team_id', $this->_projectteam);

		if (is_array($team_ids) && count($team_ids) > 0)
		{
			$ids = implode(",", array_unique($team_ids));
            
            $query->select('t.*');
            $query->from('#__sportsmanagement_team as t');
            $query->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = t.id');
            $query->join('LEFT', '#__sportsmanagement_project_team AS pt ON pt.team_id = st.id');
            $query->where('st.id IN (' . $ids .')' );
            $query->where('pt.project_id = ' . $this->_project_id );
            $query->order('name');

			//$query = "SELECT * FROM #__sportsmanagement_team WHERE id IN ($ids) ORDER BY name";
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows() > 0)
			{
				$result = $db->loadAssocList();
				$result[0]['object'] = 'JL_Team';
				$this->_team =& $result;
				return $result;
			}
			return false;
		}
		return false;
	}

	
	/**
	 * sportsmanagementModelJLXMLExports::_getClubData()
	 * 
	 * @return
	 */
	private function _getClubData()
	{
	  // // Reference global application object
//        $app = JFactory::getApplication();
//        // JInput object
//        $jinput = $app->input;
//        $option = $jinput->getCmd('option');
        //$db	= $this->getDbo();
       // $query = $db->getQuery(true);
        
		$cIDs = array();

		$teamClub_ids = $this->_getIdFromData('club_id', $this->_team);
		if (is_array($teamClub_ids))
        {
            $cIDs=array_merge($cIDs,$teamClub_ids);
            }

		//$playgroundClub_ids = $this->_getIdFromData('club_id',$this->_teamstaff);
		//if (is_array($playgroundClub_ids)){$cIDs=array_merge($cIDs,$playgroundClub_ids);}

		if (is_array($cIDs) && count($cIDs) > 0)
		{
			$ids = implode(",", array_unique($cIDs));
            $this->query->clear();
            $this->query->select('*');
            $this->query->from('#__sportsmanagement_club');
            $this->query->where('id IN (' . $ids .')' );
            $this->query->order('name');

			//$query = "SELECT * FROM #__sportsmanagement_club WHERE id IN ($ids) ORDER BY name";
			$this->jsmdb->setQuery($this->query);
			$this->jsmdb->execute();
			if ($this->jsmdb->getNumRows() > 0)
			{
				$result = $this->jsmdb->loadAssocList();
				$result[0]['object'] = 'Club';
				$this->_club = $result;
				return $result;
			}
			return false;
		}
		return false;
	}

	/**
	 * _getRoundData
	 *
	 * Get the rounds data from the joomleague_rounds table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getRoundData()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db	= $this->getDbo();
        $query = $db->getQuery(true);
        
        $query->select('*');
        $query->from('#__sportsmanagement_round');
        $query->where('project_id = ' . $this->_project_id );
            
		//$query = "SELECT * FROM #__sportsmanagement_round WHERE project_id=$this->_project_id";
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows() > 0)
		{
			$result = $db->loadAssocList();
			$result[0]['object'] = 'Round';

			return $result;
		}
		return false;
	}

	/**
	 * _getMatchData
	 *
	 * Get the matches data from the joomleague_matches table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getMatchData()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db	= $this->getDbo();
        $query = $db->getQuery(true);
        
        $query->select('*');
        $query->from('#__sportsmanagement_match as m');
        $query->join('INNER', '#__sportsmanagement_round as r ON r.id = m.round_id');
        $query->where('r.project_id = ' . $this->_project_id );
        
		//$query = "SELECT m.* FROM #__sportsmanagement_match as m INNER JOIN #__sportsmanagement_round as r ON r.id=m.round_id WHERE r.project_id=$this->_project_id";
		$db->setQuery($query);
		$db->execute();
		if ($db->getNumRows() > 0)
		{
			$result = $db->loadAssocList();
			$result[0]['object'] = 'Match';
			$this->_match = $result;
			return $result;
		}
		return false;
	}

	/**
	 * _getPlaygroundData
	 *
	 * Get the playgrounds data from the joomleague_playgrounds table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getPlaygroundData()
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $db	= $this->getDbo();
        $query = $db->getQuery(true);
        
		$pgIDs=array();
		$clubsPlayground_ids = $this->_getIdFromData('standard_playground',$this->_club);
		if (is_array($clubsPlayground_ids)){$pgIDs=array_merge($pgIDs,$clubsPlayground_ids);}

		$projectTeamsPlayground_ids = $this->_getIdFromData('standard_playground',$this->_projectteam);
		if (is_array($projectTeamsPlayground_ids)){$pgIDs=array_merge($pgIDs,$projectTeamsPlayground_ids);}

		$matchPlayground_ids = $this->_getIdFromData('playground_id',$this->_match);
		if (is_array($matchPlayground_ids)){$pgIDs=array_merge($pgIDs,$matchPlayground_ids);}

		if (is_array($pgIDs) && count($pgIDs) > 0)
		{
			$ids = implode(",",array_unique($pgIDs));
            
            $query->select('*');
            $query->from('#__sportsmanagement_playground');
            $query->where('id IN (' . $ids .')' );
            
			//$query = "SELECT * FROM #__sportsmanagement_playground WHERE id IN ($ids)";
			$db->setQuery($query);
			$db->execute();
			if ($db->getNumRows() > 0)
			{
				$result = $db->loadAssocList();
				$result[0]['object'] = 'Playground';
				$this->_playground = $result;
				return $result;
			}
			return false;
		}
		return false;
	}

	/**
	 * _getTeamPlayerData
	 *
	 * Get the match players data from the joomleague_match_player table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getTeamPlayerData()
	{
		$teamplayer_ids = $this->_getIdFromData('id', $this->_projectteam);

		if (is_array($teamplayer_ids) && count($teamplayer_ids) > 0)
		{
			$ids = implode(",", array_unique($teamplayer_ids));

			$query = "SELECT * FROM #__sportsmanagement_team_player WHERE projectteam_id IN ($ids)";
			$this->_db->setQuery($query);
			$this->_db->execute();
			if ($this->_db->getNumRows() > 0)
			{
				$result = $this->_db->loadAssocList();
				$result[0]['object'] = 'TeamPlayer';
				$this->_teamplayer = $result;

				return $result;
			}
			return false;
		}
		return false;
	}

	/**
	 * _getTeamTrainingData
	 *
	 * Get the projectteams training data from the joomleague_team_trainingdata table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getTeamTrainingData()
	{
		$teamtraining_ids = $this->_getIdFromData('id',$this->_projectteam);

		if (is_array($teamtraining_ids) && count($teamtraining_ids) > 0)
		{
			$ids = implode(',',array_unique($teamtraining_ids));

			$query = "SELECT * FROM #__sportsmanagement_team_trainingdata WHERE project_team_id IN ($ids)";
			$this->_db->setQuery($query);
			$this->_db->execute();
			if ($this->_db->getNumRows() > 0)
			{
				$result = $this->_db->loadAssocList();
				$result[0]['object'] = 'TeamTraining';
				$this->_teamtrainingdata = $result;
				return $result;
			}
			return false;
		}
		return false;
	}

	/**
	 * _getTeamStaffData
	 *
	 * Get the match players data from the joomleague_match_player table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getTeamStaffData()
	{
		$teamstaff_ids = $this->_getIdFromData('id', $this->_projectteam);

		if (is_array($teamstaff_ids) && count($teamstaff_ids) > 0)
		{
			$ids = implode(",", array_unique($teamstaff_ids));

			$query = "SELECT * FROM #__sportsmanagement_team_staff WHERE projectteam_id IN ($ids)";
			$this->_db->setQuery($query);
			$this->_db->execute();
			if ($this->_db->getNumRows() > 0)
			{
				$result = $this->_db->loadAssocList();
				$result[0]['object'] = 'TeamStaff';
				$this->_teamstaff = $result;

				return $result;
			}
			return false;
		}
		return false;
	}

	/**
	 * _getMatchPlayerData
	 *
	 * Get the match players data from the joomleague_match_player table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getMatchPlayerData()
	{
		$match_ids = $this->_getIdFromData('id', $this->_match);

		if (is_array($match_ids) && count($match_ids) > 0)
		{
			$ids = implode(",", array_unique($match_ids));

			$query = "SELECT * FROM #__sportsmanagement_match_player WHERE match_id IN ($ids)";

			$this->_db->setQuery($query);
			$this->_db->execute();
			if ($this->_db->getNumRows() > 0)
			{
				$result = $this->_db->loadAssocList();
				$result[0]['object'] = 'MatchPlayer';
				$this->_matchplayer = $result;

				return $result;
			}
			return false;
		}
		return false;
	}

	/**
	 * _getMatchStaffData
	 *
	 * Get the match staffs data from the joomleague_match_staff table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getMatchStaffData()
	{
		$match_ids = $this->_getIdFromData('id', $this->_match);

		if (is_array($match_ids) && count($match_ids) > 0)
		{
			$ids = implode(",", array_unique($match_ids));

			$query = "SELECT * FROM #__sportsmanagement_match_staff WHERE match_id IN ($ids)";
			$this->_db->setQuery($query);
			$this->_db->execute();
			if ($this->_db->getNumRows() > 0)
			{
				$result = $this->_db->loadAssocList();
				$result[0]['object'] = 'MatchStaff';
				$this->_matchstaff = $result;

				return $result;
			}
			return false;
		}
		return false;
	}

	/**
	 * _getMatchRefereeData
	 *
	 * Get the match referees data from the joomleague_match_referee table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getMatchRefereeData()
	{
		$match_ids = $this->_getIdFromData('id', $this->_match);

		if (is_array($match_ids) && count($match_ids) > 0)
		{
			$ids = implode(",", array_unique($match_ids));

			$query = "SELECT * FROM #__sportsmanagement_match_referee WHERE match_id IN ($ids)";
			$this->_db->setQuery($query);
			$this->_db->execute();
			if ($this->_db->getNumRows() > 0)
			{
				$result = $this->_db->loadAssocList();
				$result[0]['object'] = 'MatchReferee';
				$this->_matchreferee = $result;

				return $result;
			}
			return false;
		}
		return false;
	}

	/**
	 * _getPositionData
	 *
	 * Get the positions data from the joomleague_playgrounds table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getPositionData()
	{
		$position_ids = $this->_getIdFromData('position_id', $this->_projectposition);

		if (is_array($position_ids) && count($position_ids) > 0)
		{
			$ids = implode(",", array_unique($position_ids));

			$query = "SELECT * FROM #__sportsmanagement_position WHERE id IN ($ids)";
			$this->_db->setQuery($query);
			$this->_db->execute();
			if ($this->_db->getNumRows() > 0)
			{
				$result = $this->_db->loadAssocList();
				$result[0]['object'] = 'Position';
				$this->_position = $result;

				return $result;
			}
			return false;
		}
		return false;
	}

	/**
	 * _getParentPositionData
	 *
	 * Get the parent positions data from the joomleague_positions table
	 *
	 * @access private
	 * @since  1.5.5262
	 *
	 * @return array
	 */
	private function _getParentPositionData()
	{
		$position_ids = $this->_getIdFromData('parent_id', $this->_position);

		if (is_array($position_ids) && count($position_ids) > 0)
		{
			$ids = implode(",", array_unique($position_ids));

			$query = "SELECT * FROM #__sportsmanagement_position WHERE id IN ($ids)";
			$this->_db->setQuery($query);
			$this->_db->execute();
			if ($this->_db->getNumRows() > 0)
			{
				$result = $this->_db->loadAssocList();
				$result[0]['object'] = 'ParentPosition';
				$this->_parentposition = $result;

				return $result;
			}
			return false;
		}
		return false;
	}

	/**
	 * getPersonData
	 *
	 * Get the persons data from the joomleague_persons table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getPersonData()
	{
		$pgIDs=array();

		$teamPlayer_ids = $this->_getIdFromData('person_id',$this->_teamplayer);
		if (is_array($teamPlayer_ids)){$pgIDs=array_merge($pgIDs,$teamPlayer_ids);}

		$teamStaff_ids = $this->_getIdFromData('person_id',$this->_teamstaff);
		if (is_array($teamStaff_ids)){$pgIDs=array_merge($pgIDs,$teamStaff_ids);}

		$projectReferee_ids = $this->_getIdFromData('person_id',$this->_projectreferee);
		if (is_array($projectReferee_ids)){$pgIDs=array_merge($pgIDs,$projectReferee_ids);}

		if (is_array($pgIDs) && count($pgIDs) > 0)
		{
			$ids = implode(",",array_unique($pgIDs));
			$query = "SELECT * FROM #__sportsmanagement_person WHERE id IN ($ids)";
			$this->_db->setQuery($query);
			$this->_db->execute();
			if ($this->_db->getNumRows() > 0)
			{
				$result = $this->_db->loadAssocList();
				$result[0]['object'] = 'Person';
				$this->_person = $result;

				return $result;
			}
			return false;
		}
		return false;
	}

	/**
	 * _getMatchEvent
	 *
	 * Get the match events data from the joomleague_match_events_new table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getMatchEvent()
	{
		$match_ids = $this->_getIdFromData('id', $this->_match);

		if (is_array($match_ids) && count($match_ids) > 0)
		{
			$ids = implode(",", array_unique($match_ids));

			$query = "SELECT * FROM #__sportsmanagement_match_event WHERE	match_id IN ($ids)";
			$this->_db->setQuery($query);
			$this->_db->execute();
			if ($this->_db->getNumRows() > 0)
			{
				$result = $this->_db->loadAssocList();
				$result[0]['object'] = 'MatchEvent';
				$this->_matchevent = $result;
				return $result;
			}
			return false;
		}
		return false;
	}

	/**
	 * _getEventType
	 *
	 * Get the event types data from the joomleague_eventtypes table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getEventType()
	{
		$eventtype_ids = $this->_getIdFromData('event_type_id', $this->_matchevent);

		if (is_array($eventtype_ids) && count($eventtype_ids) > 0)
		{
			$ids = implode(",", array_unique($eventtype_ids));

			$query = "SELECT * FROM #__sportsmanagement_eventtype WHERE id IN ($ids)";
			$this->_db->setQuery($query);
			$this->_db->execute();
			if ($this->_db->getNumRows() > 0)
			{
				$result = $this->_db->loadAssocList();
				$result[0]['object'] = 'EventType';
				$this->_eventtype = $result;
				return $result;
			}
			return false;
		}
		return false;
	}

	/**
	 * _getPositionEventType
	 *
	 * Get the position event types data from the joomleague_position_eventtype table
	 *
	 * @access private
	 * @since  1.5.0a
	 *
	 * @return array
	 */
	private function _getPositionEventType()
	{
		$eventtype_ids	= $this->_getIdFromData('id', $this->_eventtype);
		$position_ids	= $this->_getIdFromData('id', $this->_position);

		if (is_array($eventtype_ids) && count($eventtype_ids) > 0)
		{
			$event_ids		= implode(",", array_unique($eventtype_ids));
			$position_ids	= implode(",", array_unique($position_ids));

			$query = "SELECT * FROM #__sportsmanagement_position_eventtype WHERE eventtype_id IN ($event_ids) AND position_id IN ($position_ids)";
			$this->_db->setQuery($query);
			$this->_db->execute();
			if ($this->_db->getNumRows() > 0)
			{
				$result = $this->_db->loadAssocList();
				$result[0]['object'] = 'PositionEventType';
				return $result;
			}
			return false;
		}
		return false;
	}

	/**
	 * _getPositionStatistic
	 *
	 * Get the statisctics data from the joomleague_position_statistic table
	 *
	 * @access private
	 * @since  1.5.5283
	 *
	 * @return void
	 */
	private function _getPositionStatistic()
	{
		$position_ids = $this->_getIdFromData('id', $this->_position);

		if (is_array($position_ids) && count($position_ids) > 0)
		{
			$ids = implode(",", array_unique($position_ids));

			$query = "SELECT * FROM #__sportsmanagement_position_statistic WHERE position_id IN ($ids)";
			$this->_db->setQuery($query);
			$this->_db->execute();
			if ($this->_db->getNumRows() > 0)
			{
				$result = $this->_db->loadAssocList();
				$result[0]['object'] = 'PositionStatistic';
				$this->_positionstatistic = $result;
				return $result;
			}
			return false;
		}
		return false;
	}

	/**
	 * _getMatchStatistic
	 *
	 * Get the statisctics data from the joomleague_match_statistic table
	 *
	 * @access private
	 * @since  1.5.5283
	 *
	 * @return void
	 */
	private function _getMatchStatistic()
	{
		$match_ids = $this->_getIdFromData('id', $this->_match);

		if (is_array($match_ids) && count($match_ids) > 0)
		{
			$ids = implode(",", array_unique($match_ids));

			$query = "SELECT * FROM #__sportsmanagement_match_statistic WHERE match_id IN ($ids)";
			$this->_db->setQuery($query);
			$this->_db->execute();
			if ($this->_db->getNumRows() > 0)
			{
				$result = $this->_db->loadAssocList();
				$result[0]['object'] = 'MatchStatistic';
				$this->_matchstatistic = $result;
				return $result;
			}
			return false;
		}
		return false;
	}

	/**
	 * _getMatchStaffStatistic
	 *
	 * Get the statisctics data from the joomleague_match_staff_statistic table
	 *
	 * @access private
	 * @since  1.5.5283
	 *
	 * @return void
	 */
	private function _getMatchStaffStatistic()
	{
		$match_ids = $this->_getIdFromData('id', $this->_match);

		if (is_array($match_ids) && count($match_ids) > 0)
		{
			$ids = implode(",", array_unique($match_ids));

			$query = "SELECT * FROM #__sportsmanagement_match_staff_statistic WHERE match_id IN ($ids)";
			$this->_db->setQuery($query);
			$this->_db->execute();
			if ($this->_db->getNumRows() > 0)
			{
				$result = $this->_db->loadAssocList();
				$result[0]['object'] = 'MatchStaffStatistic';
				$this->_matchstaffstatistic = $result;
				return $result;
			}
			return false;
		}
		return false;
	}

	/**
	 * _getStatistic
	 *
	 * Get the statistic data from the joomleague_statistic table
	 *
	 * @access private
	 * @since  1.5.5283
	 *
	 * @return void
	 */
	private function _getStatistic()
	{
		$sIDs=array();

		$matchstatistic_ids = $this->_getIdFromData('statistic_id',$this->_matchstatistic);	// Get all ids of match statistics assigned to the actual project
		if (is_array($matchstatistic_ids)){$sIDs=array_merge($sIDs,$matchstatistic_ids);}

		$matchstaffstatistic_ids = $this->_getIdFromData('statistic_id',$this->_matchstaffstatistic);	// Get all ids of match staff statistic assigned to the actual project
		if (is_array($matchstaffstatistic_ids)){$sIDs=array_merge($sIDs,$matchstaffstatistic_ids);}

		$positionstatistic_ids = $this->_getIdFromData('statistic_id',$this->_positionstatistic);	// Get all ids of position statistic assigned to the actual project
		if (is_array($positionstatistic_ids)){$sIDs=array_merge($sIDs,$positionstatistic_ids);}

		if (is_array($sIDs) && count($sIDs) > 0)
		{
			$ids = implode(",",array_unique($sIDs));
			$query = "SELECT * FROM #__sportsmanagement_statistic WHERE id IN ($ids)";
			$this->_db->setQuery($query);
			$this->_db->execute();
			if ($this->_db->getNumRows() > 0)
			{
				$result = $this->_db->loadAssocList();
				$result[0]['object'] = 'Statistic';
				$this->_person = $result;

				return $result;
			}
			return false;
		}
		return false;
	}

}
?>