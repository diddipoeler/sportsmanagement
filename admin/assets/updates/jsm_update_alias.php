<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      jsm_update_alias.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage updates
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;

$uri	= Factory::getUri();

$table = Factory::getApplication()->input->getVar('table');
$uri->delVar( 'table' );
$link = $uri->toString();

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
$updateDescription	='<span style="color:orange">Update Alias Fields.</span>';
$excludeFile		='false';

$maxImportTime=ComponentHelper::getParams('com_sportsmanagement')->get('max_import_time',0);
if (empty($maxImportTime))
{
	$maxImportTime=880;
}
if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}

$maxImportMemory=ComponentHelper::getParams('com_sportsmanagement')->get('max_import_memory',0);
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
case 'person':

$query = $db->getQuery(true);
$query->select('id,firstname,lastname');
$query->from('#__sportsmanagement_'.$table);
$db->setQuery($query);
$result = $db->loadObjectList();

foreach ( $result as $row )
{
// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $row->id;  
$object->alias = OutputFilter::stringURLSafe( $row->firstname ).'-'.OutputFilter::stringURLSafe( $row->lastname );
// Update their details in the table using id as the primary key.
$result_update = Factory::getDbo()->updateObject('#__sportsmanagement_'.$table, $object, 'id', true);	
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

$query = $db->getQuery(true);
$query->select('id,name');
$query->from('#__sportsmanagement_'.$table);
$db->setQuery($query);
$result = $db->loadObjectList();
	
foreach ( $result as $row )
{
// Create an object for the record we are going to update.
$object = new stdClass();
// Must be a valid primary key value.
$object->id = $row->id;  
$object->alias = OutputFilter::stringURLSafe( $row->name );
// Update their details in the table using id as the primary key.
$result_update = Factory::getDbo()->updateObject('#__sportsmanagement_'.$table, $object, 'id', true);	
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
