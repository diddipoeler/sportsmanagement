<?php 

/**
 * Rquotes helper file
 * 
 * @package    Joomla.Rquotes
 * @subpackage Modules
 * @link www.mytidbits.us
 * @license		GNU/GPL-2
 */
 
//no direct access
defined('_JEXEC') or die('Restricted access');



class modRquotesHelper
{ 

//-----------------------------------------------------------------------------------------------------------------------------
function renderRquote(&$rquote, &$params)
	{	
	require(JModuleHelper::getLayoutPath('mod_sportsmanagement_rquotes','_rquote'));
	}
//---------------------------------------------------------------------------------------------------------------------------------------------------	
function getRandomRquote($category)

{ 
		$db = JFactory::getDBO(); 
		
		$x = count($category);
		
	
	if($x =='1') // get $catid when one category is selected 	
	
		{
			$catid =	$category[0];
		}
	

	else // get quote when more than one category is selected 	
		{
			$value= array($category);			
			$rand_keys=array_rand($category,1);
			$catid = $category[$rand_keys];
		}	
	
		
		
		$query = $db->getQuery(true);
		// Select some fields
		$query->select('obj.*,p.picture as person_picture');
		// From the hello table
		$query->from('#__sportsmanagement_rquote as obj');
        // Join over the users for the checked out user.
		$query->join('LEFT', '#__sportsmanagement_person as p ON p.id = obj.person_id');
        $query->where('obj.published = 1');
        $query->where('obj.catid = '.$catid);
        	
//$query = "SELECT * from	#__sportsmanagement_rquote WHERE published='1' and catid = $catid";
		$db->setQuery( $query );
		$rows = $db->loadObjectList();
		
		$i = rand(0, count($rows) - 1 );

		$row = array( $rows[$i] );

//echo get_class($this).' '.__FUNCTION__.' category<pre>'.print_r($category,true).'</pre><br>';		
//echo get_class($this).' '.__FUNCTION__.' row<pre>'.print_r($row,true).'</pre><br>';
		

		return $row;
		
	}
	
//----------------------------------------------------------------------------------------------------
function getMultyRandomRquote($category,$num_of_random)
	{
		$db = JFactory::getDBO();
		$x= count($category);
	if($x =='1')  // get multible quotes when one category is selected 	
		{
			$catid =	$category[0];
	
		}
	

	else  // get multible quotes when more than one category is selected 	
	
		{
			$value= array($category);
			$rand_keys=array_rand($category,1);
			$catid=	$category[$rand_keys];
		}	
			
            $query = $db->getQuery(true);
		// Select some fields
		$query->select('obj.*,p.picture as person_picture');
		// From the hello table
		$query->from('#__sportsmanagement_rquote as obj');
        // Join over the users for the checked out user.
		$query->join('LEFT', '#__sportsmanagement_person as p ON p.id = obj.person_id');
        $query->where('obj.published = 1');
        $query->where('obj.catid = '.$catid);
			//$query = "SELECT * from	#__sportsmanagement_rquote WHERE published='1' and catid = $catid";
				
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

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

			foreach ($rand_keys as $key => $value) {

			$qrows[] = $rows[$value];
			}
            
//echo get_class($this).' '.__FUNCTION__.' category<pre>'.print_r($category,true).'</pre><br>';		
//echo get_class($this).' '.__FUNCTION__.' qrows<pre>'.print_r($qrows,true).'</pre><br>';
            
		return $qrows;
		}
//-----------------------------------------------


		
			
	
//--------------------------------------------------------------------------------------------------------------------------------
function getSequentialRquote($category)
	{

	// by PD, not yet implemented

	// make use of cookie to store last displayed rquote

	// if cookies not enabled then fetch randomly
//$query = "SELECT * from	#__rquotes WHERE published='1' and catid = $category";
 
 
	$db =& JFactory::getDBO();
	 	$x= count($category);
	if($x =='1') 
		{
			
			$catid =	$category[0];
			
		}	
		else
		{ echo 'Please choose only one category';}
$query = "SELECT * from	#__sportsmanagement_rquote WHERE published='1' and catid = $catid";
 
	$db->setQuery( $query );

	$rows = $db->loadObjectList();

 
	$numRows = count($rows) - 1;

	if (isset($_COOKIE['rquote'])){

		$i = intval($_COOKIE['rquote']);

		if ($i < $numRows)

			$i++;

		else 

			$i = 0;

 
		setcookie('rquote',$i,time()+3600);

		$row = array( $rows[$i] );

	} else {

		// pick a random value

		$i = rand(0, $numRows);

		setcookie('rquote',$i,time()+3600);

		$row = array( $rows[$i] );

	}

 
	return $row;

}

}
//-------------------------------------------------------------------------------------------------------------
function getTextFile(&$params,$filename)
{
jimport('joomla.filesystem.file');

		$path= JPATH_BASE."/modules/mod_sportsmanagement_rquotes/mod_sportsmanagement_rquotes/$filename";
		$cleanpath=JPATH::clean($path);
		$contents=file_get_contents($cleanpath);
		$lines=explode("\n", $contents);
		$count=count($lines);
		$rows=explode("\n", $contents);
		$num=rand(0,$count-1);
		
	require(JModuleHelper::getLayoutPath('mod_sportsmanagement_rquotes','textfile'));

	return $rows;
 }
 
//----------------------------------------------------------------------------------------------------------------------- 
function getTextFile2(&$params,$filename)
{
	jimport('joomla.filesystem.file');

	$today=date("d");
	$num=($today-1);
	$path= JPATH_BASE."/modules/mod_sportsmanagement_rquotes/mod_sportsmanagement_rquotes/$filename";
	$cleanpath=JPATH::clean($path);
	$contents=file_get_contents($cleanpath);
	$lines=explode("\n", $contents);
	$count=count($lines);
	$rows=explode("\n", $contents);

	require(JModuleHelper::getLayoutPath('mod_sportsmanagement_rquotes','textfile'));
	}

//------------------------------------------------------------------------------------------------	
function getDailyRquote($category,$x)
	{
	
	$db =& JFactory::getDBO();
	
//	$query="SELECT count(*) from #__rquote WHERE published='1' AND catid='$category'";
$xx= count($category);
	if($xx =='1') 
		
		
			$catid =	$category[0];
	
	$query="SELECT count(*) from #__sportsmanagement_rquote WHERE published='1' AND catid='$catid'";
	
	
	
	
	$db->setQuery($query,0);
	$no_of_quotes=$db->loadResult();
	$query="SELECT * FROM #__rquote_meta WHERE id='1'";
	$db->setQuery($query,0);
	$row=$db->loadColumn();
	
	$number_reached = $row[1];
	$date_modified= $row[2];
	
	// get the current day of the month (from 1 to 31)

	$day_today = date("j");

	
		if ($date_modified != $day_today){
		// we have reached the end of the quotes
		if ($number_reached >($no_of_quotes - 1)){
			$number_reached = 1;
			$db =& JFactory::getDBO();
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
	$row=$db->loadObjectList();
	
	return $row;
}
//------------------------------------------------------------------------------------------------	
function getWeeklyRquote($category,$x)
	{
	
	$db =& JFactory::getDBO();
	
//	$query="SELECT count(*) from #__rquote WHERE published='1' AND catid='$category'";
$xx= count($category);
	if($xx =='1') 
		
		
			$catid =	$category[0];
	
	$query="SELECT count(*) from #__sportsmanagement_rquote WHERE published='1' AND catid='$catid'";
	
	
	
	
	$db->setQuery($query,0);
	$no_of_quotes=$db->loadResult();
	$query="SELECT * FROM #__rquote_meta where ID='2'";
	$db->setQuery($query,0);
	$row=$db->loadColumn();
	
	$number_reached = $row[1];
	$date_modified= $row[2];
	
	// get the current day of the month (from 1 to 31)

	$day_today = date("W");

	
		if ($date_modified != $day_today){
		// we have reached the end of the quotes
		if ($number_reached >($no_of_quotes - 1)){
			$number_reached = 1;
			$db =& JFactory::getDBO();
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
	$row=$db->loadObjectList();
	
	return $row;
}
//------------------------------------------------------------------------------------------------	
function getMonthlyRquote($category,$x)
	{
	
	$db =& JFactory::getDBO();
	
//	$query="SELECT count(*) from #__rquote WHERE published='1' AND catid='$category'";
$xx= count($category);
	if($xx =='1') 
		
		
			$catid =	$category[0];
	
	$query="SELECT count(*) from #__sportsmanagement_rquote WHERE published='1' AND catid='$catid'";
	
	
	
	
	$db->setQuery($query,0);
	$no_of_quotes=$db->loadResult();
	$query="SELECT * FROM #__rquote_meta where ID='3'";
	$db->setQuery($query,0);
	$row=$db->loadColumn();
	
	$number_reached = $row[1];
	$date_modified= $row[2];
	
	// get the current day of the month (from 1 to 31)

	$day_today = date("n");

	
		if ($date_modified != $day_today){
		// we have reached the end of the quotes
		if ($number_reached >($no_of_quotes - 1)){
			$number_reached = 1;
			$db =& JFactory::getDBO();
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
	$row=$db->loadObjectList();
	
	return $row;
}
//------------------------------------------------------------------------------------------------	
function getYearlyRquote($category,$x)
	{
	
	$db =& JFactory::getDBO();
	
//	$query="SELECT count(*) from #__rquote WHERE published='1' AND catid='$category'";
$xx= count($category);
	if($xx =='1') 
		
		
			$catid =	$category[0];
	
	$query="SELECT count(*) from #__sportsmanagement_rquote WHERE published='1' AND catid='$catid'";
	
	
	
	
	$db->setQuery($query,0);
	$no_of_quotes=$db->loadResult();
	$query="SELECT * FROM #__rquote_meta where ID='4'";
	$db->setQuery($query,0);
	$row=$db->loadColumn();
	
	$number_reached = $row[1];
	$date_modified= $row[2];
	
	// get the current day of the month (from 1 to 31)

	$day_today = date("Y");


		if ($date_modified != $day_today){
		// we have reached the end of the quotes
		if ($number_reached >($no_of_quotes - 1)){
			$number_reached = 1;
			$db =& JFactory::getDBO();
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
	$row=$db->loadObjectList();
	
	return $row;
}
//------------------------------------------------------------------------------------------------	
function getTodayRquote($category,$x)
{

	$db =& JFactory::getDBO();
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
	return $row;
}
