<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;




class sportsmanagementModelTreetonode extends JSMModelLegacy
{

	var $projectid = 0;
	var $treetoid = 0;

	function __construct( )
	{
		parent::__construct( );
		
		//$app = JFactory::getApplication();
//		$input = $app->input;
		
		$this->projectid = $this->jsmjinput->getInt('p',0);
		$this->treetoid = $this->jsmjinput->getInt('tnid',0);
	}

	function getTreetonode()
	{
		if (!$this->projectid) 
        {
			$this->setError(JText::_('Missing project id'));
			return false;
		}
        $this->jsmquery->clear();
        
		$this->jsmquery->select('ttn.* ');
		$this->jsmquery->select('ttn.id AS ttnid');
		$this->jsmquery->select('c.country AS country');
		$this->jsmquery->select('c.logo_small AS logo_small');
		$this->jsmquery->select('t.name AS team_name ');
		$this->jsmquery->select('t.middle_name AS middle_name ');
		$this->jsmquery->select('t.short_name AS short_name ');
		$this->jsmquery->select('t.id AS tid ');
		$this->jsmquery->select('ttn.title AS title ');
		$this->jsmquery->select('ttn.content AS content ');
		$this->jsmquery->select('tt.tree_i AS tree_i ');
		$this->jsmquery->select('tt.hide AS hide ');
        $this->jsmquery->from('#__sportsmanagement_treeto_node AS ttn ');   
        

		$this->jsmquery->join('LEFT','#__sportsmanagement_project_team AS pt ON pt.id = ttn.team_id ');
        $this->jsmquery->join('LEFT','#__sportsmanagement_season_team_id AS st on pt.team_id = st.id ');
		$this->jsmquery->join('LEFT','#__sportsmanagement_team AS t ON t.id = st.team_id ');
		$this->jsmquery->join('LEFT','#__sportsmanagement_club AS c ON c.id = t.club_id ');
		$this->jsmquery->join('LEFT','#__sportsmanagement_treeto AS tt ON tt.id = ttn.treeto_id ');
        $this->jsmquery->where('ttn.treeto_id = ' .  (int) $this->treetoid );
        $this->jsmquery->order('ttn.row');
       
        
		$this->jsmdb->setQuery( $this->jsmquery );
//		$this->treetonode = $this->jsmdb->loadObjectList();
		
		return $this->jsmdb->loadObjectList();
	}
	
	function getNodeMatches($ttnid=0)
	{
	   $this->jsmquery->clear();
       $this->jsmquery->select('mc.id AS value ');
       $this->jsmquery->select('CONCAT(t1.name, \'_vs_\', t2.name, \' [round:\',r.roundcode,\']\') AS text');
       $this->jsmquery->from('#__sportsmanagement_match AS mc ');   
       $this->jsmquery->join('LEFT','#__sportsmanagement_project_team AS pt1 ON pt1.id = mc.projectteam1_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_project_team AS pt2 ON pt2.id = mc.projectteam2_id');
       
       $this->jsmquery->join('LEFT','#__sportsmanagement_season_team_id AS st1 on pt1.team_id = st1.id');  
       $this->jsmquery->join('LEFT','#__sportsmanagement_season_team_id AS st2 on pt2.team_id = st2.id');  
       
       $this->jsmquery->join('LEFT','#__sportsmanagement_team AS t1 ON t1.id = st1.team_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_team AS t2 ON t2.id = st2.team_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_round AS r ON r.id = mc.round_id');
       $this->jsmquery->join('LEFT','#__sportsmanagement_treeto_match AS ttm ON mc.id = ttm.match_id ');
       
//		$query = ' SELECT mc.id AS value ';
//		$query .=	' ,CONCAT(t1.name, \'_vs_\', t2.name, \' [round:\',r.roundcode,\']\') AS text ';
//	//	$query .=	' ,mc.id AS notes ';
//	//	$query .=	' ,mc.id AS info ';
//		$query .=	' FROM #__sportsmanagement_match AS mc ';
//		$query .=	' LEFT JOIN #__sportsmanagement_project_team AS pt1 ON pt1.id = mc.projectteam1_id ';
//		$query .=	' LEFT JOIN #__sportsmanagement_project_team AS pt2 ON pt2.id = mc.projectteam2_id ';
//        
//        $query .=	' LEFT JOIN #__sportsmanagement_season_team_id AS st1 on pt1.team_id = st1.id ';
//        $query .=	' LEFT JOIN #__sportsmanagement_season_team_id AS st2 on pt2.team_id = st2.id ';
//       
//		$query .=	' LEFT JOIN #__sportsmanagement_team AS t1 ON t1.id = st1.team_id ';
//		$query .=	' LEFT JOIN #__sportsmanagement_team AS t2 ON t2.id = st2.team_id ';
//		$query .=	' LEFT JOIN #__sportsmanagement_round AS r ON r.id = mc.round_id ';
//		$query .=	' LEFT JOIN #__sportsmanagement_treeto_match AS ttm ON mc.id = ttm.match_id ';
//		$query .=	' WHERE  ttm.node_id = ' . (int) $ttnid ;
//		$query .=	' ORDER BY mc.id ';
//		$query .=	';';
        
        $this->jsmquery->where('ttm.node_id = ' . (int) $ttnid );
        $this->jsmquery->order('mc.id');
		$this->jsmdb->setQuery($this->jsmquery);
	//	if ( !$result = $this->_db->loadObjectList() )
	//	{
	//		$this->setError( $this->_db->getErrorMsg() );
	//		return false;
	//	}
	//	else
	//	{
			//return $result;
			return $this->jsmdb->loadObjectList();
	//	}
	}
	
	function showNodeMatches(&$nodes)
	{
		//TODO
		$matches = $this->model->getNodeMatches($nodes);
		$lineinover = '';
		foreach ($matches as $mat)
		{
			$lineinover .= $mat->text.'<br/>';
		}
		echo $lineinover;
	}
	
	function getRoundName()
	{
		$this->jsmquery->clear();
        $this->jsmquery->select('*');
        $this->jsmquery->from('#__sportsmanagement_round AS r');   
        $this->jsmquery->where('r.project_id = ' . (int) $this->projectid );
        $this->jsmquery->order('r.round_date_first, r.ordering');
          
//        $query = 'SELECT * '
//			. ' FROM #__sportsmanagement_round AS r '
//			. ' WHERE r.project_id = ' .  $this->_db->Quote($this->projectid)
//			. ' ORDER BY r.round_date_first, r.ordering '
//			;
		$this->jsmdb->setQuery( $this->jsmquery );
		//$this->roundname = $this->jsmdb->loadObjectList();
        
        

		return $this->jsmdb->loadObjectList();
	}
}
