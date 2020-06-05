<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage models
 * @file       updates.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Log\Log;

/**
 * sportsmanagementModelUpdates
 *
 * @package
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelUpdates extends BaseDatabaseModel
{

	/**
	 * sportsmanagementModelUpdates::loadUpdateFile()
	 *
	 * @param   mixed  $myfilename
	 * @param   mixed  $file
	 *
	 * @return
	 */
	function loadUpdateFile($myfilename, $file)
	{
		include_once $myfilename;
		$data        = array();
		$updateArray = array();
		$file_name   = $file;

		if ($file == 'jl_upgrade-0_93b_to_1_5.php')
		{
			return '';
		}

		$data['id']    = 0;
		$data['count'] = 0;

		$query = 'SELECT id,count FROM #__sportsmanagement_version where file LIKE ' . $this->_db->Quote($file);
		Factory::getDbo()->setQuery($query);

		if (!$result = Factory::getDbo()->loadObject())
		{
			$this->setError($this->_db->getErrorMsg());
		}
		else
		{
			$data['id']    = $result->id;
			$data['count'] = (int) $result->count + 1;
		}

		$data['file'] = $file_name;

		$query = "SELECT * FROM #__sportsmanagement_version where file LIKE 'sportsmanagement'";
		Factory::getDbo()->setQuery($query);

		if (!$result = Factory::getDbo()->loadObject())
		{
			$this->setError($this->_db->getErrorMsg());
		}
		else
		{
			$data['version']  = !empty($version) ? $version : $result->version;
			$data['major']    = !empty($major) ? $major : $result->major;
			$data['minor']    = !empty($minor) ? $minor : $result->minor;
			$data['build']    = !empty($build) ? $build : $result->build;
			$data['revision'] = !empty($revision) ? $revision : $result->revision;
		}

		$object        = new stdClass;
		$object->id    = $data['id'];
		$object->count = $data['count'];
		$object->file  = $data['file'];

		if ($data['id'])
		{
			// Update their details in the table using id as the primary key.
			$result = Factory::getDbo()->updateObject('#__sportsmanagement_version', $object, 'id');
		}
		else
		{
			$object->count = 1;

			// Insert the object into the table.
			$result = Factory::getDbo()->insertObject('#__sportsmanagement_version', $object);
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
		$query = 'SELECT id, version, DATE_FORMAT(date,"%Y-%m-%d %H:%i") date FROM #__sportsmanagement_version';
		Factory::getDbo()->setQuery($query);

try
		{
		 $result = Factory::getDbo()->loadObjectList(); 
          }
		catch (Exception $e)
		{
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');
			$result = false;
		}
        
		return $result;
	}

	/**
	 * sportsmanagementModelUpdates::_cmpDate()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function _cmpDate($a, $b)
	{
		$ua = strtotime($a['updateFileDate']);
		$ub = strtotime($b['updateFileDate']);

		if ($ua == $ub)
		{
			return 0;
		}

		return ($ua > $ub ? -1 : 1);
	}

	/**
	 * sportsmanagementModelUpdates::_cmpName()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function _cmpName($a, $b)
	{
		return strcasecmp($a['file_name'], $b['file_name']);
	}

	/**
	 * sportsmanagementModelUpdates::_cmpVersion()
	 *
	 * @param   mixed  $a
	 * @param   mixed  $b
	 *
	 * @return
	 */
	function _cmpVersion($a, $b)
	{
		return strcasecmp($a['last_version'], $b['last_version']);
	}


	/**
	 * sportsmanagementModelUpdates::getVersionHistory()
	 *
	 * @return
	 */
	function getVersionHistory()
	{
		$query = 'SELECT * FROM #__sportsmanagement_version_history order by date DESC';
		Factory::getDbo()->setQuery($query);
		$result = Factory::getDbo()->loadObjectList();
		return $result;
	}

	/**
	 * sportsmanagementModelUpdates::loadUpdateFiles()
	 *
	 * @return
	 */
	function loadUpdateFiles()
	{
		$option = Factory::getApplication()->input->getCmd('option');
		$app    = Factory::getApplication();

		$updateFileList = Folder::files(JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'updates' . DS, '.php$');

		// Installer for extensions
		$extensions = Folder::folders(JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'extensions');

		foreach ($extensions as $ext)
		{
			$path = JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . $ext . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'install';

			if (Folder::exists($path))
			{
				foreach (Folder::files($path, '.php$') as $file)
				{
					$updateFileList[] = $ext . '/' . $file;
				}
			}
		}

		$updateFiles = array();
		$i           = 0;

		foreach ($updateFileList AS $updateFile)
		{
			$path = explode('/', $updateFile);

			if (count($path) > 1)
			{
				$filepath = JPATH_COMPONENT_SITE . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . $path[0] . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'install' . DIRECTORY_SEPARATOR . $path[1];
			}
			else
			{
				$filepath = JPATH_COMPONENT_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'updates' . DIRECTORY_SEPARATOR . $path[0];
			}

			try{
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
$fileContent = file_get_contents($filepath);    
}
elseif (version_compare(substr(JVERSION, 0, 3), '3.0', 'ge'))
{
$fileContent = File::read($filepath);
}
    
			 
				$version           = '';
				$updateDescription = '';
				$lastVersion       = '';
				$updateDate        = '';
				$updateTime        = '';
				$pos               = strpos($fileContent, '$version');

				if ($pos !== false)
				{
					$dDummy  = substr($fileContent, $pos);
					$pos2    = strpos($dDummy, '=');
					$dDummy  = substr($dDummy, $pos2);
					$pos3    = strpos($dDummy, "'");
					$dDummy  = substr($dDummy, $pos3 + 1);
					$pos4    = strpos($dDummy, "'");
					$version = trim(substr($dDummy, 0, $pos4));
				}

				$pos = strpos($fileContent, '$updateDescription');

				if ($pos !== false)
				{
					$dDummy            = substr($fileContent, $pos);
					$pos2              = strpos($dDummy, '=');
					$dDummy            = substr($dDummy, $pos2);
					$pos3              = strpos($dDummy, "'");
					$dDummy            = substr($dDummy, $pos3 + 1);
					$pos4              = strpos($dDummy, "'");
					$updateDescription = trim(substr($dDummy, 0, $pos4));
				}

				$pos = strpos($fileContent, '$lastVersion');

				if ($pos !== false)
				{
					$dDummy      = substr($fileContent, $pos);
					$pos2        = strpos($dDummy, '=');
					$dDummy      = substr($dDummy, $pos2);
					$pos3        = strpos($dDummy, "'");
					$dDummy      = substr($dDummy, $pos3 + 1);
					$pos4        = strpos($dDummy, "'");
					$lastVersion = trim(substr($dDummy, 0, $pos4));
				}

				$pos = strpos($fileContent, '$updateFileDate');

				if ($pos !== false)
				{
					$dDummy         = substr($fileContent, $pos);
					$pos2           = strpos($dDummy, '=');
					$dDummy         = substr($dDummy, $pos2);
					$pos3           = strpos($dDummy, "'");
					$dDummy         = substr($dDummy, $pos3 + 1);
					$pos4           = strpos($dDummy, "'");
					$updateFileDate = trim(substr($dDummy, 0, $pos4));
				}

				$pos = strpos($fileContent, '$updateFileTime');

				if ($pos !== false)
				{
					$dDummy         = substr($fileContent, $pos);
					$pos2           = strpos($dDummy, '=');
					$dDummy         = substr($dDummy, $pos2);
					$pos3           = strpos($dDummy, "'");
					$dDummy         = substr($dDummy, $pos3 + 1);
					$pos4           = strpos($dDummy, "'");
					$updateFileTime = trim(substr($dDummy, 0, $pos4));
				}

				$pos = strpos($fileContent, '$excludeFile');

				if ($pos !== false)
				{
					$dDummy      = substr($fileContent, $pos);
					$pos2        = strpos($dDummy, '=');
					$dDummy      = substr($dDummy, $pos2);
					$pos3        = strpos($dDummy, "'");
					$dDummy      = substr($dDummy, $pos3 + 1);
					$pos4        = strpos($dDummy, "'");
					$excludeFile = trim(substr($dDummy, 0, $pos4));

					if ($excludeFile == 'true')
					{
						continue;
					}
				}

				$updateFiles[$i]['id']                = $i;
				$updateFiles[$i]['file_name']         = $updateFile;
				$updateFiles[$i]['version']           = $version;
				$updateFiles[$i]['last_version']      = $lastVersion;
				$updateFiles[$i]['updateFileDate']    = trim($updateFileDate);
				$updateFiles[$i]['updateFileTime']    = $updateFileTime;
				$updateFiles[$i]['updateTime']        = '0000-00-00 00:00:00';
				$updateFiles[$i]['updateDescription'] = $updateDescription;
				$updateFiles[$i]['date']              = '';
				$updateFiles[$i]['count']             = 0;
				$query                                = "SELECT date,count FROM #__sportsmanagement_version where file=" . $this->_db->Quote($updateFile);
				Factory::getDbo()->setQuery($query);

				if (!$result = Factory::getDbo()->loadObject())
				{
					$this->setError($this->_db->getErrorMsg());
				}
				else
				{
					$updateFiles[$i]['date']  = $result->date;
					$updateFiles[$i]['count'] = $result->count;
				}

				$i++;
			}
            catch (Exception $e)
		{
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getCode()), Log::ERROR, 'jsmerror');
			Log::add(Text::_(__METHOD__ . ' ' . __LINE__ . ' ' . $e->getMessage()), Log::ERROR, 'jsmerror');
		}
            
		}

		$filter_order     = $app->getUserState($option . 'updates_filter_order', 'filter_order', 'dates', 'cmd');
		$filter_order_Dir = $app->getUserState($option . 'updates_filter_order_Dir', 'filter_order_Dir', '', 'word');
		$orderfn          = '_cmpDate';

		switch ($filter_order)
		{
			case 'name':
				$orderfn = '_cmpName';
				break;

			case 'version':
				$orderfn = '_cmpVersion';
				break;

			case 'date':
				$orderfn = '_cmpDate';
				break;
		}

		usort($updateFiles, array($this, $orderfn));

		if (strcasecmp($filter_order_Dir, 'ASC') == 0)
		{
			$updateFiles = array_reverse($updateFiles);
		}

		return $updateFiles;
	}
}
