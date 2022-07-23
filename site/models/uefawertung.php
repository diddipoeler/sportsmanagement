<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage uefawertung
 * @file       uefawertung.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * sportsmanagementModeluefawertung
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2022
 * @version $Id$
 * @access public
 */
class sportsmanagementModeluefawertung extends JSMModelLegacy
{
    
var $coefficientyear = '';

/**
 * sportsmanagementModeluefawertung::__construct()
 * 
 * @return void
 */
function __construct()
	{
		parent::__construct();
        $this->coefficientyear = $this->jsmjinput->post->get('coefficientyear', '');
		$this->coefficientyear = $this->jsmjinput->getString('coefficientyear', '');
		
	}
    
        
    /**
     * sportsmanagementModeluefawertung::getcoefficientyears()
     * 
     * @return void
     */
    function getcoefficientyears()
    {
        $result = array();
        $this->jsmquery->clear(); 
        $this->jsmquery->select('season AS id, season AS name');
		$this->jsmquery->from('#__sportsmanagement_uefawertung');
        $this->jsmquery->group('season');
		$this->jsmquery->order('season DESC');
        try
		{
        $this->jsmdb->setQuery($this->jsmquery );
        $result = $this->jsmdb->loadObjectList();
        }
		catch (Exception $e)
		{
			$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()), 'error');
			$this->jsmapp->enqueueMessage(Text::sprintf('COM_SPORTSMANAGEMENT_FILE_ERROR_FUNCTION_FAILED', __FILE__, __LINE__), 'error');
		
		}
        
        return $result;
    }
    
    
    
    /**
     * sportsmanagementModeluefawertung::getSeasonNames()
     * 
     * @param string $coefficientyear
     * @return
     */
    function getSeasonNames($coefficientyear = '')
	{
//		$app = Factory::getApplication();
//		$jinput = $app->input;
//		$db = sportsmanagementHelper::getDBConnection();
//		$query = $db->getQuery(true);
//        $query->clear();
//        $query->select('name');
//		$query->from('#__sportsmanagement_season');
//		$query->where('id = ' . (int) $params->get('s'));
//		$db->setQuery($query);
//		$season_name = $db->loadResult();
    $this->jsmquery->clear();

$this->jsmquery->select('season');
$this->jsmquery->from('#__sportsmanagement_uefawertung ');
$this->jsmquery->where('season <= ' . $this->jsmdb->Quote('' . $coefficientyear . ''));

$this->jsmquery->order('season DESC');
$this->jsmquery->group('season');
$this->jsmquery->setLimit('5');
$this->jsmdb->setQuery($this->jsmquery);

$row = $this->jsmdb->loadAssocList();

//echo __LINE__.' row  <br><pre>'.print_r($row  ,true).'</pre>';

$column = $this->jsmdb->loadColumn();
      return $column;
    
  }
  
  
    /**
     * sportsmanagementModeluefawertung::getcoefficientyearspoints()
     * 
     * @param string $coefficientyear
     * @return void
     */
    function getcoefficientyearspoints($coefficientyear = '')
    {
      $uefawertungneu = array();
$uefawertung = array();
      
      $this->jsmquery->clear();

$this->jsmquery->select('season');
$this->jsmquery->from('#__sportsmanagement_uefawertung ');
$this->jsmquery->where('season <= ' . $this->jsmdb->Quote('' . $coefficientyear . ''));

$this->jsmquery->order('season DESC');
$this->jsmquery->group('season');
$this->jsmquery->setLimit('5');
$this->jsmdb->setQuery($this->jsmquery);

$row = $this->jsmdb->loadAssocList();

//echo __LINE__.' row  <br><pre>'.print_r($row  ,true).'</pre>';

$column = $this->jsmdb->loadColumn();
//echo __LINE__.' column  <br><pre>'.print_r($column  ,true).'</pre>';

$season_names = "'" . implode("','", $column) . "'";

//echo __LINE__.' season_names  <br><pre>'.print_r($season_names  ,true).'</pre>';

$this->jsmquery->clear();
$this->jsmquery->select('*');
$this->jsmquery->from('#__sportsmanagement_uefawertung ');
$this->jsmquery->where('season IN (' . $season_names . ')' );

$this->jsmquery->order('season ASC');


$this->jsmdb->setQuery($this->jsmquery);
$row = $this->jsmdb->loadObjectList();
      
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
      
// desc sort
uasort($uefawertungneu,function($first,$second){
    return $first->total < $second->total;
  //return strcmp($second->total, $first->total);
});      
      
      
      
      return $uefawertungneu;  
        
    }
    
}