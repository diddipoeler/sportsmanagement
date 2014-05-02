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

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 

/**
 * sportsmanagementModelteam
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelteam extends JModelAdmin
{
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
	public function getTable($type = 'team', $prefix = 'sportsmanagementTable', $config = array()) 
	{
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
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $db		= $this->getDbo();
        $query = $db->getQuery(true);
        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
        
        $show_team_community = JComponentHelper::getParams($option)->get('show_team_community',0);
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');
        
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.team', 'team', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
        
        if ( !$show_team_community )
        {
            $form->setFieldAttribute('merge_clubs', 'type', 'hidden');
        }
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($option)->get('ph_team',''));
        $form->setFieldAttribute('picture', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/teams');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        
        $prefix = $mainframe->getCfg('dbprefix');
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' prefix<br><pre>'.print_r($prefix,true).'</pre>'),'');
        //$whichtabel = $this->getTable();
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' whichtabel<br><pre>'.print_r($whichtabel,true).'</pre>'),'');
        
        $query->select('*');
			$query->from('information_schema.columns');
            $query->where("TABLE_NAME LIKE '".$prefix."sportsmanagement_team' ");
			
			$db->setQuery($query);
            
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' dump<br><pre>'.print_r($query->dump(),true).'</pre>'),'');
            
			$result = $db->loadObjectList();
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' result<br><pre>'.print_r($result,true).'</pre>'),'');
            
            foreach($result as $field )
        {
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' COLUMN_NAME<br><pre>'.print_r($field->COLUMN_NAME,true).'</pre>'),'');
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' DATA_TYPE<br><pre>'.print_r($field->DATA_TYPE,true).'</pre>'),'');
            //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' CHARACTER_MAXIMUM_LENGTH<br><pre>'.print_r($field->CHARACTER_MAXIMUM_LENGTH,true).'</pre>'),'');
            
            switch ($field->COLUMN_NAME)
            {
                case 'country':
                case 'merge_clubs':
                break;
                default:
            switch ($field->DATA_TYPE)
            {
                case 'varchar':
                $form->setFieldAttribute($field->COLUMN_NAME, 'size', $field->CHARACTER_MAXIMUM_LENGTH);
                break;
            }
            break;
            }
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
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.team.data', array());
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
    
    
    function getTeamLogo($team_id)
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
        
        // Select some fields
		$query->select('c.logo_small,c.country');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team t');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_club c ON c.id = t.club_id');
        $query->where('t.id = '.$team_id);
        

        $db->setQuery( $query );
        $result = $db->loadObjectList();

        return $result;
    }
    
    /**
	 * return 
	 *
	 * @param int team_id
	 * @return int
	 */
	function getTeam($team_id=0,$pro_team_id=0)
	{
	   $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);
        // Select some fields
		$query->select('t.*');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team t');
        
        if ( $team_id)
        {
        $query->where('t.id = '.$team_id);
        }
        else
        {
        $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st on st.team_id = t.id');
//        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pthome ON pthome.team_id = st.id');   
        $query->where('st.id = '.$pro_team_id); 
        }

		$db->setQuery($query);
		return $db->loadObject();
	}
    
    /**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
	   $option = JRequest::getCmd('option');
	$mainframe	= JFactory::getApplication();
    // Get a db connection.
        $db = JFactory::getDbo();
       $post=JRequest::get('post');
       
       //$mainframe->enqueueMessage(JText::_('sportsmanagementModelteam save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
       //$mainframe->enqueueMessage(JText::_('sportsmanagementModelteam post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       if (isset($data['season_ids']) && is_array($data['season_ids'])) 
		{
		  foreach( $data['season_ids'] as $key => $value )
          {
          
        // Create a new query object.
        $query = $db->getQuery(true);
        // Insert columns.
        $columns = array('team_id','season_id');
        // Insert values.
        $values = array($data['id'],$value);
        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        // Set the query using our newly populated query object and execute it.
        $db->setQuery($query);

		if (!$db->query())
		{
//            $mainframe->enqueueMessage(JText::_('sportsmanagementModelteam save<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
		}  
          
          }
		//$mdl = JModel::getInstance("seasonteam", "sportsmanagementModel");
		}
        
       if (isset($post['extended']) && is_array($post['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string)$parameter;
		}
        
        if (isset($post['extendeduser']) && is_array($post['extendeduser'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extendeduser']);
			$data['extendeduser'] = (string)$parameter;
		}
        
        if ( $post['delete'] )
        {
            self::DeleteTrainigData($post['delete'][0]);
        }
        
        if ( $post['tdids'] )
        {
            self::UpdateTrainigData($post);
        }
        
        if ( $post['add_trainingData'] )
        {
            self::addNewTrainigData($data[id]);
        }
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelteam save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        //-------extra fields-----------//
        sportsmanagementHelper::saveExtraFields($post,$data['id']);
        // Proceed with the save
		return parent::save($data);   
    }
    
    /**
	* Method to delete team trainingdata
	*
	* @access	public
	* @return	array
	* @since	0.1
	*/
    function DeleteTrainigData($id)
    {
        $option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
    $db = JFactory::getDbo();
 
$query = $db->getQuery(true);
 
// delete all custom keys
$conditions = array(
    $db->quoteName('id') . '='.$id
);
 
$query->delete($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_trainingdata'));
$query->where($conditions);
 
$db->setQuery($query);    
if (!$db->query())
		{
			
            $mainframe->enqueueMessage(JText::_('sportsmanagementModelteam DeleteTrainigData<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
			$result = false;
		}
     return true;           
    }
        
    /**
	* Method to update team trainingdata
	*
	* @access	public
	* @return	array
	* @since	0.1
	*/
    function UpdateTrainigData($post)
    {
        $option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
        //$db		= $this->getDbo();
		//$query	= $db->getQuery(true);
    
    for($a=0; $a < count($post['tdids']); $a++ )    
    {
        $db		= $this->getDbo();
		$query	= $db->getQuery(true);
    // Fields to update.
    $fields = array(
    $db->quoteName('time_start') .'=\''. $post['time_start'][$post['tdids'][$a]].'\'',
    $db->quoteName('time_end') .'=\''. $post['time_end'][$post['tdids'][$a]].'\'',
    $db->quoteName('place') .'=\''. $post['place'][$post['tdids'][$a]].'\'',
    $db->quoteName('notes') .'=\''. $post['notes'][$post['tdids'][$a]].'\'',
    $db->quoteName('dayofweek') .'=\''. $post['dayofweek'][$post['tdids'][$a]].'\''
    );
 
    // Conditions for which records should be updated.
    $conditions = array(
    $db->quoteName('id') .'='. $post['tdids'][$a]
    );
 
    $query->update($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_trainingdata'))->set($fields)->where($conditions);
 
    $db->setQuery($query);
    //$mainframe->enqueueMessage(JText::_('sportsmanagementModelteam UpdateTrainigData<br><pre>'.print_r($query,true).'</pre>'),'');
 
    if (!$db->query())
		{
			
            $mainframe->enqueueMessage(JText::_('sportsmanagementModelteam UpdateTrainigData<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
			$result = false;
		}
    
    }   
    return true; 
    }
    
    /**
	* Method to return a team trainingdata array
	*
	* @access	public
	* @return	array
	* @since	0.1
	*/
	function getTrainigData($team_id=0,$pro_team_id=0)
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
        $db		= $this->getDbo();
		$query	= $db->getQuery(true);
        // Select some fields
		$query->select('tt.*');
        // From table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_trainingdata as tt');
        
        if ( $team_id)
        {
        $query->where('tt.team_id = '.$team_id);
        }
        else
        {
        $query->join('INNER', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st on st.team_id = tt.team_id');
//        $query->join('INNER','#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pthome ON pthome.team_id = st.id');   
        $query->where('st.id = '.$pro_team_id); 
        }
        
        
        $query->order('dayofweek ASC');
        $db->setQuery($query);
		
		if (!$result = $db->loadObjectList())
		{
			$mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_TITLE_NO_TRAINING'),'Notice');
            
            //$mainframe->enqueueMessage(JText::_('sportsmanagementModelteam getTrainigData<br><pre>'.print_r($query,true).'</pre>'),'Error');
            //$mainframe->enqueueMessage(JText::_('sportsmanagementModelteam getTrainigData<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
			return false;
		}
		return $result;
	}

	/**
	* Method to add a team trainingdata 
	*
	* @access	public
	* @return	array
	* @since	0.1
	*/
    function addNewTrainigData($team_id)
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
        // Get a db connection.
        $db = JFactory::getDbo();
        // Create a new query object.
        $query = $db->getQuery(true);
        // Insert columns.
        $columns = array('team_id');
        // Insert values.
        $values = array($team_id);
        // Prepare the insert query.
        $query
            ->insert($db->quoteName('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team_trainingdata'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));
        // Set the query using our newly populated query object and execute it.
        $db->setQuery($query);

		if (!$db->query())
		{
			
            $mainframe->enqueueMessage(JText::_('sportsmanagementModelteam addNewTrainigData<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
			$result = false;
		}
        $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_P_TEAM_TITLE_INSERT_TRAINING'),'Notice');
		return true;
	}
    
    
    
    
	
}
