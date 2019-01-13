<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      specialextensions.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\Folder;

/**
 * sportsmanagementModelspecialextensions
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementModelspecialextensions extends BaseDatabaseModel
{
    
    /**
     * sportsmanagementModelspecialextensions::getSpecialExtensions()
     * 
     * @return
     */
    function getSpecialExtensions()
    {
        	$option='com_sportsmanagement';
		$arrExtensions = array();
		$excludeExtension = array();
		
		if(Folder::exists(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'extensions')) {
			$folderExtensions  = Folder::folders(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'extensions',
													'.', false, false, $excludeExtension);
			if($folderExtensions !== false) {
				foreach ($folderExtensions as $ext)
				{
					$arrExtensions[] = $ext;
				}
			}
		}

		return $arrExtensions;
    }
    
}

?>