<?php

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
//require_once JPATH_COMPONENT.'/models/list.php';



class sportsmanagementModelTreetomatchs extends JSMModelList
{
	var $_identifier = "treetomatchs";



protected function getListQuery()
	{
	// Select the required fields from the table.
    $this->jsmquery->clear();
		$this->jsmquery->select('mc.id AS mid,mc.match_number AS match_number,t1.name AS projectteam1,mc.team1_result AS projectteam1result,mc.team2_result AS projectteam2result');
		$this->jsmquery->select('t2.name AS projectteam2,mc.round_id AS rid,mc.published AS published,ttm.node_id AS node_id,r.roundcode AS roundcode, mc.checked_out');
        
		$this->jsmquery->from('#__sportsmanagement_match AS mc');   
       $this->jsmquery->join('LEFT','#__sportsmanagement_project_team AS pt1 ON pt1.id = mc.projectteam1_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_project_team AS pt2 ON pt2.id = mc.projectteam2_id');
       
       $this->jsmquery->join('LEFT','#__sportsmanagement_season_team_id AS st1 on pt1.team_id = st1.id');  
       $this->jsmquery->join('LEFT','#__sportsmanagement_season_team_id AS st2 on pt2.team_id = st2.id');  
       
       $this->jsmquery->join('LEFT','#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_round AS r ON r.id = mc.round_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_treeto_match AS ttm ON mc.id = ttm.match_id ');
       
       $this->jsmquery->where('ttm.node_id = ' . (int) $this->jsmjinput->get('nid') );
       
    $this->jsmquery->order('r.roundcode');   
       
      // $this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'Notice');

		return $this->jsmquery;
    }   
    
//	function _buildQuery()
//	{
//		//// Get the WHERE and ORDER BY clauses for the query
////		$where		= $this->_buildContentWhere();
////		$orderby	= $this->_buildContentOrderBy();
//		
//		$query = ' SELECT mc.id AS mid ';
//		$query .=	' ,mc.match_number AS match_number';
//		$query .=	' ,t1.name AS projectteam1';
//		$query .=	' ,mc.team1_result AS projectteam1result';
//		$query .=	' ,mc.team2_result AS projectteam2result';
//		$query .=	' ,t2.name AS projectteam2';
//		$query .=	' ,mc.round_id AS rid ';
//		$query .=	' ,mc.published AS published ';
//		$query .=	' ,ttm.node_id AS node_id ';
//		$query .=	' ,r.roundcode AS roundcode, mc.checked_out ';
//		$query .=	' FROM #__joomleague_match AS mc ';
//		$query .=	' LEFT JOIN #__joomleague_project_team AS pt1 ON pt1.id = mc.projectteam1_id ';
//		$query .=	' LEFT JOIN #__joomleague_project_team AS pt2 ON pt2.id = mc.projectteam2_id ';
//		$query .=	' LEFT JOIN #__joomleague_team AS t1 ON t1.id = pt1.team_id ';
//		$query .=	' LEFT JOIN #__joomleague_team AS t2 ON t2.id = pt2.team_id ';
//		$query .=	' LEFT JOIN #__joomleague_round AS r ON r.id = mc.round_id ';
//		$query .=	' LEFT JOIN #__joomleague_treeto_match AS ttm ON mc.id = ttm.match_id ';
//		$query .=	$where ;
//		$query .=	$orderby ;
//		return $query;
//	}

	//function _buildContentOrderBy()
//	{
//		$orderby = ' ORDER BY r.roundcode ';
//		
//		return $orderby;
//	}

	//function _buildContentWhere()
//	{
//		$option = $this->input->getCmd('option');
//		
//		$app	= JFactory::getApplication();
//		$node_id = $app->getUserState($option . 'node_id');
//		$where = ' WHERE  ttm.node_id = ' . $node_id ;
//		
//		return $where;
//	}

	function store( $data )
	{
		$result = true;
		$peid = $data['node_matcheslist'];
        $this->jsmquery->clear();
		if ( $peid == null )
		{
//			$query = "	DELETE
//						FROM #__joomleague_treeto_match
//						WHERE node_id = '" . $data['id'] . "'";
$conditions = array(
    $this->jsmdb->quoteName('node_id') . ' = ' . $this->jsmdb->quote($data['id'])
);
        }
		else
		{
			JArrayHelper::toInteger( $peid );
			$peids = implode( ',', $peid );
//			$query = "	DELETE
//						FROM #__joomleague_treeto_match
//						WHERE node_id = '" . $data['id'] . "' AND match_id NOT IN  (" . $peids . ")";
$conditions = array(
    $this->jsmdb->quoteName('node_id') . ' = ' . $this->jsmdb->quote($data['id']),
    $this->jsmdb->quoteName('match_id') . ' NOT IN  ( ' . $this->jsmdb->quote($peids) .')'
);		
        }

$this->jsmquery->delete($this->jsmdb->quoteName('#__sportsmanagement_treeto_match'));
$this->jsmquery->where($conditions);

		$this->jsmdb->setQuery( $this->jsmquery );
		if ( !sportsmanagementModeldatabasetool::runJoomlaQuery() )
		{
			$this->setError( $this->jsmdb->getErrorMsg() );
			$result = false;
		}

		for ( $x = 0; $x < count( $data['node_matcheslist'] ); $x++ )
		{
// Create and populate an object.
$profile = new stdClass();
$profile->node_id = $data['id'];
$profile->match_id = $data['node_matcheslist'][$x];
// Insert the object into the user profile table.
$result = $this->jsmdb->insertObject('#__sportsmanagement_treeto_match', $profile);
			
//            $query = "	INSERT IGNORE
//						INTO #__joomleague_treeto_match
//						(node_id, match_id)
//						VALUES ( '" . $data['id'] . "', '".$data['node_matcheslist'][$x] . "')";
//
//			$this->_db->setQuery( $query );
			if ( !$result )
			{
				$this->setError( $this->jsmdb->getErrorMsg() );
				$result = false;
			}
		}
		return $result;
	}

	

	function getMatches()
	{
	//	$option = $this->input->getCmd('option');
//		$app	= JFactory::getApplication();
		$node_id = $this->jsmjinput->get('nid');
		$treeto_id = $this->jsmjinput->get('tid');
		$project_id = $this->jsmjinput->get('pid');


$this->jsmquery->clear();
$this->jsmsubquery1->clear();

		$this->jsmquery->select('mc.id AS value,CONCAT(t1.name, \'_vs_\', t2.name, \' [round:\',r.roundcode,\']\') AS text');
		$this->jsmquery->select('mc.id AS info');
        
		$this->jsmquery->from('#__sportsmanagement_match AS mc');   
       $this->jsmquery->join('LEFT','#__sportsmanagement_project_team AS pt1 ON pt1.id = mc.projectteam1_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_project_team AS pt2 ON pt2.id = mc.projectteam2_id');
       
       $this->jsmquery->join('LEFT','#__sportsmanagement_season_team_id AS st1 on pt1.team_id = st1.id');  
       $this->jsmquery->join('LEFT','#__sportsmanagement_season_team_id AS st2 on pt2.team_id = st2.id');  
       
       $this->jsmquery->join('LEFT','#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_round AS r ON r.id = mc.round_id');
       
       $this->jsmquery->where('r.project_id = ' . $project_id );
       
       	$this->jsmsubquery1->select('ttn.team_id');
       $this->jsmsubquery1->from('#__sportsmanagement_treeto_node AS ttn');
       $this->jsmsubquery1->join('LEFT','#__sportsmanagement_treeto_node AS ttn2 ON (ttn.node = 2*ttn2.node OR ttn.node = 2*ttn2.node + 1) ');   
       $this->jsmsubquery1->where('ttn2.id = ' . $node_id );
       $this->jsmsubquery1->where('ttn.treeto_id = ' . $treeto_id );

       $this->jsmquery->where('NOT mc.projectteam1_id IN ( ' . $this->jsmsubquery1 .' )' );
$this->jsmquery->where('NOT mc.projectteam2_id IN ( ' . $this->jsmsubquery1 .' )' );
    $this->jsmquery->order('r.id');   
       
       		//$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'Notice');
            
//		$query = ' SELECT mc.id AS value ';
//		$query .=	' ,CONCAT(t1.name, \'_vs_\', t2.name, \' [round:\',r.roundcode,\']\') AS text ';
//		$query .=	' ,mc.id AS info ';
//		$query .=	' FROM #__joomleague_match AS mc ';
//		$query .=	' LEFT JOIN #__joomleague_project_team AS pt1 ON pt1.id = mc.projectteam1_id ';
//		$query .=	' LEFT JOIN #__joomleague_project_team AS pt2 ON pt2.id = mc.projectteam2_id ';
//		$query .=	' LEFT JOIN #__joomleague_team AS t1 ON t1.id = pt1.team_id ';
//		$query .=	' LEFT JOIN #__joomleague_team AS t2 ON t2.id = pt2.team_id ';
//		$query .=	' LEFT JOIN #__joomleague_round AS r ON r.id = mc.round_id ';
//		$query .=	' WHERE  r.project_id = ' . $project_id ;
//		$query .=	' AND NOT mc.projectteam1_id IN ';
//		$query .=	' ( ';
//		$query .=		' SELECT ttn.team_id ';
//		$query .=		' FROM #__joomleague_treeto_node AS ttn' ;
//		$query .=		' LEFT JOIN #__joomleague_treeto_node AS ttn2 ';
//		$query .=		' ON (ttn.node = 2*ttn2.node OR ttn.node = 2*ttn2.node + 1) ' ;
//		$query .=		' WHERE  ttn2.id = ' . $node_id ;
//		$query .=		' AND  ttn.treeto_id = ' . $treeto_id ;
//		$query .=	' ) ';
//		$query .=	' AND NOT mc.projectteam2_id IN ';
//		$query .=	' ( ';
//		$query .=		' SELECT ttn.team_id ';
//		$query .=		' FROM #__joomleague_treeto_node AS ttn' ;
//		$query .=		' LEFT JOIN #__joomleague_treeto_node AS ttn2 ';
//		$query .=		' ON (ttn.node = 2*ttn2.node OR ttn.node = 2*ttn2.node + 1) ' ;
//		$query .=		' WHERE  ttn2.id = ' . $node_id ;
//		$query .=		' AND  ttn.treeto_id = ' . $treeto_id ;
//		$query .=	' ) ';
	//	$query .=	' ORDER BY r.id ';
//		$query .=	';';
		
		$this->jsmdb->setQuery( $this->jsmquery );
		if ( !$result = $this->jsmdb->loadObjectList() )
		{
			$this->setError( $this->jsmdb->getErrorMsg() );
			return false;
		}
		else
		{
			return $result;
		}
	}

	//function getMatchInNode()
	function getNodeMatches($node_id=0)
	{
	//	$query = ' SELECT mc.id AS value ';
//		$query .=	' ,CONCAT(t1.name, \'_vs_\', t2.name, \' [round:\',r.roundcode,\']\') AS text ';
//		$query .=	' ,mc.id AS notes ';
//		$query .=	' ,mc.id AS info ';
//		$query .=	' FROM #__joomleague_match AS mc ';
//		$query .=	' LEFT JOIN #__joomleague_project_team AS pt1 ON pt1.id = mc.projectteam1_id ';
//		$query .=	' LEFT JOIN #__joomleague_project_team AS pt2 ON pt2.id = mc.projectteam2_id ';
//		$query .=	' LEFT JOIN #__joomleague_team AS t1 ON t1.id = pt1.team_id ';
//		$query .=	' LEFT JOIN #__joomleague_team AS t2 ON t2.id = pt2.team_id ';
//		$query .=	' LEFT JOIN #__joomleague_round AS r ON r.id = mc.round_id ';
//		$query .=	' LEFT JOIN #__joomleague_treeto_match AS ttm ON mc.id = ttm.match_id ';
//		$query .=	' WHERE  ttm.node_id = ' . $node_id ;
//		$query .=	' ORDER BY mc.id ';
//		$query .=	';';
//		$this->_db->setQuery( $query );
//		if ( !$result = $this->_db->loadObjectList() )
//		{
//			$this->setError( $this->_db->getErrorMsg() );
//			return false;
//		}
//		else
//		{
//			return $result;
//		}
        
        
$this->jsmquery->clear();
		$this->jsmquery->select('mc.id AS value,CONCAT(t1.name, \'_vs_\', t2.name, \' [round:\',r.roundcode,\']\') AS text');
		$this->jsmquery->select('mc.id AS notes,mc.id AS info');
        
		$this->jsmquery->from('#__sportsmanagement_match AS mc');   
       $this->jsmquery->join('LEFT','#__sportsmanagement_project_team AS pt1 ON pt1.id = mc.projectteam1_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_project_team AS pt2 ON pt2.id = mc.projectteam2_id');
       
       $this->jsmquery->join('LEFT','#__sportsmanagement_season_team_id AS st1 on pt1.team_id = st1.id');  
       $this->jsmquery->join('LEFT','#__sportsmanagement_season_team_id AS st2 on pt2.team_id = st2.id');  
       
       $this->jsmquery->join('LEFT','#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_round AS r ON r.id = mc.round_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_treeto_match AS ttm ON mc.id = ttm.match_id ');
       
       $this->jsmquery->where('ttm.node_id = ' . $this->jsmjinput->get('nid') );
       
    $this->jsmquery->order('mc.id');           
        
        //$this->jsmapp->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($this->jsmquery->dump(),true).'</pre>'),'Notice');
        
        $this->jsmdb->setQuery( $this->jsmquery );
		if ( !$result = $this->jsmdb->loadObjectList() )
		{
			$this->setError( $this->jsmdb->getErrorMsg() );
			return false;
		}
		else
		{
			return $result;
		}
        
        
	}
}
