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
 * sportsmanagementModelteamperson
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelteamperson extends JModelAdmin
{
	
    var $_project_id = 0;
    var $_team_id = 0;
    var $_project_team_id = 0;
    static $db_num_rows = 0;
  
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
	public function getTable($type = 'teamperson', $prefix = 'sportsmanagementTable', $config = array()) 
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
		$app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $cfg_which_media_tool = JComponentHelper::getParams($option)->get('cfg_which_media_tool',0);
        //$app->enqueueMessage(JText::_('sportsmanagementModelagegroup getForm cfg_which_media_tool<br><pre>'.print_r($cfg_which_media_tool,true).'</pre>'),'Notice');
        // Get the form.
		$form = $this->loadForm('com_sportsmanagement.teamperson', 'teamperson', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) 
		{
			return false;
		}
        
        $form->setFieldAttribute('picture', 'default', JComponentHelper::getParams($option)->get('ph_player',''));
        $form->setFieldAttribute('picture', 'directory', 'com_'.COM_SPORTSMANAGEMENT_TABLE.'/database/teamplayers');
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
		$data = JFactory::getApplication()->getUserState('com_sportsmanagement.edit.teamperson.data', array());
		if (empty($data)) 
		{
			$data = $this->getItem();
		}
		return $data;
	}
	
	
    /**
	 * Method to update checked teamplayers
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function saveshort()
	{
		$app = JFactory::getApplication();
        $date = JFactory::getDate();
	   $user = JFactory::getUser();
        /* Ein Datenbankobjekt beziehen */
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get the input
        $pks = JRequest::getVar('cid', null, 'post', 'array');
        //$post = JRequest::get('post');
        $post = $jinput->post->getArray(array());
        $this->_project_id	= $post['pid'];
        $this->persontype	= $post['persontype'];
        
//        $app->enqueueMessage('saveshort pks<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
//        $app->enqueueMessage('saveshort post<br><pre>'.print_r($post, true).'</pre><br>','Notice');
        
        $result = true;
		for ($x=0; $x < count($pks); $x++)
		{
//			$tblPerson = $this->getTable();
//			//$tblPerson->id = $post['person_id'.$pks[$x]];
//            $tblPerson->id = $post['person_id'.$pks[$x]];
//            $tblPerson->person_id = $pks[$x];
//			$tblPerson->project_position_id	= $post['project_position_id'.$pks[$x]];
//			$tblPerson->jerseynumber = $post['jerseynumber'.$pks[$x]];
//			$tblPerson->market_value = $post['market_value'.$pks[$x]];
//            $tblPerson->modified = $date->toSql();
//            $tblPerson->modified_by = $user->get('id');
            
//            $app->enqueueMessage(__FUNCTION__.' '.__LINE__.' season_team_person_id<br><pre>'.print_r($post['person_id'.$pks[$x]], true).'</pre><br>','Error');
//            $app->enqueueMessage(__FUNCTION__.' '.__LINE__.' project_position_id<br><pre>'.print_r($post['project_position_id'.$pks[$x]], true).'</pre><br>','Error');
//            $app->enqueueMessage(__FUNCTION__.' '.__LINE__.' jerseynumber<br><pre>'.print_r($post['jerseynumber'.$pks[$x]], true).'</pre><br>','Error');
//            $app->enqueueMessage(__FUNCTION__.' '.__LINE__.' market_value<br><pre>'.print_r($post['market_value'.$pks[$x]], true).'</pre><br>','Error');
            
            
            // Fields to update.
$fields = array(
    $db->quoteName('project_position_id') . ' = ' . $post['project_position_id'.$pks[$x]],
    $db->quoteName('jerseynumber') . ' = '.$post['jerseynumber'.$pks[$x]],
    $db->quoteName('market_value') . ' = '.$post['market_value'.$pks[$x]],
    $db->quoteName('modified') . ' = '.$db->Quote( '' . $date->toSql() . '' ) ,
    $db->quoteName('modified_by') . ' = '.$user->get('id')
    
);
 
// Conditions for which records should be updated.
$conditions = array(
    $db->quoteName('id') . ' = '.$post['person_id'.$pks[$x]]
);

//exit;

$query->clear(); 
$query->update($db->quoteName('#__sportsmanagement_season_team_person_id'))->set($fields)->where($conditions);
$db->setQuery($query);
//sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
        
            if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
            {
            $my_text = 'id<br><pre>'.print_r($pks[$x], true).'</pre><br>';
            $my_text .= 'project_position_id<br><pre>'.print_r($post['project_position_id'.$pks[$x]], true).'</pre><br>';
            $my_text .= 'jerseynumber<br><pre>'.print_r($post['jerseynumber'.$pks[$x]], true).'</pre><br>';
            $my_text .= 'market_value<br><pre>'.print_r($post['market_value'.$pks[$x]], true).'</pre><br>';
            $my_text .= 'position_id<br><pre>'.print_r($post['position_id'.$pks[$x]], true).'</pre><br>';
            $my_text .= 'person_id<br><pre>'.print_r($post['person_id'.$pks[$x]], true).'</pre><br>';
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text);
            }

			//if(!$tblPerson->store()) 
            if( !sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__) )
            {
                $app->enqueueMessage(__FILE__.' '.get_class($this).' '.__FUNCTION__.' <br><pre>'.print_r($this->_db->getErrorMsg(), true).'</pre><br>','Error');
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				$result=false;
			}
            else
            {
                $tblprojectposition = JTable::getInstance("projectposition", "sportsmanagementTable");
                $tblprojectposition->load((int) $post['project_position_id'.$pks[$x]]);
                
                if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
                {
                $app->enqueueMessage(__FILE__.' '.__METHOD__.' '.__LINE__.' position_id<br><pre>'.print_r($tblprojectposition->position_id, true).'</pre><br>','Notice');
                }
                
                $tblperson = JTable::getInstance("person", "sportsmanagementTable");
                $tblperson->load((int) $pks[$x]);
                $tblperson->position_id = $tblprojectposition->position_id;
                
                if (!$tblperson->store())
	            {
		        $app->enqueueMessage(__FILE__.' '.__METHOD__.' '.__LINE__.' <br><pre>'.print_r($tblperson->getErrorMsg(), true).'</pre><br>','Error');
	            }
                
                // alten eintrag löschen
                // Create a new query object.
                $query = $db->getQuery(true);
                // delete all
                $conditions = array(
                $db->quoteName('person_id') . '='.$pks[$x],
                $db->quoteName('project_id') . '='.$this->_project_id,
                $db->quoteName('persontype') . '='.$this->persontype
                );
 
                $query->delete($db->quoteName('#__sportsmanagement_person_project_position'));
                $query->where($conditions);
 
                $db->setQuery($query); 
                sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
                if ( self::$db_num_rows )
                {
                $app->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_'.strtoupper('sportsmanagement_person_project_position').'_ITEMS_DELETED',self::$db_num_rows),'');
                } 

                //if (!$db->query())
//		          {
//			            $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getErrorMsg<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
//		          }
                  
                // Create and populate an object.
                $profile = new stdClass();
                //$profile->person_id = $post['person_id'.$pks[$x]];
                $profile->person_id = $pks[$x];
                $profile->project_id = $this->_project_id;
                $profile->project_position_id = $post['project_position_id'.$pks[$x]];
                $profile->persontype = $this->persontype;
                // Insert the object into table.
                $result = JFactory::getDbo()->insertObject('#__sportsmanagement_person_project_position', $profile);
                
                if (!$result)
	            {
		        //$app->enqueueMessage(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($db->getErrorMsg(), true).'</pre><br>','Error');
	            }
                
                //$app->enqueueMessage(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($profile, true).'</pre><br>','Error');
                
            }
		}
		return $result;
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
	 * Method to remove teamplayer
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	public function delete(&$pks)
	{
	$app = JFactory::getApplication();
    //$app->enqueueMessage(JText::_('delete pks<br><pre>'.print_r($pks,true).'</pre>'),'');
    /* Ein Datenbankobjekt beziehen */
    $db = JFactory::getDbo();
    /* Ein JDatabaseQuery Objekt beziehen */
    $query = $db->getQuery(true);
    
	$result = false;
    if (count($pks))
		{
		//JArrayHelper::toInteger($cid);
			$cids = implode(',',$pks);
                        
            // delete all 
$conditions = array(
    $db->quoteName('teamplayer_id') . ' IN ('.$cids.')'
);
$query->clear(); 
$query->delete($db->quoteName('#__sportsmanagement_match_player'));
$query->where($conditions);
$db->setQuery($query);
sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
            if ( self::$db_num_rows )
            {
            $app->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_'.strtoupper('sportsmanagement_match_player').'_ITEMS_DELETED',self::$db_num_rows),'');
            }
            
            // delete all 
$conditions = array(
    $db->quoteName('in_for') . ' IN ('.$cids.')'
);
$query->clear(); 
$query->delete($db->quoteName('#__sportsmanagement_match_player'));
$query->where($conditions);
$db->setQuery($query);
sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
            if ( self::$db_num_rows )
            {
            $app->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_'.strtoupper('sportsmanagement_match_player').'_ITEMS_DELETED',self::$db_num_rows),'');
            }
            
            // delete all 
$conditions = array(
    $db->quoteName('teamplayer_id') . ' IN ('.$cids.')'
);
$query->clear(); 
$query->delete($db->quoteName('#__sportsmanagement_match_statistic'));
$query->where($conditions);
$db->setQuery($query);
sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
            if ( self::$db_num_rows )
            {
            $app->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_'.strtoupper('sportsmanagement_match_statistic').'_ITEMS_DELETED',self::$db_num_rows),'');
            } 
            
            // delete all 
$conditions = array(
    $db->quoteName('teamplayer_id') . ' IN ('.$cids.')'
);
$query->clear(); 
$query->delete($db->quoteName('#__sportsmanagement_match_event'));
$query->where($conditions);
$db->setQuery($query);
sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);
            if ( self::$db_num_rows )
            {
            $app->enqueueMessage(JText::sprintf('COM_SPORTSMANAGEMENT_'.strtoupper('sportsmanagement_match_event').'_ITEMS_DELETED',self::$db_num_rows),'');
            }  
            
        }  
    
    
    //if ( $result )
    //{        
    return parent::delete($pks);
    //}
     
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
	   $app = JFactory::getApplication();
       $option = JRequest::getCmd('option');
       $season_id = $app->getUserState( "$option.season_id");
       $post = JRequest::get('post');
       $db = $this->getDbo();
	   $query = $db->getQuery(true);
       $date = JFactory::getDate();
	   $user = JFactory::getUser();
       //$query2 = $db->getQuery(true);
        
       //$app->enqueueMessage(JText::_('sportsmanagementModelplayground save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
       //$app->enqueueMessage(JText::_('sportsmanagementModelplayground post<br><pre>'.print_r($post,true).'</pre>'),'Notice');
       
       // update personendaten
       // Fields to update.
    $fields = array(
    $db->quoteName('injury') .'=\''.$data['injury'].'\'',
        $db->quoteName('injury_date') .'=\''.$data['injury_date'].'\'',
        $db->quoteName('injury_end') .'=\''.$data['injury_end'].'\'',
        $db->quoteName('injury_detail') .'=\''.$data['injury_detail'].'\'',
        $db->quoteName('injury_date_start') .'=\''.$data['injury_date_start'].'\'',
        $db->quoteName('injury_date_end') .'=\''.$data['injury_date_end'].'\'',
        $db->quoteName('suspension') .'=\''.$data['suspension'].'\'',
        $db->quoteName('suspension_date') .'=\''.$data['suspension_date'].'\'',
        $db->quoteName('suspension_end') .'=\''.$data['suspension_end'].'\'',
        $db->quoteName('suspension_detail') .'=\''.$data['suspension_detail'].'\'',
        $db->quoteName('susp_date_start') .'=\''.$data['susp_date_start'].'\'',
        $db->quoteName('susp_date_end') .'=\''.$data['susp_date_end'].'\'',
        $db->quoteName('away') .'=\''.$data['away'].'\'',
		$db->quoteName('away_date') .'=\''.$data['away_date'].'\'',
        $db->quoteName('away_end') .'=\''.$data['away_end'].'\'',
        $db->quoteName('away_detail') .'=\''.$data['away_detail'].'\'',
        $db->quoteName('away_date_start') .'=\''.$data['away_date_start'].'\'',
        $db->quoteName('away_date_end') .'=\''.$data['away_date_end'].'\'',
        
        $db->quoteName('modified') .' = '. $db->Quote( '' . $date->toSql() . '' ) .'',
        $db->quoteName('modified_by') .'='.$user->get('id')
        );
     // Conditions for which records should be updated.
    $conditions = array(
    $db->quoteName('id') .'='. $data['person_id']
    );
     $query->update($db->quoteName('#__sportsmanagement_person'))->set($fields)->where($conditions);
     $db->setQuery($query);   
 
  
 if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
		{
		    $app->enqueueMessage(JText::_('sportsmanagementModelteamplayer save personendaten<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
		}
        
// update personendaten pro saison
       // Fields to update.
    unset($fields);
    unset($conditions);  
    $query->clear(); 
    $fields = array(
    $db->quoteName('picture') .'=\''.$data['picture'].'\'',
    $db->quoteName('modified') .'=\''.$date->toSql().'\'',
    $db->quoteName('modified_by') .'='.$user->get('id')
        );
     // Conditions for which records should be updated.
    $conditions = array(
    $db->quoteName('person_id') .'='. $data['person_id'],
    $db->quoteName('season_id') .'='. $season_id
    );
     $query->update($db->quoteName('#__sportsmanagement_season_person_id'))->set($fields)->where($conditions);
     $db->setQuery($query);   
 
 if (!sportsmanagementModeldatabasetool::runJoomlaQuery())
		{
		    $app->enqueueMessage(JText::_('sportsmanagementModelteamplayer save person season <br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
		}
                
        
 
        
       if (isset($post['extended']) && is_array($post['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string)$parameter;
		}
        
        //$app->enqueueMessage(JText::_('sportsmanagementModelplayground save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        // Proceed with the save
		return parent::save($data);   
    }
    
    
   
}
