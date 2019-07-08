<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      predictionmembers.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage models
 */

defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;

/**
 * sportsmanagementModelPredictionMembers
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPredictionMembers extends JSMModelList
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
                parent::setDbo($this->jsmdb);
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
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        if ( $this->jsmjinput->getInt('prediction_id') )
		{
		$this->setState('filter.prediction_id', $this->jsmjinput->getInt('prediction_id') );
        $this->jsmapp->setUserState( "com_sportsmanagement.prediction_id", $this->jsmjinput->getInt('prediction_id') );	
		}
		else
		{
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.prediction_id', 'filter_prediction_id', '');
        $this->setState('filter.prediction_id', $temp_user_request);
		}
        // List state information.
        $value = $this->getUserStateFromRequest($this->context . '.list.start', 'limitstart', 0, 'int');
		$this->setState('list.start', $value);       
		// Filter.order
		$orderCol = $this->getUserStateFromRequest($this->context. '.filter_order', 'filter_order', '', 'string');
		if (!in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'u.username';
		}
		$this->setState('list.ordering', $orderCol);
		$listOrder = $this->getUserStateFromRequest($this->context. '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
		if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'ASC';
		}
		$this->setState('list.direction', $listOrder);


	}
        
	/**
	 * sportsmanagementModelPredictionMembers::getListQuery()
	 * 
	 * @return
	 */
	function getListQuery()
	{
        // Create a new query object.		
		$this->jsmquery->clear();
        $this->jsmquery->select(array('tmb.*','u.name AS realname', 'u.username AS username', 'p.name AS predictionname','u1.username as modusername' ))
        ->from('#__sportsmanagement_prediction_member AS tmb')
        ->join('LEFT', '#__sportsmanagement_prediction_game AS p ON p.id = tmb.prediction_id')
        ->join('LEFT', '#__users AS u ON u.id = tmb.user_id')
        ->join('LEFT', '#__users AS u1 ON u1.id = tmb.modified_by');

        if (is_numeric($this->getState('filter.prediction_id')))
        {
            $this->jsmquery->where('tmb.prediction_id = ' . $this->getState('filter.prediction_id'));
        }
        if (is_numeric($this->getState('filter.state')))
        {
            $this->jsmquery->where('tmb.approved = ' . $this->getState('filter.state'));
        }
        
        if ($this->getState('filter.search'))
		{
        $this->jsmquery->where('(LOWER(u.username) LIKE ' . $this->jsmdb->Quote( '%' . $this->getState('filter.search') . '%' ) );
        }

		 $this->jsmquery->order($this->jsmdb->escape($this->getState('list.ordering', 'u.username')).' '.
                $this->jsmdb->escape($this->getState('list.direction', 'ASC')));
 
		return $this->jsmquery;
	}
	
	/**
	 * sportsmanagementModelPredictionMembers::getPredictionProjectName()
	 * 
	 * @param mixed $predictionID
	 * @return
	 */
	function getPredictionProjectName($predictionID)
	{
        // Create a new query object.		
		$this->jsmquery->clear();
	
    // Select some fields
        $this->jsmquery->select('ppj.name AS pjName');
        $this->jsmquery->from('#__sportsmanagement_prediction_game AS ppj ');
        $this->jsmquery->where('ppj.id = ' . $predictionID);
        
try{
		$this->jsmdb->setQuery($this->jsmquery);
		$result = $this->jsmdb->loadResult();
		return $result;
        }
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');
        return false;
        }
	}
	
	/**
	 * sportsmanagementModelPredictionMembers::getPredictionMembers()
	 * 
	 * @param mixed $prediction_id
	 * @return
	 */
	function getPredictionMembers($prediction_id)
	{
        // Create a new query object.		
		$this->jsmquery->clear();
        
        // Select some fields
        $this->jsmquery->select('pm.user_id AS value, u.name AS text');
        $this->jsmquery->from('#__sportsmanagement_prediction_member AS pm ');
        $this->jsmquery->join('LEFT', '#__users AS u ON u.id = pm.user_id');
        $this->jsmquery->where('prediction_id = ' . (int) $prediction_id);
       
try{
    $this->jsmdb->setQuery($this->jsmquery);
	$results = $this->jsmdb->loadObjectList();
    return $results;
    			}
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');
        return false;
        }	
	}
	
	/**
	 * sportsmanagementModelPredictionMembers::getJLUsers()
	 * 
	 * @param mixed $prediction_id
	 * @return
	 */
	function getJLUsers($prediction_id)
	{
        // Create a new query object.		
		$this->jsmquery->clear();
        
     // Select some fields
        $this->jsmquery->select('pm.user_id AS value');
        $this->jsmquery->from('#__sportsmanagement_prediction_member AS pm ');
        $this->jsmquery->join('LEFT', '#__users AS u ON u.id = pm.user_id');
        $this->jsmquery->where('prediction_id = ' . (int) $prediction_id);
        
	$this->jsmdb->setQuery($this->jsmquery);
    
    if(version_compare(JVERSION,'3.0.0','ge')) 
{
// Joomla! 3.0 code here
		$records = $this->jsmdb->loadColumn();
}
elseif(version_compare(JVERSION,'2.5.0','ge')) 
{
// Joomla! 2.5 code here
		$records = $this->jsmdb->loadResultArray();
}

    if ( $predresult = $records )
    {
        // Select some fields
        $this->jsmquery->clear();
        $this->jsmquery->select('id AS value, name AS text');
        $this->jsmquery->from('#__users ');
        $this->jsmquery->where('id not in (' . implode(",", $records ) .')');
        $this->jsmquery->order('name');
        

    }
    else
    {
        // Select some fields
        $this->jsmquery->clear();
        $this->jsmquery->select('id AS value, name AS text');
        $this->jsmquery->from('#__users ');
        $this->jsmquery->order('name');

    }   
		
try{
		$this->jsmdb->setQuery( $this->jsmquery );
		$result = $this->jsmdb->loadObjectList();
		return $result;
}
        catch (Exception $e)
        {
        $this->jsmapp->enqueueMessage(Text::_($e->getMessage()), 'error');
        return false;
        }
	}
	
	

}
?>
