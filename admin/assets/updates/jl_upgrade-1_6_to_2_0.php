<?php
// ToDo:
// - Remove old checks for already existing records in different functions as it was done with matches table
// - check ranking class changes in tables or templates etc...
// no direct access
defined('_JEXEC') or die('Restricted access');

$version			= '2.0.48.4f4c0e7';
$updateFileDate		= '2013-02-09';
$updateFileTime		= '13:25';
$updatefilename		= 'jl_upgrade-1-6_to_2_0';
$lastVersion		= '1.6';
$JLtablePrefix		= 'joomleague';
$updateDescription	= '<span style="color:orange">Perform an update of existing old JL 1.6 tables inside the database to work with latest JoomLeague</span>';
$excludeFile		= 'false';

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
$maxImportTime=JComponentHelper::getParams('com_joomleague')->get('max_import_time',0);
if (empty($maxImportTime))
{
	$maxImportTime=480;
}
if ((int)ini_get('max_execution_time') < $maxImportTime){
	@set_time_limit($maxImportTime);
}

$maxImportMemory=JComponentHelper::getParams('com_joomleague')->get('max_import_memory',0);
if (empty($maxImportMemory))
{
	$maxImportMemory='150M';
}
if ((int)ini_get('memory_limit') < (int)$maxImportMemory){
	ini_set('memory_limit',$maxImportMemory);
}

$updates=array();

if (0 ==0)
{
	$updates['joomleague_project'][0]['tablename']='#__joomleague_project';
	
	$updates['joomleague_project'][1]['action']="ALTER TABLE";
	$updates['joomleague_project'][1]['job']="CHANGE";
	$updates['joomleague_project'][1]['field_from']="serveroffset";
	$updates['joomleague_project'][1]['field_to']="timezone";
	$updates['joomleague_project'][1]['values']="VARCHAR(50) NOT NULL DEFAULT 'Europe/Amsterdam'";
	$updates['joomleague_project'][1]['after']="extension";
}

/**
 * obj2Array#
 * converts simpleXml object to array
 *
 * Variables: $o['obj']: simplexml object
 *
 * @return
 *
 */
function obj2Array($obj)
{
	$arr=(array)$obj;
	if (empty($arr))
	{
		$arr='';
	}
	else
	{
		foreach ($arr as $key=>$value)
		{
			if (!is_scalar($value))
			{
				$arr[$key]=obj2Array($value);
			}
		}
	}
	return $arr;
}

function PrintStepResult($result)
{
	if ($result)
	{
		$output=' - <span style="color:green">'.JText::_('SUCCESS').'</span>';
	}
	else
	{
		$output=' - <span style="color:red">'.JText::_('FAILED').'</span>';
	}

	return $output;
}

function doQueries($queries)
{
	$db = JFactory::getDBO();

	/* execute modifications */
	if (count($queries))
	{
		foreach ($queries as $query)
		{
			$db->setQuery($query[0]);
			$db->query($query[0]);
			$bla=$db->getErrorNum();

			echo '<br />';
			if ($bla ==0)
			{
				echo '<img	align="top" src="components/com_joomleague/assets/images/ok.png"
						alt="'.JText::_('SUCCESS').'" title"'.JText::_('SUCCESS').'">';
				echo '&nbsp;';
				echo $query[1];
			}
			else
			{
				echo '<img	align="top" src="components/com_joomleague/assets/images/error.png"
						alt="'.JText::_('Error').'" title"'.JText::_('Error').'">';
				echo '&nbsp;';
				echo '<pre>'.$db->getErrorMsg()."</pre>$query<br/>$query[0]";
			}
		}
		echo '<br />';
	}
	else
	{
		echo ' - <span style="color:green">'.JText::_('No update was needed').'</span>';
	}
	return '';
}

function Update_Tables($updates,$tablename)
{
	$db = JFactory::getDBO();
	$queries=array();
	$tables[0]=$updates[$tablename][0]['tablename'];

	echo JText::sprintf('Updating table [%s]','<b>'.$tables[0].'</b>');

	$fields=$db->getTableFields($tables);
	$fieldlist=implode(",",array_keys($fields[ $tables[0] ]));

	foreach ($updates[$tablename] as $update)
	{
		if ((isset ($update['field'])) && (!is_null($update['field'])))
		{
			$after=$update['after'];

			if (!in_array($update['field'],array_keys($fields[ $tables[0] ])))
			{
				if ($after!="")
				{
					if (in_array($after,array_keys($fields[ $tables[0] ])))
					{
						$after="AFTER `".$after."`";
					}
					else
					{
						$after="";
					}
				}
				$queries[]=array($update['action']." `".$tables[0]."` ".$update['job']." `".$update['field']."` " .
								$update['values']." ".$after,
								JText::sprintf('COM_JOOMLEAGUE_DB_UPDATE_ADDING_FIELD_TO_TABLE','<b>'.$update['field'].'</b>'));
				$fields[ $tables[0] ][$update['field']]="#";
			}
		}
	}

	echo doQueries($queries).'<br />';
	return '';
}

function Change_Table_Columns($updates,$tablename)
{
	$db = JFactory::getDBO();
	$queries=array();
	$tables[0]=$updates[$tablename][0]['tablename'];

	echo JText::sprintf('Updating table [%s]','<b>'.$tables[0].'</b>');

	$fields=$db->getTableFields($tables);
	$fieldlist=implode(",",array_keys($fields[ $tables[0] ]));

	foreach ($updates[$tablename] as $update)
	{
		if ((isset($update['field_from'])) && (isset($update['field_to'])))
		{
			$queries[]=array($update['action']." `".$tables[0]."` ".$update['job']." `" .
					$update['field_from']."` `".$update['field_to']."` ".$update['values'],
					JText::sprintf('- Changing column [%s] in table to [%s]', $update['field_from'], $update['field_to']));
			$fields[ $tables[0] ][$update['field_from']]="#";
		}
	}

	echo doQueries($queries).'<br />';
	return '';
}

function Delete_Table_Columns($dUpdates,$tablename)
{
	$db = JFactory::getDBO();
	$queries=array();
	$tables[0]=$dUpdates[$tablename][0]['tablename'];
	//echo '<br /><pre>~'.print_r($dUpdates,true).'~</pre><br />';

	echo JText::sprintf('Updating table [%s]','<b>'.$tables[0].'</b>');

	$fields=$db->getTableFields($tables);
	$fieldlist=implode(",",array_keys($fields[$tables[0]]));
	foreach ($dUpdates[$tablename] as $update)
	{
		if ((isset($update['field'])) && (!is_null($update['field'])))
		{
			$queries[]=array(	$update['action']." `".$tables[0]."` ".$update['job']." `".$update['field']."`",
					JText::sprintf('- Deleting column [%s] in table',$update['field']));
			$fields[ $tables[0] ][$update['field']]="#";
		}
	}
	echo doQueries($queries).'<br />';
	return '';
}


function UpdateTemplateMasters()
{
	/**********************************
	 ******** Update Script for xml template to store the non existing Variable
	***********************************/

	/*
	 * developer: diddipoeler
	* date: 13.01.2011
	* Bugtracker Backend Bug #579
	* change old textname in newtextname
	*/
	$convert = array (
			'JL_AGAINST' => 'AGAINST',
			'JL_AVG' => 'AVG',
			'JL_BONUS' => 'BONUS',
			'JL_DIFF' => 'DIFF',
			'JL_FOR' => 'FOR',
			'JL_GB' => 'GB',
			'JL_H2H' => 'H2H',
			'JL_H2H_AWAY' => 'H2H_AWAY',
			'JL_H2H_DIFF' => 'H2H_DIFF',
			'JL_H2H_FOR' => 'H2H_FOR',
			'JL_LEGS' => 'LEGS',
			'JL_LEGS_DIFF' => 'LEGS_DIFF',
			'JL_LEGS_RATIO' => 'LEGS_RATIO',
			'JL_LEGS_WIN' => 'LEGS_WIN',
			'JL_LOSSES' => 'LOSSES',
			'JL_NEGPOINTS' => 'NEGPOINTS',
			'JL_OLDNEGPOINTS' => 'OLDNEGPOINTS',
			'JL_PLAYED' => 'PLAYED',
			'JL_POINTS' => 'POINTS',
			'JL_POINTS_RATIO' => 'POINTS_RATIO',
			'JL_QUOT' => 'QUOT',
			'JL_RESULTS' => 'RESULTS',
			'JL_SCOREAGAINST' => 'SCOREAGAINST',
			'JL_SCOREFOR' => 'SCOREFOR',
			'JL_SCOREPCT' => 'SCOREPCT',
			'JL_START' => 'START',
			'JL_TIES' => 'TIES',
			'JL_WINPCT' => 'WINPCT',
			'JL_WINS' => 'WINS'
	);

	 
	echo '<b>'.JText::_('Updating new variables and templates for usage in the Master-Templates').'</b><br />';
	$db = JFactory::getDBO();

	$query='SELECT id,name,master_template FROM #__joomleague_project';
	$db->setQuery($query);
	if ($projects=$db->loadObjectList()) // check that there are projects...
	{
		//echo '<br />';
		$xmldir=JPATH_SITE.DS.'components'.DS.'com_joomleague'.DS.'settings'.DS.'default';

		if ($handle=JFolder::files($xmldir))
		{
			# check that each xml template has a corresponding record in the
			# database for this project (except for projects using master templates).
			# If not,create the rows with default values
			# from the xml file

			foreach ($handle as $file)
			{
				if ((strtolower(substr($file,-3))=='xml') &&
						(substr($file,0,(strlen($file) - 4))!='table') &&
						(substr($file,0,10)!='prediction')
				)
				{
					$defaultconfig=array ();
					$template=substr($file,0,(strlen($file) - 4));
					$out=simplexml_load_file($xmldir.DS.$file,'SimpleXMLElement',LIBXML_NOCDATA);
					$temp='';
					$arr=obj2Array($out);
					$outName=JText::_($out->name[0]);
					echo '<br />'.JText::sprintf('Template: [%1$s] - Name: [%2$s]',"<b>$template</b>","<b>$outName</b>").'<br />';
					if (isset($arr["fieldset"]["field"]))
					{
						foreach ($arr["fieldset"]["field"] as $param)
						{
							$temp .= $param["@attributes"]["name"]."=".$param["@attributes"]["default"]."\n";
							$defaultconfig[$param["@attributes"]["name"]]=$param["@attributes"]["default"];
						}
					}
					else
					{
						foreach ($arr["fieldset"] as $paramsgroup)
						{
							foreach ($paramsgroup["field"] as $param)
							{
									
								if (!isset($param["@attributes"]))
								{
									if (isset($param["name"]))
									{
										$temp .= $param["name"]."=".$param["default"]."\n";
										$defaultconfig[$param["name"]]=$param["default"];
									}
								}
								else if (isset($param["name"]))
								{
									/*
									 * developer: diddipoeler
									* date: change on 13.01.2011
									* Bugtracker Backend Bug #579
									* error message string to object example template teamstats
									*/
									//$temp .= $param["@attributes"]["name"]."=".$param["@attributes"]["default"]."\n";
									$temp .= $param["name"]."=".$param["default"]."\n";
									//$defaultconfig[$param["@attributes"]["name"]]=$param["@attributes"]["default"];
									$defaultconfig[$param["name"]]=$param["default"];
								}
							}
						}
					}
					$changeNeeded=false;
					foreach ($projects as $proj)
					{
						$count_diff=0;
						$configvalues=array();
						$query="SELECT params FROM #__joomleague_template_config WHERE project_id=$proj->id AND template='".$template."'";
						$db->setQuery($query);
						if ($id=$db->loadResult())
						{
							//template present in db for this project
							$string='';
							$templateTitle='';
							$params=explode("\n",trim($id));
							foreach($params AS $param)
							{
								$row = explode("=",$param);
								if(isset($row[1])) {
									list ($name,$value)= $row;
									$configvalues[$name]=$value;
								}
							}

							foreach($defaultconfig AS $key => $value)
							{
								if (!array_key_exists($key,$configvalues))
								{
									$count_diff++;
									$configvalues[$key]=$value;
								}
							}
								
							if ($count_diff || $template == 'ranking' || $template=='overall')
							{
								foreach ($configvalues AS $key=>$value)
								{
									if (preg_match("/%H:%m/i", $value)) {
										// change text
										$value = 'H:i';
									} else {
										// text ok										
									}
									
									/*
									 * developer: diddipoeler
									* date: change on 13.01.2011
									* Bugtracker Backend Bug #579
									* change old textname in newtextname
									*/
									if (preg_match("/JL_/i", $value))
									{
										// change text
										$value = str_replace(array_keys($convert), array_values($convert), $value  );
									}
									else
									{
										// text ok
									}

									$value = trim($value);
									$string .= "$key=$value\n";
								}
								echo JText::sprintf('Difference found for project [%1$s]','<b>'.$proj->name.'</b>').' - ';
								$changeNeeded=true;
								$query =" UPDATE #__joomleague_template_config ";
								$query .= " SET ";
								$query .= " title='".$out->name[0]."',";
								$query .= " params='$string' ";
								$query .= " WHERE template='$template' AND project_id=".$proj->id;

								$db->setQuery($query);
								if (!$db->query())
								{
									echo '<span style="color:red">';
									echo JText::sprintf(	'Problems while saving config for [%1$s] with project-ID [%2$s]!',
											'<b>'.$template.'</b>',
											'<b>'.$proj->id.'</b>');
									echo '</span>'.'<br />';
									echo $db->getErrorMsg().'<br />';
								}
								else
								{
									echo JText::sprintf(	'Updated template [%1$s] with project-ID [%2$s]',
											'<span style="color:green;"><b>'.$template.'</b></span>',
											'<span style="color:green"><b>'.$proj->id.'</b></span>').PrintStepResult(true).'<br />';
								}
							}
						}
						elseif ($proj->master_template ==0)
						{	//check that project has own templates
							//or if template not present,create a row with default values
							echo '<br /><span style="color:orange;">'.JText::sprintf(	'Need to insert into project with project-ID [%1$s] - Project name is [%2$s]',
									'<b>'.$proj->id.'</b>','<b>'.$proj->name.'</b>').'</span><br />';
							$changeNeeded=true;
							$temp=trim($temp);
							$query="	INSERT
							INTO #__joomleague_template_config
							(template,title,params,project_id)
							VALUES
							('$template','".$out->name[0]."','$temp','$proj->id')";
							$db->setQuery($query);
							//echo error,allows to check if there is a mistake in the template file

							if (!$db->query())
							{
								echo '<span style="color:red; font-weight:bold; ">'.JText::sprintf('Error with [%s]:',$template).'</span><br />';
								echo $db->getErrorMsg().'<br/>';
							}
							else
							{
								echo JText::sprintf(	'Inserted %1$s into project with ID %2$s',
										'<b>'.$template.'</b>','<b>'.$proj->id.'</b>').PrintStepResult(true).'<br />';
							}
						}
					}
					if (!$changeNeeded)
					{
						echo '<span style="color:green">'.JText::_('No changes needed for this template').'</span>'.'<br />';
					}
				}
			}
		}
		else
		{
			echo ' - <span style="color:red">'.JText::_('No templates found').'</span>';
		}
	}
	else
	{
		echo ' - <span style="color:green">'.JText::_('No update was needed').'</span>';
	}

	return '';
}

function build_SelectQuery($tablename,$param1)
{
	$query="SELECT * FROM #__joomleague_".$tablename." WHERE name='".$param1."'";
	return $query;
}

function build_InsertQuery_Position($tablename,$param1,$param2,$param3,$param4,$sports_type_id,$order_count)
{
	$alias=JFilterOutput::stringURLSafe($param1);
	$query="INSERT INTO #__joomleague_".$tablename." (`name`,`alias`,`".$param2."`,`parent_id`,`sports_type_id`,`published`,`ordering`) VALUES ('".$param1."','".$alias."','".$param4."','".$param3."','".$sports_type_id."','1','".$order_count."')";
	return $query;
}

function build_InsertQuery_Event($tablename,$param1,$param2,$sports_type_id,$order_count)
{
	$alias=JFilterOutput::stringURLSafe($param1);
	$query="INSERT INTO #__joomleague_".$tablename." (`name`,`alias`,`icon`,`sports_type_id`,`published`,`ordering`) VALUES ('".$param1."','".$alias."','".$param2."','".$sports_type_id."','1','".$order_count."')";
	return $query;
}

function build_InsertQuery_PositionEventType($param1,$param2)
{
	$query="	INSERT INTO	#__joomleague_position_eventtype
			(`position_id`,`eventtype_id`)
			VALUES
			('".$param1."','".$param2."')";
	return $query;
}

function tableExists($tableName)
{
	$db = JFactory::getDBO();
	$query='SELECT * FROM #__'.$tableName;
	$db->setQuery($query);
	$result=$db->query();
	if ((!$result) || ($db->getNumRows() ==0)) // check that table exists...
	{
		echo '<span style="color:red">'.JText::sprintf('Failed checking existance of table [#__%s]',$tableName).'</span><br />';
		echo JText::_ ('DO NOT WORRY... Surely you make a clean install of JoomLeague 1.5 or the table in your DB was empty!!!');
		echo '<br /><br />';
		return false;
	}
	return true;
}

function getVersion()
{
	$db = JFactory::getDBO();
	$query='SELECT * FROM #__joomleague_version ORDER BY date DESC ';
	$db->setQuery($query);
	$result=$db->loadObject();
	if (!$result){
		return '';
	}
	return $result->version;
}

function getUpdatePart()
{
	$option = JRequest::getCmd('option');

	$mainframe = JFactory::getApplication();
	$update_part=$mainframe->getUserState($option.'update_part');
	if ($update_part =='')
	{
		$update_part=1;
	}
	#return 1;
	return $update_part;
}

function setUpdatePart($val=1)
{
	$option = JRequest::getCmd('option');
	$mainframe = JFactory::getApplication();
	$update_part=$mainframe->getUserState($option.'update_part');
	if ($val!=0)
	{
		if ($update_part=='')
		{
			$update_part=1; //1;
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

// ------------------------------------------------------------------------------------------------------------------------

?>
<hr>
<?php
$mtime=microtime();
$mtime=explode(" ",$mtime);
$mtime=$mtime[1] + $mtime[0];
$starttime=$mtime;

$totalUpdateParts=4;
setUpdatePart();

$output1=JText::_('COM_JOOMLEAGUE_DB_UPDATE');

$output2='<span style="color:green; ">';
$output2 .= JText::sprintf('COM_JOOMLEAGUE_DB_UPDATE_TITLE',$lastVersion,$version,$updateFileDate,$updateFileTime);
$output2 .= '</span>';
JToolBarHelper::title($output1);
echo '<p><h2 style="text-align:center; ">'.$output2.'</h2></p>';

echo '<p><h3 style="text-align:center; color:red; ">';
echo JText::_('COM_JOOMLEAGUE_DB_UPDATE_VERIFY_TEXT');
echo '</h3></p>';

echo '<p style="text-align:center; ">'.JText::sprintf('COM_JOOMLEAGUE_DB_UPDATE_TOTALSTEPS','<b>'.$totalUpdateParts.'</b>').'</p>';
echo '<p style="text-align:center; ">'.JText::sprintf('COM_JOOMLEAGUE_DB_UPDATE_STEP_OF_STEP','<b>'.getUpdatePart().'</b>','<b>'.$totalUpdateParts.'</b>').'</p>';

/**/
if (getUpdatePart() < $totalUpdateParts)
{
	// Add here a color transformation for <a> so it is easier to see that a new step has to be confirmed
	echo '<table align="center" width="80%" border="0"><tr><td width="50%">';
	$outStr='<h3 style="text-align:center; ">';
	$outStr .= '<a href="javascript:location.reload(true)" >';
	$outStr .= '<strong>';
	$outStr .= JText::sprintf('COM_JOOMLEAGUE_DB_UPDATE_CLICK_HERE',getUpdatePart()+1,$totalUpdateParts);
	$outStr .= '</strong>';
	$outStr .= '</a>';
	$outStr .= '</h3>';
	if (getUpdatePart()%2 ==1)
	{
		echo $outStr.'</td><td width="50%">&nbsp;';
	}
	else
	{
		echo '&nbsp;</td><td width="50%">'.$outStr;
	}
	echo '</td></tr>';
	echo '</table>';

	echo '<p style="text-align:center; ">';
	echo '<b>';
	echo JText::sprintf('COM_JOOMLEAGUE_DB_UPDATE_REMEMBER_TOTAL_STEPS_COUNT',$totalUpdateParts);
	echo '</b>';
	echo '<br />';
	echo JText::_('COM_JOOMLEAGUE_DB_UPDATE_SCROLL_DOWN');
	echo '</p>';
	echo '<p style="text-align:center; ">';
	echo JText::_('COM_JOOMLEAGUE_DB_UPDATE_INFO_UNKNOWN_ETC').'<br />';
	echo JText::_('COM_JOOMLEAGUE_DB_UPDATE_INFO_JUST_INFOTEXT').'<br />';
	echo '</p>';
}
echo '<hr>';
/**/

if (getUpdatePart()==1)
{
	echo '<p>';
	echo '<h3>';
	echo '<span style="color:orange">';
	echo JText::sprintf(	'COM_JOOMLEAGUE_DB_UPDATE_DELETE_WARNING',
											'</span><b><i><a href="index.php?option=com_user&task=logout">',
											'</i></b></a><span style="color:orange">');
				echo '</span>';
				echo '</h3>';
				echo '</p>';
				$JLTablesVersion=getVersion();
				if (($JLTablesVersion!='') && ($JLTablesVersion<'0.93'))
				{
					echo '<span style="color:red">';
					echo JText::_('COM_JOOMLEAGUE_DB_UPDATE_ATTENTION');
					echo '<br /><br />';
					echo JText::_('You are updating from an older release of JoomLeague than 0.93!');
					echo '<br />';
					echo JText::sprintf('Actually your JoomLeague-MYSQL-Tables are ready for JoomLeague v%1$s','<b>'.$JLTablesVersion.'</b>');
					echo '<br />';
					echo JText::_('Update may not be completely sucessfull as we require JoomLeague-MYSQL-tables according to the release 0.93!');
					echo '</span><br />';
					echo '<span style="color:green">';
					echo JText::sprintf(	'It would be better to update your JoomLeague installation to v0.93 before you update to JoomLeague %1$s!',
										'<b>'.$version.'</b>');
			echo '</span><br /><br />';
			echo '<span style="color:red">'.JText::_('COM_JOOMLEAGUE_DB_UPDATE_DANGER').'</span><br /><br />';
			echo '<span style="color:red">'.JText::_('PLEASE use this script ONLY IF you REALLY know what you do!!!').'</span><br />';
				}
}

if (getUpdatePart()==2)
{
	echo Change_Table_Columns($updates,'joomleague_project');
	echo '<hr>';
}
unset($updates);

if (getUpdatePart()==3)
{
	echo UpdateTemplateMasters().'<br />';
	echo '<hr>';
}
if (getUpdatePart()==4)
{
		$arrQueries = array();
		
		$query = "update #__joomleague_club set logo_big = replace(logo_big, 'media/com_joomleague/clubs/large', 'images/com_joomleague/database/clubs/large')";
		array_push($arrQueries, $query);
		
		$query = "update #__joomleague_club set logo_middle = replace(logo_middle, 'media/com_joomleague/clubs/medium', 'images/com_joomleague/database/clubs/medium')";
		array_push($arrQueries, $query);
		
		$query = "update #__joomleague_club set logo_small = replace(logo_small, 'media/com_joomleague/clubs/small', 'images/com_joomleague/database/clubs/small')";
		array_push($arrQueries, $query);
		
		$query = "update #__joomleague_eventtype set icon = replace(icon, 'media/com_joomleague/event_icons', 'images/com_joomleague/database/events')";
		array_push($arrQueries, $query);
		
		$query = "update #__joomleague_person set picture = replace(picture, 'media/com_joomleague/persons', 'images/com_joomleague/database/persons')";
		array_push($arrQueries, $query);
		
		$query = "update #__joomleague_team_player set picture = replace(picture, 'media/com_joomleague/persons', 'images/com_joomleague/database/persons')";
		array_push($arrQueries, $query);
		
		$query = "update #__joomleague_project set picture = replace(picture, 'media/com_joomleague/projects', 'images/com_joomleague/database/projects')";
		array_push($arrQueries, $query);
		
		$query = "update #__joomleague_playground set picture = replace(picture, 'media/com_joomleague/playgrounds', 'images/com_joomleague/database/playgrounds')";
		array_push($arrQueries, $query);
		
		$query = "update #__joomleague_sports_type set icon = replace(icon, 'media/com_joomleague/sportstypes', 'images/com_joomleague/database/sport_types')";
		array_push($arrQueries, $query);
		
		$query = "update #__joomleague_team set picture = replace(picture, 'media/com_joomleague/teams', 'images/com_joomleague/database/teams')";
		array_push($arrQueries, $query);
		
		$query = "update #__joomleague_project_team set picture = replace(picture, 'media/com_joomleague/teams', 'images/com_joomleague/database/teams')";
		array_push($arrQueries, $query);
		
		$query = "update #__joomleague_statistic set icon = replace(icon, 'media/com_joomleague/statistics', 'images/com_joomleague/database/statistics')";
		array_push($arrQueries, $query);
		
		$db = JFactory::getDBO();
		$query="SHOW TABLES LIKE '%_joomleague%'";
			
		$db->setQuery($query);
		$results = $db->loadColumn();
		if(is_array($results)) {
			echo JText::_('Database Tables Picture Path Migration');
			foreach ($arrQueries as $key=>$value) {
				$db->setQuery($value);
				if (!$db->query())
				{
					echo '-> '.JText::_('Failed').'! <br>';
					echo $db->getErrorMsg();
					//return false;
				} else {
					//echo "-> done !<br>";		
				}
				
			}
			echo ' - <span style="color:green">'.JText::_('Success').'</span><br />';
		} else {
			echo JText::_('No Picture Path Migration neccessary!'); 
		}
		echo '<hr>';
}

if (getUpdatePart()==$totalUpdateParts)
{
	echo JText::_('Deleting temporary fields in the tables which were needed for the update routine!').'<br /><br />';
	//echo Delete_Table_Columns($updates3,'joomleague_template_config');
	//echo Delete_Table_Columns($updates3,'joomleague_project');
	echo '<br />';
	echo '<hr>';

	echo '<p><h1 style="text-align:center; color:green; ">';
	echo JText::_('COM_JOOMLEAGUE_DB_UPDATE_CONGRATULATIONS');
	echo '<br />';
	echo JText::_('COM_JOOMLEAGUE_DB_UPDATE_ALL_STEPS_FINISHED');
	echo '<br />';
	echo JText::_('COM_JOOMLEAGUE_DB_UPDATE_USE_NOW');
	echo '</h1></p>';

	setUpdatePart(0);
}
else
{
	echo '<h3 style="text-align:center; ">';
	echo '<a href="javascript:location.reload(true)">';
	echo '<strong>';
	echo JText::sprintf('COM_JOOMLEAGUE_DB_UPDATE_CLICK_HERE',getUpdatePart()+1,$totalUpdateParts).'<br />';
	echo JText::_('COM_JOOMLEAGUE_DB_UPDATE_MAY_NEED_TIME').'<br />';
	echo '</strong>';
	echo '</a>';
	echo '</h3>';
	echo '<p style="text-align:center; ">';
	echo JText::sprintf('COM_JOOMLEAGUE_DB_UPDATE_TIME_MEMORY_SET',$maxImportTime,$maxImportMemory).'<br />';
	echo JText::_('COM_JOOMLEAGUE_DB_UPDATE_INFO_TIMEOUT_ERROR').'<br />';
	echo JText::_('COM_JOOMLEAGUE_DB_UPDATE_INFO_LOCAL_UPDATE').'<br />';
	echo '</p>';
	echo '<h2 style="text-align:center; color:orange; ">';
	echo JText::_('COM_JOOMLEAGUE_DB_UPDATE_BE_PATIENT');
	echo '</h2>';
}
if (JComponentHelper::getParams('com_joomleague')->get('show_debug_info',0))
{
	echo '<center><hr>';
	echo JText::sprintf('Memory Limit is %1$s',ini_get('memory_limit')).'<br />';
	echo JText::sprintf('Memory Peak Usage was %1$s Bytes',number_format(memory_get_peak_usage(true),0,'','.')).'<br />';
	echo JText::sprintf('Time Limit is %1$s seconds',ini_get('max_execution_time')).'<br />';
	$mtime=microtime();
	$mtime=explode(" ",$mtime);
	$mtime=$mtime[1] + $mtime[0];
	$endtime=$mtime;
	$totaltime=($endtime - $starttime);
	echo JText::sprintf('This page was created in %1$s seconds',$totaltime);
	echo '<hr></center>';
}

?>