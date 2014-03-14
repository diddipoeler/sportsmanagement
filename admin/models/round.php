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
 * sportsmanagementModelround
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelround extends JModelAdmin
{
    var $_identifier = "rounds";
    
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
	public function getTable($type = 'round', $prefix = 'sportsmanagementTable', $config = array()) 
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
        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
		// Get the form.
		$form = $this->loadForm('com_sportsmanagement.round', 'round', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
        
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($option)->get('ph_team',''));
        $form->setFieldAttribute('picture', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/rounds');
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
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.round.data', array());
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
	 * Method to update checked project rounds
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	public function saveshort()
	{
		$mainframe =& JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
        //$show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        
        // Get the input
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        if ( !$pks )
        {
            return JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_SAVE_NO_SELECT');
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
			$tblRound = & $this->getTable();
			$tblRound->id = $pks[$x];
            $tblRound->roundcode	= $post['roundcode'.$pks[$x]];
			$tblRound->name	= $post['name'.$pks[$x]];
            $tblRound->round_date_first	= sportsmanagementHelper::convertDate($post['round_date_first'.$pks[$x]], 0);
            $tblRound->round_date_last	= sportsmanagementHelper::convertDate($post['round_date_last'.$pks[$x]], 0);;

			if(!$tblRound->store()) 
            {
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				return false;
			}
		}
		return JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_SAVE');
	}
    
	
    function massadd()
	{
	$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
    
    //$show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info_'.$this->_identifier,0) ;
    
    $post = JRequest::get('post');
    $project_id	= $mainframe->getUserState( "$option.pid", '0' );
    $add_round_count = (int)$post['add_round_count'];
    
        $max=0;
		if ($add_round_count > 0) // Only MassAdd a number of new and empty rounds
		{
			$max = $this->getMaxRound($project_id);
			$max++;
			$i=0;
			for ($x=0; $x < $add_round_count; $x++)
			{
				$i++;
                $tblRound =& $this->getTable();
                $tblRound->project_id = $project_id;
				$tblRound->roundcode = $max;
				$tblRound->name = JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_CTRL_ROUND_NAME',$max);

				if ( $tblRound->store() )
				{
					$msg = JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_CTRL_ROUNDS_ADDED',$i);
				}
				else
				{
					$msg = JText::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_CTRL_ERROR_ADD').$this->_db->getErrorMsg();
				}
				$max++;
			}
		}
        
    
    if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
    {
 	$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($add_round_count,true).'</pre>'   ),'');
    $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($project_id,true).'</pre>'   ),'');
    }
    
    return $msg;
       
    }
    
    /**
	 * return 
	 *
	 * @param int project_id
	 * @return int
	 */
    function getMaxRound($project_id)
	{
	   // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        // select some fields
		$query->select('COUNT(roundcode)');
		// from table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
        // where
        $query->where('project_id = '.(int) $project_id);
        
		$result = 0;
		if ($project_id > 0)
		{
			$db->setQuery($query);
			$result = $db->loadResult();
		}
		return $result;
	}
    
   
   
   function getRoundcode($round_id)
   {
    // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        // select some fields
		$query->select('roundcode');
		// from table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
        // where
        $query->where('id = '.$round_id);
        

		$db->setQuery($query);
		return $db->loadResult();
    
   }
   
    /**
	 * 
	 * @param $roundcode
	 * @param $project_id
	 */
	function getRoundId($roundcode, $project_id)
	{
	   // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        // select some fields
		$query->select('id');
		// from table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
        // where
        $query->where('roundcode = '.$roundcode);
        $query->where('project_id = '.(int) $project_id);
        
		$db->setQuery($query);
		$result = $db->loadResult();
		return $result;
	}
    
    /**
	 * return 
	 *
	 * @param int round_id
	 * @return int
	 */
	function getRound($round_id)
	{
	   // Get a db connection.
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        // select some fields
		$query->select('*');
		// from table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_round');
        // where
        $query->where('id = '.$round_id);

		$db->setQuery($query);
		return $db->loadObject();
	}
    
    
    /**
	 * Method to remove matchdays from round
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	public function deleteRoundMatches($pks)
	{
	$mainframe =& JFactory::getApplication();
    $mainframe->enqueueMessage(JText::_('delete pks<br><pre>'.print_r($pks,true).'</pre>'),'');
    /* Ein Datenbankobjekt beziehen */
    $db = JFactory::getDbo();
    /* Ein JDatabaseQuery Objekt beziehen */
    $query = $db->getQuery(true);
    
	//$result = false;
    if (count($pks))
		{
			//JArrayHelper::toInteger($cid);
			$cids = implode(',',$pks);
            $mainframe->enqueueMessage(JText::_('delete cids<br><pre>'.print_r($cids,true).'</pre>'),'');
            // wir löschen mit join
            $query = 'DELETE m,ms,mc,mss,mst,mev,mre,mpl
            FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_match as m    
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic as ms
            ON ms.match_id = m.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_commentary as mc
            ON m.id = mc.match_id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff_statistic as mss
            ON mss.match_id = m.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff as mst
            ON mst.match_id = m.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event as mev
            ON mev.match_id = m.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee as mre
            ON mre.match_id = m.id
            LEFT JOIN #__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player as mpl
            ON mpl.match_id = m.id
            WHERE m.round_id IN ('.$cids.')';
            $db->setQuery($query);
            $db->query();
            if (!$db->query()) 
            {
                $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
                return false; 
            }
            
        }    
   return true;     
   }
    
   /**
	 * Method to remove rounds
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	public function delete(&$pks)
	{
	$mainframe =& JFactory::getApplication();
    
    if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
    {
    $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($pks,true).'</pre>'),'');
    }
    
    $success = $this->deleteRoundMatches($pks);  
    
    if ( $success )
    {        
    return parent::delete($pks);
    }
         
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
       $post=JRequest::get('post');
       
       //$mainframe->enqueueMessage(JText::_('sportsmanagementModelplayground save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
       //$mainframe->enqueueMessage(JText::_('sportsmanagementModelplayground post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       if (isset($post['extended']) && is_array($post['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string)$parameter;
		}
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelplayground save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        // Proceed with the save
		return parent::save($data);   
    } 
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
	
}
