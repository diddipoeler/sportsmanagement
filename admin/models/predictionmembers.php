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

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.modellist' );



/**
 * sportsmanagementModelPredictionMembers
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPredictionMembers extends JModelList
{
	var $_identifier = "predmembers";
	
     /**
      * sportsmanagementModelPredictionMembers::__construct()
      * 
      * @param mixed $config
      * @return void
      */
     public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'u.username',
                        'u.name',
                        'p.name',
                        'tmb.last_tipp',
                        'tmb.reminder',
                        'tmb.receipt',
                        'tmb.show_profile',
                        'tmb.admintipp',
                        'tmb.approved',
                        'tmb.modified',
                        'tmb.modified_by'
                        );
                parent::__construct($config);
                $getDBConnection = sportsmanagementHelper::getDBConnection();
                parent::setDbo($getDBConnection);
        }
    
    /**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Initialise variables.
        
        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' context ->'.$this->context.''),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.prediction_id', 'filter_prediction_id', '');
        $this->setState('filter.prediction_id', $temp_user_request);

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
        
        //$app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('u.username', 'asc');
	}
        
	/**
	 * sportsmanagementModelPredictionMembers::getListQuery()
	 * 
	 * @return
	 */
	function getListQuery()
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        
        
        // Create a new query object.
        $query = $this->_db->getQuery(true);
        $query->select(array('tmb.*','u.name AS realname', 'u.username AS username', 'p.name AS predictionname','u1.username' ))
        ->from('#__sportsmanagement_prediction_member AS tmb')
        ->join('LEFT', '#__sportsmanagement_prediction_game AS p ON p.id = tmb.prediction_id')
        ->join('LEFT', '#__users AS u ON u.id = tmb.user_id')
        ->join('LEFT', '#__users AS u1 ON u1.id = tmb.modified_by');

        if (is_numeric($this->getState('filter.prediction_id')))
        {
            $query->where('tmb.prediction_id = ' . $this->getState('filter.prediction_id'));
        }
        if (is_numeric($this->getState('filter.state')))
        {
            $query->where('tmb.approved = ' . $this->getState('filter.state'));
        }
        
        if ($this->getState('filter.search'))
		{
        $query->where('(LOWER(u.username) LIKE ' . $db->Quote( '%' . $this->getState('filter.search') . '%' ) );
        }



		 $query->order($db->escape($this->getState('list.ordering', 'u.username')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
 
 if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
        {
        $my_text .= ' <br><pre>'.print_r($query->dump(),true).'</pre>';    
        sportsmanagementHelper::setDebugInfoText(__METHOD__,__FUNCTION__,__CLASS__,__LINE__,$my_text); 
        }
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');

		return $query;
	}




	
	
	/**
	 * sportsmanagementModelPredictionMembers::getPredictionProjectName()
	 * 
	 * @param mixed $predictionID
	 * @return
	 */
	function getPredictionProjectName($predictionID)
	{
	// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
	
    // Select some fields
        $query->select('ppj.name AS pjName');
        $query->from('#__sportsmanagement_prediction_game AS ppj ');
        $query->where('ppj.id = ' . $predictionID);
        

		$db->setQuery($query);
		$result = $db->loadResult();
	  
    //$app->enqueueMessage(JText::_('<br />predictionID<pre>~' . print_r($predictionID,true) . '~</pre><br />'),'Notice');
    //$app->enqueueMessage(JText::_('<br />result<pre>~' . print_r($result,true) . '~</pre><br />'),'Notice');
    
		return $result;
	}
	
	/**
	 * sportsmanagementModelPredictionMembers::getPredictionMembers()
	 * 
	 * @param mixed $prediction_id
	 * @return
	 */
	function getPredictionMembers($prediction_id)
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        
        // Select some fields
        $query->select('pm.user_id AS value, u.name AS text');
        $query->from('#__sportsmanagement_prediction_member AS pm ');
        $query->join('LEFT', '#__users AS u ON u.id = pm.user_id');
        $query->where('prediction_id = ' . (int) $prediction_id);
       

    $db->setQuery($query);
	$results = $db->loadObjectList();
    return $results;				
	}
	
	/**
	 * sportsmanagementModelPredictionMembers::getJLUsers()
	 * 
	 * @param mixed $prediction_id
	 * @return
	 */
	function getJLUsers($prediction_id)
	{
	   // Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Create a new query object.		
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        
	//$not_in = array();
     // Select some fields
        $query->select('pm.user_id AS value');
        $query->from('#__sportsmanagement_prediction_member AS pm ');
        $query->join('LEFT', '#__users AS u ON u.id = pm.user_id');
        $query->where('prediction_id = ' . (int) $prediction_id);
        
	$db->setQuery($query);
    
    if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
		$records = $db->loadColumn();
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
		$records = $db->loadResultArray();
}

    if ( $predresult = $records )
    {
        // Select some fields
        $query->clear();
        $query->select('id AS value, name AS text');
        $query->from('#__users ');
        $query->where('id not in (' . implode(",", $records ) .')');
        $query->order('name');
        

    }
    else
    {
        // Select some fields
        $query->clear();
        $query->select('id AS value, name AS text');
        $query->from('#__users ');
        $query->order('name');

    }   
		

		$db->setQuery( $query );

		if ( !$result = $db->loadObjectList() )
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $db->getErrorMsg(), __LINE__);
			return false;
		}
		else
		{
			return $result;
		}
	}
	
	

}
?>