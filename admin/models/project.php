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
 * sportsmanagementModelProject
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelProject extends JModelAdmin
{
	static $db_num_rows = 0;
    var $_tables_to_delete = array();
  
	
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
	public function getTable($type = 'project', $prefix = 'sportsmanagementTable', $config = array()) 
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
        // Create a new query object.
        $db = JFactory::getDBO();
        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');

        //sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
        
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.project', 'project', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
        
        //$mainframe->enqueueMessage(JText::_(__FILE__.' '.__FUNCTION__. '<br><pre>'.print_r($form,true).'</pre>'),'Notice');
        $sports_type_id = $form->getValue('sports_type_id');
        
        $query = $db->getQuery(true);
        // select some fields
		$query->select('name');
		// from table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_sports_type');
        // where
        $query->where('id = '.(int) $sports_type_id);
        $db->setQuery($query);
        $result = $db->loadResult();
        
        switch ($result)
        {
            case 'COM_SPORTSMANAGEMENT_ST_TENNIS';
            break;
            default:
            $form->setFieldAttribute('use_tie_break', 'type', 'hidden');
            $form->setFieldAttribute('tennis_single_matches', 'type', 'hidden');
            $form->setFieldAttribute('tennis_double_matches', 'type', 'hidden');
            break;
        }
        
        //$mainframe->enqueueMessage(JText::_(__FILE__.' '.__FUNCTION__. ' sports_type_id<br><pre>'.print_r($result,true).'</pre>'),'Notice');
        
        switch ( JComponentHelper::getParams($option)->get('which_article_component') )
    {
        case 'com_content':
        $form->setFieldAttribute('category_id', 'type', 'category');
        $form->setFieldAttribute('category_id', 'extension', 'com_content');
        break;
        case 'com_k2':
        $form->setFieldAttribute('category_id', 'type', 'categorylistk2');

        break;
    }
        
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($option)->get('ph_logo_big',''));
        $form->setFieldAttribute('picture', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/projects');
        $form->setFieldAttribute('picture', 'type', $cfg_which_media_tool);
        
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
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.project.data', array());
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
	 * return 
	 *
	 * @param int team_id
	 * @return int
	 */
	function getProjectTeam($projectteam_id)
	{
	   $mainframe = JFactory::getApplication();
       $query = JFactory::getDbo()->getQuery(true);
       
       $query->select('t.*');
       $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t');
       $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st on st.team_id = t.id');
       $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
       
       $query->where('pt.id = ' . $projectteam_id);
       
//		$query='SELECT t.*
//				  FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team as t
//                  INNER JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team as pt
//                  on t.id = pt.team_id 
//				  WHERE pt.id='.$projectteam_id;
		JFactory::getDbo()->setQuery($query);
		return JFactory::getDbo()->loadObject();
	}
    
    /**
	 * return 
	 *
	 * @param int project_id
	 * @return int
	 */
	function getProject($project_id)
	{
	   $mainframe = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       //// Create a new query object.
//		$db = JFactory::getDbo();
		$query	= JFactory::getDbo()->getQuery(true);
        $query->select('*');
        $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project');
        $query->where('id = ' . $project_id);
        
//		$query='SELECT *
//				  FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_project
//				  WHERE id='.$project_id;
		
        JFactory::getDbo()->setQuery($query);
		return JFactory::getDbo()->loadObject();
	}

    
    /**
	 * Method to return the project teams array (id, name)
	 *
	 * @access  public
	 * @return  array
	 * @since 0.1
	 */
/*
	function getProjectTeams($project_id)
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
		//$project_id = $mainframe->getUserState($option . 'project');

		$query = '	SELECT	pt.id AS value,
							t.name AS text,
							t.short_name AS short_name,
							t.notes

					FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t
					LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = t.id
					WHERE pt.project_id = ' . $project_id . '
					ORDER BY text ASC ';

		$this->_db->setQuery($query);

		if (!$result = $this->_db->loadObjectList())
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		else
		{
			return $result;
		}
	}
*/
    
    /**
	 * @param int iDivisionId
	 * return project teams as options
	 * @return unknown_type
	 */
	function getProjectTeamsOptions($project_id,$iDivisionId=0)
	{
		$option = JRequest::getCmd('option');
		$mainframe	= JFactory::getApplication();
        //$db	= $this->getDbo();
		$query = JFactory::getDbo()->getQuery(true);
        $this->project_art_id	= $mainframe->getUserState( "$option.project_art_id", '0' );
        
		//$project_id = $mainframe->getUserState($option . 'project');
        
        if ( $this->project_art_id == 3 )
        {
            // Select some fields
		    $query->select("pt.id AS value,concat(t.lastname,' - ',t.firstname,'' ) AS text");
            // From table
		    $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_person AS t');
            $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_person_id AS st on st.person_id = t.id');
            $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
        }
        else
        {
            // Select some fields
		    $query->select('pt.id AS value');
            //$query->select('CASE WHEN CHAR_LENGTH(t.name) < 25 THEN t.name ELSE t.middle_name END AS text');
            $query->select('t.name AS text');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_team AS t');
            $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_season_team_id AS st on st.team_id = t.id');
            $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team AS pt ON pt.team_id = st.id');
        }
		
        $query->where('pt.project_id = ' . $project_id);
        
        if( $iDivisionId > 0 )  
        {
            $query->where('pt.division_id = ' . $iDivisionId);
		}
		
        $query->order('text ASC'); 

		JFactory::getDbo()->setQuery($query);
		$result = JFactory::getDbo()->loadObjectList();
		if ($result === FALSE)
		{
			JError::raiseError(0, JFactory::getDbo()->getErrorMsg());
			return false;
		}
		else
		{
			return $result;
		}
	}
    
    
    /**
	 * Method to remove projects
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	public function delete(&$pks)
	{
	$mainframe = JFactory::getApplication();
    $option = JRequest::getCmd('option');
    $success = $this->deleteProjectsData($pks);  
    
    if ( $success )
    {
    $mainframe->setUserState( "$option.pid", 0 );     
    return parent::delete($pks);
    }
         
   }
   
    /**
	 * Method to remove all project datas
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function deleteProjectsData($pk=array())
	{
	$mainframe = JFactory::getApplication();

    $query = JFactory::getDbo()->getQuery(true);
    
	$result = false;
    if (count($pk))
		{
			$cids = implode(',',$pk);
            // rounds 
            $query->clear();
            $query->select('r.id');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round as r');
            $query->where('r.project_id IN ('.implode(",",$pk).')');
            JFactory::getDBO()->setQuery($query);
            $rounds = JFactory::getDbo()->loadColumn();
            
            // matches 
            if ( $rounds )
            {
            $query->clear();
            $query->select('m.id');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match as m');
            $query->where('m.round_id IN ('.implode(",",$rounds).')');
            JFactory::getDBO()->setQuery($query);
            $matches = JFactory::getDbo()->loadColumn();
            }
            
            // project_teams 
            $query->clear();
            $query->select('p.id');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_team as p');
            $query->where('p.project_id IN ('.implode(",",$pk).')');
            JFactory::getDBO()->setQuery($query);
            $project_teams = JFactory::getDbo()->loadColumn();
            
            // project_referee 
            $query->clear();
            $query->select('p.id');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_referee as p');
            $query->where('p.project_id IN ('.implode(",",$pk).')');
            JFactory::getDBO()->setQuery($query);
            $project_referee = JFactory::getDbo()->loadColumn();
            
            // project_position 
            $query->clear();
            $query->select('p.id');
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_project_position as p');
            $query->where('p.project_id IN ('.implode(",",$pk).')');
            JFactory::getDBO()->setQuery($query);
            $project_position = JFactory::getDbo()->loadColumn();
            
            // zu löschende tabellen
            $field = 'project_id';
            $id = implode(",",$pk);
            $temp = new stdClass();
            $temp->table = '_project_position';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_project_referee';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_project_team';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_round';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_division';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            if ( $rounds )
            {
            $field= 'round_id';
            $id = implode(",",$rounds);
            $temp = new stdClass();
            $temp->table = '_match';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            }
            if ( $matches )
            {
            $field= 'match_id';
            $id = implode(",",$matches);
            $temp = new stdClass();
            $temp->table = '_match_commentary';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_match_event';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_match_player';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_match_referee';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_match_single';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_match_staff';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_match_staff_statistic';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            $temp = new stdClass();
            $temp->table = '_match_statistic';
            $temp->field = $field;
            $temp->id = $id;
            $export[] = $temp;
            }
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' export<br><pre>'.print_r($export,true).'</pre>'),'');    
            $this->_tables_to_delete = array_merge($export);
            //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' _tables_to_delete<br><pre>'.print_r($this->_tables_to_delete,true).'</pre>'),'');
            
            // jetzt starten wir das löschen
            foreach( $this->_tables_to_delete as $row_to_delete )
            {
            $query->clear();
            $query->delete()->from('#__'.COM_SPORTSMANAGEMENT_TABLE.$row_to_delete->table)->where($row_to_delete->field.' IN ('.$row_to_delete->id.')' );
            JFactory::getDbo()->setQuery($query);
            sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
            if ( self::$db_num_rows )
            {
            $mainframe->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT'.strtoupper($row_to_delete->table).'_ITEMS_DELETED',self::$db_num_rows),'');
            }    
            }
            
            

           
           
        }    
   return true;     
   } 

    /**
	 * Method to update checked project
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	public function saveshort()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
        //$show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        
        // Get the input
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        if ( !$pks )
        {
            return JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SAVE_NO_SELECT');
        }
        $post = JRequest::get('post');
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
        $mainframe->enqueueMessage(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($post, true).'</pre><br>','Notice');
        }
        
        //$result=true;
		for ($x=0; $x < count($pks); $x++)
		{
			$tblProject = & $this->getTable();
			$tblProject->id = $pks[$x];
            $tblProject->project_type	= $post['project_type'.$pks[$x]];

			if(!$tblProject->store()) 
            {
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				return false;
			}
		}
		return JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SAVE');
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
	   $mainframe = JFactory::getApplication();
       $address_parts = array();
       $post = JRequest::get('post');
       $date = time();    // aktuelles Datum
       
       //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post,true).'</pre>'),'');
       
       $data['modified'] = date('Y-m-d H:i:s', $date);
       $post['modified'] = date('Y-m-d H:i:s', $date);
       
       $data['sports_type_id'] = $data['request']['sports_type_id'];
       $data['agegroup_id'] = $data['request']['agegroup_id'];
       
       $data['fav_team'] = implode(',',$post['jform']['fav_team']);
       
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
        
       //$mainframe->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' data<br><pre>'.print_r($data,true).'</pre>'),'');
       
       //-------extra fields-----------//
        sportsmanagementHelper::saveExtraFields($post,$data['id']);
        
       // Proceed with the save
		return parent::save($data);   
    }    
    
	
}
