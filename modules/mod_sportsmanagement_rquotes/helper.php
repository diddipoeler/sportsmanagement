<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHRLEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/
 
//no direct access
defined('_JEXEC') or die('Restricted access');



/**
 * modRquotesHelper
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class modRquotesHelper
{ 

//-----------------------------------------------------------------------------------------------------------------------------
/**
 * modRquotesHelper::renderRquote()
 * 
 * @param mixed $rquote
 * @param mixed $params
 * @return void
 */
static function renderRquote(&$rquote, &$params,$module)
	{	
	require(JModuleHelper::getLayoutPath($module->module,'_rquote'));
	}
//---------------------------------------------------------------------------------------------------------------------------------------------------	
/**
 * modRquotesHelper::getRandomRquote()
 * 
 * @param mixed $category
 * @return
 */
static function getRandomRquote($category,$num_of_random, &$params)
{
    $x = 0;
    $catid = 0;
    $row = array();
    $app = JFactory::getApplication();
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params<br><pre>'.print_r($params,true).'</pre>'),'Notice');
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' category<br><pre>'.print_r($category,true).'</pre>'),'Notice');
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' num_of_random<br><pre>'.print_r($num_of_random,true).'</pre>'),'Notice');
    
        if ( $params->get('cfg_which_database') )
        {
		$db = sportsmanagementHelper::getDBConnection(TRUE, $params->get('cfg_which_database') );
        }
        else
        {
        $db = sportsmanagementHelper::getDBConnection();    
        } 
		
        if ( isset($category) )
        {
            
        if(is_array($category)) // get $catid when one category is selected 	
		{
			$x = count($category);
		}

        
//		$x = count($category);
//        $app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' x<br><pre>'.print_r($x,true).'</pre>'),'Notice');
	
	if( $x == 1) // get $catid when one category is selected 	
		{
			$catid = $category[0];
		}
	else // get quote when more than one category is selected 	
		{
		  if( is_array($category) && count($category) != 0 ) // get $catid when one category is selected 	
		{
			$value = array($category);			
			$rand_keys = array_rand($category,1);
			$catid = $category[$rand_keys];
            }
		}	
	
		
		
		$query = $db->getQuery(true);
		// Select some fields
		$query->select('obj.*,p.picture as person_picture');
		// From the hello table
		$query->from('#__sportsmanagement_rquote as obj');
        // Join over the users for the checked out user.
		$query->join('LEFT', '#__sportsmanagement_person as p ON p.id = obj.person_id');
        $query->where('obj.published = 1');
        if ( $catid )
        {
        $query->where('obj.catid = '.$catid);
        }
        	
//$app->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' '.'<pre>'.print_r($query->dump(),true).'</pre>' ),'');

		$db->setQuery( $query );
		$rows = $db->loadObjectList();
		
		$i = rand(0, count($rows) - 1 );

		if ( $rows )
        {
        $row = array( $rows[$i] );
        }

//echo get_class($this).' '.__FUNCTION__.' category<pre>'.print_r($category,true).'</pre><br>';		
//echo get_class($this).' '.__FUNCTION__.' row<pre>'.print_r($row,true).'</pre><br>';
		
}
$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
		return $row;
		
	}
	
//----------------------------------------------------------------------------------------------------
/**
 * modRquotesHelper::getMultyRandomRquote()
 * 
 * @param mixed $category
 * @param mixed $num_of_random
 * @return
 */
static function getMultyRandomRquote($category,$num_of_random, &$params)
	{
	   $app = JFactory::getApplication();
	   $x = 0;
       $catid = 0;
       $qrows = NULL;
		
        if ( $params->get('cfg_which_database') )
        {
		$db = sportsmanagementHelper::getDBConnection(TRUE, $params->get('cfg_which_database') );
        }
        else
        {
        $db = sportsmanagementHelper::getDBConnection();    
        } 
        
		if(is_array($category)) // get $catid when one category is selected 	
		{
			$x = count($category);
		}
	if($x =='1')  // get multible quotes when one category is selected 	
		{
			$catid = $category[0];
		}
	else  // get multible quotes when more than one category is selected 	
		{
		  if(is_array($category)) // get $catid when one category is selected 	
		{
			$value = array($category);
			$rand_keys = array_rand($category,1);
			$catid = $category[$rand_keys];
            }
		}	
			
            $query = $db->getQuery(true);
		// Select some fields
		$query->select('obj.*,p.picture as person_picture');
		// From the hello table
		$query->from('#__sportsmanagement_rquote as obj');
        // Join over the users for the checked out user.
		$query->join('LEFT', '#__sportsmanagement_person as p ON p.id = obj.person_id');
        $query->where('obj.published = 1');
        if ( $catid )
        {
        $query->where('obj.catid = '.$catid);
        }

//$app->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' '.'<pre>'.print_r($query->dump(),true).'</pre>' ),'');
				
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

if ( $rows )
{
			/**
			* create array based on number of rows.
			*/
			$cnt = count($rows);
			$numbers = array_fill(0, $cnt,'');

			/**
			* Get  unique random keys from $numbers array.
			* change  to number of desired random quotes
			*/
			
			$rand_keys = array_rand($numbers,"$num_of_random");

			/**
			* create array of data rows to return.
			*/
			$qrows = array();

			foreach ($rand_keys as $key => $value) 
            {
			$qrows[] = $rows[$value];
			}
 }
            
//echo get_class($this).' '.__FUNCTION__.' category<pre>'.print_r($category,true).'</pre><br>';		
//echo get_class($this).' '.__FUNCTION__.' qrows<pre>'.print_r($qrows,true).'</pre><br>';
$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect            
		return $qrows;
		}
//-----------------------------------------------


		
			
	
//--------------------------------------------------------------------------------------------------------------------------------
/**
 * modRquotesHelper::getSequentialRquote()
 * 
 * @param mixed $category
 * @return
 */
static function getSequentialRquote($category, &$params)
	{
	   $app = JFactory::getApplication();
	   $x = 0;
       $row = NULL;
       $catid = 0;
       
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' params<br><pre>'.print_r($params,true).'</pre>'),'Notice');
    //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' category<br><pre>'.print_r($category,true).'</pre>'),'Notice');


	// by PD, not yet implemented

	// make use of cookie to store last displayed rquote

	// if cookies not enabled then fetch randomly
//$query = "SELECT * from	#__rquotes WHERE published='1' and catid = $category";
 
 
	if ( $params->get('cfg_which_database') )
        {
		$db = sportsmanagementHelper::getDBConnection(TRUE, $params->get('cfg_which_database') );
        }
        else
        {
        $db = sportsmanagementHelper::getDBConnection();    
        } 
        
	 	if(is_array($category)) // get $catid when one category is selected 	
		{
			$x = count($category);
		}
	if($x == 1) 
		{
			
			$catid = $category[0];
			
		}	
		elseif( $x > 1)
		{ 
		  echo JText::_('MOD_SPORTSMANAGEMENT_RQUOTES_SAVE_DISPLAY_INFORMATION_ONE');
          }
//$query = "SELECT * from	#__sportsmanagement_rquote WHERE published='1' and catid = $catid";
$query = $db->getQuery(true);
		// Select some fields
		$query->select('obj.*,p.picture as person_picture');
		// From the hello table
		$query->from('#__sportsmanagement_rquote as obj');
        // Join over the users for the checked out user.
		$query->join('LEFT', '#__sportsmanagement_person as p ON p.id = obj.person_id');
        $query->where('obj.published = 1');
        if ( $catid )
        {
        $query->where('obj.catid = '.$catid);
        } 
	$db->setQuery( $query );

//$app->enqueueMessage(JText::_(__METHOD__.' '.__FUNCTION__.' '.'<pre>'.print_r($query->dump(),true).'</pre>' ),'');

	$rows = $db->loadObjectList();

 if ( $rows )
 {
	$numRows = count($rows) - 1;

	if (isset($_COOKIE['rquote']))
    {
		$i = intval($_COOKIE['rquote']);
		if ($i < $numRows)
			$i++;
		else 
			$i = 0;
 
		setcookie('rquote',$i,time()+3600);

		$row = array( $rows[$i] );

	}
    else 
    {
		// pick a random value
		$i = rand(0, $numRows);
		setcookie('rquote',$i,time()+3600);
		$row = array( $rows[$i] );
	}
}
$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect 
	return $row;

}

//}
//-------------------------------------------------------------------------------------------------------------
/**
 * getTextFile()
 * 
 * @param mixed $params
 * @param mixed $filename
 * @return
 */
function getTextFile(&$params,$filename,$module)
{
jimport('joomla.filesystem.file');

		$path= JPATH_BASE."/modules/".$module->module."/".$module->module."/".$filename;
		$cleanpath = JPATH::clean($path);
		$contents = JFile::read($cleanpath);
		$lines = explode("\n", $contents);
		$count = count($lines);
		$rows = explode("\n", $contents);
		$num = rand(0,$count-1);
		
	require(JModuleHelper::getLayoutPath($module->module,'textfile'));

	return $rows;
 }
 
//----------------------------------------------------------------------------------------------------------------------- 
/**
 * getTextFile2()
 * 
 * @param mixed $params
 * @param mixed $filename
 * @return void
 */
function getTextFile2(&$params,$filename,$module)
{
	jimport('joomla.filesystem.file');

	$today = date("d");
	$num=($today-1);
	$path = JPATH_BASE."/modules/".$module->module."/".$module->module."/".$filename;
	$cleanpath = JPATH::clean($path);
	$contents = JFile::read($cleanpath);
	$lines = explode("\n", $contents);
	$count = count($lines);
	$rows = explode("\n", $contents);

	require(JModuleHelper::getLayoutPath($module->module,'textfile'));
	}

//------------------------------------------------------------------------------------------------	
/**
 * getDailyRquote()
 * 
 * @param mixed $category
 * @param mixed $x
 * @return
 */
function getDailyRquote($category,$x, &$params)
	{
	
	$db = sportsmanagementHelper::getDBConnection(TRUE, $params->get('cfg_which_database') );
	
//	$query="SELECT count(*) from #__rquote WHERE published='1' AND catid='$category'";
$xx= count($category);
	if($xx =='1') 
		
		
			$catid =	$category[0];
	
	$query="SELECT count(*) from #__sportsmanagement_rquote WHERE published='1' AND catid='$catid'";
	
	
	
	
	$db->setQuery($query,0);
	$no_of_quotes=$db->loadResult();
	$query="SELECT * FROM #__rquote_meta WHERE id='1'";
	$db->setQuery($query,0);
	$row=$db->loadRow();
	
	$number_reached = $row[1];
	$date_modified= $row[2];
	
	// get the current day of the month (from 1 to 31)

	$day_today = date("j");

	
		if ($date_modified != $day_today){
		// we have reached the end of the quotes
		if ($number_reached >($no_of_quotes - 1)){
			$number_reached = 1;
			$db = sportsmanagementHelper::getDBConnection(TRUE, $params->get('cfg_which_database') );
			$query3 = "UPDATE `#__rquote_meta`  SET `date_modified`= '$day_today', number_reached = '$number_reached' WHERE id='1'";
			$db->setQuery($query3);
			$row=$db->query();
		} else {
		// we haven't reached the end of the quotes	- therefore we increment $number_reached
		
		$number_reached = $number_reached + 1;
		
		
		$query3 = "UPDATE `#__rquote_meta`  SET `date_modified`= '$day_today', number_reached = '$number_reached' WHERE id='1'";
	$db->setQuery($query3);
	$row=$db->query();
		}
	}
	// we get the quote with 'catid = $number_reached' from the database
	$getQuoteQuery = "SELECT * FROM #__sportsmanagement_rquote WHERE published='1' AND catid = '$catid' AND daily_number = '$number_reached'";
	$db->setQuery($getQuoteQuery,0);
	$row = $db->loadObjectList();
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
	return $row;
}
//------------------------------------------------------------------------------------------------	
/**
 * getWeeklyRquote()
 * 
 * @param mixed $category
 * @param mixed $x
 * @return
 */
function getWeeklyRquote($category,$x, &$params)
	{
	
	$db = sportsmanagementHelper::getDBConnection(TRUE, $params->get('cfg_which_database') );
	
//	$query="SELECT count(*) from #__rquote WHERE published='1' AND catid='$category'";
$xx= count($category);
	if($xx =='1') 
		
		
			$catid =	$category[0];
	
	$query="SELECT count(*) from #__sportsmanagement_rquote WHERE published='1' AND catid='$catid'";
	
	
	
	
	$db->setQuery($query,0);
	$no_of_quotes=$db->loadResult();
	$query="SELECT * FROM #__rquote_meta where ID='2'";
	$db->setQuery($query,0);
	$row=$db->loadRow();
	
	$number_reached = $row[1];
	$date_modified= $row[2];
	
	// get the current day of the month (from 1 to 31)

	$day_today = date("W");

	
		if ($date_modified != $day_today){
		// we have reached the end of the quotes
		if ($number_reached >($no_of_quotes - 1)){
			$number_reached = 1;
			$db = sportsmanagementHelper::getDBConnection(TRUE, $params->get('cfg_which_database') );
			$query3 = "UPDATE `#__rquote_meta` SET `date_modified`= '$day_today', number_reached = '$number_reached'WHERE id='2' ";
			$db->setQuery($query3);
			$row=$db->query();
		} else {
		// we haven't reached the end of the quotes	- therefore we increment $number_reached
		
		$number_reached = $number_reached + 1;
		
		
		$query3 = "UPDATE `#__rquote_meta` SET `date_modified`= '$day_today', number_reached = '$number_reached' WHERE id='2'";
	$db->setQuery($query3);
	$row=$db->query();
		}
	}
	// we get the quote with 'catid = $number_reached' from the database
	$getQuoteQuery = "SELECT * FROM #__sportsmanagement_rquote WHERE published='1' AND catid = '$catid' AND daily_number = '$number_reached'";
	$db->setQuery($getQuoteQuery,0);
	$row = $db->loadObjectList();
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
	return $row;
}
//------------------------------------------------------------------------------------------------	
/**
 * getMonthlyRquote()
 * 
 * @param mixed $category
 * @param mixed $x
 * @return
 */
function getMonthlyRquote($category,$x, &$params)
	{
	
	$db = sportsmanagementHelper::getDBConnection(TRUE, $params->get('cfg_which_database') );
	
//	$query="SELECT count(*) from #__rquote WHERE published='1' AND catid='$category'";
$xx= count($category);
	if($xx =='1') 
		
		
			$catid =	$category[0];
	
	$query="SELECT count(*) from #__sportsmanagement_rquote WHERE published='1' AND catid='$catid'";
	
	
	
	
	$db->setQuery($query,0);
	$no_of_quotes=$db->loadResult();
	$query="SELECT * FROM #__rquote_meta where ID='3'";
	$db->setQuery($query,0);
	$row = $db->loadRow();
	
	$number_reached = $row[1];
	$date_modified= $row[2];
	
	// get the current day of the month (from 1 to 31)

	$day_today = date("n");

	
		if ($date_modified != $day_today){
		// we have reached the end of the quotes
		if ($number_reached >($no_of_quotes - 1)){
			$number_reached = 1;
			$db = sportsmanagementHelper::getDBConnection(TRUE, $params->get('cfg_which_database') );
			$query3 = "UPDATE `#__rquote_meta` SET `date_modified`= '$day_today', number_reached = '$number_reached'WHERE id='3'";
			$db->setQuery($query3);
			$row=$db->query();
		} else {
		// we haven't reached the end of the quotes	- therefore we increment $number_reached
		
		$number_reached = $number_reached + 1;
		
		
		$query3 = "UPDATE `#__rquote_meta` SET `date_modified`= '$day_today', number_reached = '$number_reached'WHERE id='3'";
	$db->setQuery($query3);
	$row=$db->query();
		}
	}
	// we get the quote with 'catid = $number_reached' from the database
	$getQuoteQuery = "SELECT * FROM #__sportsmanagement_rquote WHERE published='1' AND catid = '$catid' AND daily_number = '$number_reached'";
	$db->setQuery($getQuoteQuery,0);
	$row = $db->loadObjectList();
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
	return $row;
}
//------------------------------------------------------------------------------------------------	
/**
 * getYearlyRquote()
 * 
 * @param mixed $category
 * @param mixed $x
 * @return
 */
function getYearlyRquote($category,$x, &$params)
	{
	
	$db = sportsmanagementHelper::getDBConnection(TRUE, $params->get('cfg_which_database') );
	
//	$query="SELECT count(*) from #__rquote WHERE published='1' AND catid='$category'";
$xx= count($category);
	if($xx =='1') 
		
		
			$catid =	$category[0];
	
	$query="SELECT count(*) from #__sportsmanagement_rquote WHERE published='1' AND catid='$catid'";
	
	
	
	
	$db->setQuery($query,0);
	$no_of_quotes=$db->loadResult();
	$query="SELECT * FROM #__rquote_meta where ID='4'";
	$db->setQuery($query,0);
	$row=$db->loadRow();
	
	$number_reached = $row[1];
	$date_modified= $row[2];
	
	// get the current day of the month (from 1 to 31)

	$day_today = date("Y");


		if ($date_modified != $day_today){
		// we have reached the end of the quotes
		if ($number_reached >($no_of_quotes - 1)){
			$number_reached = 1;
			$db = sportsmanagementHelper::getDBConnection(TRUE, $params->get('cfg_which_database') );
			$query3 = "UPDATE `#__rquote_meta` SET `date_modified`= '$day_today', number_reached = '$number_reached'WHERE id='4'";
			$db->setQuery($query3);
			$row=$db->query();
		} else {
		// we haven't reached the end of the quotes	- therefore we increment $number_reached
		
		$number_reached = $number_reached + 1;
		
		
		$query3 = "UPDATE `#__rquote_meta` SET `date_modified`= '$day_today', number_reached = '$number_reached'WHERE id='4'";
	$db->setQuery($query3);
	$row=$db->query();
		}
	}
	// we get the quote with 'catid = $number_reached' from the database
	$getQuoteQuery = "SELECT * FROM #__sportsmanagement_rquote WHERE published='1' AND catid = '$catid' AND daily_number = '$number_reached'";
	$db->setQuery($getQuoteQuery,0);
	$row = $db->loadObjectList();
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
	return $row;
}
//------------------------------------------------------------------------------------------------	
/**
 * getTodayRquote()
 * 
 * @param mixed $category
 * @param mixed $x
 * @return
 */
function getTodayRquote($category,$x, &$params)
{

	$db = sportsmanagementHelper::getDBConnection(TRUE, $params->get('cfg_which_database') );
	$catid =	$category[0];
	$day_today = date("z");

	$getQuoteQuery = "SELECT * FROM #__sportsmanagement_rquote WHERE published='1' AND catid = '$catid' AND daily_number = '$day_today' ";
	$db->setQuery($getQuoteQuery,0);
	$row=$db->loadObjectList();
	
	if(!$row)
	{
		$query = "SELECT * from	#__sportsmanagement_rquote WHERE published='1' and catid = $catid";
		$db->setQuery( $query );
		$rows = $db->loadObjectList();
		
		$i = rand(0, count($rows) - 1 );

		$row = array( $rows[$i] );
	}
	$db->disconnect(); // See: http://api.joomla.org/cms-3/classes/JDatabaseDriver.html#method_disconnect
	return $row;
}

}
