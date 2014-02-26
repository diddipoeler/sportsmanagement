<?php



// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.modellist' );


/**
 * sportsmanagementModelPredictionGames
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelPredictionGames extends JModelList
{
	var $_identifier = "predgames";
	
    
    public function __construct($config = array())
        {   
                $config['filter_fields'] = array(
                        'pre.name',
                        'pre.id',
                        'pre.ordering'
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

//		$image_folder = $this->getUserStateFromRequest($this->context.'.filter.image_folder', 'filter_image_folder', '');
//		$this->setState('filter.image_folder', $image_folder);
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' image_folder<br><pre>'.print_r($image_folder,true).'</pre>'),'');


//		// Load the parameters.
//		$params = JComponentHelper::getParams('com_sportsmanagement');
//		$this->setState('params', $params);

		// List state information.
		parent::populateState('pre.name', 'asc');
	}
    
	function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        
        $prediction_id = $mainframe->getUserState( "$option.prediction_id", '0' );
        $search	= $this->getState('filter.search');
        $search_state	= $this->getState('filter.state');
        
        
        // Create a new query object.		
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
        $query->select(array('pre.*', 'u.name AS editor'))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_game AS pre')
        ->join('LEFT', '#__users AS u ON u.id = pre.checked_out');

        if ( $prediction_id > 0 )
		{
			$query->where('pre.id = ' . $prediction_id);
		}
        if ( $search )
		{
			$query->where("LOWER(pre.name) LIKE " . $db->Quote('%'.$search.'%'));
		}
        if (is_numeric($search_state))
		{
        $query->where('pre.published = '.$search_state);
        }

		
		return $query;
	}



	

	function getChilds( $pred_id, $all = false )
	{
		$what = 'pro.*';
		if ( $all )
		{
			$what = 'pro.project_id';
		}
		//$query = "SELECT " . $what . " FROM #__joomleague_predictiongame_project WHERE prediction_id = '" . $this->id . "'";
		$query = "	SELECT	" . $what . ",
							joo.name AS project_name

					FROM #__".COM_SPORTSMANAGEMENT_TABLE."_prediction_project AS pro
					LEFT JOIN #__".COM_SPORTSMANAGEMENT_TABLE."_project AS joo ON joo.id=pro.project_id

					WHERE pro.prediction_id = '" . $pred_id . "'";

		$this->_db->setQuery( $query );
		if ( $all )
		{
			return $this->_db->loadResultArray();
		}
		return $this->_db->loadAssocList( 'id' );
	}

	function getAdmins( $pred_id, $list = false )
	{
		$as_what = '';
		if ( $list )
		{
			$as_what = ' AS value';
		}
		#$query = "SELECT user_id" . $as_what . " FROM #__joomleague_predictiongame_admins WHERE prediction_id = " . $this->id;
		$query = "SELECT user_id" . $as_what . " FROM #__".COM_SPORTSMANAGEMENT_TABLE."_prediction_admin WHERE prediction_id = " . $pred_id;

		$this->_db->setQuery( $query );
		if ( $list )
		{
			return $this->_db->loadObjectList();
		}
		else
		{
			return $this->_db->loadResultArray();
		}
	}

	/**
	* Method to return a prediction games array
	*
	* @access  public
	* @return  array
	* @since 0.1
	*/
	function getPredictionGames()
	{
		$query = "	SELECT	id AS value,
							name AS text
					FROM #__".COM_SPORTSMANAGEMENT_TABLE."_prediction_game
					ORDER by name";

		$this->_db->setQuery( $query );

		if ( !$result = $this->_db->loadObjectList() )
		{
			$this->setError( $this->_db->getErrorMsg() );
			return false;
		}
		else
		{
			return $result;
		}
	}

}
?>