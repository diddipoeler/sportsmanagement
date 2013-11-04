<?php
/**
 * @copyright	Copyright (C) 2013 fussballineuropa.de. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');



/**
 * Sportsmanagement Component Match Model
 *
 * @author	diddipoeler
 * @package	Sportsmanagement
 * @since	0.1
 */

class sportsmanagementModelMatch extends JModelAdmin
{

	const MATCH_ROSTER_STARTER			= 0;
	const MATCH_ROSTER_SUBSTITUTE_IN	= 1;
	const MATCH_ROSTER_SUBSTITUTE_OUT	= 2;
	const MATCH_ROSTER_RESERVE			= 3;

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
	public function getTable($type = 'match', $prefix = 'sportsmanagementTable', $config = array()) 
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
		// Get the form.
		$form = $this->loadForm('com_sportsmanagement.match', 'match', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.match.data', array());
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
	function saveorder($cid=array(),$order)
	{
		$row =& $this->getTable();
		
		// update ordering values
		for ($i=0; $i < count($cid); $i++)
		{
			$row->load((int) $cid[$i]);
			if ($row->ordering != $order[$i])
			{
				$row->ordering=$order[$i];
				if (!$row->store())
				{
					$this->setError($this->_db->getErrorMsg());
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
	function saveshort()
	{
		$mainframe =& JFactory::getApplication();
        $show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        // Get the input
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        $post = JRequest::get('post');
        
        if ( $show_debug_info )
        {
        $mainframe->enqueueMessage('saveshort pks<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
        $mainframe->enqueueMessage('saveshort post<br><pre>'.print_r($post, true).'</pre><br>','Notice');
        }
        
        $result=true;
		for ($x=0; $x < count($pks); $x++)
		{
			$tblMatch = & $this->getTable();
			$tblMatch->id = $pks[$x];
			$tblMatch->match_number	= $post['match_number'.$pks[$x]];
            
            $tblMatch->match_date = $post['match_date'.$pks[$x]];
            $tblMatch->match_time = $post['match_time'.$pks[$x]];
            $tblMatch->crowd = $post['crowd'.$pks[$x]];
            $tblMatch->round_id	= $post['round_id'.$pks[$x]];
            $tblMatch->projectteam1_id = $post['projectteam1_id'.$pks[$x]];
            $tblMatch->projectteam2_id = $post['projectteam2_id'.$pks[$x]];
            $tblMatch->team1_result	= $post['team1_result'.$pks[$x]];
            $tblMatch->team2_result	= $post['team2_result'.$pks[$x]];

			if(!$tblMatch->store()) {
				$this->setError($this->_db->getErrorMsg());
				$result=false;
			}
		}
		return $result;
	}
    
    /**
	 * Method to remove a matchday
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	function delete($pk=array())
	{
	$mainframe =& JFactory::getApplication();
    /* Ein Datenbankobjekt beziehen */
    $db = JFactory::getDbo();
    /* Ein JDatabaseQuery Objekt beziehen */
    $query = $db->getQuery(true);
    
    $mainframe->enqueueMessage(JText::_('match delete pk<br><pre>'.print_r($pk,true).'</pre>'   ),'');
	$result = false;
    if (count($pk))
		{
			//JArrayHelper::toInteger($cid);
			$cids = implode(',',$pk);
            /* Der Query wird erstellt */
            /*
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic as ms');
            
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff_statistic as mss');
            
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff as mst');
            
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event as mev');
            
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee as mre');
            
            $query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player as mpl');
            
            //$query->delete('ms,mss,mst,mev,mre,mpl');
            $query->delete('ms');
            $query->delete('mss');
            $query->delete('mst');
            $query->delete('mev');
            $query->delete('mre');
            $query->delete('mpl');
            
            $query->where('ms.match_id IN ('.$cids.')');
            $query->where('mss.match_id IN ('.$cids.')');
            $query->where('mst.match_id IN ('.$cids.')');
            $query->where('mev.match_id IN ('.$cids.')');
            $query->where('mre.match_id IN ('.$cids.')');
            $query->where('mpl.match_id IN ('.$cids.')');
            */
            
            $query->delete('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_statistic as ms');
            $query->where('ms.match_id IN ('.$cids.')');
            $query->delete('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff_statistic as mss');
            $query->where('mss.match_id IN ('.$cids.')');
            $query->delete('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_staff as mst');
            $query->where('mst.match_id IN ('.$cids.')');
            $query->delete('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_event as mev');
            $query->where('mev.match_id IN ('.$cids.')');
            $query->delete('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_referee as mre');
            $query->where('mre.match_id IN ('.$cids.')');
            $query->delete('#__'.COM_SPORTSMANAGEMENT_TABLE.'_match_player as mpl');
            $query->where('mpl.match_id IN ('.$cids.')');
            $db->setQuery($query);
            
            $mainframe->enqueueMessage(JText::_('match delete query<br><pre>'.print_r($query,true).'</pre>'   ),'');
            
            return parent::delete($pk);
        }    
   return true;     
   }     
    
}
?>
