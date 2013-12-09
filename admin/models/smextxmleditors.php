<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');



jimport('joomla.application.component.model');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');



class sportsmanagementModelsmextxmleditors extends JModel
{

function getXMLFiles()
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $path = JPATH_ADMINISTRATOR.DS.'components'.DS.$option.DS.'assets'.DS.'extended';
        // Get a list of files in the search path with the given filter.
       //$files = JFolder::files($path, $filter);
       $files = JFolder::files($path, '.xml$|.php$');
        
        return $files;
        
    }	

}
?>