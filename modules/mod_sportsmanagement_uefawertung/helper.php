<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_uefawertung
 * @file       helper.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

/**
 * modJSMUefaWERTUNG
 *
 * @package
 * @author    abcde
 * @copyright 2015
 * @version   $Id$
 * @access    public
 */
class modJSMUefaWERTUNG
{

	/**
	 * modJSMUefaWERTUNG::getData()
	 *
	 * @param   mixed  $params
	 *
	 * @return
	 */
	public static function getData($params)
	{
		$app = Factory::getApplication();
		$jinput = $app->input;
		$db = sportsmanagementHelper::getDBConnection();
		$query = $db->getQuery(true);
        $query->clear();
        $query->select('name');
		$query->from('#__sportsmanagement_season');
		$query->where('id = ' . (int) $params->get('s'));
		$db->setQuery($query);
		$season_name = $db->loadResult();
      
//echo __LINE__.' params  <br><pre>'.print_r($params  ,true).'</pre>';      
//echo __LINE__.' season_name  <br><pre>'.print_r($season_name  ,true).'</pre>';

$query->clear();

$query->select('season');
$query->from('#__sportsmanagement_uefawertung ');
$query->where('season <= ' . $db->Quote('' . $season_name . ''));

$query->order('season DESC');
$query->group('season');
$query->setLimit('5');
$db->setQuery($query);

$row = $db->loadAssocList();

//echo __LINE__.' row  <br><pre>'.print_r($row  ,true).'</pre>';

$column = $db->loadColumn();
//echo __LINE__.' column  <br><pre>'.print_r($column  ,true).'</pre>';

$season_names = "'" . implode("','", $column) . "'";

//echo __LINE__.' season_names  <br><pre>'.print_r($season_names  ,true).'</pre>';

$query->clear();
$query->select('*');
$query->from('#__sportsmanagement_uefawertung ');
$query->where('season IN (' . $season_names . ')' );

$query->order('season ASC');


$db->setQuery($query);
$row = $db->loadObjectList();

//echo __LINE__.' query  <br><pre>'.print_r($query->dump()  ,true).'</pre>';
//echo __LINE__.' points  <br><pre>'.print_r($row  ,true).'</pre>';

$uefawertungneu = array();
$uefawertung = array();
foreach ( $row as $key => $value )   
{
$uefawertung[$value->country][$value->season] = $value->points;

}

$nummer = 0;
foreach ( $uefawertung as $key => $value )   
{
$start = 1; 
$total = 0;  
foreach ( $value as $key2 => $value2 )   
{  

  
  
//echo __LINE__.' key2  <br><pre>'.print_r($key2  ,true).'</pre>';
//echo __LINE__.' value2  <br><pre>'.print_r($value2  ,true).'</pre>';
if ( $start == 1 )  
{
$object = new stdClass();  
$object->team = $key;   
}
switch ($start)  
{
  case 1:
    case 2:
    case 3:
    case 4:
//$object = new stdClass();
//$object->season = $key2;  
//$object->points = $value2;    
   // $object->team = $key; 
    $object->$key2 = $value2; 
    //$uefawertungneu[$nummer][] = $object;   
//$uefawertungneu[$key][] = $object;   
    $total += $value2;
    break;
  case 5:
  //  $object = new stdClass();
//$object->season = $key2;  
//$object->points = $value2;  
    $object->$key2 = $value2; 
    //$object->team = $key;
//$uefawertungneu[$key][] = $object; 
   // $uefawertungneu[$nummer][] = $object;   
    $total += $value2;
    //$object = new stdClass();
    
    //$total = preg_replace('.', ',', $total);
   // $total = str_replace('.', ',', $total);
$object->total = $total;  
//$object->points = $total;   
    //$object->team = $key; 
    $uefawertungneu[$nummer] = $object;   
//$uefawertungneu[$key][] = $object; 
    $total = 0;
    $start = 1;
    break;
}  
$start++;  
} 

  $nummer++;
}




//echo __LINE__.' points  <br><pre>'.print_r($uefawertung  ,true).'</pre>';



// desc sort
uasort($uefawertungneu,function($first,$second){
    return $first->total < $second->total;
  //return strcmp($second->total, $first->total);
});

echo __LINE__.' points sortiert  <br><pre>'.print_r($uefawertungneu  ,true).'</pre>';



		

		$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect

		return $result;

	}

}
