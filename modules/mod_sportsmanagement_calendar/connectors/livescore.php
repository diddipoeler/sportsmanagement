<?php
class LivescoreConnector extends JLCalendar{
  //var $database = JFactory::getDbo();
  var $xparams;
  var $prefix;
  function getMatches ( &$caldates, &$params, &$matches ) {
    $this->xparams = $params;
    $this->prefix = $params->prefix;
    $rows = LivescoreConnector::getRows($caldates);
    $output = LivescoreConnector::formatRows($rows, $matches);
    //print_r($output);
    return $output;
  }
  function formatRows( $rows, &$matches ) {
    $newrows = array();
    
    foreach ($rows AS $key => $row) {
      $newrows[$key]['type'] = 'ls';
      $newrows[$key]['date'] = $row->mdate;
      $newrows[$key]['result'] = 'LIVE!';
      $newrows[$key]['headingtitle'] = parent::jl_utf8_convert ('LiveScore', 'iso-8859-1', 'utf-8');
      $newrows[$key]['homename'] = parent::jl_utf8_convert ($row->heim, 'iso-8859-1', 'utf-8');
      $newrows[$key]['homepic'] = '';
      $newrows[$key]['awaypic'] = '';
      $newrows[$key]['awayname'] = parent::jl_utf8_convert ($row->gast, 'iso-8859-1', 'utf-8');
		  $newrows[$key]['matchcode'] = $row->saison;
		  $newrows[$key]['project_id'] = 'LIVE!';
		  $matches[] = $newrows[$key];
		}
		return $newrows;
  }
  function getRows($caldates, $ordering='ASC'){
    $database = JFactory::getDbo();
      $query = "SELECT  * FROM #__livescore_games";
      $where = ' WHERE ';
      $where .= " mdate >= '".$caldates['start']."'";
      $where .= " AND mdate <= '".$caldates['end']."'";
      $where .= ' ORDER BY mdate '.$ordering;
      $query = ($this->prefix != '') ? str_replace('#__', $this->prefix, $query) : $query;
      $query .= $where;
      $database->setQuery($query);
      if ( !$result = $database->loadObjectList() ) $result = Array();
      
      return $result;
  }
  
  function build_url( &$row ) {
    
  }
  
}  