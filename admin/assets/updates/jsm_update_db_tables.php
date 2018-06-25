<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
//jimport('joomla.html.pane');

//jimport('joomla.html.html.sliders');
//jimport('joomla.html.html.tabs');
JHtml::_('bootstrap.framework');
jimport('joomla.html.html.bootstrap');
//jimport('joomla.application.component.view');
//jimport('joomla.html.html.bootstrap');
  
$version			= '1.0.58';
$updateFileDate		= '2017-01-15';
$updateFileTime		= '00:05';
$updateDescription	='<span style="color:orange">Update all tables using the current install sql-file.</span>';
$excludeFile		='false';
$option = JFactory::getApplication()->input->getCmd('option');

$maxImportTime = JComponentHelper::getParams($option)->get('max_import_time',0);
if (empty($maxImportTime))
{
	$maxImportTime=880;
}
if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}

$maxImportMemory = JComponentHelper::getParams($option)->get('max_import_memory',0);
if (empty($maxImportMemory))
{
	$maxImportMemory='150M';
}
if ((int)ini_get('memory_limit') < (int)$maxImportMemory){ini_set('memory_limit',$maxImportMemory);}

/**
 * getUpdatePart()
 * 
 * @return
 */
function getUpdatePart()
{
	$option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
	$update_part = $app->getUserState($option.'update_part');
	return $update_part;
}

/**
 * setUpdatePart()
 * 
 * @param integer $val
 * @return void
 */
function setUpdatePart($val=1)
{
	$option = JFactory::getApplication()->input->getCmd('option');
	$app = JFactory::getApplication();
	$update_part=$app->getUserState($option.'update_part');
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
	$app->setUserState($option.'update_part',$update_part);
}

/**
 * ImportTables()
 * 
 * @return
 */
function ImportTables()
{
	$db = sportsmanagementHelper::getDBConnection();
    $option = JFactory::getApplication()->input->getCmd('option');

	$imports = file_get_contents(JPATH_ADMINISTRATOR.'/components/'.$option.'/sql/install.mysql.utf8.sql');

$imports=preg_replace("%/\*(.*)\*/%Us",'',$imports);
		$imports=preg_replace("%^--(.*)\n%mU",'',$imports);
		$imports=preg_replace("%^$\n%mU",'',$imports);
	
		$imports=explode(';',$imports);
		$cntPanel=0;
//		echo JHtml::_('sliders.start','tables',array(
//						'allowAllClose' => true,
//						'startTransition' => true,
//						true));


/*
// Example accordion usage
echo JHtml::_('bootstrap.startAccordion', 'slide-example', array('active' => 'slide2'));
echo JHtml::_('bootstrap.addSlide', 'slide-example', JText::_('Slide 1'), 'slide1');
echo "Content of slide 1";
echo JHtml::_('bootstrap.endSlide');
echo JHtml::_('bootstrap.addSlide', 'slide-example', JText::_('Slide 2'), 'slide2');
echo "Content of slide 2";
echo JHtml::_('bootstrap.endSlide');
echo JHtml::_('bootstrap.addSlide', 'slide-example', JText::_('Slide 3'), 'slide3');
echo "Content of slide 3";
echo JHtml::_('bootstrap.endSlide');
echo JHtml::_('bootstrap.addSlide', 'slide-example', JText::_('Slide 4'), 'slide4');
echo "Content of slide 4";
echo JHtml::_('bootstrap.endSlide');
echo JHtml::_('bootstrap.endAccordion');
*/


//$slidesOptions = '';
 // Define slides options
        $slidesOptions = array(
            "active" => "slide1_id" // It is the ID of the active tab.
        );
echo JHtml::_('bootstrap.startAccordion', 'slide-group-id', $slidesOptions);

$slide_id = 1;                        
		foreach ($imports as $import)
		{
			$import=trim($import);
			if (!empty($import))
			{
				$DummyStr=$import;
				$DummyStr=substr($DummyStr,strpos($DummyStr,'`')+1);
				$DummyStr=substr($DummyStr,0,strpos($DummyStr,'`'));
				$db->setQuery($import);
				$panelName = substr(str_replace('sportsmanagement','',str_replace('_','',$DummyStr)),1);
				
                //echo JHtml::_('sliders.panel',$DummyStr,'panel-'.$panelName);
				echo JHtml::_('bootstrap.addSlide', 'slide-group-id', JText::_($panelName), "slide".$slide_id."_id");
                	
				echo '<table class="table" style="width:100%; " border="0"><thead><tr><td colspan="2" class="key" style="text-align:center;"><h3>';
				echo "Checking existence of table [$DummyStr] - <span style='color:";
				if ($db->execute()){echo "green'>".JText::_('Success');}else{echo "red'>".JText::_('Failed');}
				echo '</span>';
				echo '</h3></td></tr></thead><tbody>';
				$DummyStr=$import;
				$DummyStr=substr($DummyStr,strpos($DummyStr,'`')+1);
				$tableName=substr($DummyStr,0,strpos($DummyStr,'`'));
	
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
	
				$DummyStr=trim(trim(substr($DummyStr,0,strpos($DummyStr,'PRIMARY KEY'))),',');
				$fields=explode("\r\n",$DummyStr);
				if ($fields[0]==$DummyStr)
				{
					$fields=explode("\n",$DummyStr);
					if ($fields[0]==$DummyStr){$fields=explode("\r",$DummyStr);}
				}
	
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
				$keys = $db->getTableKeys($tableName);
                
                //echo 'table <pre>'.print_r($tableName,true).'</pre>';
                //echo 'keys <pre>'.print_r($keys,true).'</pre>';
                //exit;
                if ( sizeof($keys) != 0 )
                        {
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
						$skip = false;
                        
                        //echo 'queryDelete <pre>'.print_r($queryDelete,true).'</pre><br>';    
                        
                        if ( sizeof($keys) != 0 )
                        {
						foreach($keys as $key) 
                        {
                            //if ( $key->Table == 'jos_sportsmanagement_person_project_position' )
//                            {
//                                exit;
//                            }
							preg_match('/`(.*?)`/', $keyName, $reg);
                            
//                            if ( $tableName === '#__sportsmanagement_person_project_position' )
//                        {
//                            echo 'reg <pre>'.print_r($reg,true).'</pre><br>';
//                            }
                            
							if(strcasecmp($key->Key_name, $reg[1])!==0) 
                            {
								echo "<span style='color:orange; '>".JText::sprintf('Skipping handling of %1$s',$queryDelete).'</span>';
								$skip = true;
								break;
							}
						}
                        }
                        
						if($skip) continue;
                                                
                        if ( !empty($queryDelete) )
                        {
                        try {    
						$db->setQuery($queryDelete);
                        $db->execute();
                        echo "$queryDelete - <span style='color:green'".JText::_('Success').'</span>';
                        } catch (Exception $e) {
                        echo "$queryDelete - <span style='color:red'".JText::_('Failed').'</span>';
                        }    
                        }
					}
					else
					{
						echo "<span style='color:orange; '>".JText::sprintf('Skipping handling of %1$s',$index).'</span>';
					}
					echo '&nbsp;</td></tr>';
					$k=(1-$k);
				}
                
                }
	
				$rows=count($newFields)+1;
				echo '<tr><th class="key" style="vertical-align:top; width:10; white-space:nowrap; " rowspan="'.$rows.'">';
				echo JText::_('Updating fields:');
				echo '</th></tr>';
				$columns = $db->getTableColumns($tableName, false);
				foreach ($newFields AS $field)
				{
					$dFfieldName=substr($field,strpos($field,'`')+1);
					$fieldName=substr($dFfieldName,0,strpos($dFfieldName,'`'));
					$dFieldSetting=substr($dFfieldName,strpos($dFfieldName,'`')+1);
					echo '<tr class="row'.$k.'"><td>';
					$add = true;
					$query="ALTER TABLE `$tableName` ADD `$fieldName` $dFieldSetting";
					if(array_key_exists($fieldName, $columns) && 
						(strcasecmp($fieldName,$columns[$fieldName]->Field)===0) && 
						strpos(strtolower($dFieldSetting), $columns[$fieldName]->Type)) {
						echo "<span style='color:orange; '>".JText::sprintf('Skipping handling of %1$s',$query).'</span>';
						continue;
					} else {
						if(isset($columns[$fieldName])) {
							if(strpos(strtolower($dFieldSetting), $columns[$fieldName]->Type)) {
								$add = true;
							} else {
								$add = false;
							}	
						} 
					}
					if($add) {
					   if ( $query )
                       {
						try {    
						$db->setQuery($query);
                        $db->execute();
                        echo "$query - <span style='color:green'".JText::_('Success').'</span>';
                        } catch (Exception $e) {
                        echo "$query - <span style='color:red'".JText::_('Failed').'</span>';
                        }
                        }
					} else {
						if(array_key_exists($fieldName, $columns)) {
							$query="ALTER TABLE `$tableName` CHANGE `$fieldName` `$fieldName` $dFieldSetting";
						}
                        if ( $query )
                        {
                        try {    
						$db->setQuery($query);
                        $db->execute();
                        echo "$query - <span style='color:green'".JText::_('Success').'</span>';
                        } catch (Exception $e) {
                        echo "$query - <span style='color:red'".JText::_('Failed').'</span>';
                        }
                        }
					}
					echo '&nbsp;</td></tr>';
					$k=(1-$k);
				}
	
				$rows=count($newIndexes)+1;
				echo '<tr><th class="key" style="vertical-align:top; width:10; white-space:nowrap; " rowspan="'.$rows.'">';
				echo JText::_('Adding keys/indexes:');
				echo '</th></tr>';
				$keys = $db->getTableKeys($tableName);
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
						$skip = false;
						foreach($keys as $key) {
							preg_match('/`(.*?)`/', $keyName, $reg);
							if(strcasecmp($key->Key_name, $reg[1])===0) {
								echo "<span style='color:orange; '>".JText::sprintf('Skipping handling of %1$s',$queryDelete).'</span>';
								$skip = true;
								break;
							}
						}
						if($skip) continue;
                        if ( $queryAdd )
                        {
                        try {    
						$db->setQuery($queryAdd);
                        $db->execute();
                        echo "$queryAdd - <span style='color:green'".JText::_('Success').'</span>';
                        } catch (Exception $e) {
                        echo "$queryAdd - <span style='color:red'".JText::_('Failed').'</span>';
                        }
                        }
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
				$slide_id++;	
			}
			unset($import);
            echo JHtml::_('bootstrap.endSlide');
		}
		//echo JHtml::_('sliders.end');
        echo JHtml::_('bootstrap.endAccordion');
		return '';



}

?>
<hr />
<?php
	$mtime=microtime();
	$mtime=explode(" ",$mtime);
	$mtime=$mtime[1] + $mtime[0];
	$starttime=$mtime;

	JToolbarHelper::title(JText::_('JSM Sportsmanagement - Database update process'));
	echo '<h2>'.JText::sprintf(	'JSM Sportsmanagement v%1$s - %2$s - Filedate: %3$s / %4$s',
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
		echo '<input type="button" onclick="document.body.innerHTML=\'please wait...\';location.reload(true)" value="';
		echo JText::sprintf('Click here to do step %1$s of %2$s steps to finish the update.',getUpdatePart()+1,$totalUpdateParts);
		echo '" />';
	}
?>