<?php


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
                        'tmb.approved'
                        );
                parent::__construct($config);
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
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Initialise variables.
		$app = JFactory::getApplication('administrator');
        
        //$mainframe->enqueueMessage(JText::_('sportsmanagementModelsmquotes populateState context<br><pre>'.print_r($this->context,true).'</pre>'   ),'');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$published = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
        
        $temp_user_request = $this->getUserStateFromRequest($this->context.'.filter.prediction_id', 'filter_prediction_id', '');
        $this->setState('filter.prediction_id', $temp_user_request);

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('u.username', 'asc');
	}
        
	function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        
        $search	= $this->getState('filter.search');
        $search_state	= $this->getState('filter.state');
        $prediction_id = $this->getState('filter.prediction_id');
        
        // Create a new query object.
        $query = $this->_db->getQuery(true);
        $query->select(array('tmb.*','u.name AS realname', 'u.username AS username', 'p.name AS predictionname' ))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_member AS tmb')
        ->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_game AS p ON p.id = tmb.prediction_id')
        ->join('LEFT', '#__users AS u ON u.id = tmb.user_id');

        if (is_numeric($prediction_id))
        {
            $query->where('tmb.prediction_id = ' . $prediction_id);
        }
        if (is_numeric($search_state))
        {
            $query->where('tmb.approved = ' . $search_state);
        }
        
        if ($search)
		{
        $query->where('(LOWER(u.username) LIKE ' . $db->Quote( '%' . $search . '%' ) );
        }



		 $query->order($db->escape($this->getState('list.ordering', 'u.username')).' '.
                $db->escape($this->getState('list.direction', 'ASC')));
 
$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__.' <br><pre>'.print_r($query->dump(),true).'</pre>'),'');

		return $query;
	}




	
	
	function getPredictionProjectName($predictionID)
	{
	$mainframe			=& JFactory::getApplication();
	$option				= 'com_joomleague';
	
		$query="SELECT	ppj.name AS pjName
				  FROM #__joomleague_prediction_game AS ppj 
          where ppj.id = " . $predictionID;
				  

		$this->_db->setQuery($query);
		$result = $this->_db->loadResult();
	  
    //$mainframe->enqueueMessage(JText::_('<br />predictionID<pre>~' . print_r($predictionID,true) . '~</pre><br />'),'Notice');
    //$mainframe->enqueueMessage(JText::_('<br />result<pre>~' . print_r($result,true) . '~</pre><br />'),'Notice');
    
		return $result;
	}
	
	function getPredictionMembers($prediction_id)
	{
	$query="	SELECT	pm.user_id AS value,
							u.name AS text
					FROM #__joomleague_prediction_member AS pm
					LEFT JOIN #__users AS u ON	u.id = pm.user_id
					WHERE	prediction_id = " . (int) $prediction_id;
	
    $this->_db->setQuery($query);
	$results = $this->_db->loadObjectList();
    return $results;				
	}
	
	function getJLUsers($prediction_id)
	{
	//$not_in = array();
	$query="	SELECT	pm.user_id AS value
					FROM #__joomleague_prediction_member AS pm
					LEFT JOIN #__users AS u ON	u.id = pm.user_id
					WHERE	prediction_id = " . (int) $prediction_id;
	$this->_db->setQuery($query);
    
    if ( $predresult = $this->_db->loadResultArray() )
    {
    $query = "	SELECT	id AS value,
							name AS text
					FROM #__users
          where id not in (" . implode(",", $this->_db->loadResultArray() ) .")
					ORDER by name";
    }
    else
    {
    $query = "	SELECT	id AS value,
							name AS text
					FROM #__users
					ORDER by name";
    }   
		

		$this->_db->setQuery( $query );

		if ( !$result = $this->_db->loadObjectList() )
		{
			sportsmanagementModeldatabasetool::writeErrorLog(get_class($this), __FUNCTION__, __FILE__, $this->_db->getErrorMsg(), __LINE__);
			return false;
		}
		else
		{
			return $result;
		}
	}
	
	function save_memberlist()
	{
	$mainframe			=& JFactory::getApplication();
	$option				= 'com_joomleague';
	
  $post	= JRequest::get('post');
	$cid	= JRequest::getVar('cid', array(0), 'post', 'array');
  $prediction_id = (int) $cid[0];
  //echo '<br />save_memberlist post<pre>~' . print_r($post,true) . '~</pre><br />';
  
  //$mainframe->enqueueMessage(JText::_('<br />save_memberlist post<pre>~' . print_r($post,true) . '~</pre><br />'),'Notice');
  //$mainframe->enqueueMessage(JText::_('<br />prediction id<pre>~' . print_r($prediction_id,true) . '~</pre><br />'),'Notice');
  
  
  foreach ( $post['prediction_members'] as $key => $value )
  {
  //$mainframe->enqueueMessage(JText::_('<br />memberlist id<pre>~' . print_r($value,true) . '~</pre><br />'),'Notice');
  //$table = 'predictionmember';
  $table = 'predictionentry';
  $rowproject =& JTable::getInstance( $table, 'Table' );
  //$rowproject->load( $value );
  $rowproject->prediction_id = $prediction_id;
  $rowproject->user_id = $value;
  $rowproject->registerDate = JHtml::date(time(),'%Y-%m-%d %H:%M:%S');
  $rowproject->approved = 1;
  if ( !$rowproject->store() )
  {
  //echo 'project -> '.$value. ' nicht gesichert <br>';
  }
  else
  {
  //echo 'project -> '.$value. ' gesichert <br>';
  }
        
  }
  
  }
	

}
?>