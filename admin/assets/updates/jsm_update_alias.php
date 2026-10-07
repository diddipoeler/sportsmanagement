<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage updates
 * @file       jsm_update_alias.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Joomla\Database\DatabaseInterface;

$app = Factory::getApplication();
$table = $app->getInput()->getCmd('table');


$version           = '1.0.53';

$minor = 0;
$major = 0;
$build = 0;
$revision = '';

$updateFileDate    = '2016-02-01';
$updateFileTime    = '00:05';
$updateDescription = '<span style="color:orange">'.Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UPDATES_ALIAS').'</span>';
$excludeFile       = 'false';

$maxImportTime = ComponentHelper::getParams('com_sportsmanagement')->get('max_import_time', 0);

if (empty($maxImportTime))
{
	$maxImportTime = 880;
}


if ((int) ini_get('max_execution_time') < $maxImportTime)
{
	@set_time_limit($maxImportTime);
}

$maxImportMemory = ComponentHelper::getParams('com_sportsmanagement')->get('max_import_memory', 0);

if (empty($maxImportMemory))
{
	$maxImportMemory = '150M';
}


if ((int) ini_get('memory_limit') < (int) $maxImportMemory)
{
	ini_set('memory_limit', $maxImportMemory);
}


if (!class_exists(SportsManagementDatabaseResolver::class)) {
    $resolverFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Helper/SportsManagementDatabaseResolver.php';
    if (is_file($resolverFile)) {
        require_once $resolverFile;
    }
}

if (!class_exists(SportsManagementDatabaseResolver::class)) {
    throw new \RuntimeException('SportsManagement database resolver could not be loaded.', 500);
}

/** @var DatabaseInterface $joomlaDatabase */
$joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
$db = (new SportsManagementDatabaseResolver())->resolve(null, $joomlaDatabase);


if ($table)
{
	switch ($table)
	{
		case 'person':

			$query = $db->createQuery();
			$query->select('id,firstname,lastname');
			$query->from('#__sportsmanagement_' . $table);
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				// Create an object for the record we are going to update.
				$object = new stdClass;

				// Must be a valid primary key value.
				$object->id    = $row->id;
				$object->alias = OutputFilter::stringURLSafe($row->firstname) . '-' . OutputFilter::stringURLSafe($row->lastname);

				// Update their details in the table using id as the primary key.
				$result_update = $db->updateObject('#__sportsmanagement_' . $table, $object, 'id', true);
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

			$query = $db->createQuery();
			$query->select('id,name');
			$query->from('#__sportsmanagement_' . $table);
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $row)
			{
				// Create an object for the record we are going to update.
				$object = new stdClass;

				// Must be a valid primary key value.
				$object->id    = $row->id;
				$object->alias = OutputFilter::stringURLSafe($row->name);

				// Update their details in the table using id as the primary key.
				$result_update = $db->updateObject('#__sportsmanagement_' . $table, $object, 'id', true);
			}
			break;
	}
}


echo '<form method="get" id="adminForm" action="">';
foreach ([
    'person' => 'Personen',
    'league' => 'Ligen',
    'season' => 'Saison',
    'club' => 'Vereine',
    'team' => 'Mannschaften',
    'playground' => 'Spielstätten',
    'division' => 'Gruppen',
    'project' => 'Projekte',
    'round' => 'Spieltage',
] as $tableValue => $label) {
    echo '<button type="submit" name="table" value="' . $tableValue . '">' . $label . '</button>';
}
echo '</form>';


