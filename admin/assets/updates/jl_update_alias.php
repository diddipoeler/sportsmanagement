<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$uri	= JFactory::getUri();

//$link = $uri->toString();
//$link = $uri->current();

$table = JRequest::getVar('table');
$uri->delVar( 'table' );
$link = $uri->toString();
//$request = JRequest::get();

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



$version			= '2.1.12';
$updateFileDate		= '2013-08-23';
$updateFileTime		= '23:10';
$updateDescription	='<span style="color:orange">Update Alias Fields JoomLeague v 2.1.12</span>';
$excludeFile		='false';

//require_once( JPATH_ADMINISTRATOR.'/components/com_joomleague/'. 'helpers' . DS . 'jinstallationhelper.php' );

$maxImportTime=JComponentHelper::getParams('com_joomleague')->get('max_import_time',0);
if (empty($maxImportTime))
{
	$maxImportTime=880;
}
if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}

$maxImportMemory=JComponentHelper::getParams('com_joomleague')->get('max_import_memory',0);
if (empty($maxImportMemory))
{
	$maxImportMemory='150M';
}
if ((int)ini_get('memory_limit') < (int)$maxImportMemory){ini_set('memory_limit',$maxImportMemory);}


$db =& JFactory::getDBO();


if ( $table )
{

switch ($table)
{
case 'person':
$query="SELECT id, firstname, lastname
	       	FROM #__joomleague_".$table." 
            ";
$db->setQuery($query);
$result = $db->loadObjectList();
echo '<br>';
foreach ( $result as $row )
{
$parts = array( trim( $row->firstname ), trim( $row->lastname ) );
$alias= JFilterOutput::stringURLSafe( implode( ' ', $parts ) );
$row->alias = JFilterOutput::stringURLSafe( $alias );

echo "<span style='color:orange; '>".JText::sprintf('ID-> %1$s Vorname-> %2$s Nachname-> %3$s Alias-> %4$s <br>',$row->id,$row->firstname,$row->lastname,$row->alias).'</span>';


$tbl = JTable::getInstance($table, "Table");
$tbl->load($row->id);   
$tbl->alias = $row->alias;
if (!$tbl->store())
{
echo 'fehler beim speichern der personendaten <br>';
} 
else
{
echo "<span style='color:green; '>".JText::sprintf('update: %1$s <br>','OK').'</span>';    
}

}
break;


case 'league':
case 'season':
case 'club':
case 'team':
case 'playground':
case 'division':
case 'project':
case 'round':
$query="SELECT id, name
	       	FROM #__joomleague_".$table." 
            ";
$db->setQuery($query);
$result = $db->loadObjectList();
echo '<br>';
foreach ( $result as $row )
{
$row->alias = JFilterOutput::stringURLSafe( $row->name );

echo "<span style='color:orange; '>".JText::sprintf('ID-> %1$s Name-> %2$s Alias-> %3$s<br>',$row->id,$row->name,$row->alias).'</span>';


$tbl = JTable::getInstance($table, "Table");
$tbl->load($row->id);   
$tbl->alias = $row->alias;
if (!$tbl->store())
{
echo 'fehler beim speichern<br>';
} 
else
{
echo "<span style='color:green; '>".JText::sprintf('update: %1$s <br>','OK').'</span>';    
}

}
break;

}

}


echo '<form method="post" id="adminForm" action="'.$link.'" >';
echo '<br><input type="button" onclick="document.body.innerHTML=\'please wait...\';sendData(\'person\')" value="Personen" />';
echo '<input type="button" onclick="document.body.innerHTML=\'please wait...\';sendData(\'league\')" value="Ligen" />';
echo '<input type="button" onclick="document.body.innerHTML=\'please wait...\';sendData(\'season\')" value="Saison" />';
echo '<input type="button" onclick="document.body.innerHTML=\'please wait...\';sendData(\'club\')" value="Vereine" />';
echo '<input type="button" onclick="document.body.innerHTML=\'please wait...\';sendData(\'team\')" value="Mannschaften" />';
echo '<input type="button" onclick="document.body.innerHTML=\'please wait...\';sendData(\'playground\')" value="Spielstätten" />';
echo '<input type="button" onclick="document.body.innerHTML=\'please wait...\';sendData(\'division\')" value="Gruppen" />';
echo '<input type="button" onclick="document.body.innerHTML=\'please wait...\';sendData(\'project\')" value="Projekte" />';
echo '<input type="button" onclick="document.body.innerHTML=\'please wait...\';sendData(\'round\')" value="Spieltage" />';
echo '</form>';


?>