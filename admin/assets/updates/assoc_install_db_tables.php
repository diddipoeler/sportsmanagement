<?php
/**
 * Joomleague Component script file to CREATE all tables of Prediction Extension
 *
 * @author	Kurt Norgaz
 * @package	JoomLeague
 * @since	1.5 - 2010-08-18
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
jimport('joomla.html.pane');

$version			= '2.0.0';
$updateFileDate		= '2013-01-01';
$updateFileTime		= '17:00';
$updateDescription	='<span style="color:orange">Install/Update all tables for the Association Extension</span>';
$excludeFile		='false';

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

function getUpdatePart()
{
	$option = JRequest::getCmd('option');
	$mainframe =& JFactory::getApplication();
	$update_part=$mainframe->getUserState($option.'update_part');
	return $update_part;
}

function setUpdatePart($val=1)
{
	$option = JRequest::getCmd('option');
	$mainframe =& JFactory::getApplication();
	$update_part=$mainframe->getUserState($option.'update_part');
	if ($val!=0)
	{
		if ($update_part=='')
		{
			$update_part=1;
		}
		else
		{
			$update_part++;
		}
	}
	else
	{
		$update_part=0;
	}
	$mainframe->setUserState($option.'update_part',$update_part);
}

function ImportTables()
{
	$db =& JFactory::getDBO();

	$imports=file_get_contents(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomleague'.DS.'sql'.DS.'association_db.sql');
	$imports=preg_replace("%/\*(.*)\*/%Us",'',$imports);
	$imports=preg_replace("%^--(.*)\n%mU",'',$imports);
	$imports=preg_replace("%^$\n%mU",'',$imports);

	$imports=explode(';',$imports);
	foreach ($imports as $import)
	{
		$import=trim($import);
		if (!empty($import))
		{
			$DummyStr=$import;
			$DummyStr=substr($DummyStr,strpos($DummyStr,'`')+1);
			$DummyStr=substr($DummyStr,0,strpos($DummyStr,'`'));
			$db->setQuery($import);
			
			$pane =& JPane::getInstance('sliders');
			echo $pane->startPane('pane');
			echo $pane->startPanel($DummyStr,$DummyStr);
			
			//echo "<pre>$import</pre>";
			echo '<table class="adminlist" style="width:100%; " border="0"><thead><tr><td colspan="2" class="key" style="text-align:center;"><h3>';
			echo "Checking existence of table [$DummyStr] - <span style='color:";
				if ($db->query()){echo "green'>".JText::_('Success');}else{echo "red'>".JText::_('Failed');}
			echo '</span>';
			echo '</h3></td></tr></thead><tbody>';
			$DummyStr=$import;
			$DummyStr=substr($DummyStr,strpos($DummyStr,'`')+1);
			$tableName=substr($DummyStr,0,strpos($DummyStr,'`'));
			//echo "<br />$tableName<br />";

			$DummyStr=substr($DummyStr,strpos($DummyStr,'(')+1);
			$DummyStr=substr($DummyStr,0,strpos($DummyStr,'ENGINE'));
			$keysIndexes=trim(trim(substr($DummyStr,strpos($DummyStr,'PRIMARY KEY'))),')');
			$indexes=explode("\r\n",$keysIndexes);
			if ($indexes[0]==$keysIndexes)
			{
				$indexes=explode("\n",$keysIndexes);
				if ($indexes[0]==$keysIndexes)
				{
					$indexes=explode("\r",$keysIndexes);
				}
			}
			//echo '<pre>'.print_r($indexes,true).'</pre>';
			//echo '<pre>'.print_r($keysIndexes,true).'</pre>';

			$DummyStr=trim(trim(substr($DummyStr,0,strpos($DummyStr,'PRIMARY KEY'))),',');
			$fields=explode("\r\n",$DummyStr);
			if ($fields[0]==$DummyStr)
			{
				$fields=explode("\n",$DummyStr);
				if ($fields[0]==$DummyStr){$fields=explode("\r",$DummyStr);}
			}
			//echo '<pre>'.print_r($fields,true).'</pre>';

			$newIndexes=array();
			$i=(-1);
			foreach ($indexes AS $index)
			{
				$dummy=trim($index,' ,');
				if (!empty($dummy))
				{
					$i++;
					$newIndexes[$i]=$dummy;
				}
			}
			//echo '<pre>'.print_r($newIndexes,true).'</pre>';

			$newFields=array();
			$i=(-1);
			foreach ($fields AS $field)
			{
				$dummy=trim($field,' ,');
				if (!empty($dummy))
				{
					$i++;
					$newFields[$i]=$dummy;
				}
			}
			//echo '<pre>'.print_r($newFields,true).'</pre>';

			$rows=count($newIndexes)+1;
			echo '<tr><th class="key" style="vertical-align:top; width:10; white-space:nowrap; " rowspan="'.$rows.'">';
			echo JText::sprintf('Table needs following<br />keys/indexes:',$tableName);
			echo '</th></tr>';
			$k=0;
			foreach ($newIndexes AS $index)
			{
				$index=trim($index);
				echo '<tr class="row'.$k.'"><td>';
				if (!empty($index)){echo $index;}
				echo '</td></tr>';
				$k=(1-$k);
			}

			$rows=count($newIndexes)+1;
			echo '<tr><th class="key" style="vertical-align:top; width:10; white-space:nowrap; " rowspan="'.$rows.'">';
			echo JText::_('Dropping keys/indexes:');
			echo '</th></tr>';
			foreach ($newIndexes AS $index)
			{
				$query='';
				$index=trim($index);
				echo '<tr class="row'.$k.'"><td>';
				if (substr($index,0,11)!='PRIMARY KEY')
				{
					$keyName='';
					$queryDelete='';
					if (substr($index,0,3)=='KEY')
					{
						$keyName=substr($index,0,strpos($index,'('));
						$queryDelete="ALTER TABLE `$tableName` DROP $keyName";
					}
					elseif (substr($index,0,5)=='INDEX')
					{
						$keyName=substr($index,0,strpos($index,'('));
						$queryDelete="ALTER TABLE `$tableName` DROP $keyName";
					}
					elseif (substr($index,0,6)=='UNIQUE')
					{
						$keyName=trim(substr($index,6));
						$keyName=substr($keyName,0,strpos($keyName,'('));
						$queryDelete="ALTER TABLE `$tableName` DROP $keyName";
					}
					$db->setQuery($queryDelete);
					echo "$queryDelete - <span style='color:";
						if ($db->query()){echo "green'>".JText::_('Success');}else{echo "red'>".JText::_('Failed');}
					echo '</span>';
				}
				else
				{
					echo "<span style='color:orange; '>".JText::sprintf('Skipping handling of %1$s',$index).'</span>';
				}
				echo '&nbsp;</td></tr>';
				$k=(1-$k);
			}

			$rows=count($newFields)+1;
			echo '<tr><th class="key" style="vertical-align:top; width:10; white-space:nowrap; " rowspan="'.$rows.'">';
			echo JText::_('Updating fields:');
			echo '</th></tr>';
			foreach ($newFields AS $field)
			{
				$dFfieldName=substr($field,strpos($field,'`')+1);
				$fieldName=substr($dFfieldName,0,strpos($dFfieldName,'`'));
				$dFieldSetting=substr($dFfieldName,strpos($dFfieldName,'`')+1);
				$query="ALTER TABLE `$tableName` ADD `$fieldName` $dFieldSetting";
				$db->setQuery($query);
				echo '<tr class="row'.$k.'"><td>';
				if (!$db->query())
				{
					$query="ALTER TABLE `$tableName` CHANGE `$fieldName` `$fieldName` $dFieldSetting";
					$db->setQuery($query);
					echo "$query - <span style='color:";
						if ($db->query()){echo "green'>".JText::_('Success');}else{echo "red'>".JText::_('Failed');} //fehlgeschlagen
					echo '</span>';
				}
				else
				{
					echo "$query - <span style='color:green'>".JText::_('Success').'</span>';
				}
				echo '&nbsp;</td></tr>';
				$k=(1-$k);
			}

			$rows=count($newIndexes)+1;
			echo '<tr><th class="key" style="vertical-align:top; width:10; white-space:nowrap; " rowspan="'.$rows.'">';
			echo JText::_('Adding keys/indexes:');
			echo '</th></tr>';
			foreach ($newIndexes AS $index)
			{
				$query='';
				$index=trim($index);
				echo '<tr class="row'.$k.'"><td>';
				if (substr($index,0,11)!='PRIMARY KEY')
				{
					$keyName='';
					$queryAdd='';
					if (substr($index,0,3)=='KEY')
					{
						$keyName=substr($index,0,strpos($index,'('));
						$queryAdd="ALTER TABLE `$tableName` ADD $index";
					}
					elseif (substr($index,0,5)=='INDEX')
					{
						$keyName=substr($index,0,strpos($index,'('));
						$queryAdd="ALTER TABLE `$tableName` ADD $index";
					}
					elseif (substr($index,0,6)=='UNIQUE')
					{
						$keyName=trim(substr($index,6));
						$keyName=substr($keyName,0,strpos($keyName,'('));
						$queryAdd="ALTER TABLE `$tableName` ADD $index";
					}
					$db->setQuery($queryAdd);
					echo "$queryAdd - <span style='color:";
						if ($db->query()){echo "green'>".JText::_('Success');}else{echo "red'>".JText::_('Failed');}
					echo '</span>';
				}
				else
				{
					echo "<span style='color:orange; '>".JText::sprintf('Skipping handling of %1$s',$index).'</span>';
				}
				echo '&nbsp;</td></tr>';
				$k=(1-$k);
			}
			echo '</tbody></table>';
			unset($newIndexes);
			unset($newFields);
			
			echo $pane->endPanel();
			echo $pane->endPane();
		}
		unset($import);
	}
	return '';
}

?>
<hr />
<?php
	$mtime=microtime();
	$mtime=explode(" ",$mtime);
	$mtime=$mtime[1] + $mtime[0];
	$starttime=$mtime;

	JToolBarHelper::title(JText::_('Association Extension - Database update process'));
	echo '<h2>'.JText::sprintf(	'JoomLeague v%1$s - %2$s - Filedate: %3$s / %4$s',
								$version,$updateDescription,$updateFileDate,$updateFileTime).'</h2>';
	$totalUpdateParts = 2;
	setUpdatePart();

	if (getUpdatePart() < $totalUpdateParts)
	{
		echo '<p><b>';
			echo JText::sprintf('Please remember that this update routine has totally %1$s update steps!',$totalUpdateParts).'</b><br />';
			echo JText::_('So please go to the bottom of this page to check if there are errors and more update steps to do!');
		echo '</p>';
		echo '<p style="color:red; font-weight:bold; ">';
			echo JText::_('DANGER!!!').'<br />';
			echo JText::_('This script WILL MAKE CHANGES in your DATABASE without any more warning!!!').'<br />';
			echo JText::_('It is recommended that you make a backup of your Database before!!!').'<br />';
		echo '</p>';
		echo '<hr>';
	}

	if (getUpdatePart()==$totalUpdateParts)
	{
		echo '<hr />';
		echo ImportTables();
		echo '<br /><center><hr />';
			echo JText::sprintf('Memory Limit is %1$s',ini_get('memory_limit')).'<br />';
			echo JText::sprintf('Memory Peak Usage was %1$s Bytes',number_format(memory_get_peak_usage(true),0,'','.')).'<br />';
			echo JText::sprintf('Time Limit is %1$s seconds',ini_get('max_execution_time')).'<br />';
			$mtime=microtime();
			$mtime=explode(" ",$mtime);
			$mtime=$mtime[1] + $mtime[0];
			$endtime=$mtime;
			$totaltime=($endtime - $starttime);
			echo JText::sprintf('This page was created in %1$s seconds',$totaltime);
		echo '<hr /></center>';
		setUpdatePart(0);
	}
	else
	{
		echo '<a href="javascript:location.reload(true)" ><b>';
			echo JText::sprintf('Click here to do step %1$s of %2$s steps to finish the update. PLEASE BY SURE WHAT YOU DO BY CLICKING HERE!',getUpdatePart()+1,$totalUpdateParts);
		echo '</b></a>';
	}
?>