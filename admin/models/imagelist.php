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

/**
 * sportsmanagementModelimagelist
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelimagelist extends BaseDatabaseModel
{

public static function getFiles($path, $scopeName)
{
      $directory = JPATH_ROOT . DIRECTORY_SEPARATOR . $path;
$filesOutput = [];
$files = Folder::files($directory);
$directories = Folder::folders($directory);

echo '<pre>'.print_r($files,true).'</pre>';
echo '<pre>'.print_r($directories,true).'</pre>';

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

				$fileMeta = [
					'size' => filesize($directory . DIRECTORY_SEPARATOR . $file),
					'is_writable' => (int)is_writable($directory . DIRECTORY_SEPARATOR . $file),
					'name' => implode('.', $fileParse),
					'exs' => $exs,
					'file' => $file,
					'fileP' => '',
					'dateC' => $fileDate,
					'dateM' => $fileDate,
				];  
  
  
  $filesOutput[] = $fileMeta;
  
  
  

}
echo '<pre>'.print_r($filesOutput,true).'</pre>';    
  
$directoriesOutput = [];
			foreach ($directories as $value)
			{
				$directoriesOutput[] = [
					'name' => $value,
					'is_writable' => (int)is_writable($directory . DIRECTORY_SEPARATOR . $value),
					'is_empty' => (int)self::dirIisEmpty($directory . DIRECTORY_SEPARATOR . $value)
				];
			}  
  echo '<pre>'.print_r($directoriesOutput,true).'</pre>'; 
  
  
}




}
