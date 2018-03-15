<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      projectposition.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');



/**
 * Sportsmanagement Component Positionstool Model
 *
 * @package	Sportsmanagement
 * @since	0.1
 */
class sportsmanagementModelProjectposition extends JModelAdmin
{
	var $_identifier = "pposition";
    var $_project_id = 0;
	
	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 *
	 * @return	boolean
	 * @since	1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check specific edit permission then general edit permission.
		return JFactory::getUser()->authorise('core.edit', 'com_sportsmanagement.message.'.((int) isset($data[$key]) ? $data[$key] : 0)) or parent::allowEdit($data, $key);
	}
    
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'projectposition', $prefix = 'sportsmanagementTable', $config = array()) 
	{
	$config['dbo'] = sportsmanagementHelper::getDBConnection(); 
		return JTable::getInstance($type, $prefix, $config);
	}
    
	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true) 
	{
		// Get the form.
		$form = $this->loadForm('com_sportsmanagement.projectposition', 'projectposition', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
		return $form;
	}
    
	/**
	 * Method to get the script that have to be included on the form
	 *
	 * @return string	Script files
	 */
	public function getScript() 
	{
		return 'administrator/components/com_sportsmanagement/models/forms/sportsmanagement.js';
	}
    
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData() 
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.projectposition.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
	}
    
    /**
	 * Method to save item order
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function saveorder($pks = NULL, $order = NULL)
	{
		$row =& $this->getTable();
		
		// update ordering values
		for ($i=0; $i < count($pks); $i++)
		{
			$row->load((int) $pks[$i]);
			if ($row->ordering != $order[$i])
			{
				$row->ordering=$order[$i];
				if (!$row->store())
				{
					sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
					return false;
				}
			}
		}
		return true;
	}
	
	

	/**
	 * Method to update project positions list
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function store($data)
	{
		$app = JFactory::getApplication();
        echo '<br /><pre>1~'.print_r($data,true).'~</pre><br />';
		$result=true;
		//$peid=(isset($data['project_teamslist']));
		$peid=(isset($data['project_positionslist']));
		if ($peid==null)
		{
			$query="DELETE FROM #__".COM_SPORTSMANAGEMENT_TABLE."_project_position WHERE project_id=".$data['project_id'];
		}
		else
		{
			$pidArray=$data['project_positionslist'];
			JArrayHelper::toInteger($pidArray);
			$peids=implode(",",$pidArray);
			$query="DELETE FROM #__".COM_SPORTSMANAGEMENT_TABLE."_project_position WHERE project_id=".$data['project_id']." AND position_id NOT IN ($peids)";
		}
		$this->_db->setQuery($query);
		if (!$this->_db->execute())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			$result=false;
		}
		for ($x=0; $x < count($data['project_positionslist']); $x++)
		{
			$query="INSERT IGNORE INTO #__".COM_SPORTSMANAGEMENT_TABLE."_project_position (project_id,position_id) VALUES ('".$data['project_id']."','".$data['project_positionslist'][$x]."')";
			$this->_db->setQuery($query);
			if(!$this->_db->execute())
			{
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				$result=false;
			}
		}
		return $result;
	}
	
	/**
	 * Method to return the project positions array (id,name)
	 *
	 * @access  public
	 * @return  array
	 * @since 0.1
	 */
	function getProjectPositions()
	{
		$app = JFactory::getApplication();
		$project_id=$app->getUserState('com_joomleagueproject');
		$query='	SELECT	p.id AS value,
							p.name AS text,
							p.sports_type_id AS type,
							p.parent_id AS parentID
					FROM #__joomleague_position AS p
					LEFT JOIN #__joomleague_project_position AS pp ON pp.position_id=p.id
					WHERE pp.project_id='.$project_id.'
					ORDER BY p.parent_id ASC,p.name ASC ';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		return $result;
	}

	/**
	* Method to assign positions of an existing project to a copied project
	*
	* @access  public
	* @return  array
	* @since 0.1
	*/
	function cpCopyPositions($post)
	{
		$old_id=(int)$post['old_id'];
		$project_id=(int)$post['id'];
		//copy positions
		$query="SELECT * FROM #__".COM_SPORTSMANAGEMENT_TABLE."_project_position WHERE project_id=".$old_id;
		$this->_db->setQuery($query);
		if ($results=$this->_db->loadAssocList())
		{
			foreach($results as $result)
			{
				$p_position =& $this->getTable();
				$p_position->bind($result);
				$p_position->set('id',NULL);
				$p_position->set('project_id',$project_id);
				if (!$p_position->store())
				{
					echo $this->_db->getErrorMsg();
					return false;
				}
				$newid = $this->getDbo()->insertid();
				$query = "UPDATE #__".COM_SPORTSMANAGEMENT_TABLE."_team_player " . 
							"SET project_position_id = " . $newid .
							" WHERE project_position_id = " . $result['id'];
				$this->_db->setQuery($query);
				if(!$this->_db->execute())
				{
					sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
					$result=false;
				}	
			}
		}
		return true;
	}
	
	/**
	 * return count of projectpositions
	 *
	 * @param int project_id
	 * @return int
	 */
	function getProjectPositionsCount($project_id)
	{
		$query='SELECT count(*) AS count
		FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position AS pp
		JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project AS p on p.id = pp.project_id
		WHERE p.id='.$project_id;
		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}
	
}
?>