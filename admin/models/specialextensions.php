<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


/**
 * sportsmanagementModelspecialextensions
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementModelspecialextensions extends JModel
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
		
		if(JFolder::exists(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'extensions')) {
			$folderExtensions  = JFolder::folders(JPATH_SITE.DS.'components'.DS.'com_sportsmanagement'.DS.'extensions',
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