<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage updates
 * @file       jsm_update_db_tables.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Component\ComponentHelper;

HTMLHelper::_('bootstrap.framework');
jimport('joomla.html.html.bootstrap');

$version           = '1.0.58';
$updateFileDate    = '2017-01-15';
$updateFileTime    = '00:05';
$updateDescription = '<span style="color:orange">Update all tables using the current install sql-file.</span>';
$excludeFile       = 'false';
$option            = Factory::getApplication()->input->getCmd('option');

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
 * getUpdatePart()
 *
 * @return
 */
function getUpdatePart()
{
	$option      = Factory::getApplication()->input->getCmd('option');
	$app         = Factory::getApplication();
	$update_part = $app->getUserState($option . 'update_part');

	return $update_part;
}

/**
 * setUpdatePart()
 *
 * @param   integer  $val
 *
 * @return void
 */
function setUpdatePart($val = 1)
{
	$option      = Factory::getApplication()->input->getCmd('option');
	$app         = Factory::getApplication();
	$update_part = $app->getUserState($option . 'update_part');

	if ($val != 0)
	{
		if ($update_part == '')
		{
			$update_part = 1;
		}
		else
		{
			$update_part++;
		}
	}
	else
	{
		$update_part = 0;
	}

	$app->setUserState($option . 'update_part', $update_part);
}

/**
 * ImportTables()
 *
 * @return
 */
function ImportTables()
{
	$db     = sportsmanagementHelper::getDBConnection();
	$option = Factory::getApplication()->input->getCmd('option');

	$imports = file_get_contents(JPATH_ADMINISTRATOR . '/components/' . $option . '/sql/install.mysql.utf8.sql');

	$imports = preg_replace("%/\*(.*)\*/%Us", '', $imports);
	$imports = preg_replace("%^--(.*)\n%mU", '', $imports);
	$imports = preg_replace("%^$\n%mU", '', $imports);

	$imports  = explode(';', $imports);
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
			$DummyStr = substr($DummyStr, 0, strpos($DummyStr, '`'));
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
			$keysIndexes = trim(trim(substr($DummyStr, strpos($DummyStr, 'PRIMARY KEY'))), ')');
			$indexes     = explode("\r\n", $keysIndexes);

			if ($indexes[0] == $keysIndexes)
			{
				$indexes = explode("\n", $keysIndexes);

				if ($indexes[0] == $keysIndexes)
				{
					$indexes = explode("\r", $keysIndexes);
				}
			}

			$DummyStr = trim(trim(substr($DummyStr, 0, strpos($DummyStr, 'PRIMARY KEY'))), ',');
			$fields   = explode("\r\n", $DummyStr);

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
	echo '<input type="button" onclick="document.body.innerHTML=\'please wait...\';location.reload(true)" value="';
	echo Text::sprintf('Click here to do step %1$s of %2$s steps to finish the update.', getUpdatePart() + 1, $totalUpdateParts);
	echo '" />';
}
