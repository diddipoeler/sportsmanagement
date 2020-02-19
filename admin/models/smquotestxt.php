<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      smquotestxt.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage models
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;


jimport('joomla.application.component.model');
use Joomla\CMS\Filesystem\Folder;
jimport('joomla.filesystem.file');



/**
 * sportsmanagementModelsmquotestxt
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelsmquotestxt extends JModel
{

function getTXTFiles()
    {
        $app = Factory::getApplication();
        $option = Factory::getApplication()->input->getCmd('option');
        $path = JPATH_SITE.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'mod_sportsmanagement_rquotes'.DIRECTORY_SEPARATOR.'mod_sportsmanagement_rquotes';
        // Get a list of files in the search path with the given filter.
       $files = Folder::files($path, '.txt$|.php$');
        
        return $files;
        
    }	

}
?>