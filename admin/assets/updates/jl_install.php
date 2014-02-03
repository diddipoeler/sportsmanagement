<?php
// no direct access
defined('_JEXEC') or die('Restricted access');

$version			= '2.0.43.b3fd04d-a';
$updateFileDate		= '2012-09-13';
$updateFileTime		= '00:05';
$updateDescription	='<span style="color:green">Installationscript called during installation.</span>';
$excludeFile		='true';

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
jimport('joomla.installer.installer');

require_once( JPATH_ADMINISTRATOR.'/components/com_sportsmanagement/'. 'helpers' . DS . 'jinstallationhelper.php' );

function PrintStepResult($status)
{
	switch ($status)
	{
		case 0:
			$output=' - <span style="color:red">'.JText::_('Failed').'</span><br />';
			break;
		case 1:
			$output=' - <span style="color:green">'.JText::_('Success').'</span><br />';
			break;
		case 2:
			$output=' - <span style="color:orange">'.JText::_('Skipped').'</span><br />';
			break;
	}
	return $output;
}

function getVersion()
{
	$db = JFactory::getDBO();
    $db->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = "com_sportsmanagement" and type ="component" ');
    $paramsdata = json_decode( $db->loadResult(), true );
    /*
	$version=new stdClass();
	$version->major=2;
	$version->minor=0;
	$version->build=0;
	$version->revision='a';
	$version->file='joomleague';
	$version->date='0000-00-00 00:00:00';

	$query='SELECT * FROM #__joomleague_version ORDER BY date DESC';
	$db->setQuery($query);
	$result=$db->loadObject();
	if (!$result){
		return $version;
	}
	*/
	return $paramsdata['version'];
}

/**
 * make sure the version table has the proper structure (1.0 import !)
 * if not, update it
 */
function _checkVersionTable()
{
	$db = Jfactory::getDBO();

	$res = $db->getTableFields('#__joomleague_version');
	$cols = array_keys(reset($res));

	if (!in_array('major', $cols))
	{
		$query = ' ALTER TABLE #__joomleague_version ADD `major` INT NOT NULL ,
				ADD `minor` INT NOT NULL ,
				ADD `build` INT NOT NULL ,
				ADD `count` INT NOT NULL ,
				ADD `revision` VARCHAR(128) NOT NULL ,
				ADD `file` VARCHAR(255) NOT NULL';
		$db->setQuery($query);
		if (!$db->query()) {
			echo JText::_('Failed updating version table');
		}
	}
}

function updateVersion($versionData)
{
	echo JText::_('Updating database version');

  /*
  // diddipoeler 
  // blödsinn
  
	$status=0;
	$updateVersionFile=JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomleague'.DS.'assets'.DS.'updates'.DS.'update_version.sql';

	if (JFile::exists($updateVersionFile))
	{
		$fileContent=JFile::read($updateVersionFile);
	}
	else
	{
		$fileContent="update #__joomleague_version set major='2', minor='0', build='0', revision='a5bdbf', version='a', file='joomleague'";
	}

	$dummy=explode("'",$fileContent);
	$versionData			= new stdClass();
	$versionData->major		= $dummy[1];
	$versionData->minor		= $dummy[3];
	$versionData->build		= $dummy[5];
	$versionData->revision	= $dummy[7];
	$versionData->date		= NULL;
	$versionData->version	= $dummy[9];
	$versionData->file		= $dummy[11];
	$tblVersion = JTable::getInstance("Version", "Table");
	$tblVersion->load(1);
	echo " from '" .
			$tblVersion->major . "." . $tblVersion->minor . "." . $tblVersion->build . "." . $tblVersion->revision . "-" . $tblVersion->version . " "
					. "' to '";
	if($tblVersion->version=="") {
		$tblVersion->id		= 0;
	} else {
		$tblVersion->id		= 1;
	}
	$tblVersion->version	= $versionData->version;
	$tblVersion->major		= $versionData->major;
	$tblVersion->minor		= $versionData->minor;
	$tblVersion->build		= $versionData->build;
	$tblVersion->revision	= $versionData->revision;
	$tblVersion->date		= NULL;
	$tblVersion->file		= $versionData->file;
	$tblVersion->count		= ++$tblVersion->count;
	if (!$tblVersion->store())
	{
		echo($tblVersion->getError());
	}
	*/
  $status=1;
	
  //echo $versionData->major . "." . $versionData->minor . "." . $versionData->build . "." . $versionData->revision . "-" . $versionData->version . "' ";
	
  
//   $db =& JFactory::getDBO();
//   $db_table = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomleague'.DS.'sql'.DS.'updates'.DS.$this->release.'.sql';
//   $result = JInstallationHelper::populateDatabase($db, $db_table, $errors);
  
  
  return $status;
}

function addGhostPlayer()
{
	echo JText::_('Inserting Ghost-Player data');
	$status=0;
	$db = JFactory::getDBO();

	// Add new Parent position to PlayersPositions
	$queryAdd="INSERT INTO #__joomleague_person (`firstname`,`lastname`,`nickname`,`birthday`,`show_pic`,`published`,`ordering`)
			VALUES('!Unknown','!Player','!Ghost','1970-01-01','0','1','0')";

	$query="SELECT * FROM #__joomleague_person WHERE id=1 AND firstname='!Unknown' AND nickname='!Ghost' AND lastname='!Player'";
	$db->setQuery($query);
	if (!$dbresult=$db->loadObject())
	{
		$db->setQuery($queryAdd);
		$result=$db->query();
		$status=1;
	}
	else
	{
		$status=2;
	}
	return $status;
}

function addSportsType()
{
	echo JText::_('Inserting default Sport-Types');

	$status=0;
	$db= JFactory::getDBO();
	$extension 	= "com_joomleague_sport_types";
	$lang 		= JFactory::getLanguage();
	$source 	= JPATH_ADMINISTRATOR . '/components/' . $extension;
	$lang->load("$extension", JPATH_ADMINISTRATOR, null, false, false)
	||	$lang->load($extension, $source, null, false, false)
	||	$lang->load($extension, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
	||	$lang->load($extension, $source, $lang->getDefault(), false, false);

	$props 		= $lang->getProperties();
	$strings 	= &$props['strings'];
	$status=1;
	$praefix = 'COM_SPORTSMANAGEMENT_ST_';
	foreach ($strings as $key => $value) {
		// Add all Sport-types e.g. Soccer to #__joomleague_sports_type
		$pos = strpos($key, $praefix);
		if($pos !== false) {
			$name = strtolower(substr($key, strlen($praefix)));
			$tblSportsType = JTable::getInstance("SportsType", "Table");
			$tblSportsType->name = $key;
			$tblSportsType->icon = JPATH::clean('images/com_joomleague/database/sport_types/'.$name.'.png');
			if (!$tblSportsType->store())
			{
				//echo($tblSportsType->getError());
				$status=2;
			} 
			JFolder::create(JPATH::clean(JPATH_ROOT.'/images/com_joomleague/database/events/'.$name));
		}
	}
	return $status;
}
_checkVersionTable();

$versionData=getVersion();
$major=$versionData->major;
$minor=$versionData->minor;
$build=$versionData->build;
$revision=$versionData->revision;
$version=sprintf('v%1$s.%2$s.%3$s.%4$s',$major,$minor,$build,$revision);

echo PrintStepResult(addGhostPlayer());
echo PrintStepResult(addSportsType());
echo PrintStepResult(updateVersion($versionData));
?>