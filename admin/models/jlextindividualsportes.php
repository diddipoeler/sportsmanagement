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
jimport('joomla.application.component.modellist');
//require_once(JPATH_COMPONENT.DS.'models'.DS.'list.php');



/**
 * sportsmanagementModeljlextindividualsportes
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModeljlextindividualsportes extends JModelList
{
	var $_identifier = "jlextindividualsportes";
    
   public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'mc.id'
                        );
            parent::__construct($config);
            }
    
    
    
        
    protected function getListQuery()
	{
		$app = JFactory::getApplication();
        $option = JFactory::getApplication()->input->getCmd('option');
        $project_id			= $app->getUserState( "$option.pid", '0' );
		$match_id		= JFactory::getApplication()->input->getvar('id', 0);;
		$projectteam1_id		= JFactory::getApplication()->input->getvar('team1', 0);;
		$projectteam2_id		= JFactory::getApplication()->input->getvar('team2', 0);;
        //$search	= $app->getUserStateFromRequest($option.'.'.$this->_identifier.'.search','search','','string');
        //$search_nation		= $app->getUserStateFromRequest($option.'.'.$this->_identifier.'.search_nation','search_nation','','word');
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        
        // Select some fields
		$query->select('mc.*');
		// From the hello table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_single AS mc');
        $query->where('mc.match_id = '.$match_id);

$query->order($db->escape($this->getState('list.ordering', 'mc.id')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
        
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
 

        return $query;
	}
    



	function checkGames($project,$match_id,$rid,$projectteam1_id,$projectteam2_id)
    {
        $option = JFactory::getApplication()->input->getCmd('option');
		$app	= JFactory::getApplication();
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        
        //$app->enqueueMessage(__FILE__.' '.get_class($this).' '.__FUNCTION__.' project<br><pre>'.print_r($project, true).'</pre><br>','');
        //$app->enqueueMessage(__FILE__.' '.get_class($this).' '.__FUNCTION__.' match_id<br><pre>'.print_r($match_id, true).'</pre><br>','');
        
        // Select some fields
		$query->select('COUNT(mc.id)');
		// From the hello table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_single AS mc');
        $query->where('mc.match_id = '.$match_id);
        $query->where('mc.match_type = "SINGLE" ');
        $db->setQuery($query);
        $singleresult = $db->loadResult();
        
        //$app->enqueueMessage(__FILE__.' '.get_class($this).' '.__FUNCTION__.' singleresult<br><pre>'.print_r($singleresult, true).'</pre><br>','');
        
        if ( $singleresult < $project->tennis_single_matches )
        {
            $insertmatch = $project->tennis_single_matches - $singleresult;
            
            //$app->enqueueMessage(__FILE__.' '.get_class($this).' '.__FUNCTION__.' insertmatch<br><pre>'.print_r($insertmatch, true).'</pre><br>','');
            
            for ($i=0; $i < $insertmatch; $i++)
		    {
		      // Create and populate an object.
              $temp = new stdClass();
              $temp->round_id = $rid;
              $temp->projectteam1_id = $projectteam1_id;
              $temp->projectteam2_id = $projectteam2_id;
              $temp->match_id = $match_id;
              $temp->match_type = 'SINGLE';
              $temp->published = 1;
              // Insert the object
              $result = JFactory::getDbo()->insertObject('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_single', $temp);
		    }  
        }
        
        $query = $db->getQuery(true);
        $query->clear();
        
        // Select some fields
		$query->select('COUNT(mc.id)');
		// From the hello table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_single AS mc');
        $query->where('mc.match_id = '.$match_id);
        $query->where('mc.match_type = "DOUBLE" ');
        $db->setQuery($query);
        $doubleresult = $db->loadResult();
        
        //$app->enqueueMessage(__FILE__.' '.get_class($this).' '.__FUNCTION__.' doubleresult<br><pre>'.print_r($doubleresult, true).'</pre><br>','');
        
        if ( $doubleresult < $project->tennis_double_matches )
        {
            $insertmatch = $project->tennis_double_matches - $doubleresult;
            
            //$app->enqueueMessage(__FILE__.' '.get_class($this).' '.__FUNCTION__.' insertmatch<br><pre>'.print_r($insertmatch, true).'</pre><br>','');
            
            for ($i=0; $i < $insertmatch; $i++)
		    {
		      // Create and populate an object.
              $temp = new stdClass();
              $temp->round_id = $rid;
              $temp->projectteam1_id = $projectteam1_id;
              $temp->projectteam2_id = $projectteam2_id;
              $temp->match_id = $match_id;
              $temp->match_type = 'DOUBLE';
              $temp->published = 1;
              // Insert the object
              $result = JFactory::getDbo()->insertObject('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_single', $temp);
		    } 
            
        }
        
    }
    




	/**
	 * Method to return the project teams array (id, name)
	 *
	 * @access  public
	 * @return  array
	 * @since 0.1
	 */
	function getProjectTeams($project_id)
	{
		$option = JFactory::getApplication()->input->getCmd('option');

		$app	= JFactory::getApplication();
		//$project_id = $app->getUserState($option . 'project');
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
//$projectteam1_id		= JFactory::getApplication()->input->getvar('team1', 0);

// Select some fields
		$query->select('pt.id AS value');
        $query->select('t.name AS text,t.short_name AS short_name,t.notes');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = t.id');  
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
        $query->where('pt.project_id = ' . $project_id);
        //$query->where('pl.published = 1');
        $query->order('text ASC');
        
/*
		$query = '	SELECT	pt.id AS value,
							t.name AS text,
							t.short_name AS short_name,
							t.notes

					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = t.id
					WHERE pt.project_id = ' . $project_id . '
					ORDER BY text ASC ';
*/
		$db->setQuery($query);

		if (!$result = $db->loadObjectList())
		{
			$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
			return false;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * @param int iDivisionId
	 * return project teams as options
	 * @return unknown_type
	 */
	function getProjectTeamsOptions($iDivisionId=0)
	{
		$option = JFactory::getApplication()->input->getCmd('option');

		$app	=& JFactory::getApplication();
		$project_id = $app->getUserState($option . 'project');

		$query = ' SELECT	pt.id AS value, '
		. ' CASE WHEN CHAR_LENGTH(t.name) < 25 THEN t.name ELSE t.middle_name END AS text '
		. ' FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t '
		. ' LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = t.id '
		. ' WHERE pt.project_id = ' . $project_id;
		if($iDivisionId>0)  {
			$query .=' AND pt.division_id = ' .$iDivisionId;
		}
		$query .= ' ORDER BY text ASC ';

		$this->_db->setQuery($query);
		$result = $this->_db->loadObjectList();
		if ($result === FALSE)
		{
			JError::raiseError(0, $this->_db->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}

	function getMatchesByRound($roundId)
	{
		$query = 'SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_single WHERE round_id='.$roundId;
		$this->_db->setQuery($query);
		//echo($this->_db->getQuery());
		$result = $this->_db->loadObjectList();
		if ($result === FALSE)
		{
			JError::raiseError(0, $this->_db->getErrorMsg());
			return false;
		}
		return $result;
	}
	
	
	function getPlayer($teamid,$project_id)
	{
  $option = JFactory::getApplication()->input->getCmd('option');
	$app	= JFactory::getApplication();
    // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        $season_id	= $app->getUserState( "$option.season_id", '0' );

// Select some fields
		$query->select('tp.id AS value');
        $query->select('concat(pl.firstname," - ",pl.nickname," - ",pl.lastname) as text');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pl');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp ON tp.person_id = pl.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st ON st.team_id = tp.team_id');  
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
        $query->where('pt.id ='. $db->Quote($teamid));
        $query->where('pt.project_id ='. $project_id);
        
        $query->where('tp.season_id ='. $season_id);
        $query->where('st.season_id ='. $season_id);
        
        $query->where('pl.published = 1');
        $query->order('pl.lastname ASC');
        
		$db->setQuery($query);
        $result = $db->loadObjectList(); 
        
        if ( !$result && COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' ' .  ' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Error');
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
        }
		return $result;
  
  }
 
  function getSportType($id)
  {
  $option = JFactory::getApplication()->input->getCmd('option');
	$app	=& JFactory::getApplication();
  $query='SELECT name
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type
					WHERE id='. $this->_db->Quote($id);
		$this->_db->setQuery($query);
		$sporttype = $this->_db->loadResult();
		$app->setUserState($option.'sporttype',$sporttype);
		$app->enqueueMessage(JText::_('Sporttype: '.$sporttype ),'');
		
		switch ( strtolower($sporttype) )
		{
    case 'ringen':
    $this->_getSinglefile();
    break;
    }
		
		
		return $sporttype;
		
  }
  
  function _getSinglefile()
  {
  $option = JFactory::getApplication()->input->getCmd('option');
	$app	=& JFactory::getApplication();
	
	$match_id		= $app->getUserState( $option . 'match_id' );
	$query='SELECT match_number
					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match
					WHERE id='. $this->_db->Quote($match_id);
		$this->_db->setQuery($query);
		$match_number = $this->_db->loadResult();
	
	$dir = JPATH_SITE.DS.'tmp'.DS.'ringerdateien';
  $files = JFolder::files($dir, '^MKEinzelkaempfe_Data_'.$match_number, false, false, array('^Termine_Schema') );
  
  $app->enqueueMessage(JText::_('_getSinglefile: '.print_r($files,true) ),'');
  
  if ( $files )
  {
  $app->enqueueMessage(JText::_('Einzelk&auml;mpfe '.$match_number.' vorhanden' ),'Notice');
  }
  else
  {
  $app->enqueueMessage(JText::_('Einzelk&auml;mpfe '.$match_number.' nicht vorhanden' ),'Error');
  }
  
  }

}
?>