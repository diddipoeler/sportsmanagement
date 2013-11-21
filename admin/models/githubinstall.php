<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.model');
 

class sportsmanagementModelgithubinstall extends JModel
{

function CopyGithubLink($link)
{
    $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
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
    
}


    
}


?>    