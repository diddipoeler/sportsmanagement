<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      division.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage division
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
//jimport('joomla.application.component.modeladmin');
 

/**
 * sportsmanagementModeldivision
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModeldivision extends JSMModelAdmin
{


/**
 * sportsmanagementModeldivision::count_teams_division()
 * 
 * @param integer $division_id
 * @return void
 */
function count_teams_division($division_id = 0)
{
$division_teams = array();
try {	
$this->jsmquery->clear();
$this->jsmquery->select('m.projectteam1_id');
$this->jsmquery->from('#__sportsmanagement_match as m');
$this->jsmquery->where('division_id = '.$division_id);
$this->jsmquery->where('projectteam1_id != 0');
$this->jsmquery->group('projectteam1_id');
$this->jsmdb->setQuery($this->jsmquery);
$results = $this->jsmdb->loadObjectList('projectteam1_id');
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    //JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}
	
foreach ( $results as $key => $value )
{
$division_teams[$key] = $key;
}

try {
$this->jsmquery->clear();
$this->jsmquery->select('m.projectteam2_id');
$this->jsmquery->from('#__sportsmanagement_match as m');
$this->jsmquery->where('division_id = '.$division_id);
$this->jsmquery->where('projectteam2_id != 0');
$this->jsmquery->group('projectteam2_id');
$this->jsmdb->setQuery($this->jsmquery);
$results = $this->jsmdb->loadObjectList('projectteam2_id');
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    //JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}
	
foreach ( $results as $key => $value )
{
$division_teams[$key] = $key;
}

try {
$this->jsmquery->clear();
$this->jsmquery->select('id');
$this->jsmquery->from('#__sportsmanagement_project_team');
$this->jsmquery->where('division_id = '.$division_id);
$this->jsmdb->setQuery($this->jsmquery);
$results = $this->jsmdb->loadObjectList('id');
} catch (Exception $e) {
    $msg = $e->getMessage(); // Returns "Normally you would have other code...
    $code = $e->getCode(); // Returns '500';
    //JFactory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
}

foreach ( $results as $key => $value )
{
$division_teams[$key] = $key;
}
	
return count($division_teams);

}
    
    	/**
    	 * sportsmanagementModeldivision::saveshort()
    	 * 
    	 * @return
    	 */
    	public function saveshort()
	{
		// Reference global application object
        $app = JFactory::getApplication();
        $date = JFactory::getDate();
	   $user = JFactory::getUser();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        
        //$show_debug_info = JComponentHelper::getParams($option)->get('show_debug_info',0) ;
        
        // Get the input
        $pks = JFactory::getApplication()->input->getVar('cid', null, 'post', 'array');
        if ( !$pks )
        {
            return JText::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_SAVE_NO_SELECT');
        }
        $post = JFactory::getApplication()->input->post->getArray(array());
        
        if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $app->enqueueMessage(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
        $app->enqueueMessage(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($post, true).'</pre><br>','Notice');
        }
        
        //$result=true;
		for ($x=0; $x < count($pks); $x++)
		{
			$tblRound = & $this->getTable();
			$tblRound->id = $pks[$x];
			$tblRound->name	= $post['name'.$pks[$x]];
            
            $tblRound->alias = JFilterOutput::stringURLSafe( $post['name'.$pks[$x]] );
            // Set the values
		    $tblRound->modified = $date->toSql();
		    $tblRound->modified_by = $user->get('id');
        
            

			if(!$tblRound->store()) 
            {
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				return false;
			}
		}
		return JText::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_SAVE');
	}
    
    
    /**
	 * Method to remove division
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	public function delete(&$pks)
	{
	$app = JFactory::getApplication();
    //$app->enqueueMessage(JText::_('delete pks<br><pre>'.print_r($pks,true).'</pre>'),'');
    
    return parent::delete($pks);
    
         
   } 
   
    
}