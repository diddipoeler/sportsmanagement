<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
jimport('joomla.application.component.model');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive'); 

/**
 * sportsmanagementModelgithubinstall
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelgithubinstall extends JModelLegacy
{

    var $storeFailedColor = 'red';
	var $storeSuccessColor = 'green';
	var $existingInDbColor = 'orange';
    var $_success_text = '';
    
function CopyGithubLink($link)
{
    $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $app = JFactory::getApplication();
        
        $gitinstall = '';
        //$gitinstall = $mainframe->getUserState( "$option.install");
        
        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' install<br><pre>'.print_r($gitinstall,true).'</pre>'),'');

if ( $gitinstall )
{
    
}   
else
{     
        //set the target directory
		$base_Dir = JPATH_SITE . DS . 'tmp'. DS;
        $file['name'] = basename($link);
        $filename = $file['name'];
        $filepath = $base_Dir . $filename;

$my_text = '';
if ( !copy($link,$filepath) )
{
//echo "<script> alert('".JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_COPY_FAILED' )."'); </script>\n";
$my_text = '<span style="color:'.$this->storeFailedColor.'">';
$my_text .= JText::sprintf('Die ZIP-Datei der Komponente [ %1$s ] konnte nicht kopiert werden!',"</span><strong>".$link."</strong>");
$my_text .= '<br />';
}
else
{
//echo "<script> alert('" . JText::_( 'COPY COMPLETE'.'-'.$folder.'-'.$type.'-'.$filename.'-'.$field ) . "'); window.history.go(-1); window.parent.selectImage_".$type."('$filename', '$filename','$field'); </script>\n";
//echo "<script> alert('".JText::_( 'COM_SPORTSMANAGEMENT_ADMIN_IMAGEHANDLER_COPY_SUCCESS' )."');   </script>\n";
$my_text = '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= JText::sprintf('Die ZIP-Datei der Komponente [ %1$s ] konnte kopiert werden!',"</span><strong>".$link."</strong>");
$my_text .= '<br />';

}
$this->_success_text['Komponente:'] = $my_text;


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

//echo 'package<br><pre>'.print_r($package,true).'</pre>';

// Install the package
//$my_text = '';

		if (!$installer->install($package['dir'])) 
        {
			// There was an error installing the package
			//$msg = JText::sprintf('COM_INSTALLER_INSTALL_ERROR', JText::_('COM_INSTALLER_TYPE_TYPE_'.strtoupper($package['type'])));
            $my_text .= '<span style="color:'.$this->storeFailedColor.'">';
			$my_text .= JText::sprintf('Die Komponente [ %1$s ] konnte nicht installiert werden!',"</span><strong>".strtoupper($package['type'])."</strong>");
			$my_text .= '<br />';
			//$result = false;
		} else {
			// Package installed sucessfully
			//$msg = JText::sprintf('COM_INSTALLER_INSTALL_SUCCESS', JText::_('COM_INSTALLER_TYPE_TYPE_'.strtoupper($package['type'])));
            $my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
			$my_text .= JText::sprintf('Die Komponente [ %1$s ] wurde installiert!',"</span><strong>".strtoupper($package['type'])."</strong>");
			$my_text .= '<br />';
                        
			//$result = true;
		}

$this->_success_text['Komponente:'] = $my_text;

//echo "<script> alert('".$msg."');window.parent.SqueezeBox.close();   </script>\n";
//echo "<script> alert('".$msg."');   </script>\n";




}

foreach ($this->_success_text as $key => $value)
		{
			?>
			<fieldset>
				<legend><?php echo JText::_($key); ?></legend>
				<table class='adminlist'><tr><td><?php echo $value; ?></td></tr></table>
			</fieldset>
			<?php
		}            
}


    
}


?>    