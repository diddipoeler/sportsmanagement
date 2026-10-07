<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage updates
 * @file       jsm_update_timestamp.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Joomla\Database\DatabaseInterface;

$app = Factory::getApplication();
$table = $app->getInput()->getCmd('table');

<?PHP

$version           = '1.0.53';

$minor = 0;
$major = 0;
$build = 0;
$revision = '';

$updateFileDate    = '2016-02-01';
$updateFileTime    = '00:05';
$updateDescription = '<span style="color:orange">'.Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UPDATES_TIMESTAMP').'</span>';
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

$toTimestamp = static function (mixed $value) use ($app): int|false {
    if ($value === null || $value === '0000-00-00 00:00:00' || $value === '0000-00-00 15:30:00') {
        $value = 'now';
    }

    try {
        return (new Date((string) $value, new \DateTimeZone('UTC')))->toUnix();
    } catch (\Throwable $e) {
        $app->enqueueMessage(
            Text::sprintf('COM_SPORTSMANAGEMENT_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()),
            'error'
        );

        return false;
    }
};


if ($table)
{
	switch ($table)
	{
		case 'project':
			$query = $db->createQuery();
			$query->select('p.id,p.modified');
			$query->from('#__sportsmanagement_project as p');
			$query->where("p.modified_timestamp = 0");
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $projekt)
			{
				if ($projekt->modified != $db->getNullDate())
				{
					$projekt->modified_timestamp = $toTimestamp($projekt->modified);

					// Create an object for the record we are going to update.
					$object = new stdClass;

					// Must be a valid primary key value.
					$object->id                 = $projekt->id;
					$object->modified_timestamp = $projekt->modified_timestamp;

					// Echo 'modified_timestamp -> '.$projekt->modified_timestamp.'<br>';
					// Update their details in the table using id as the primary key.
					$result_update = $db->updateObject('#__sportsmanagement_project', $object, 'id');
				}
			}

			break;

		case 'match':

			$query = $db->createQuery();
			$query->select('m.id,m.match_date');
			$query->from('#__sportsmanagement_match as m');
			$query->where("m.match_timestamp = 0");
			$db->setQuery($query);
			$result = $db->loadObjectList();

			foreach ($result as $match)
			{
				if ($match->match_date != $db->getNullDate())
				{
					$match->match_timestamp = $toTimestamp($match->match_date);

					// Create an object for the record we are going to update.
					$object = new stdClass;

					// Must be a valid primary key value.
					$object->id              = $match->id;
					$object->match_timestamp = $match->match_timestamp;

					// Update their details in the table using id as the primary key.
					$result_update = $db->updateObject('#__sportsmanagement_match', $object, 'id');
				}
			}

			break;
	}
}


echo '<form method="get" id="adminForm" action="">';
echo '<br><button type="submit" name="table" value="project">Projekte</button>';
echo '<button type="submit" name="table" value="match">Match</button>';
echo '</form>';


