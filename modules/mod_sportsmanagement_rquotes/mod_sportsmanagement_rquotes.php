<?php

 /**
 * Rquotes main file
 * 
 * @package    Joomla.Rquotes
 * @subpackage Modules
 * @link www.mytidbits.us
 * @license		GNU/GPL-2
 */
 
 //no direct access
defined('_JEXEC') or die('Restricted access'); 
if(!defined('DS')){
define('DS',DIRECTORY_SEPARATOR);
error_reporting(0);
}

	//include helper file	
	require_once(dirname(__FILE__).DS.'helper.php'); 

$source = $params->get('source');
//text file params
$filename=$params->get('filename','rquotes.txt');
$randomtext=$params->get('randomtext');
//database params
$style = $params->get('style', 'default'); 
$category = $params->get('category','');
$rotate = $params->get('rotate');
$num_of_random = $params->get('num_of_random');

//echo get_class($this).' '.__FUNCTION__.' source<pre>'.print_r($source,true).'</pre><br>';
//echo get_class($this).' '.__FUNCTION__.' style<pre>'.print_r($style,true).'</pre><br>';
//echo get_class($this).' '.__FUNCTION__.' category<pre>'.print_r($category,true).'</pre><br>';
//echo get_class($this).' '.__FUNCTION__.' rotate<pre>'.print_r($rotate,true).'</pre><br>';
//echo get_class($this).' '.__FUNCTION__.' num_of_random<pre>'.print_r($num_of_random,true).'</pre><br>';

switch ($source) 
{
case 'db':
if($rotate=='single_random')
{

 $list = modRquotesHelper::getRandomRquote($category,$num_of_random);

}


elseif($rotate=='multiple_random')
{

 $list = modRquotesHelper::getMultyRandomRquote($category,$num_of_random);

}
elseif($rotate=='sequential') 

{

	$list = modRquotesHelper::getSequentialRquote($category);

}
elseif($rotate=='daily')
{

$list= getDailyRquote($category);

	
}

elseif($rotate=='weekly')
{

	$list= getWeeklyRquote($category);
	
}
elseif($rotate=='monthly')
{
	
	$list= getMonthlyRquote($category);
	
}
elseif($rotate=='yearly')
{
	
	$list= getYearlyRquote($category);
	
}
//start
elseif($rotate=='today')
{
	
	$list= getTodayRquote($category);
	
}

//end
require(JModuleHelper::getLayoutPath('mod_sportsmanagement_rquotes', $style,'default'));
break;

case 'text':
if (!$randomtext)
{
$list=getTextFile($params,$filename);
}
else
{
$list=getTextFile2($params,$filename);
}
break;
default:
echo('Please choose a text file and Daily or Every page load and save it to display information.');


}
?> 
