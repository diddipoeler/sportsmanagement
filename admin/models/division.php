<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      division.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage division
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;

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
 * sportsmanagementModeldivision::divisiontoproject()
 * 
 * @return void
 */
function divisiontoproject()
{
$post = $this->jsmjinput->post->getArray(array());    
$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' post -> <pre>'.print_r($post,true).'</pre>'),'');    

$divisions = $post['cid'];
$project_id = $post['pid'];

foreach ($divisions as $key => $value ) {
$this->jsmquery->clear();
$this->jsmquery->select('dv.name');
$this->jsmquery->from('#__sportsmanagement_division AS dv');
$this->jsmquery->where('dv.project_id = ' . $project_id);
$this->jsmquery->where('dv.id = ' . $value);
$this->jsmdb->setQuery( $this->jsmquery );
$resultdvname = $this->jsmdb->loadResult();
$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' resultdvname -> <pre>'.print_r($resultdvname,true).'</pre>'),'');

$this->jsmquery->clear();
$this->jsmquery->select('s.name');
$this->jsmquery->from('#__sportsmanagement_season AS s');
$this->jsmquery->join('INNER', '#__sportsmanagement_project AS p on p.season_id = s.id');
$this->jsmquery->where('p.id = ' . $project_id);
$this->jsmdb->setQuery($this->jsmquery);
$reaulseasonname = $this->jsmdb->loadResult();
$this->jsmapp->enqueueMessage(Text::_(__METHOD__.' '.__LINE__.' reaulseasonname -> <pre>'.print_r($reaulseasonname,true).'</pre>'),'');





}
    
}

/**
 * sportsmanagementModeldivision::count_teams_division()
 * 
 * @param integer $division_id
 * @return void
 */
function count_teams_division($division_id = 0)
{
$results = array();    
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
    $results = array();
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
    $results = array();
    //Factory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
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
    $results = array();
    //Factory::getApplication()->enqueueMessage(__METHOD__.' '.__LINE__.' '.$msg, 'error'); // commonly to still display that error
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
        $app = Factory::getApplication();
        $date = Factory::getDate();
	   $user = Factory::getUser();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
       
        // Get the input
        $pks = Factory::getApplication()->input->getVar('cid', null, 'post', 'array');
        if ( !$pks )
        {
            return Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_SAVE_NO_SELECT');
        }
        $post = Factory::getApplication()->input->post->getArray(array());

		for ($x=0; $x < count($pks); $x++)
		{
			$tblRound = & $this->getTable();
			$tblRound->id = $pks[$x];
			$tblRound->name	= $post['name'.$pks[$x]];
            
            $tblRound->alias = OutputFilter::stringURLSafe( $post['name'.$pks[$x]] );
            // Set the values
		    $tblRound->modified = $date->toSql();
		    $tblRound->modified_by = $user->get('id');

			if(!$tblRound->store()) 
            {
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				return false;
			}
		}
		return Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVISIONS_SAVE');
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
	$app = Factory::getApplication();
    
    return parent::delete($pks);
    
         
   } 
   
    
}