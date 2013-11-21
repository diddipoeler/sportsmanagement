<?php



// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.modellist' );


class sportsmanagementModelPredictionGames extends JModelList
{
	var $_identifier = "predgames";
	
	function getListQuery()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        // Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();
        
        // Create a new query object.
        $query = $this->_db->getQuery(true);
        $query->select(array('pre.*', 'u.name AS editor'))
        ->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_prediction_game AS pre')
        ->join('LEFT', '#__users AS u ON u.id = pre.checked_out');

        if ($where)
        {
            $query->where($where);
        }
        if ($orderby)
        {
            $query->order($orderby);
        }

		
		return $query;
	}

	function _buildContentOrderBy()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');

		$filter_order		= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. 'pre_filter_order','filter_order','pre.name','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. 'pre_filter_order_Dir','filter_order_Dir','','word');

		if ( $filter_order == 'pre.name' )
		{
			$orderby 	= 'pre.name ' . $filter_order_Dir;
		}
		else
		{
			$orderby 	= '' . $filter_order . ' ' . $filter_order_Dir . ' , pre.name ';
		}

		return $orderby;
	}

	function _buildContentWhere()
	{
		$mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');

		$filter_state		= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. 'pre_filter_state','filter_state','','word');
		$filter_order		= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. 'pre_filter_order','filter_order','pre.name','cmd');
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. 'pre_filter_order_Dir','filter_order_Dir','','word');
		$search				= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. 'pre_search','search','','string');
		$search_mode		= $mainframe->getUserStateFromRequest( $option .'.'.$this->_identifier. 'pre_search_mode','search_mode','','string');
		$search				= JString::strtolower( $search );

		$where = array();
		$prediction_id = $mainframe->getUserState( "$option.prediction_id", '0' );
		if ( $prediction_id > 0 )
		{
			$where[] = 'pre.id = ' . $prediction_id;
		}

		if ( $search )
		{
			$where[] = "LOWER(pre.name) LIKE " . $this->_db->Quote( $search . '%' );
		}

		if ( $filter_state )
		{
			if ( $filter_state == 'P' )
			{
				$where[] = 'pre.published = 1';
			}
			elseif ($filter_state == 'U' )
				{
					$where[] = 'pre.published = 0';
				}
		}

		$where 	= ( count( $where ) ? ''. implode( ' AND ', $where ) : '' );

		return $where;
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