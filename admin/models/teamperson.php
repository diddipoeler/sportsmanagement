<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      teamperson.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * sportsmanagementModelteamperson
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelteamperson extends JSMModelAdmin
{
	
    var $_project_id = 0;
    var $_team_id = 0;
    var $_project_team_id = 0;
    static $db_num_rows = 0;

	
/**
 * sportsmanagementModelteamperson::set_state()
 * 
 * @param mixed $ids
 * @param mixed $tpids
 * @param mixed $state
 * @return void
 */
function set_state($ids,$tpids,$state)
{	
$this->jsmuser = JFactory::getUser(); 
$this->jsmdate = JFactory::getDate();

for ($x=0; $x < count($ids); $x++)
		{
$person_id = $ids[$x];		  
$season_team_person_id = $tpids[$person_id];          
// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $season_team_person_id;
$object->published = $state;
$object->modified = $this->jsmdate->toSql();
$object->modified_by = $this->jsmuser->get('id');
// Update their details in the table using id as the primary key.
$result = JFactory::getDbo()->updateObject('#__sportsmanagement_season_team_person_id', $object, 'id'); 
          
        }  
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
        $pks = JFactory::getApplication()->input->getVar('cid', null, 'post', 'array');
        //$post = JFactory::getApplication()->input->post->getArray(array());
        $post = $jinput->post->getArray(array());
        $this->_project_id	= $post['pid'];
        $this->persontype	= $post['persontype'];
        
//        $app->enqueueMessage('saveshort pks<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
//        $app->enqueueMessage('saveshort post<br><pre>'.print_r($post, true).'</pre><br>','Notice');
        
        $result = true;
		
// ###############################
// update der positionen bei den spielen, wenn keine vorhanden sind
//build the html options for position
$position_ids = array();        
$mdlPositions = JModelLegacy::getInstance('Positions', 'sportsmanagementModel');
$project_ref_positions = $mdlPositions->getProjectPositions($this->project_id, 1);
if ($project_ref_positions) {
            $position_ids = array_merge($position_ids, $project_ref_positions);
        }
//$app->enqueueMessage(' position_ids<br><pre>'.print_r($position_ids, true).'</pre><br>','Notice');
for ($x=0; $x < count($pks); $x++)
{


$team_player_id = $post['tpid'][$pks[$x]];
//$app->enqueueMessage('player id<br><pre>'.print_r($team_player_id, true).'</pre><br>','Notice');
$project_position_id = $post['project_position_id'.$pks[$x]];
//$app->enqueueMessage(' project_position_id<br><pre>'.print_r($project_position_id, true).'</pre><br>','Notice');
foreach($position_ids as $items => $item) {
    if($item->value == $project_position_id) {
       $results = $item->position_id;
    }
} 	

//$app->enqueueMessage(' results <br><pre>'.print_r($results , true).'</pre><br>','Notice');


$this->jsmquery->clear();
// Fields to update.
$fields = array(
    $this->jsmdb->quoteName('project_position_id') . ' = ' . $results
);

// Conditions for which records should be updated.
$conditions = array(
    $this->jsmdb->quoteName('project_position_id') . ' = 0', 
    $this->jsmdb->quoteName('teamplayer_id') . ' = ' . $team_player_id 
);

try{
$this->jsmquery->update($this->jsmdb->quoteName('#__sportsmanagement_match_player'))->set($fields)->where($conditions);
//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' jsmquery<br><pre>'.print_r($this->jsmquery,true).'</pre>'),'');	   
$this->jsmdb->setQuery($this->jsmquery);
$resultupdate = $this->jsmdb->execute();
}
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.' '.$e->getMessage()), 'error');
        return false;
        }
}
// ###############################
	
		for ($x=0; $x < count($pks); $x++)
		{
		  
          if ( $post['jerseynumber'.$pks[$x]] == '' )
          {
            $post['jerseynumber'.$pks[$x]] = 0;
          }
          if ( $post['market_value'.$pks[$x]] == '' )
          {
            $post['market_value'.$pks[$x]] = 0;
          }
         
            
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
	 * Method to remove teamplayer
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	public function delete(&$pks)
	{
	$app = JFactory::getApplication();
    
    // JInput object
        $jinput = $app->input;
        $post = $jinput->post->getArray(array());
        $option = $jinput->getCmd('option');
        
        $project_team_id = $post['project_team_id'];
        $team_id = $post['team_id'];
        $pid = $post['pid'];
        $persontype = $post['persontype'];
        
        $app->enqueueMessage(__METHOD__.' '.__LINE__.' project_team_id<br><pre>'.print_r($project_team_id, true).'</pre><br>','Notice');
        $app->enqueueMessage(__METHOD__.' '.__LINE__.' team_id<br><pre>'.print_r($team_id, true).'</pre><br>','Notice');
        $app->enqueueMessage(__METHOD__.' '.__LINE__.' pid<br><pre>'.print_r($pid, true).'</pre><br>','Notice');
        $app->enqueueMessage(__METHOD__.' '.__LINE__.' persontype<br><pre>'.print_r($persontype, true).'</pre><br>','Notice');
        
        $app->enqueueMessage(__METHOD__.' '.__LINE__.' $pks<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
        $app->enqueueMessage(__METHOD__.' '.__LINE__.' post<br><pre>'.print_r($post, true).'</pre><br>','Notice');
    
    
    
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
       $option = JFactory::getApplication()->input->getCmd('option');
       $season_id = $app->getUserState( "$option.season_id");
       $post = JFactory::getApplication()->input->post->getArray(array());
       $db = $this->getDbo();
	   $query = $db->getQuery(true);
       $date = JFactory::getDate();
	   $user = JFactory::getUser();
       //$query2 = $db->getQuery(true);
       if (isset($post['extended']) && is_array($post['extended'])) 
		{
			// Convert the extended field to a string.
			$parameter = new JRegistry;
			$parameter->loadArray($post['extended']);
			$data['extended'] = (string)$parameter;
		}
		
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
        //$db->quoteName('extended') .'=\''.$data['extended'].'\'',
        $db->quoteName('modified') .' = '. $db->Quote( '' . $date->toSql() . '' ) .'',
        $db->quoteName('modified_by') .'='.$user->get('id')
        );
     // Conditions for which records should be updated.
    $conditions = array(
    $db->quoteName('id') .'='. $data['person_id']
    );
     $query->update($db->quoteName('#__sportsmanagement_person'))->set($fields)->where($conditions);
     $db->setQuery($query);   
 
  
 try{
sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);  
}
	catch (Exception $e)
{
    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
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
 
 try{
sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);  
}
	catch (Exception $e)
{
    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
}
        
        // alten eintrag löschen
        // Create a new query object.
        $query = $db->getQuery(true);
        // delete all
        $conditions = array(
        $db->quoteName('person_id') . '='.$data['person_id'],
        $db->quoteName('project_id') . '='.$post['pid'],
        $db->quoteName('persontype') . '='.$post['persontype']
        );
 
$query->delete($db->quoteName('#__sportsmanagement_person_project_position'));
$query->where($conditions);
$db->setQuery($query); 
try{
sportsmanagementModeldatabasetool::runJoomlaQuery(__CLASS__);  
}
	catch (Exception $e)
{
    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
}     
// Create and populate an object.
$profile = new stdClass();
//$profile->person_id = $post['person_id'.$pks[$x]];
$profile->person_id = $data['person_id'];
$profile->project_id = $post['pid'];
$profile->project_position_id = $data['project_position_id'];
$profile->persontype = $post['persontype'];
try{		
// Insert the object into table.
$result = JFactory::getDbo()->insertObject('#__sportsmanagement_person_project_position', $profile);        
}
	catch (Exception $e)
{
//    $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' '.$e->getMessage()), 'error');
}  
        
       
        
        //$app->enqueueMessage(JText::_('sportsmanagementModelplayground save<br><pre>'.print_r($data,true).'</pre>'),'Notice');
        
        // Proceed with the save
		return parent::save($data);   
    }
    
    
   
}
