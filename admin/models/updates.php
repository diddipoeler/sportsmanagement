<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');



/**
 * sportsmanagementModelUpdates
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelUpdates extends JModelLegacy
{

	/**
	 * sportsmanagementModelUpdates::loadUpdateFile()
	 * 
	 * @param mixed $myfilename
	 * @param mixed $file
	 * @return
	 */
	function loadUpdateFile($myfilename,$file)
	{
		include_once($myfilename);
		$data=array();
		$updateArray=array();
		$file_name=$file;
$this->app = JFactory::getApplication();
		if ($file=='jl_upgrade-0_93b_to_1_5.php'){return '';}
		$data['id'] = 0;
		$data['count'] = 0;

		$query='SELECT id,count FROM #__sportsmanagement_version where file LIKE '.$this->_db->Quote($file);
		$this->_db->setQuery($query);	
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query,true).'</pre>'),'Notice');			
		if (!$result=$this->_db->loadObject())
		{
			$this->setError($this->_db->getErrorMsg());
		}
		else
		{
			$data['id']=$result->id;
			$data['count']=(int) $result->count + 1;
		}
		$data['file'] = $file_name;

		$query="SELECT * FROM #__sportsmanagement_version where file LIKE 'sportsmanagement'";
		$this->_db->setQuery($query);
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' query<br><pre>'.print_r($query,true).'</pre>'),'Notice');			
		if (!$result=$this->_db->loadObject())
		{
			$this->setError($this->_db->getErrorMsg());

//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getErrorMsg<br><pre>'.print_r($this->_db->getErrorMsg(),true).'</pre>'),'Notice');			
		}
		else 
		{
			$data['version']=!empty($version) ? $version : $result->version;
			$data['major']=!empty($major) ? $major : $result->major;
			$data['minor']=!empty($minor) ? $minor : $result->minor;
			$data['build']=!empty($build) ? $build : $result->build ;
			$data['revision']=!empty($revision) ? $revision : $result->revision;			
		}
// Create and populate an object.
$object= new stdClass();
$object->id = $data['id'];
$object->count = $data['count'];
$object->file = $data['file'];

 
if ( $data['id'] ) 
{
// Update their details in the table using id as the primary key.
$result = JFactory::getDbo()->updateObject('#__sportsmanagement_version', $object, 'id');
}
else
{
$object->count = 1;
// Insert the object into the table.
$result = $this->_db->insertObject('#__sportsmanagement_version', $object);	
}		


		return '';
	}

	/**
	 * sportsmanagementModelUpdates::getVersions()
	 * 
	 * @return
	 */
	function getVersions()
	{
		$query='SELECT id, version, DATE_FORMAT(date,"%Y-%m-%d %H:%i") date FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_version';
		$this->_db->setQuery($query);
		if (!$result=$this->_db->loadObjectList())
		{
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return $result;
	}

	/**
	 * sportsmanagementModelUpdates::_cmpDate()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function _cmpDate($a,$b)
	{
		$ua=strtotime($a['updateFileDate']);
		$ub=strtotime($b['updateFileDate']);
		if ($ua==$ub){return 0;}
		return ($ua > $ub ? -1 : 1);
	}

	/**
	 * sportsmanagementModelUpdates::_cmpName()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function _cmpName($a,$b)
	{
		return strcasecmp($a['file_name'],$b['file_name']);
	}

	/**
	 * sportsmanagementModelUpdates::_cmpVersion()
	 * 
	 * @param mixed $a
	 * @param mixed $b
	 * @return
	 */
	function _cmpVersion($a,$b)
	{
		return strcasecmp($a['last_version'],$b['last_version']);
	}


  /**
   * sportsmanagementModelUpdates::getVersionHistory()
   * 
   * @return
   */
  function getVersionHistory()
  {
  $query='SELECT * FROM #__'.COM_SPORTSMANAGEMENT_TABLE.'_version_history order by date DESC';
		$this->_db->setQuery($query);		
		$result = $this->_db->loadObjectList();
  return $result;
  }
  
	/**
	 * sportsmanagementModelUpdates::loadUpdateFiles()
	 * 
	 * @return
	 */
	function loadUpdateFiles()
	{
		$option = JFactory::getApplication()->input->getCmd('option');
		$app = JFactory::getApplication();
		//$updateFileList=JFolder::files(JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'updates'.DS,'.php$',false,true,array('',''));
		$updateFileList=JFolder::files(JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'updates'.DS,'.php$');
		// installer for extensions
		$extensions=JFolder::folders(JPATH_COMPONENT_SITE.DS.'extensions');
		foreach ($extensions as $ext)
		{
			$path=JPATH_COMPONENT_SITE.DS.'extensions'.DS.$ext.DS.'admin'.DS.'install';
			if (JFolder::exists($path))
			{
				foreach (JFolder::files($path,'.php$') as $file)
				{
					$updateFileList[]=$ext.'/'.$file;
				}
			}
		}
		$updateFiles=array();
		$i=0;
		foreach ($updateFileList AS $updateFile)
		{
			$path=explode('/',$updateFile);
			if (count($path) > 1)
			{
				$filepath=JPATH_COMPONENT_SITE.DS.'extensions'.DS.$path[0].DS.'admin'.DS.'install'.DS.$path[1];
			}
			else
			{
				$filepath=JPATH_COMPONENT_ADMINISTRATOR.DS.'assets'.DS.'updates'.DS.$path[0];
			}
			if ($fileContent=JFile::read($filepath))
			{
				$version='';
				$updateDescription='';
				$lastVersion='';
				$updateDate='';
				$updateTime='';
				$pos=strpos($fileContent,'$version');
				if ($pos !== false)
				{
					$dDummy=substr($fileContent,$pos);
					$pos2=strpos($dDummy,'=');
					$dDummy=substr($dDummy,$pos2);
					$pos3=strpos($dDummy,"'");
					$dDummy=substr($dDummy,$pos3 + 1);
					$pos4=strpos($dDummy,"'");
					$version=trim(substr($dDummy,0,$pos4));
				}
				$pos=strpos($fileContent,'$updateDescription');
				if ($pos !== false)
				{
					$dDummy=substr($fileContent,$pos);
					$pos2=strpos($dDummy,'=');
					$dDummy=substr($dDummy,$pos2);
					$pos3=strpos($dDummy,"'");
					$dDummy=substr($dDummy,$pos3 + 1);
					$pos4=strpos($dDummy,"'");
					$updateDescription=trim(substr($dDummy,0,$pos4));
				}
				$pos=strpos($fileContent,'$lastVersion');
				if ($pos !== false)
				{
					$dDummy=substr($fileContent,$pos);
					$pos2=strpos($dDummy,'=');
					$dDummy=substr($dDummy,$pos2);
					$pos3=strpos($dDummy,"'");
					$dDummy=substr($dDummy,$pos3 + 1);
					$pos4=strpos($dDummy,"'");
					$lastVersion=trim(substr($dDummy,0,$pos4));
				}
				$pos=strpos($fileContent,'$updateFileDate');
				if ($pos !== false)
				{
					$dDummy=substr($fileContent,$pos);
					$pos2=strpos($dDummy,'=');
					$dDummy=substr($dDummy,$pos2);
					$pos3=strpos($dDummy,"'");
					$dDummy=substr($dDummy,$pos3 + 1);
					$pos4=strpos($dDummy,"'");
					$updateFileDate=trim(substr($dDummy,0,$pos4));
				}
				$pos=strpos($fileContent,'$updateFileTime');
				if ($pos !== false)
				{
					$dDummy=substr($fileContent,$pos);
					$pos2=strpos($dDummy,'=');
					$dDummy=substr($dDummy,$pos2);
					$pos3=strpos($dDummy,"'");
					$dDummy=substr($dDummy,$pos3 + 1);
					$pos4=strpos($dDummy,"'");
					$updateFileTime=trim(substr($dDummy,0,$pos4));
				}
				$pos=strpos($fileContent,'$excludeFile');
				if ($pos !== false)
				{
					$dDummy=substr($fileContent,$pos);
					$pos2=strpos($dDummy,'=');
					$dDummy=substr($dDummy,$pos2);
					$pos3=strpos($dDummy,"'");
					$dDummy=substr($dDummy,$pos3 + 1);
					$pos4=strpos($dDummy,"'");
					$excludeFile=trim(substr($dDummy,0,$pos4));
					if($excludeFile=='true') continue;
				}
				$updateFiles[$i]['id']=$i;
				$updateFiles[$i]['file_name']=$updateFile;
				$updateFiles[$i]['version']=$version;
				$updateFiles[$i]['last_version']=$lastVersion;
				$updateFiles[$i]['updateFileDate']=trim($updateFileDate);
				$updateFiles[$i]['updateFileTime']=$updateFileTime;
				$updateFiles[$i]['updateTime']='0000-00-00 00:00:00';
				$updateFiles[$i]['updateDescription']=$updateDescription;
				$updateFiles[$i]['date']='';
				$updateFiles[$i]['count']=0;
				$query="SELECT date,count FROM #__".COM_SPORTSMANAGEMENT_TABLE."_version where file=".$this->_db->Quote($updateFile);
				$this->_db->setQuery($query);
				if (!$result=$this->_db->loadObject())
				{
					$this->setError($this->_db->getErrorMsg());
				}
				else
				{
					$updateFiles[$i]['date']=$result->date;
					$updateFiles[$i]['count']=$result->count;
				}
				$i++;
			}
		}
		$filter_order		= $app->getUserState($option.'updates_filter_order',		'filter_order',		'dates',	'cmd');
		$filter_order_Dir	= $app->getUserState($option.'updates_filter_order_Dir',	'filter_order_Dir',	'',			'word');
		$orderfn='_cmpDate';
		switch ($filter_order)
		{
			case 'name':
				$orderfn='_cmpName';
				break;

			case 'version':
				$orderfn='_cmpVersion';
				break;

			case 'date':
				$orderfn='_cmpDate';
				break;
		}
		usort($updateFiles,array($this,$orderfn));
		if (strcasecmp($filter_order_Dir,'ASC')==0){
			$updateFiles=array_reverse($updateFiles);
		}
		return $updateFiles;
	}
}
?>
