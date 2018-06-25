<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
// Include library dependencies
//jimport('joomla.filter.input');

jimport('joomla.filter.output');


// prüft vor Benutzung ob die gewünschte Klasse definiert ist
if ( !class_exists('sportsmanagementHelper') ) 
{
//add the classes for handling
$classpath = JPATH_ADMINISTRATOR.DS.JSM_PATH.DS.'helpers'.DS.'sportsmanagement.php';
JLoader::register('sportsmanagementHelper', $classpath);
JModelLegacy::getInstance("sportsmanagementHelper", "sportsmanagementModel");
}

$uri	= JFactory::getUri();

//$link = $uri->toString();
//$link = $uri->current();

$table = JFactory::getApplication()->input->getVar('table');
$uri->delVar( 'table' );
$link = $uri->toString();
//$request = JFactory::getApplication()->input->get();

//echo '<br>table<pre>',print_r($table,true),'</pre>';

//echo '<br>post<pre>',print_r($_POST,true),'</pre>';
//echo '<br>request<pre>',print_r($_REQUEST,true),'</pre>';
//echo '<br>request<pre>',print_r($request,true),'</pre>';
//echo '<br>uri<pre>',print_r($uri,true),'</pre>';

//echo '<br>link<pre>',print_r($link ,true),'</pre>';

?>
<script type="text/javascript">
function sendData (sData) {
var oldLocation = '<?PHP echo $link;?>';
window.location = oldLocation + '&table=' + sData ;
//  window.location.search = sData;
//  window.location.reload(true)
}
</script>
<?PHP

$version			= '1.0.53';
$updateFileDate		= '2016-02-01';
$updateFileTime		= '00:05';
$updateDescription	='<span style="color:orange">Update Timestamp Fields.</span>';
$excludeFile		='false';

$maxImportTime=JComponentHelper::getParams('com_sportsmanagement')->get('max_import_time',0);
if (empty($maxImportTime))
{
	$maxImportTime=880;
}
if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}

$maxImportMemory=JComponentHelper::getParams('com_sportsmanagement')->get('max_import_memory',0);
if (empty($maxImportMemory))
{
	$maxImportMemory='150M';
}
if ((int)ini_get('memory_limit') < (int)$maxImportMemory){ini_set('memory_limit',$maxImportMemory);}


$db = sportsmanagementHelper::getDBConnection();


if ( $table )
{

switch ($table)
{

case 'project':
$query = $db->getQuery(true);
$query->select('p.id,p.modified');
$query->from('#__sportsmanagement_project as p');
$query->where("p.modified_timestamp = 0");
$db->setQuery($query);
$result = $db->loadObjectList();

foreach ($result as $projekt)
{
if ( $projekt->modified != $db->getNullDate() )
{
$projekt->modified_timestamp = sportsmanagementHelper::getTimestamp($projekt->modified);

// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $projekt->id;
$object->modified_timestamp = $projekt->modified_timestamp;

//echo 'modified_timestamp -> '.$projekt->modified_timestamp.'<br>';
// Update their details in the table using id as the primary key.
$result_update = JFactory::getDbo()->updateObject('#__sportsmanagement_project', $object, 'id');
}

}

break;

case 'match':

$query = $db->getQuery(true);
$query->select('m.id,m.match_date');
$query->from('#__sportsmanagement_match as m');
$query->where("m.match_timestamp = 0");
$db->setQuery($query);
$result = $db->loadObjectList();

foreach ($result as $match)
{
if ( $match->match_date != $db->getNullDate() )
{
$match->match_timestamp = sportsmanagementHelper::getTimestamp($match->match_date);

// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $match->id;
$object->match_timestamp = $match->match_timestamp;
// Update their details in the table using id as the primary key.
$result_update = JFactory::getDbo()->updateObject('#__sportsmanagement_match', $object, 'id');
}

}

break;

}

}


echo '<form method="post" id="adminForm" action="'.$link.'" >';
echo '<br><input type="button" onclick="document.body.innerHTML=\'please wait...\';sendData(\'project\')" value="Projekte" />';
echo '<input type="button" onclick="document.body.innerHTML=\'please wait...\';sendData(\'match\')" value="Match" />';
echo '<input type="button" onclick="document.body.innerHTML=\'please wait...\';sendData(\'person\')" value="Personen" />';
echo '</form>';


?>
