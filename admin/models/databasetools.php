<?php
/**
 * @copyright	Copyright (C) 2006-2013 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL,see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License,and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$maxImportTime=JComponentHelper::getParams('com_joomleague')->get('max_import_time',0);
if (empty($maxImportTime))
{
	$maxImportTime=480;
}
if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}

$maxImportMemory=JComponentHelper::getParams('com_joomleague')->get('max_import_memory',0);
if (empty($maxImportMemory))
{
	$maxImportMemory='150M';
}
if ((int)ini_get('memory_limit') < (int)$maxImportMemory){@ini_set('memory_limit',$maxImportMemory);}

jimport('joomla.application.component.model');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

/**
 * Joomleague Component DatabaseTools Model
 *
 * @author	Kurt Norgaz
 * @package	JoomLeague
 * @since	1.5.0a
 */

class sportsmanagementModelDatabaseTools extends JModel
{
	
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
				$arr[$key]=$this->obj2Array($value);
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

    function optimize()
	{
		$query="SHOW TABLES LIKE '%_joomleague%'";
		$this->_db->setQuery($query);
		$results=$this->_db->loadResultArray();
		foreach ($results as $result)
		{
			$query='OPTIMIZE TABLE `'.$result.'`'; $this->_db->setQuery($query);
		}		
		
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
	return true;
	}

	function repair()
	{
		$query="SHOW TABLES LIKE '%_joomleague%'";
		$this->_db->setQuery($query);
		$results=$this->_db->loadResultArray();
		foreach ($results as $result)
		{
			$query='REPAIR TABLE `'.$result.'`'; $this->_db->setQuery($query);
		}		
		
		if (!$this->_db->query())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
	return true;
	}
    
    function updatetemplatemasters()
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
	//$db = JFactory::getDBO();

	$query='SELECT id,name,master_template FROM #__joomleague_project';
	$this->_db->setQuery($query);
	if ($projects=$this->_db->loadObjectList()) // check that there are projects...
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
					$arr=$this->obj2Array($out);
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
						$this->_db->setQuery($query);
						if ($id=$this->_db->loadResult())
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

								$this->_db->setQuery($query);
								if (!$this->_db->query())
								{
									echo '<span style="color:red">';
									echo JText::sprintf(	'Problems while saving config for [%1$s] with project-ID [%2$s]!',
											'<b>'.$template.'</b>',
											'<b>'.$proj->id.'</b>');
									echo '</span>'.'<br />';
									echo $this->_db->getErrorMsg().'<br />';
								}
								else
								{
									echo JText::sprintf(	'Updated template [%1$s] with project-ID [%2$s]',
											'<span style="color:green;"><b>'.$template.'</b></span>',
											'<span style="color:green"><b>'.$proj->id.'</b></span>').$this->PrintStepResult(true).'<br />';
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
							$this->_db->setQuery($query);
							//echo error,allows to check if there is a mistake in the template file

							if (!$this->_db->query())
							{
								echo '<span style="color:red; font-weight:bold; ">'.JText::sprintf('Error with [%s]:',$template).'</span><br />';
								echo $this->_db->getErrorMsg().'<br/>';
							}
							else
							{
								echo JText::sprintf(	'Inserted %1$s into project with ID %2$s',
										'<b>'.$template.'</b>','<b>'.$proj->id.'</b>').$this->PrintStepResult(true).'<br />';
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
    
    
    function picturepath()
	{
	$arrQueries = array();
		
		$query = "update #__joomleague_club set logo_big = replace(logo_big, 'media/com_joomleague/clubs/large', 'images/com_joomleague/database/clubs/large')";
		array_push($arrQueries, $query);
		
		$query = "update #__joomleague_club set logo_middle = replace(logo_middle, 'media/com_joomleague/clubs/medium', 'images/com_joomleague/database/clubs/medium')";
		array_push($arrQueries, $query);
		
		$query = "update #__joomleague_club set logo_small = replace(logo_small, 'media/com_joomleague/clubs/small', 'images/com_joomleague/database/clubs/small')";
		array_push($arrQueries, $query);
    
    $query = "update #__joomleague_club set trikot_home = replace(trikot_home, 'media/com_joomleague/clubs/trikot_home', 'images/com_joomleague/database/clubs/trikot_home')";
		array_push($arrQueries, $query);
    $query = "update #__joomleague_club set trikot_away = replace(trikot_away, 'media/com_joomleague/clubs/trikot_away', 'images/com_joomleague/database/clubs/trikot_away')";
		array_push($arrQueries, $query);
		
		$query = "update #__joomleague_eventtype set icon = replace(icon, 'media/com_joomleague/event_icons', 'images/com_joomleague/database/events')";
		array_push($arrQueries, $query);
		
		$query = "update #__joomleague_person set picture = replace(picture, 'media/com_joomleague/persons', 'images/com_joomleague/database/persons')";
		array_push($arrQueries, $query);

// diddipoeler test
// update j25_joomleague_person set picture = replace(picture, 'media/com_joomleague/persons', 'images/com_joomleague/database/persons')
// platzhalter
// update j25_joomleague_person set picture = replace(picture, 'media/com_joomleague/placeholders/placeholder_150_2.png', 'images/com_joomleague/database/placeholders/placeholder_150_2.png')		
// update j25_joomleague_team_player set picture = replace(picture, 'media/com_joomleague/placeholders/placeholder_150_2.png', 'images/com_joomleague/database/placeholders/placeholder_150_2.png')
// update j25_joomleague_team_staff set picture = replace(picture, 'media/com_joomleague/placeholders/placeholder_150_2.png', 'images/com_joomleague/database/placeholders/placeholder_150_2.png')

// update j25_joomleague_project_team set picture = replace(picture, 'media/com_joomleague/placeholders/placeholder_450_2.png', 'images/com_joomleague/database/placeholders/placeholder_450_2.png')

		$query = "update #__joomleague_team_player set picture = replace(picture, 'media/com_joomleague/persons', 'images/com_joomleague/database/persons')";
		array_push($arrQueries, $query);
		
		// diddipoeler
		// leider vergessen
		$query = "update #__joomleague_team_staff set picture = replace(picture, 'media/com_joomleague/persons', 'images/com_joomleague/database/persons')";
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
        
        $query="SHOW TABLES LIKE '%_joomleague%'";
			
		$this->_db->setQuery($query);
		$results = $this->_db->loadColumn();
		if(is_array($results)) {
			echo JText::_('Database Tables Picture Path Migration');
			foreach ($arrQueries as $key=>$value) {
				$this->_db->setQuery($value);
				if (!$this->_db->query())
				{
					echo '-> '.JText::_('Failed').'! <br>';
					$this->setError($this->_db->getErrorMsg());
					echo $this->_db->getErrorMsg();
					//return false;
				} else {
					//echo "-> done !<br>";		
				}
				
			}
			echo ' - <span style="color:green">'.JText::_('Success').'</span><br />';
            return true;
		} else {
			echo JText::_('No Picture Path Migration neccessary!'); 
            return false;
		}	
	//return true;
	}

}
?>