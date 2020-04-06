<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       smextxmleditors.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Filesystem\Folder;
jimport('joomla.filesystem.file');



/**
 * sportsmanagementModelsmextxmleditors
 *
 * @package
 * @author    diddi
 * @copyright 2014
 * @version   $Id$
 * @access    public
 */
class sportsmanagementModelsmextxmleditors extends BaseDatabaseModel
{

    /**
 * sportsmanagementModelsmextxmleditors::getXMLFiles()
 *
 * @return
 */
    function getXMLFiles()
    {
        // Reference global application object
        $app = Factory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        $path = JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.$option.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'extended';
        // Get a list of files in the search path with the given filter.
           $files = Folder::files($path, '.xml$|.php$');
     
        return $files;
      
    }  

}
?>
