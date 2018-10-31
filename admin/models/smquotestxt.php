<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;


jimport('joomla.application.component.model');
jimport('joomla.filesystem.folder');
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
        $path = JPATH_SITE.DS.'modules'.DS.'mod_sportsmanagement_rquotes'.DS.'mod_sportsmanagement_rquotes';
        // Get a list of files in the search path with the given filter.
       //$files = JFolder::files($path, $filter);
       $files = JFolder::files($path, '.txt$|.php$');
        
        return $files;
        
    }	

}
?>