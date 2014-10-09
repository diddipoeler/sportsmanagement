<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
//require_once (JPATH_COMPONENT.DS.'models'.DS.'list.php');



class sportsmanagementModelTreetos extends JModelList
{
	var $_identifier = "treetos";
	static $_project_id = 0;
    
    public function __construct($config = array())
        {
            $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
                $this->_project_id	= $app->getUserState( "$option.pid", '0' );
                //$config['filter_fields'] = array(
//                        'r.name',
//                        'r.roundcode',
//                        'r.round_date_first',
//                        'r.round_date_last',
//                        'r.id',
//                        'r.ordering'
//                        );
                parent::__construct($config);
        }
        
	protected function getListQuery()
	{
	   $app = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $search	= $this->getState('filter.search');
        
//		// Get the WHERE and ORDER BY clauses for the query
//		$where		= $this->_buildContentWhere();
//		$orderby	= $this->_buildContentOrderBy();
		
        $query = JFactory::getDbo()->getQuery(true);
        // Select some fields
		$query->select('tt.*');
		// From the rounds table
		$query->from('#__'.COM_SPORTSMANAGEMENT_TABLE.'_treeto AS tt');
        $query->join('LEFT', '#__'.COM_SPORTSMANAGEMENT_TABLE.'_division d on d.id = tt.division_id');
        $query->where('tt.project_id = ' . $this->_project_id);
		
//        $query = '	SELECT	tt.* ';
//		$query .=	' FROM #__joomleague_treeto AS tt ';
//		$query .=	' LEFT JOIN #__joomleague_division d on d.id = tt.division_id ';
//		$query .=	$where . $orderby ;


		return $query;
	}


	//function _buildContentOrderBy()
//	{
//		$orderby 	= ' ORDER BY tt.id DESC ';
//		return $orderby;
//	}

	//function _buildContentWhere()
//	{
//		$option = JRequest::getCmd('option');
//		$app	= JFactory::getApplication();
//		$project_id = $app->getUserState( $option . 'project' );
//		$division = (int) $app->getUserStateFromRequest( $option.'tt_division', 'division', 0 );
//		$division=JString::strtolower($division);
//		$where = ' WHERE  tt.project_id = ' . $project_id ;
//		if($division > 0)
//		{
//			$where .= ' AND d.id = ' . $this->_db->Quote($division) ;
//		}
//		return $where;
//	}

	function storeshort( $cid, $data )
	{
		$result = true;
		for ( $x = 0; $x < count( $cid ); $x++ )
		{
			
			$tblTreeto = JTable::getInstance('Treeto','sportsmanagementTable');
			$tblTreeto->id = $cid[$x];
			$tblTreeto->division_id =	$data['division_id' . $cid[$x]];
			
			if (!$tblTreeto->check())
			{
				$this->setError($tblTreeto->getError());
				$result = false;
			}
			if (!$tblTreeto->store())
			{
				$this->setError($tblTreeto->getError());
				$result = false;
			}
		}
		return $result;
	}

}
?>