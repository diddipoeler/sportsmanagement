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
use Joomla\CMS\Pagination\Pagination;

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
static public $filesOutput = array();
	
var $items = array();  
  
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
public function getFiles($path, $scopeName)
{
$directory = JPATH_ROOT . DIRECTORY_SEPARATOR . 'images/com_sportsmanagement/database/'.$path;
//$filesOutput = [];
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
  
  self::$filesOutput[] = $fileMeta;
  
  
  

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
 // $this->items = self::$filesOutput;

$value = $this->getUserStateFromRequest($this->context . '.limit', 'limit', Factory::getApplication()->getCfg('list_limit', 0));
echo __METHOD__.' '.__LINE__.' limit <pre>'.print_r($value,true).'</pre>';
$value = Factory::getApplication()->input->getUInt('limitstart', 0);
echo __METHOD__.' '.__LINE__.' limitstart <pre>'.print_r($value,true).'</pre>';
  
  $this->items = self::$filesOutput;
  $this->getTotal();
  
  //echo __METHOD__.' '.__LINE__.' getTotal <pre>'.print_r($this->getTotal(),true).'</pre>';
  
	return self::$filesOutput;
}
/*
  public function getItems()
	{
    // Get a storage key.
		//$store = $this->getStoreId();
    $store = $this->getStoreId('getstart');
    echo __METHOD__.' '.__LINE__.' store <pre>'.print_r($store,true).'</pre>';
    echo __METHOD__.' '.__LINE__.' getstart <pre>'.print_r($this->getStart(),true).'</pre>';
    echo __METHOD__.' '.__LINE__.' getstate <pre>'.print_r($this->getState('list.limit'),true).'</pre>';
    echo __METHOD__.' '.__LINE__.' getTotal <pre>'.print_r($this->getTotal(),true).'</pre>';
    
    //$this->cache[$store] = $this->_getList($this->_getListQuery(), $this->getStart(), $this->getState('list.limit'));
    $this->cache[$store] = self::$filesOutput;
    
    echo __METHOD__.' '.__LINE__.' cache <pre>'.print_r($this->cache[$store],true).'</pre>';
    return $this->cache[$store];
  }
  */
  public function getPagination()
	{
		// Get a storage key.
		$store = $this->getStoreId('getPagination');

    $limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
    // Create the pagination object and add the object to the internal cache.
		$this->cache[$store] = new Pagination($this->getTotal(), $this->getStart(), $limit);
//echo __METHOD__.' '.__LINE__.' cache <pre>'.print_r($this->cache[$store],true).'</pre>';
		return $this->cache[$store];
    
    
    /*
		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');

		// Create the pagination object and add the object to the internal cache.
		$this->cache[$store] = new Pagination($this->getTotal(), $this->getStart(), $limit);

		return $this->cache[$store];
    */
	}
  
  public function getTotal()
	{
		// Get a storage key.
		$store = $this->getStoreId('getTotal');
//echo __METHOD__.' '.__LINE__.' items <pre>'.print_r($this->items,true).'</pre>';
    
    $this->cache[$store] = sizeof($this->items);
//echo __METHOD__.' '.__LINE__.' cache <pre>'.print_r($this->cache[$store],true).'</pre>';    
    return $this->cache[$store];
    /*
		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		try
		{
			// Load the total and add the total to the internal cache.
			$this->cache[$store] = sizeof($this->items);
		}
		catch (\RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
echo __METHOD__.' '.__LINE__.' cache <pre>'.print_r($this->cache[$store],true).'</pre>';
		return $this->cache[$store];
    */
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

//  echo __METHOD__.' '.__LINE__.' store <pre>'.print_r($store,true).'</pre>';
//  echo __METHOD__.' '.__LINE__.' store <pre>'.print_r($this->cache[$store],true).'</pre>';
  /*
		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}
*/
		$start = $this->getState('list.start');
		$limit = $this->getState('list.limit');
		$total = $this->getTotal();
  //$total = sizeof(self::$filesOutput);
  
//  echo __METHOD__.' '.__LINE__.' files <pre>'.print_r(self::$filesOutput,true).'</pre>';
//echo __METHOD__.' '.__LINE__.' getTotal <pre>'.print_r($total,true).'</pre>';
  
		if ($start > $total - $limit)
		{
			$start = max(0, (int) (ceil($total / $limit) - 1) * $limit);
		}

		// Add the total to the internal cache.
		$this->cache[$store] = $start;

		return $this->cache[$store];
	}

protected function populateState($ordering = null, $direction = null)
	{
		// Reference global application object
		$app = Factory::getApplication();

		// JInput object
		$jinput = $app->input;
		$option = $jinput->getCmd('option');

		// Initialise variables.
		$app = Factory::getApplication('site');

		// List state information

		$value = $this->getUserStateFromRequest($this->context . '.limit', 'limit', $app->getCfg('list_limit', 0));
		$this->setState('list.limit', $value);

		$value = $jinput->getUInt('limitstart', 0);
		$this->setState('list.start', $value);
/*
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
*/
		$published = $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);
/*	
		$temp_user_request = $this->getUserStateFromRequest($this->context . '.filter.search_nation', 'filter_search_nation', '');
		$this->setState('filter.search_nation', $temp_user_request);
*/
		$filter_order = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', '', 'string');
/*
		if (!in_array($filter_order, $this->filter_fields))
		{
			$filter_order = 'v.name';
		}
*/
		$filter_order_Dir = $this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');

		if (!in_array(strtoupper($filter_order_Dir), array('ASC', 'DESC', '')))
		{
			$filter_order_Dir = 'ASC';
		}

		$this->setState('filter_order', $filter_order);
		$this->setState('filter_order_Dir', $filter_order_Dir);

	}
	
function getListQuery()
	{
	//return self::$filesOutput;
}
	
}
