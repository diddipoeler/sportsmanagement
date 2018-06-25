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

jimport('joomla.application.component.modellist');



/**
 * sportsmanagementModelProjectReferees
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementModelProjectReferees extends JModelList
{
	var $_identifier = "preferees";
    var $_project_id = 0;


/**
 * sportsmanagementModelProjectReferees::__construct()
 * 
 * @param mixed $config
 * @return void
 */
public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'p.firstname',
				'p.lastname',
				'p.nickname',
				'p.phone',
				'p.email',
				'p.mobile',
				'pref.*',
				'pref.project_position_id',
				'u.name AS editor',
				'pref.picture'
                        );
                parent::__construct($config);
                $getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($getDBConnection);
        }


/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Initialise variables.
		//$app = JFactory::getApplication('administrator');
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context -> '.$this->context.''),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);
        
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.project_position_id', 'filter_project_position_id', '');
		$this->setState('filter.project_position_id', $temp_user_request);
        
        $value = JFactory::getApplication()->input->getUInt('limitstart', 0);
		$this->setState('list.start', $value);

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('p.lastname', 'asc');
	}

	/**
	 * sportsmanagementModelProjectReferees::getListQuery()
	 * 
	 * @return
	 */
	protected function getListQuery()
	{
		$app	= JFactory::getApplication();
		$option = JFactory::getApplication()->input->getCmd('option');
        //$search	= $this->getState('filter.search');
        //$search_nation	= $this->getState('filter.search_nation');
        //$search_project_position_id	= $this->getState('filter.project_position_id');
        
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
       
        // Create a new query object.
        $query = JFactory::getDbo()->getQuery(true);
        // Select some fields
		$query->select(implode(",",$this->filter_fields).',tp.person_id as person_id');
        // From the club table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS p');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_person_id AS tp on tp.person_id = p.id');
        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pref on pref.person_id = tp.id');
        $query->join('LEFT','#__users AS u ON u.id = pref.checked_out');
        $query->where('tp.persontype = 3');
        $query->where('p.published = 1');
        $query->where('tp.season_id = '.$this->_season_id);
        $query->where('pref.project_id = '.$this->_project_id);
        
        if ($this->getState('filter.project_position_id'))
		{
        $query->where('pref.project_position_id = '.$this->getState('filter.project_position_id'));
        }
        
        if ($this->getState('filter.search'))
		{
        $query->where('(LOWER(p.lastname) LIKE ' . JFactory::getDbo()->Quote( '%' . $this->getState('filter.search') . '%' ).
						   'OR LOWER(p.firstname) LIKE ' . JFactory::getDbo()->Quote( '%' . $this->getState('filter.search') . '%' ) .
						   'OR LOWER(p.nickname) LIKE ' . JFactory::getDbo()->Quote( '%' . $this->getState('filter.search') . '%' ) . ')');
        }

$query->order(JFactory::getDbo()->escape($this->getState('list.ordering', 'p.lastname')).' '.
                JFactory::getDbo()->escape($this->getState('list.direction', 'ASC')));
        
if ( COM_SPORTSMANAGEMENT_SHOW_QUERY_DEBUG_INFO )
        {
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'Notice');
        }

		return $query;
        

	}


	/**
	 * add the specified persons to team
	 *
	 * @param array int person ids
	 * @return int number of row inserted
	 */
	function storeAssigned($cid,$project_id)
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        $db = sportsmanagementHelper::getDBConnection();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $query = JFactory::getDbo()->getQuery(true);
        
		if (!count($cid))
        {
			return 0;
		}
        // Select some fields
        $query->select('pt.id');
        // From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pt');
        $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS r ON r.person_id = pt.id');
        $query->where('r.project_id = '.$project_id);  
        $query->where('pt.published = 1');

		$db->setQuery($query);
    
    if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
		$current = $db->loadColumn();
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
		$current = $db->loadResultArray();
}

//		$current = JFactory::getDbo()->loadResultArray();
		$added=0;
		foreach ($cid AS $pid)
		{
			if ((!isset($current)) || (!in_array($pid,$current)))
			{
				$new = JTable::getInstance('ProjectReferee','Table');
				$new->person_id=$pid;
				$new->project_id=$this->_project_id;
				if (!$new->check())
				{
					$this->setError($new->getError());
					continue;
				}
				//Get data from person
                // Select some fields
                $query->clear();
        $query->select('pl.picture');
        // From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS pl');
        $query->where('pl.id = '.$pid);  
        $query->where('pl.published = 1');
        
				JFactory::getDbo()->setQuery($query);
				$player = JFactory::getDbo()->loadObject();
				if ($player)
				{
					$new->picture = $player->picture;
				}
				if (!$new->store())
				{
					$this->setError($new->getError());
					continue;
				}
				$added++;
			}
		}
		return $added;
	}

	/**
	 * remove the specified projectreferees from project
	 *
	 * @param array projectreferee ids
	 * @return int number of row removed
	 */
	function unassign($cid)
	{
		if (!count($cid)){
			return 0;
		}
		$removed=0;
		for ($x=0; $x < count($cid); $x++)
		{
			$query='DELETE FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee WHERE id='.$cid[$x];
			JFactory::getDbo()->setQuery($query);
			if(!JFactory::getDbo()->execute())
			{
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, JFactory::getDbo()->getErrorMsg(), __LINE__);
				continue;
			}
			$removed++;
		}
		return $removed;
	}

	/**
	 * return count of projectreferees
	 *
	 * @param int project_id
	 * @return int
	 */
	function getProjectRefereesCount($project_id)
	{
	   $option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
        $query = JFactory::getDbo()->getQuery(true);
        
        // Select some fields
        $query->select('count(*) AS count');
        // From the table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee AS pr');
        $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p on p.id = pr.project_id');
        $query->where('p.id = '.$project_id);  

		JFactory::getDbo()->setQuery($query);
		return JFactory::getDbo()->loadResult();
	}


}
?>
