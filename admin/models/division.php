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
//$this->jsmapp->enqueueMessage(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($results, true).'</pre><br>','Notice');
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
//$this->jsmapp->enqueueMessage(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($results, true).'</pre><br>','Notice');

//$this->jsmapp->enqueueMessage(__METHOD__.' '.__LINE__.'<br><pre>'.print_r($division_teams, true).'</pre><br>','Notice');

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
