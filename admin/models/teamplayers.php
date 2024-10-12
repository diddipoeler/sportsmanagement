<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teamplayers
 * @file       teamplayers.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementModelteamplayers
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2013
 * @access    public
 */
class sportsmanagementModelteamplayers extends JSMModelList
{
	static $db_num_rows = 0;
	var $_identifier = "teamplayers";
	var $_project_id = 0;
	var $_season_id = 0;
	var $_team_id = 0;
	var $_project_team_id = 0;
	var $_persontype = 0;

	/**
	 * sportsmanagementModelteamplayers::__construct()
	 *
	 * @param   mixed  $config
	 *
	 * @return void
	 */
	public function __construct($config = array())
	{
		$config['filter_fields'] = array(
			'ppl.lastname',
			'tp.person_id',
			'ppl.position_id',
			'ppl.published',
			'ppl.ordering',
			'ppl.picture',
			'ppl.id',
			'tp.market_value','tp.market_text',
			'tp.jerseynumber','state'
		);
		parent::__construct($config);
		$getDBConnection = sportsmanagementHelper::getDBConnection();
		parent::setDbo($getDBConnection);
	}
	
	/**
	 * sportsmanagementModelteamplayers::getprojectpublished()
	 * 
	 * @param mixed $items
	 * @return
	 */
	function getprojectpublished($items = NULL)
	{
		//echo '<pre>'.print_r($items,true).'</pre>';
      
      foreach ($items as $count_i => $item)
	{
        $this->jsmquery->clear();
        $this->jsmquery->select('ppp.published');
		$this->jsmquery->from('#__sportsmanagement_person_project_position AS ppp');
		$this->jsmquery->where('ppp.person_id = '. $item->person_id);
		$this->jsmquery->where('ppp.project_id = ' . $this->_project_id);
		$this->jsmquery->where('ppp.persontype = ' . $this->getState('filter.persontype'));
        $this->jsmdb->setQuery($this->jsmquery);
		$item->project_published = $this->jsmdb->loadResult();
        
      }
		
	return $items;	
	}

	function getprojectposition($items = NULL)
	{
		foreach ($items as $count_i => $item)
		{
			$this->jsmquery->clear();
			$this->jsmquery->select('ppp.project_position_id');
			$this->jsmquery->from('#__sportsmanagement_person_project_position AS ppp');
			$this->jsmquery->where('ppp.person_id = '. $item->person_id);
			$this->jsmquery->where('ppp.project_id = ' . $this->_project_id);
			//$this->jsmquery->where('ppp.persontype = ' . $this->getState('filter.persontype'));
			$this->jsmdb->setQuery($this->jsmquery);
			$item->project_position_id = $this->jsmdb->loadResult();
		}
		return $items;	
	}

	/**
	 * sportsmanagementModelteamplayers::getListQuery()
	 *
	 * @return
	 */
	function getListQuery()
	{
		$this->jsmquery->clear();

		$this->_project_id = $this->jsmapp->getUserState("$this->jsmoption.pid", '0');

		$this->jsmquery->select('ppl.id,ppl.firstname,ppl.lastname,ppl.nickname,ppl.picture,ppl.id as person_id,ppl.injury,ppl.suspension,ppl.away,ppl.ordering,ppl.checked_out,ppl.checked_out_time  ');
		$this->jsmquery->select('ppl.position_id as person_position_id');
		$this->jsmquery->select('tp.id as tpid, tp.market_text, tp.market_value, tp.jerseynumber,tp.picture as season_picture,tp.published,tp.tt_startpoints');
		$this->jsmquery->select('u.name AS editor');
		$this->jsmquery->select('st.season_id AS season_id,st.id as projectteam_id');
		$this->jsmquery->select('ppl.country');

		$this->jsmquery->from('#__sportsmanagement_person AS ppl');
		$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_person_id AS tp on tp.person_id = ppl.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id and st.season_id = tp.season_id');
		$this->jsmquery->join('LEFT', '#__users AS u ON u.id = tp.checked_out');
		$this->jsmquery->where('ppl.published = 1');
		$this->jsmquery->where('st.team_id = ' . $this->getState('filter.team_id'));
		$this->jsmquery->where('st.season_id = ' . $this->getState('filter.season_id'));
		$this->jsmquery->where('tp.season_id = ' . $this->getState('filter.season_id'));
		$this->jsmquery->where('tp.persontype = ' . $this->getState('filter.persontype'));

		$this->jsmsubquery1->clear();
		$this->jsmsubquery1->select('ppos.id');
		$this->jsmsubquery1->from('#__sportsmanagement_project_position AS ppos');
		$this->jsmsubquery1->join('LEFT', '#__sportsmanagement_person_project_position AS ppp on ppp.project_position_id = ppos.id');
		$this->jsmsubquery1->where('ppp.person_id = ppl.id');
		$this->jsmsubquery1->where('ppp.project_id = ' . $this->_project_id);
		$this->jsmsubquery1->where('ppp.persontype = ' . $this->getState('filter.persontype'));
		$this->jsmquery->select('(' . $this->jsmsubquery1 . ') AS project_position_id');

		$this->jsmsubquery2->clear();
		$this->jsmsubquery2->select('ppp.published');
		$this->jsmsubquery2->from('#__sportsmanagement_person_project_position AS ppp');
		$this->jsmsubquery2->where('ppp.person_id = ppl.id');
		$this->jsmsubquery2->where('ppp.project_id = ' . $this->_project_id);
		$this->jsmsubquery2->where('ppp.persontype = ' . $this->getState('filter.persontype'));
		//$this->jsmquery->select('(' . $this->jsmsubquery2 . ') AS project_published');

		if (is_numeric($this->getState('filter.state')))
		{
			$this->jsmquery->where('tp.published = ' . $this->getState('filter.state'));
		}

		if ($this->getState('filter.search'))
		{
			$this->jsmquery->where(
				'(LOWER(ppl.lastname) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') .
				'OR LOWER(ppl.firstname) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') .
				'OR LOWER(ppl.nickname) LIKE ' . $this->jsmdb->Quote('%' . $this->getState('filter.search') . '%') . ')'
			);
		}

		$this->jsmquery->order(
			$this->jsmdb->escape($this->getState('list.ordering', 'ppl.lastname')) . ' ' .
			$this->jsmdb->escape($this->getState('list.direction', 'ASC'))
		);

		return $this->jsmquery;
	}

	
	/**
	 * sportsmanagementModelteamplayers::PersonProjectPosition()
	 * 
	 * @param mixed $project_id
	 * @param mixed $_persontype
	 * @return
	 */
	function PersonProjectPosition($project_id, $_persontype)
	{
	   $result = array();
		$this->jsmquery->clear();
		$this->jsmquery->select('ppl.*');
		$this->jsmquery->from('#__sportsmanagement_person_project_position AS ppl');
		$this->jsmquery->where('ppl.project_id = ' . $project_id);
		$this->jsmquery->where('ppl.persontype = ' . $_persontype);
		$this->jsmdb->setQuery($this->jsmquery);
		$result = $this->jsmdb->loadObjectList();

		if (!$result)
		{
			return $result;
		}

		return $result;

	}


	/**
	 * sportsmanagementModelteamplayers::checkProjectPositions()
	 * 
	 * @param mixed $project_id
	 * @param mixed $persontype
	 * @param mixed $team_id
	 * @param mixed $season_id
	 * @param integer $insert
	 * @return
	 */
	function checkProjectPositions($project_id, $persontype, $team_id, $season_id, $insert = 1)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Create a new query object.
		// $db   = sportsmanagementHelper::getDBConnection();
		$db          = sportsmanagementHelper::getDBConnection();
		$query       = $db->getQuery(true);
		$date        = Factory::getDate();
		$user        = Factory::getUser();
		$modified    = $date->toSql();
		$modified_by = $user->get('id');

		/**
		 * tabelle: sportsmanagement_person_project_position
		 * feld import_id einfügen
		 */
		$jsm_table = '#__sportsmanagement_person_project_position';

		try
		{
			$query = $db->getQuery(true);
			$query->clear();
			$query = "ALTER TABLE `" . $jsm_table . "` ADD `import_id` INT(11) NOT NULL DEFAULT '0' ";
			$db->setQuery($query);
			sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);

			// $result = $db->execute();
		}
		catch (Exception $e)
		{
			//    // catch any database errors.
			//    $db->transactionRollback();
			//    JErrorPage::render($e);
		}

		// Select some fields
		$query = $db->getQuery(true);
		$query->clear();
		$query->select('stp.person_id,ppos.id as project_position_id');
		$query->from('#__sportsmanagement_season_team_person_id as stp');
		$query->join('INNER', '#__sportsmanagement_person AS p ON p.id = stp.person_id');

		$query->join('INNER', '#__sportsmanagement_project_position AS ppos ON ppos.position_id = p.position_id');

		$query->where('stp.team_id = ' . $team_id);
		$query->where('stp.season_id = ' . $season_id);
		$query->where('stp.persontype = ' . $persontype);
		$query->where('ppos.project_id = ' . $project_id);

		try
		{
			$db->setQuery($query);
			$result = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			// Catch any database errors.
			$result = false;

			//    $db->transactionRollback();
			//    JErrorPage::render($e);
		}

		if ($result)
		{
			foreach ($result as $row)
			{
				$query->clear();
				$query->select('person_id');
				$query->from('#__sportsmanagement_person_project_position');
				$query->where('person_id = ' . $row->person_id);
				$query->where('project_id = ' . $project_id);
				$query->where('project_position_id = ' . $row->project_position_id);
				$query->where('persontype = ' . $persontype);
				$db->setQuery($query);
				$resultcheck = $db->loadResult();

				if (!$resultcheck)
					// Projekt position eintragen
				{
					// Create a new query object.
					$insertquery = $db->getQuery(true);

					// Insert columns.
					$columns = array('person_id', 'project_id', 'project_position_id', 'persontype', 'import_id');

					// Insert values.
					$values = array($row->person_id, $project_id, $row->project_position_id, $persontype, 1);

					// Prepare the insert query.
					$insertquery
						->insert($db->quoteName('#__sportsmanagement_person_project_position'))
						->columns($db->quoteName($columns))
						->values(implode(',', $values));

					// Set the query using our newly populated query object and execute it.
					$db->setQuery($insertquery);

					if ($insert)
					{
						if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
						{
						}
						else
						{
						}
					}
				}
			}

			return true;
		}
		else
		{
			return false;
		}

	}

	
	
	
	/**
	 * sportsmanagementModelteamplayers::getTeamplayersMatch()
	 * 
	 * @param integer $team_id
	 * @param integer $season_id
	 * @param integer $projectteam_id
	 * @param integer $project_id
	 * @param integer $match_id
	 * @return
	 */
	function getTeamplayersMatch($team_id = 0, $season_id = 0, $projectteam_id = 0, $project_id = 0, $match_id = 0)
	{
$result = array();
$players_count = array();

$this->jsmquery->clear();
			$this->jsmquery->select('tp.id');
			$this->jsmquery->from('#__sportsmanagement_season_team_person_id AS tp ');
			$this->jsmquery->join('LEFT', '#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id and st.season_id = tp.season_id');
			$this->jsmquery->join('LEFT', '#__sportsmanagement_project_team AS pthome ON pthome.team_id = st.id');
			$this->jsmquery->where('pthome.id =' . $projectteam_id);
			$this->jsmquery->where('tp.season_id = ' . $season_id);
			$this->jsmquery->where('tp.persontype = 1');
            try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->loadColumn();
}
		catch (Exception $e)
		{
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
		}
        
			if ($result)
			{
				$players = implode(",", $result);

				/** Count match */
				$this->jsmquery->clear();
				$this->jsmquery->select('mp.teamplayer_id, mp.project_position_id, pos.name as project_position_name');
				$this->jsmquery->from('#__sportsmanagement_match_player AS mp  ');
                //$this->jsmquery->join('INNER', '#__sportsmanagement_project_position AS ppos ON ppos.id = mp.project_position_id');
                $this->jsmquery->join('INNER', '#__sportsmanagement_position AS pos ON pos.id = mp.project_position_id');
				$this->jsmquery->where('mp.match_id = ' . $match_id . ' AND (came_in=0 OR came_in=1) AND mp.teamplayer_id in (' . $players . ')');
                try
		{
				$this->jsmdb->setQuery($this->jsmquery);
				$players_count = $this->jsmdb->loadObjectList();
                }
		catch (Exception $e)
		{
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
		}
                
                
                
			}
       
       
       
       return $players_count;
       
       }
	
	/**
	 * sportsmanagementModelteamplayers::getProjectTeamplayers()
	 * 
	 * @param integer $team_id
	 * @param integer $season_id
	 * @param integer $projectteam_id
	 * @param integer $generate
	 * @param integer $project_id
	 * @return
	 */
	function getProjectTeamplayers($team_id = 0, $season_id = 0, $projectteam_id = 0, $generate = 0, $project_id = 0)
	{
	   $result = array();
//$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' team_id -> ' . $team_id . ''), '');
//$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' season_id -> ' . $season_id . ''), '');
//$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' projectteam_id -> ' . $projectteam_id . ''), '');
        	   
        $this->jsmquery->clear();
		$this->jsmquery->select('ppl.*,tp.id as season_team_person_id');
		$this->jsmquery->from('#__sportsmanagement_person AS ppl');
		$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_person_id AS tp on tp.person_id = ppl.id');
		$this->jsmquery->join('INNER', '#__sportsmanagement_season_team_id AS st on st.team_id = tp.team_id');
        if ( $team_id )
        {
		$this->jsmquery->where('st.team_id IN (' . $team_id . ')');
        }
        
        if ( $projectteam_id )
        {
        $this->jsmquery->join('INNER', '#__sportsmanagement_project_team AS pt on pt.team_id = st.id');
        $this->jsmquery->where('pt.id = ' . $projectteam_id);    
        }
		$this->jsmquery->where('st.season_id = ' . $season_id);
		$this->jsmquery->where('tp.season_id = ' . $season_id);

if ( $generate )
{
        $this->jsmsubquery1->clear();
		$this->jsmsubquery1->select('ppos.id');
		$this->jsmsubquery1->from('#__sportsmanagement_project_position AS ppos');
		$this->jsmsubquery1->join('LEFT', '#__sportsmanagement_person_project_position AS ppp on ppp.project_position_id = ppos.id');
		$this->jsmsubquery1->where('ppp.person_id = ppl.id');
		$this->jsmsubquery1->where('ppp.project_id = ' . $project_id);
		$this->jsmsubquery1->where('ppp.persontype = 1');
		$this->jsmquery->select('(' . $this->jsmsubquery1 . ') AS project_position_id');
        
$this->jsmsubquery1->clear();
$this->jsmsubquery1->select('pos.name');
		$this->jsmsubquery1->from('#__sportsmanagement_position AS pos');
$this->jsmsubquery1->join('LEFT', '#__sportsmanagement_project_position AS ppp on ppp.position_id = pos.id');
$this->jsmsubquery1->where('ppp.project_id = ' . $project_id);
$this->jsmsubquery1->where('ppp.id = project_position_id' );
$this->jsmquery->select('(' . $this->jsmsubquery1 . ') AS project_position_name');
        
        }
        
		try
		{
			$this->jsmdb->setQuery($this->jsmquery);
			$result = $this->jsmdb->loadObjectList();
		}
		catch (Exception $e)
		{
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'notice');
$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'notice');
		}

if ( Factory::getConfig()->get('debug') )
{  
Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' query' . '<pre>'.print_r($this->jsmquery->dump(),true).'</pre>' ), Log::NOTICE, 'jsmerror');
}




		return $result;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since 1.6
	 */
	protected function populateState($ordering = 'ppl.lastname', $direction = 'asc')
	{
		if (ComponentHelper::getParams($this->jsmoption)->get('show_debug_info_backend'))
		{
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' context -> ' . $this->context . ''), '');
			$this->jsmapp->enqueueMessage(Text::_(__METHOD__ . ' ' . __LINE__ . ' identifier -> ' . $this->_identifier . ''), '');
		}
		$list = $this->getUserStateFromRequest($this->context . '.list', 'list', array(), 'array');

		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search'));
		$this->setState('filter.state', $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state', '', 'string'));

		if (Factory::getApplication()->input->getVar('team_id'))
		{
			$this->setState('filter.team_id', Factory::getApplication()->input->getVar('team_id'));
		}
		else
		{
			$this->setState('filter.team_id', $this->jsmapp->getUserState("$this->jsmoption.team_id", '0'));
		}

		if (Factory::getApplication()->input->getVar('persontype'))
		{
			$this->setState('filter.persontype', Factory::getApplication()->input->getVar('persontype'));
		}
		else
		{
			$this->setState('filter.persontype', $this->jsmapp->getUserState("$this->jsmoption.persontype", '0'));
		}

		if (Factory::getApplication()->input->getVar('project_team_id'))
		{
			$this->setState('filter.project_team_id', Factory::getApplication()->input->getVar('project_team_id'));
		}
		else
		{
			$this->setState('filter.project_team_id', $this->jsmapp->getUserState("$this->jsmoption.project_team_id", '0'));
		}

		$this->setState('filter.pid', $this->jsmapp->getUserState("$this->jsmoption.pid", '0'));
		$this->setState('filter.season_id', $this->jsmapp->getUserState("$this->jsmoption.season_id", '0'));
		$this->setState('list.limit', $this->getUserStateFromRequest($this->context . '.list.limit', 'list_limit', $this->jsmapp->get('list_limit'), 'int'));
		$this->setState('list.start', $this->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0, 'int'));

		$orderCol = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');

		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'ppl.lastname';
		}

		$this->setState('list.ordering', $orderCol);
		$listOrder = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');

		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}

		$this->setState('list.direction', $listOrder);

	}


}
