<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/


// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla modelform library
//jimport('joomla.application.component.model');

if( version_compare(JSM_JVERSION,'3','eq') ) 
{
jimport('joomla.filesystem.archive'); 	
}	
elseif( version_compare(JSM_JVERSION,'4','eq') ) 
{
//use Joomla\Archive\Archive;
jimport('vendor.joomla.archive.src.archive'); 
}	
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');


/**
 * sportsmanagementModelgithubinstall
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelgithubinstall extends JSMModelLegacy
{

    var $storeFailedColor = 'red';
	var $storeSuccessColor = 'green';
	var $existingInDbColor = 'orange';
    var $_success_text = '';
    
/**
 * sportsmanagementModelgithubinstall::CopyGithubLink()
 * 
 * @param mixed $link
 * @return
 */
function CopyGithubLink($link)
{
    //$app = JFactory::getApplication();
        //$option = JFactory::getApplication()->input->getCmd('option');
        
        $gitinstall = '';
        //$gitinstall = $app->getUserState( "$option.install");
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' install<br><pre>'.print_r($gitinstall,true).'</pre>'),'');

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

if( version_compare(JSM_JVERSION,'3','eq') ) 
{
$result = JArchive::extract($dest,$extractdir);
}
elseif( version_compare(JSM_JVERSION,'4','eq') ) 
{	
$archive = new Archive;
$result = $archive->extract($dest, $extractdir);
}	
	
// Get an installer instance
$installer = JInstaller::getInstance();
// Get the path to the package to install
$p_dir = JPATH_SITE.DS.'tmp'.DS.'sportsmanagement-master'.DS;
$p_dir_modules = JPATH_SITE.DS.'tmp'.DS.'sportsmanagement-master'.DS.'modules'.DS;
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

$install_modules = JFolder::folders($p_dir_modules , $filter = '.');
//$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' install<br><pre>'.print_r($install_modules,true).'</pre>'),'');

$my_text = '';
foreach( $install_modules as $key => $value)
{
 $my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
			$my_text .= JText::sprintf('Das Modul [ %1$s ] wurde installiert!',"</span><strong>".strtoupper($value)."</strong>");
			$my_text .= '<br />';

}
$this->_success_text['Module:'] = $my_text;


//echo "<script> alert('".$msg."');window.parent.SqueezeBox.close();   </script>\n";
//echo "<script> alert('".$msg."');   </script>\n";




}


return $this->_success_text;	
}


    
}


?>    
