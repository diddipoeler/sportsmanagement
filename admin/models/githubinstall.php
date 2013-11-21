<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.model');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive'); 

class sportsmanagementModelgithubinstall extends JModel
{

function CopyGithubLink($link)
{
    $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $app = JFactory::getApplication();
        //set the target directory
		$base_Dir = JPATH_SITE . DS . 'tmp'. DS;
        $file['name'] = basename($link);
        $filename = $file['name'];
        $filepath = $base_Dir . $filename;


if ( !copy($link,$filepath) )
{
echo "<script> alert('".JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_COPY_FAILED' )."'); </script>\n";

}
else
{
//echo "<script> alert('" . JText::_( 'COPY COMPLETE'.'-'.$folder.'-'.$type.'-'.$filename.'-'.$field ) . "'); window.history.go(-1); window.parent.selectImage_".$type."('$filename', '$filename','$field'); </script>\n";
echo "<script> alert('".JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_COPY_SUCCESS' )."');   </script>\n";

}

$extractdir = JPATH_SITE.DS.'tmp';
$dest = JPATH_SITE.DS.'tmp'.DS.$file['name'];
$result = JArchive::extract($dest,$extractdir);

// Get an installer instance
$installer = JInstaller::getInstance();
// Get the path to the package to install
$p_dir = JPATH_SITE.DS.'tmp'.DS.'sportsmanagement-master'.DS;
// Detect the package type
$type = JInstallerHelper::detectType($p_dir);        


$package['packagefile'] = null;
$package['extractdir'] = null;
$package['dir'] = $p_dir;
$package['type'] = $type;

echo 'package<br><pre>'.print_r($package,true).'</pre>';



            
}


    
}


?>    