<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage updates
 * @file       jsm_update_db_tables.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Service\SportsManagementAdministratorApplicationResolver;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\InstallSchemaStatementParser;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;

// The standalone update entry point can run before component autoloading.
if (!class_exists(SportsManagementAdministratorApplicationResolver::class)) {
    $applicationResolverFile = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Service/SportsManagementAdministratorApplicationResolver.php';

    if (!is_file($applicationResolverFile)) {
        throw new \RuntimeException('SportsManagement administrator application resolver is missing.', 500);
    }

    require_once $applicationResolverFile;
}

// Older migration pages can be included before the administrator PSR-4 map
// is registered. Resolve these classes from fixed component paths.
foreach ([
    SportsManagementDatabaseResolver::class =>
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Helper/SportsManagementDatabaseResolver.php',
    InstallSchemaStatementParser::class =>
        JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/src/Helper/InstallSchemaStatementParser.php',
] as $helperClass => $helperFile) {
    if (!class_exists($helperClass)) {
        if (!is_file($helperFile)) {
            throw new \RuntimeException('SportsManagement database upgrade dependency is missing.', 500);
        }

        require_once $helperFile;
    }
}

$app = SportsManagementAdministratorApplicationResolver::resolve();

// This legacy screen can update database schema objects; only component
// administrators may access the migration action.
if (!$app->getIdentity()->authorise('core.admin', 'com_sportsmanagement')) {
    throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

HTMLHelper::_('bootstrap.framework');

$version           = '4.20.00';

$minor = 0;
$major = 0;
$build = 0;
$revision = '';


$updateFileDate    = '2024-03-11';
$updateFileTime    = '00:05';
$updateDescription = '<span style="color:orange">'.Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UPDATES_DB_TABLES').'</span>';
$excludeFile       = 'false';
$option            = 'com_sportsmanagement';

$maxImportTime = ComponentHelper::getParams($option)->get('max_import_time', 0);

if (empty($maxImportTime))
{
	$maxImportTime = 880;
}


if ((int) ini_get('max_execution_time') < $maxImportTime)
{
	@set_time_limit($maxImportTime);
}

$maxImportMemory = ComponentHelper::getParams($option)->get('max_import_memory', 0);

if (empty($maxImportMemory))
{
	$maxImportMemory = '150M';
}


if ((int) ini_get('memory_limit') < (int) $maxImportMemory)
{
	ini_set('memory_limit', $maxImportMemory);
}

/**
 * Read the current database-upgrade stage from Joomla user state.
 *
 * Uses the fixed SportsManagement component state key so a request cannot
 * choose a different extension's state namespace.
 *
 * @return int Valid stage in the range zero through two.
 */
function getUpdatePart(): int
{
    $app = SportsManagementAdministratorApplicationResolver::resolve();
    $stage = $app->getUserState('com_sportsmanagementupdate_part', 0);

    // Legacy sessions may contain empty or malformed stage values. Avoid
    // incrementing unbounded or nonnumeric user state to execute SQL.
    return in_array($stage, [0, 1, 2, '0', '1', '2'], true)
        ? (int) $stage
        : 0;
}

/**
 * Advance or reset the database-upgrade stage in the Joomla user session.
 *
 * Increments the persisted stage for positive values or resets it to zero
 * after the last migration step has finished.
 *
 * @param int $val Positive to advance by one stage, zero to reset.
 * @return void
 */
function setUpdatePart(int $val = 1): void
{
    $app = SportsManagementAdministratorApplicationResolver::resolve();
    $stage = $val === 0 ? 0 : min(2, getUpdatePart() + 1);
    $app->setUserState('com_sportsmanagementupdate_part', $stage);
}

/**
 * Compare and update the SportsManagement SQL table definitions.
 *
 * Reads the bundled installation SQL file, checks existing table columns and
 * indexes, executes the required migration statements and renders a Joomla
 * Bootstrap accordion showing the outcome of each database operation.
 *
 * @return string Empty string after rendering the migration report.
 */
function ImportTables()
{
    /** @var DatabaseInterface $defaultDatabase */
    $defaultDatabase = Factory::getContainer()->get(DatabaseInterface::class);

    if (!$defaultDatabase instanceof DatabaseInterface) {
        throw new \RuntimeException('Joomla database service is unavailable.', 500);
    }

    $db = (new SportsManagementDatabaseResolver())->resolve(null, $defaultDatabase);
    $option = 'com_sportsmanagement';

	$imports = file_get_contents(JPATH_ADMINISTRATOR . '/components/' . $option . '/sql/install.mysql.utf8.sql');

    if (!is_string($imports) || trim($imports) === '') {
        throw new \RuntimeException('SportsManagement SQL installation schema is missing.', 500);
    }

    // Keep quoted SQL defaults and comment contents intact: a semicolon
    // inside a string must never be interpreted as a second SQL command.
    $imports = InstallSchemaStatementParser::split($imports);

	$cntPanel = 0;

	// $slidesOptions = '';
	// Define slides options
	$slidesOptions = array(
		"active" => "slide1_id" // It is the ID of the active tab.
	);
	echo HTMLHelper::_('bootstrap.startAccordion', 'slide-group-id', $slidesOptions);

	$slide_id = 1;

	foreach ($imports as $import)
	{
		$import = trim($import);

		if (!empty($import))
		{
			$DummyStr = $import;
			$DummyStr = substr($DummyStr, strpos($DummyStr, '`') + 1);
          //echo "<span style='color:orange; '>" . __LINE__.' DummyStr<pre>'.print_r($DummyStr,true).'</pre>' . '</span>';
			$DummyStr = substr($DummyStr, 0, strpos($DummyStr, '`'));
          //echo "<span style='color:orange; '>" . __LINE__.' DummyStr<pre>'.print_r($DummyStr,true).'</pre>' . '</span>';
          $jsmtable = $DummyStr;
          
			$db->setQuery($import);
			$panelName = substr(str_replace('sportsmanagement', '', str_replace('_', '', $DummyStr)), 1);

			// Echo HTMLHelper::_('sliders.panel',$DummyStr,'panel-'.$panelName);
			echo HTMLHelper::_('bootstrap.addSlide', 'slide-group-id', Text::_($panelName), "slide" . $slide_id . "_id");

			echo '<table class="table" style="width:100%; " border="0"><thead><tr><td colspan="2" class="key" style="text-align:center;"><h3>';
			echo "Checking existence of table [$DummyStr] - <span style='color:";

			if ($db->execute())
			{
				echo "green'>" . Text::_('Success');
			}
			else
			{
				echo "red'>" . Text::_('Failed');
			}

			echo '</span>';
			echo '</h3></td></tr></thead><tbody>';
			$DummyStr  = $import;
			$DummyStr  = substr($DummyStr, strpos($DummyStr, '`') + 1);
			$tableName = substr($DummyStr, 0, strpos($DummyStr, '`'));

			$DummyStr    = substr($DummyStr, strpos($DummyStr, '(') + 1);
			$DummyStr    = substr($DummyStr, 0, strpos($DummyStr, 'ENGINE'));
          
          
          switch ($jsmtable)
          {
            case '#__sportsmanagement_person_project_position':
              $keysIndexes = trim(trim(substr($DummyStr, strpos($DummyStr, 'UNIQUE KEY'))), ')');
              break;
	 case '#__sportsmanagement_log_entries':
              $keysIndexes = trim(trim(substr($DummyStr, strpos($DummyStr, 'KEY'))), ')');
              break;
            default:
              $keysIndexes = trim(trim(substr($DummyStr, strpos($DummyStr, 'PRIMARY KEY'))), ')');
              break;
          }
          
			
          
          
          
          //echo "<span style='color:orange; '>" . __LINE__.' keysIndexes<pre>'.print_r($keysIndexes,true).'</pre>' . '</span>';
          
          
			$indexes     = explode("\r\n", $keysIndexes);

			if ($indexes[0] == $keysIndexes)
			{
				$indexes = explode("\n", $keysIndexes);

				if ($indexes[0] == $keysIndexes)
				{
					$indexes = explode("\r", $keysIndexes);
				}
			}

          switch ($jsmtable)
          {
            case '#__sportsmanagement_person_project_position':
                $DummyStr = trim(trim(substr($DummyStr, 0, strpos($DummyStr, 'UNIQUE KEY'))), ',');
              break;
	  case '#__sportsmanagement_log_entries':
                $DummyStr = trim(trim(substr($DummyStr, 0, strpos($DummyStr, 'KEY'))), ',');
              break;
            default:
              $DummyStr = trim(trim(substr($DummyStr, 0, strpos($DummyStr, 'PRIMARY KEY'))), ',');
              break;
          }
			
			$fields   = explode("\r\n", $DummyStr);
// $jsmtable
          //echo "<span style='color:orange; '>" . __LINE__.' jsmtable<pre>'.print_r($jsmtable,true).'</pre>' . '</span>';
          //echo "<span style='color:orange; '>" . __LINE__.' DummyStr<pre>'.print_r($DummyStr,true).'</pre>' . '</span>';
          //echo "<span style='color:orange; '>" . __LINE__.' fields<pre>'.print_r($fields,true).'</pre>' . '</span>';
          //echo "<span style='color:orange; '>" . __LINE__.' import<pre>'.print_r($import,true).'</pre>' . '</span>';
          
			if ($fields[0] == $DummyStr)
			{
				$fields = explode("\n", $DummyStr);

				if ($fields[0] == $DummyStr)
				{
					$fields = explode("\r", $DummyStr);
				}
			}

			$newIndexes = array();
			$i          = (-1);

			foreach ($indexes AS $index)
			{
				$dummy = trim($index, ' ,');

				if (!empty($dummy))
				{
					$i++;
					$newIndexes[$i] = $dummy;
				}
			}

			$newFields = array();
			$i         = (-1);

			foreach ($fields AS $field)
			{
				$dummy = trim($field, ' ,');

				if (!empty($dummy))
				{
					$i++;
					$newFields[$i] = $dummy;
				}
			}

			$rows = count($newIndexes) + 1;
			echo '<tr><th class="key" style="vertical-align:top; width:10; white-space:nowrap; " rowspan="' . $rows . '">';
			echo Text::sprintf('Table needs following<br />keys/indexes:', $tableName);
			echo '</th></tr>';
			
			//echo "<span style='color:orange; '>" . __LINE__.' fields<pre>'.print_r($fields,true).'</pre>' . '</span>';
			
			$k = 0;

			foreach ($newIndexes AS $index)
			{
				$index = trim($index);
				echo '<tr class="row' . $k . '"><td>';

				if (!empty($index))
				{
					echo $index;
				}

				echo '</td></tr>';
				$k = (1 - $k);
			}

			$rows = count($newIndexes) + 1;
			echo '<tr><th class="key" style="vertical-align:top; width:10; white-space:nowrap; " rowspan="' . $rows . '">';
			echo Text::_('Dropping keys/indexes:');
			echo '</th></tr>';
			$keys = $db->getTableKeys($tableName);

			if (sizeof($keys) != 0)
			{
				foreach ($newIndexes AS $index)
				{
					$query = '';
					$index = trim($index);
					echo '<tr class="row' . $k . '"><td>';

					if (substr($index, 0, 11) != 'PRIMARY KEY')
					{
						$keyName     = '';
						$queryDelete = '';

						if (substr($index, 0, 3) == 'KEY')
						{
							$keyName     = substr($index, 0, strpos($index, '('));
							$queryDelete = "ALTER TABLE `$tableName` DROP $keyName";
						}
                        elseif (substr($index, 0, 5) == 'INDEX')
						{
							$keyName     = substr($index, 0, strpos($index, '('));
							$queryDelete = "ALTER TABLE `$tableName` DROP $keyName";
						}
                        elseif (substr($index, 0, 6) == 'UNIQUE')
						{
							$keyName     = trim(substr($index, 6));
							$keyName     = substr($keyName, 0, strpos($keyName, '('));
							$queryDelete = "ALTER TABLE `$tableName` DROP $keyName";
						}

						$skip = false;

						if (sizeof($keys) != 0)
						{
							foreach ($keys as $key)
							{
								preg_match('/`(.*?)`/', $keyName, $reg);

								if (strcasecmp($key->Key_name, $reg[1]) !== 0)
								{
									echo "<span style='color:orange; '>" . Text::sprintf('Skipping handling of %1$s', $queryDelete) . '</span>';
									$skip = true;
									break;
								}
							}
						}

						if ($skip)
						{
							continue;
						}

						if (!empty($queryDelete))
						{
							try
							{
								$db->setQuery($queryDelete);
								$db->execute();
								echo "$queryDelete - <span style='color:green'" . Text::_('Success') . '</span>';
							}
							catch (Exception $e)
							{
								echo "$queryDelete - <span style='color:red'" . Text::_('Failed') . '</span>';
							}
						}
					}
					else
					{
						echo "<span style='color:orange; '>" . Text::sprintf('Skipping handling of %1$s', $index) . '</span>';
					}

					echo '&nbsp;</td></tr>';
					$k = (1 - $k);
				}
			}

			$rows = count($newFields) + 1;
			echo '<tr><th class="key" style="vertical-align:top; width:10; white-space:nowrap; " rowspan="' . $rows . '">';
			echo Text::_('Updating fields:');
			echo '</th></tr>';
			$columns = $db->getTableColumns($tableName, false);

			foreach ($newFields AS $field)
			{
				$dFfieldName   = substr($field, strpos($field, '`') + 1);
				$fieldName     = substr($dFfieldName, 0, strpos($dFfieldName, '`'));
				$dFieldSetting = substr($dFfieldName, strpos($dFfieldName, '`') + 1);
				echo '<tr class="row' . $k . '"><td>';
				$add   = true;
				$query = "ALTER TABLE `$tableName` ADD `$fieldName` $dFieldSetting";

				if (array_key_exists($fieldName, $columns)
					&& (strcasecmp($fieldName, $columns[$fieldName]->Field) === 0)
					&& strpos(strtolower($dFieldSetting), $columns[$fieldName]->Type)
				)
				{
					echo "<span style='color:orange; '>" . Text::sprintf('Skipping handling of %1$s', $query) . '</span>';
					continue;
				}
				else
				{
					if (isset($columns[$fieldName]))
					{
						if (strpos(strtolower($dFieldSetting), $columns[$fieldName]->Type))
						{
							$add = true;
						}
						else
						{
							$add = false;
						}
					}
				}

				if ($add)
				{
					if ($query)
					{
						try
						{
							$db->setQuery($query);
							$db->execute();
							echo "$query - <span style='color:green'" . Text::_('Success') . '</span>';
						}
						catch (Exception $e)
						{
							echo "$query - <span style='color:red'" . Text::_('Failed') . '</span>';
						}
					}
				}
				else
				{
					if (array_key_exists($fieldName, $columns))
					{
						$query = "ALTER TABLE `$tableName` CHANGE `$fieldName` `$fieldName` $dFieldSetting";
					}

					if ($query)
					{
						try
						{
							$db->setQuery($query);
							$db->execute();
							echo "$query - <span style='color:green'" . Text::_('Success') . '</span>';
						}
						catch (Exception $e)
						{
							echo "$query - <span style='color:red'" . Text::_('Failed') . '</span>';
						}
					}
				}

				echo '&nbsp;</td></tr>';
				$k = (1 - $k);
			}

			$rows = count($newIndexes) + 1;
			echo '<tr><th class="key" style="vertical-align:top; width:10; white-space:nowrap; " rowspan="' . $rows . '">';
			echo Text::_('Adding keys/indexes:');
			echo '</th></tr>';
			$keys = $db->getTableKeys($tableName);

			foreach ($newIndexes AS $index)
			{
				$query = '';
				$index = trim($index);
				echo '<tr class="row' . $k . '"><td>';

				if (substr($index, 0, 11) != 'PRIMARY KEY')
				{
					$keyName  = '';
					$queryAdd = '';

					if (substr($index, 0, 3) == 'KEY')
					{
						$keyName  = substr($index, 0, strpos($index, '('));
						$queryAdd = "ALTER TABLE `$tableName` ADD $index";
					}
                    elseif (substr($index, 0, 5) == 'INDEX')
					{
						$keyName  = substr($index, 0, strpos($index, '('));
						$queryAdd = "ALTER TABLE `$tableName` ADD $index";
					}
                    elseif (substr($index, 0, 6) == 'UNIQUE')
					{
						$keyName  = trim(substr($index, 6));
						$keyName  = substr($keyName, 0, strpos($keyName, '('));
						$queryAdd = "ALTER TABLE `$tableName` ADD $index";
					}

					$skip = false;

					foreach ($keys as $key)
					{
						preg_match('/`(.*?)`/', $keyName, $reg);

						if (strcasecmp($key->Key_name, $reg[1]) === 0)
						{
							echo "<span style='color:orange; '>" . Text::sprintf('Skipping handling of %1$s', $queryDelete) . '</span>';
							$skip = true;
							break;
						}
					}

					if ($skip)
					{
						continue;
					}

					if ($queryAdd)
					{
						try
						{
							$db->setQuery($queryAdd);
							$db->execute();
							echo "$queryAdd - <span style='color:green'" . Text::_('Success') . '</span>';
						}
						catch (Exception $e)
						{
							echo "$queryAdd - <span style='color:red'" . Text::_('Failed') . '</span>';
						}
					}
				}
				else
				{
					echo "<span style='color:orange; '>" . Text::sprintf('Skipping handling of %1$s', $index) . '</span>';
				}

				echo '&nbsp;</td></tr>';
				$k = (1 - $k);
			}

			echo '</tbody></table>';
			unset($newIndexes);
			unset($newFields);
			$slide_id++;
		}

		unset($import);
		echo HTMLHelper::_('bootstrap.endSlide');
	}

	echo HTMLHelper::_('bootstrap.endAccordion');

	return '';

}

?>
    <hr/>
<?php
$mtime     = microtime();
$mtime     = explode(" ", $mtime);
$mtime     = $mtime[1] + $mtime[0];
$starttime = $mtime;

ToolbarHelper::title(Text::_('JSM Sportsmanagement - Database update process'));
echo '<h2>' . Text::sprintf(
		'JSM Sportsmanagement v%1$s - %2$s - Filedate: %3$s / %4$s',
		$version, $updateDescription, $updateFileDate, $updateFileTime
	) . '</h2>';
// Never mutate the schema on a plain GET request. Each upgrade stage must
// be explicitly submitted with a valid Joomla session token.
if ($app->getInput()->post->getInt('run_update', 0) !== 1) {
    echo '<form method="post" id="adminForm" action="">';
    echo '<button class="btn btn-warning" type="submit" name="run_update" value="1">';
    echo htmlspecialchars(Text::_('COM_SPORTSMANAGEMENT_GLOBAL_UPDATES_DB_TABLES'), ENT_QUOTES, 'UTF-8');
    echo '</button>';
    echo HTMLHelper::_('form.token');
    echo '</form>';

    return;
}

if (!$app->checkToken('post')) {
    throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
}

$totalUpdateParts = 2;
setUpdatePart();

if (getUpdatePart() < $totalUpdateParts)
{
	echo '<p><b>';
	echo Text::sprintf('Please remember that this update routine has totally %1$s update steps!', $totalUpdateParts) . '</b><br />';
	echo Text::_('So please go to the bottom of this page to check if there are errors and more update steps to do!');
	echo '</p>';
	echo '<p style="color:red; font-weight:bold; ">';
	echo Text::_('It is recommended that you make a backup of your Database before!!!') . '<br />';
	echo '</p>';
	echo '<hr>';
}

if (getUpdatePart() == $totalUpdateParts)
{
	echo '<hr />';
	echo ImportTables();
	echo '<br /><center><hr />';
	echo Text::sprintf('Memory Limit is %1$s', ini_get('memory_limit')) . '<br />';
	echo Text::sprintf('Memory Peak Usage was %1$s Bytes', number_format(memory_get_peak_usage(true), 0, '', '.')) . '<br />';
	echo Text::sprintf('Time Limit is %1$s seconds', ini_get('max_execution_time')) . '<br />';
	$mtime     = microtime();
	$mtime     = explode(" ", $mtime);
	$mtime     = $mtime[1] + $mtime[0];
	$endtime   = $mtime;
	$totaltime = ($endtime - $starttime);
	echo Text::sprintf('This page was created in %1$s seconds', $totaltime);
	echo '<hr /></center>';
	setUpdatePart(0);
}
else
{
    // The previous reload(true) button converted the second stage to a GET.
    // Requiring a new explicit POST with its own CSRF token keeps the second
    // schema migration stage reachable without relaxing the write guards.
    echo '<form method="post" id="adminForm" action="">';
    echo '<input type="hidden" name="run_update" value="1">';
    echo '<button class="btn btn-warning" type="submit">';
    echo htmlspecialchars(
        Text::sprintf(
            'Click here to do step %1$s of %2$s steps to finish the update.',
            getUpdatePart() + 1,
            $totalUpdateParts
        ),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
    echo '</button>';
    echo HTMLHelper::_('form.token');
    echo '</form>';
}
