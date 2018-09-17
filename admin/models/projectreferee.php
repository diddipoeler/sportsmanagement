<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      projectreferee.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage projectreferee
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\Utilities\ArrayHelper; 

 
/**
 * SportsManagement Model
 */
class sportsmanagementModelprojectreferee extends JSMModelAdmin
{
    
    /**
	 * Method to update checked project referees
	 *
	 * @access	public
	 * @return	boolean	True on success
	 *
	 */
	function saveshort()
	{
		$app =& JFactory::getApplication();
        // Get the input
        $pks = JFactory::getApplication()->input->getVar('cid', null, 'post', 'array');
        $post = JFactory::getApplication()->input->post->getArray(array());
        
        //$app->enqueueMessage('sportsmanagementModelprojectreferee saveshort pks<br><pre>'.print_r($pks, true).'</pre><br>','Notice');
        //$app->enqueueMessage('sportsmanagementModelprojectreferee saveshort post<br><pre>'.print_r($post, true).'</pre><br>','Notice');
        
        $result=true;
		for ($x=0; $x < count($pks); $x++)
		{
			$tblPerson = & $this->getTable();
			$tblPerson->id = $pks[$x];
			$tblPerson->project_position_id	= $post['project_position_id'.$pks[$x]];

			if(!$tblPerson->store()) {
				sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
				$result=false;
			}
		}
		return $result;
	}
    
    /**
	 * Method to remove projectreferee
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	0.1
	 */
	public function delete(&$pks)
	{
	$app =& JFactory::getApplication();
    $app->enqueueMessage(JText::_('delete pks<br><pre>'.print_r($pks,true).'</pre>'),'');
    /* Ein Datenbankobjekt beziehen */
    $db = JFactory::getDbo();
    /* Ein JDatabaseQuery Objekt beziehen */
    $query = $db->getQuery(true);
    
	$result = false;
    if (count($pks))
		{
			$cids = implode(',',$pks);
            $app->enqueueMessage(JText::_('delete cids<br><pre>'.print_r($cids,true).'</pre>'),'');
            // wir löschen mit join
            $query = 'DELETE mre
            FROM #__sportsmanagement_project_referee as m    
            LEFT JOIN #__sportsmanagement_match_referee as mre
            ON mre.project_referee_id = m.id
            WHERE m.id IN ('.$cids.')';
            $db->setQuery($query);
            $db->execute();
            if (!$db->execute()) 
            {
                $app->enqueueMessage(JText::_('delete getErrorMsg<br><pre>'.print_r($db->getErrorMsg(),true).'</pre>'),'Error');
                return false; 
            }
            
            
        }  
    
    
    //if ( $result )
    //{        
    //return parent::delete($pks);
    //}
      return parent::delete($pks);   
   } 
   

	
}
