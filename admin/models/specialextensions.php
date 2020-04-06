<?php
/**
* 
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       specialextensions.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\Folder;

/**
 * sportsmanagementModelspecialextensions
 * 
 * @package 
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
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
        
        if(Folder::exists(JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_sportsmanagement'.DIRECTORY_SEPARATOR.'extensions')) {
               $folderExtensions  = Folder::folders(
                   JPATH_SITE.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_sportsmanagement'.DIRECTORY_SEPARATOR.'extensions',
                   '.', false, false, $excludeExtension
               );
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
