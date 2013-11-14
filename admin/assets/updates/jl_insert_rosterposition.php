<?php
/**
 * Joomleague Component script file to CREATE all tables of JoomLeague 1.5
 *
 * @author	Kurt Norgaz
 * @package	JoomLeague
 * @since	1.5 - 2010-08-18
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.pane');
jimport('joomla.installer.installer');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

$version			= '1.6.0-nathalie';
$updateFileDate		= '2012-08-07';
$updateFileTime		= '23:10';
$updateDescription	='<span style="color:orange">Insert the rosterpositions for rosterplayground JoomLeague v1.6.0</span>';
$excludeFile		='false';

require_once( JPATH_ADMINISTRATOR.'/components/com_joomleague/'. 'helpers' . DS . 'jinstallationhelper.php' );

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
$db_table = JPATH_ADMINISTRATOR.'/components/com_joomleague/sql/rosterposition.sql';
// echo '<br>'.$db_table.'<br>';
// $fileContent = JFile::read($db_table);
// $sql_teil = explode(";",$fileContent);

$result = JInstallationHelper::populateDatabase($db, $db_table, $errors);

/*
echo 'fileContent<br><pre>';
print_r($fileContent);
echo '</pre><br>';

echo 'sql_teil<br><pre>';
print_r($sql_teil);
echo '</pre><br>';
*/
  
?>