<?php


// no direct access
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'helpers'.DS.'countries.php');
require_once (JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'helpers'.DS.'route.php');


class modJSMNewProjectHelper
{

	
	function getData()
	{
		//global $mainframe;
    $db = &JFactory::getDBO();
    
    
$heutestart = date("Y-m-d");		
$heuteende = date("Y-m-d");
		
$heutestart .= ' 00:00:00';		
$heuteende .= ' 23:59:00';

$query = "SELECT pro.id,
pro.name,
pro.current_round as roundcode,
CONCAT_WS(':',pro.id,pro.alias) AS project_slug,
le.name as liganame,
le.country
FROM #__sportsmanagement_project as pro
inner join #__sportsmanagement_league as le 
on le.id = pro.league_id
WHERE pro.modified BETWEEN '" . $heutestart . "' AND '" . $heuteende . "' order by pro.name ASC ";

$db->setQuery( $query );
$anzahl = $db->loadObjectList();
		
foreach ( $anzahl as $row )
{

if ( $row->roundcode )
{
$query = "SELECT r.name,
CONCAT_WS(':',r.id,r.alias) AS round_slug
FROM #__sportsmanagement_round as r
WHERE r.project_id =" . $row->id . "
and r.id= ".$row->roundcode;
$db->setQuery( $query );

$result2 = $db->loadObject();
$row->roundcode = $result2->round_slug;

}







$temp = new stdClass();
$temp->name = $row->name;
$temp->liganame = $row->liganame;
$temp->roundcode = $row->roundcode;
//$temp->id = $row->id;
$temp->id = $row->project_slug;
$temp->country = $row->country;
$result[] = $temp;
$result = array_merge($result);
}

		return $result;
		
	}
	
	

	
}