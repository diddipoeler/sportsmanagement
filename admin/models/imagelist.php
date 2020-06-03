<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage imagelist
 * @file       imagelist.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * sportsmanagementModelimagelist
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelimagelist extends ListModel
{
var $_identifier = "imagelist";
var $limitstart = 0;
var $limit = 0;
	
public function __construct($config = array())
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput                   = $app->input;
		$this->limitstart         = $jinput->getVar('limitstart', 0, '', 'int');
		parent::__construct($config);

		// $getDBConnection = sportsmanagementHelper::getDBConnection();
//		parent::setDbo($this->jsmdb);
	}	
	
/**
 * sportsmanagementModelimagelist::getFiles()
 * 
 * @param mixed $path
 * @param mixed $scopeName
 * @return
 */
public static function getFiles($path, $scopeName)
{
$directory = JPATH_ROOT . DIRECTORY_SEPARATOR . 'images/com_sportsmanagement/database/'.$path;
$filesOutput = [];
$files = Folder::files($directory);
$directories = Folder::folders($directory);

//echo '<pre>'.print_r($files,true).'</pre>';
//echo '<pre>'.print_r($directories,true).'</pre>';

foreach ($files as $file)
{
$fileParse = explode('.', $file);
$exs = array_pop($fileParse);
				$fileDate = filemtime($directory . DIRECTORY_SEPARATOR . $file);

				$stat = stat($directory . DIRECTORY_SEPARATOR . $file);

				if (($stat !== false) && isset($stat[ 'mtime' ]))
				{
					$fileDate = $stat['mtime'];
				}

$fileMeta = new stdclass;
$fileMeta->size = filesize($directory . DIRECTORY_SEPARATOR . $file);
$fileMeta->is_writable = (int)is_writable($directory . DIRECTORY_SEPARATOR . $file);
$fileMeta->name = implode('.', $fileParse);
$fileMeta->exs = $exs;
$fileMeta->file = $file;
$fileMeta->fileP = '';
$fileMeta->path_relative = $path;
$fileMeta->width_60 = '60';
$fileMeta->height_60 = '60';
$fileMeta->dateC = $fileDate;
$fileMeta->dateM = $fileDate;
                    
/*
				$fileMeta = [
					'size' => filesize($directory . DIRECTORY_SEPARATOR . $file),
					'is_writable' => (int)is_writable($directory . DIRECTORY_SEPARATOR . $file),
					'name' => implode('.', $fileParse),
					'exs' => $exs,
					'file' => $file,
					'fileP' => '',
					'path_relative' => $path,
					'width_60' => '60',
					'height_60' => '60',
					'dateC' => $fileDate,
					'dateM' => $fileDate,
				];  
  */
  
  $filesOutput[] = $fileMeta;
  
  
  

}
//echo '<pre>'.print_r($filesOutput,true).'</pre>';    
  
$directoriesOutput = [];
			foreach ($directories as $value)
			{
				$directoriesOutput[] = [
					'name' => $value,
					'is_writable' => (int)is_writable($directory . DIRECTORY_SEPARATOR . $value),
					'is_empty' => (int)self::dirIisEmpty($directory . DIRECTORY_SEPARATOR . $value)
				];
			}  
//  echo '<pre>'.print_r($directoriesOutput,true).'</pre>'; 
  /*
  return json_encode([
				'files' => $filesOutput,
				'directories' => $directoriesOutput
			]);
	*/
	return $filesOutput;
}

public function getStart()
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;

		// $limitstart = $this->getUserStateFromRequest($this->context.'.limitstart', 'limitstart');
		$this->setState('list.start', $this->limitstart);

		$store = $this->getStoreId('getstart');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$start = $this->getState('list.start');
		$limit = $this->getState('list.limit');
		$total = $this->getTotal();

		if ($start > $total - $limit)
		{
			$start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
		}

		// Add the total to the internal cache.
		$this->cache[$store] = $start;

		return $this->cache[$store];
	}


}
