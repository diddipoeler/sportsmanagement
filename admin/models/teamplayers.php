<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroups.php
* @author                diddipoeler, stony und svdoldie (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
*        SportsManagement is free software: you can redistribute it and/or modify
*        it under the terms of the GNU General Public License as published by
*  the Free Software Foundation, either version 3 of the License, or
*  (at your option) any later version.
*
*  SportsManagement is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  You should have received a copy of the GNU General Public License
*  along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
*  Diese Datei ist Teil von SportsManagement.
*
*  SportsManagement ist Freie Software: Sie können es unter den Bedingungen
*  der GNU General Public License, wie von der Free Software Foundation,
*  Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
*  veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
*  SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
*  OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
*  Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
*  Siehe die GNU General Public License für weitere Details.
*
*  Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
*  Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');


/**
 * sportsmanagementModelTeamPlayers
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2013
 * @access public
 */
class sportsmanagementModelTeamPlayers extends JModelList
{
	var $_identifier = "teamplayers";
    var $_project_id = 0;
    var $_season_id = 0;
    var $_team_id = 0;
    var $_project_team_id = 0;

	function getListQuery()
	{
		$option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
		        
        $this->_project_id	= $app->getUserState( "$option.pid", '0' );
        $this->_season_id	= $app->getUserState( "$option.season_id", '0' );
        $this->_team_id = JFactory::getApplication()->input->getVar('team_id');
        $this->_project_team_id = JFactory::getApplication()->input->getVar('project_team_id');
        
        if ( !$this->_team_id )
        {
            $this->_team_id	= $app->getUserState( "$option.team_id", '0' );
        }
        if ( !$this->_project_team_id )
        {
            $this->_project_team_id	= $app->getUserState( "$option.project_team_id", '0' );
        }
        
        // Get the WHERE and ORDER BY clauses for the query
		$where = self::_buildContentWhere();
		$orderby = self::_buildContentOrderBy();
        
        if ( COM_SPORTSMANAGEMENT_USE_NEW_TABLE )
        {
        $query->select(array('ppl.firstname',
							'ppl.lastname',
							'ppl.nickname',
                            'ppl.height',
                            'ppl.weight',
                            
                            'ppl.injury',
                            'ppl.suspension',
                            'ppl.away',
                            
                            'ppl.id',
                            'ppl.id AS person_id',
							'tp.*',
                            'tp.id as tpid',
							'u.name AS editor'))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS ppl')
        ->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_person_id AS tp on tp.person_id = ppl.id')
        ->join('LEFT', '#__users AS u ON u.id = tp.checked_out');
        }
        else
        {    
        $query->select(array('ppl.firstname',
							'ppl.lastname',
							'ppl.nickname',
                            'ppl.height',
                            'ppl.weight',
                            'ppl.id',
                            'ppl.id AS person_id',
							'tp.*',
                            'tp.id as tpid',
							'u.name AS editor'))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS ppl')
        ->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player AS tp on tp.person_id = ppl.id')
        ->join('LEFT', '#__users AS u ON u.id = tp.checked_out');
        }

        
        if ($where)
        {
            $query->where($where);
        }
        if ($orderby)
        {
            $query->order($orderby);
        }

		return $query;
	}

	function _buildContentOrderBy()
	{
		$option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        $filter_order		= $app->getUserStateFromRequest($option.'.'.$this->_identifier.'.tp_filter_order','filter_order','tp.ordering','cmd');
		$filter_order_Dir	= $app->getUserStateFromRequest($option.'.'.$this->_identifier.'.tp_filter_order_Dir','filter_order_Dir','','word');
		if ($filter_order=='ppl.lastname')
		{
			$orderby=' ppl.lastname '.$filter_order_Dir;
		}
		else
		{
			$orderby=' '.$filter_order.' '.$filter_order_Dir.',ppl.lastname ';
		}
		return $orderby;
	}

	function _buildContentWhere()
	{
		$option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
		
        //$project_id=$app->getUserState($option.'project');
		//$team_id=$app->getUserState($option.'project_team_id');
        
		$filter_state	= $app->getUserStateFromRequest( $option . '.'.$this->_identifier.'.tp_filter_state','filter_state','','word' );
		$search			= $app->getUserStateFromRequest($option.'.'.$this->_identifier.'.tp_search','search','','string');
		$search_mode	= $app->getUserStateFromRequest($option.'.'.$this->_identifier.'.tp_search_mode','search_mode','','string');
		$search=JString::strtolower($search);
		$where=array();
		
        if ( COM_SPORTSMANAGEMENT_USE_NEW_TABLE )
        {
            $where[]="ppl.published = '1'";
            $where[]='tp.team_id='.$this->_team_id;
            $where[]='tp.season_id='.$this->_season_id;
            $where[]='tp.persontype=1';
        }
        else
        {
        $where[]='tp.projectteam_id= '.$this->_project_team_id;
		$where[]="ppl.published = '1'";
        }
        
        
		if ($search)
		{
			if ($search_mode)
			{
				$where[]='(LOWER(ppl.lastname) LIKE '.$this->_db->Quote($search.'%') .
							'OR LOWER(ppl.firstname) LIKE '.$this->_db->Quote($search.'%') .
							'OR LOWER(ppl.nickname) LIKE '.$this->_db->Quote($search.'%').')';
			}
			else
			{
				$where[]='(LOWER(ppl.lastname) LIKE '.$this->_db->Quote('%'.$search.'%').
							'OR LOWER(ppl.firstname) LIKE '.$this->_db->Quote('%'.$search.'%') .
							'OR LOWER(ppl.nickname) LIKE '.$this->_db->Quote('%'.$search.'%').')';
			}
		}

		if ( $filter_state )
		{
			if ( $filter_state == 'P' )
			{
				$where[] = 'tp.published = 1';
			}
			elseif ($filter_state == 'U' )
			{
				$where[] = 'tp.published = 0';
			}
		}

		$where=(count($where) ? ' '.implode(' AND ',$where) : '');
		return $where;
	}

	function getProjectTeamplayers($project_team_id = 0)
    {
        $option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        // Create a new query object.
		$db		= sportsmanagementHelper::getDBConnection();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser(); 
		
        //$app->enqueueMessage('sportsmanagementModelTeamPlayers getProjectTeamplayers project_team_id<br><pre>'.print_r($project_team_id, true).'</pre><br>','Notice');
        
        // Select some fields
		$query->select('pl.*');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person as pl');
        $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_player as tpl on tpl.person_id = pl.id');
        $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team as pt on pt.id = tpl.projectteam_id');
        $query->where('pt.team_id = '.$project_team_id);
        $db->setQuery($query);
        //$db->query();
        $result = $db->loadObjectList();
        //$app->enqueueMessage('sportsmanagementModelTeamPlayers getProjectTeamplayers query<br><pre>'.print_r($query, true).'</pre><br>','Notice');
                
		if (!$result)
		{
			//$this->setError($this->_db->getErrorMsg());
            $app->enqueueMessage(__METHOD__.' '.__LINE__.' message<br><pre>'.print_r($db->getErrorMsg(), true).'</pre><br>','Error');
            $app->enqueueMessage(__METHOD__.' '.__LINE__.' nummer<br><pre>'.print_r($db->getErrorNum(), true).'</pre><br>','Error');
			return false;
		}
		return $result;
    }

	
	

	

	

	
	/**
	 * remove specified players from team
	 * @param $cids player ids
	 * @return int count of removed
	 */
	function remove($cids)
	{
		$count=0;
		foreach($cids as $cid)
		{
			$object=&$this->getTable('teamplayer');
			if ($object->canDelete($cid) && $object->delete($cid))
			{
				$count++;
			}
			else
			{
				$this->setError(JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEAMSTAFFS_MODEL_ERROR_REMOVE_TEAMPLAYER',$object->getError()));
			}
		}
		return $count;
	}

}
?>