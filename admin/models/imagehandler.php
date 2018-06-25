<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      imagehandler.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage imagehandler
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.model');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');


/**
 * sportsmanagementModelImagehandler
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementModelImagehandler extends JModelLegacy
{
	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		$option = JFactory::getApplication()->input->getCmd('option');
		$app	= JFactory::getApplication();

		$limit		= $app->getUserStateFromRequest( $option.'.imageselect'.'limit', 'limit', $app->getCfg('list_limit'), 'int');
		$limitstart = $app->getUserStateFromRequest( $option.'.imageselect'.'limitstart', 'limitstart', 0, 'int' );
		$search 	= $app->getUserStateFromRequest( $option.'.search', 'search', '', 'string' );
		$search 	= trim(JString::strtolower( $search ) );

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);
		$this->setState('search', $search);

	}

	/**
	 * sportsmanagementModelImagehandler::getState()
	 * 
	 * @param mixed $property
	 * @param mixed $default
	 * @return
	 */
	function getState($property = null, $default = NULL)
	{
		static $set;

		if (!$set) {
			$folder = JFactory::getApplication()->input->getVar( 'folder' );
			$this->setState('folder', $folder);

			$set = true;
		}
		return parent::getState($property);
	}

	/**
	 * Build imagelist
	 *
	 * @return array $list The imagefiles from a directory to display
	 * @since 0.9
	 */
	function getImages()
	{
		$list = $this->getList();

		$listimg = array();

		if($this->getState('limitstart')>$this->getState('total'))
			$this->setState('limitstart',0);

		$s = $this->getState('limitstart')+1;

		for ( $i = ($s - 1); $i < $s + $this->getState('limit'); $i++ ) {
			if ($i+1 <= $this->getState('total') ) {

					$list[$i]->size = $this->_parseSize(filesize($list[$i]->path));

					$info = @getimagesize($list[$i]->path);
					$list[$i]->width		= @$info[0];
					$list[$i]->height	= @$info[1];
					//$list[$i]->type		= @$info[2];
					//$list[$i]->mime		= @$info['mime'];

					if (($info[0] > 60) || ($info[1] > 60)) {
						$dimensions = $this->_imageResize($info[0], $info[1], 60);
						$list[$i]->width_60 = $dimensions[0];
						$list[$i]->height_60 = $dimensions[1];
					} else {
						$list[$i]->width_60 = $list[$i]->width;
						$list[$i]->height_60 = $list[$i]->height;
					}


    			$listimg[] = $list[$i];
			}
		}

		return $listimg;
	}

	/**
	 * Build imagelist
	 *
	 * @return array $list The imagefiles from a directory
	 * @since 0.9
	 */
	function getList()
	{
		$option = JFactory::getApplication()->input->getCmd('option');
		$app	= JFactory::getApplication();
		// JInput object 
         $jinput = $app->input; 

        static $list;

		// Only process the list once per request
		if (is_array($list)) {
			return $list;
		}

		// Get folder from request
		$folder = $jinput->getString( 'folder', '' );
		//$folder = $this->getState('folder');
		$search = $this->getState('search');

		// Initialize variables
		$basePath = JPATH_SITE.DS.'images'.DS.$option.DS.'database'.DS.$folder;

		$images 	= array ();
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' folder -> '.$folder.''),'Notice');

		// Get the list of files and folders from the given folder
		$fileList 	= JFolder::files($basePath);

		// Iterate over the files if they exist
		if ($fileList !== false) {
			foreach ($fileList as $file)
			{
				if (is_file($basePath.DS.$file) && substr($file, 0, 1) != '.'
					&& strtolower($file) !== 'index.html'
					&& strtolower($file) !== 'thumbs.db'
					&& strtolower($file) !== 'readme.txt'
					)

					{

					if ( $search == '') {
						$tmp = new JObject();
						$tmp->name = $file;
						$tmp->path = JPath::clean($basePath.DS.$file);

						$images[] = $tmp;

					} elseif(stristr( $file, $search)) {
						$tmp = new JObject();
						$tmp->name = $file;
						$tmp->path = JPath::clean($basePath.DS.$file);

						$images[] = $tmp;

					}
				}
			}
		}

		$list = $images;

		$this->setState('total', count($list));
		if($this->getState('limit')==0)
			$this->setState('limit', count($list));

		return $list;
	}

	/**
	 * Method to get a pagination object for the images
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getState('total'), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	/**
	 * Build display size
	 *
	 * @return array width and height
	 * @since 0.9
	 */
	function _imageResize($width, $height, $target)
	{
		//takes the larger size of the width and height and applies the
		//formula accordingly...this is so this script will work
		//dynamically with any size image
		if ($width > $height) {
			$percentage = ($target / $width);
		} else {
			$percentage = ($target / $height);
		}

		//gets the new value and applies the percentage, then rounds the value
		$width = round($width * $percentage);
		$height = round($height * $percentage);

		return array($width, $height);
	}

	/**
	 * Return human readable size info
	 *
	 * @return string size of image
	 * @since 0.9
	 */
	function _parseSize($size)
	{
		if ($size < 1024) {
			return $size . ' bytes';
		}
		else
		{
			if ($size >= 1024 && $size < 1024 * 1024) {
				return sprintf('%01.2f', $size / 1024.0) . ' Kb';
			} else {
				return sprintf('%01.2f', $size / (1024.0 * 1024)) . ' Mb';
			}
		}
	}
	
	
    
	
}
?>
