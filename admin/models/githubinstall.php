<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      githubinstall.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage models
 */


/* No direct access to this file */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\Archive\Archive;

if( version_compare(JSM_JVERSION,'3','eq') ) 
{
jimport('joomla.filesystem.archive');	
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
    var $_success_text = array();
    
/**
 * sportsmanagementModelgithubinstall::CopyGithubLink()
 * 
 * @param mixed $link
 * @return
 */
function CopyGithubLink($link)
{
    
    $gitinstall = '';

if ( $gitinstall )
{
    
}   
else
{     
        /* set the target directory */
		$base_Dir = JPATH_SITE . DS . 'tmp'. DS;
        $file['name'] = basename($link);
        $filename = $file['name'];
        $filepath = $base_Dir . $filename;
if ( !isset($this->_success_text['Komponente:']) )
{
$this->_success_text['Komponente:'] = 'text';	
}
	
$my_text = '';
if ( !copy($link,$filepath) )
{

    $my_text = '<span style="color:'.$this->storeFailedColor.'">';
    $my_text .= Text::sprintf('Die ZIP-Datei der Komponente [ %1$s ] konnte nicht kopiert werden!',"</span><strong>".$link."</strong>");
    $my_text .= '<br />';
}
else
{

$my_text = '<span style="color:'.$this->storeSuccessColor.'">';
$my_text .= Text::sprintf('Die ZIP-Datei der Komponente [ %1$s ] konnte kopiert werden!',"</span><strong>".$link."</strong>");
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
	
/* Get an installer instance */

$installer = JInstaller::getInstance();

/* Get the path to the package to install */

$p_dir = JPATH_SITE.DS.'tmp'.DS.'sportsmanagement-master'.DS;
$p_dir_modules = JPATH_SITE.DS.'tmp'.DS.'sportsmanagement-master'.DS.'modules'.DS;

/* Detect the package type */
$type = JInstallerHelper::detectType($p_dir);   


$package['packagefile'] = null;
$package['extractdir'] = null;
$package['dir'] = $p_dir;
$package['type'] = $type;


/* Install the package */

if (!$installer->install($package['dir']))
    {
			
        $my_text .= '<span style="color:'.$this->storeFailedColor.'">';
		$my_text .= Text::sprintf('Die Komponente [ %1$s ] konnte nicht installiert werden!', "</span><strong>".strtoupper($package['type'])."</strong>");
		$my_text .= '<br />';
			
	}
else
    {
		$my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
		$my_text .= Text::sprintf('Die Komponente [ %1$s ] wurde installiert!', "</span><strong>".strtoupper($package['type'])."</strong>");
		$my_text .= '<br />';
            
	}

$this->_success_text['Komponente:'] = $my_text;

$install_modules = JFolder::folders($p_dir_modules , $filter = '.');

$my_text = '';

foreach( $install_modules as $key => $value)
{
    $my_text .= '<span style="color:'.$this->storeSuccessColor.'">';
    $my_text .= Text::sprintf('Das Modul [ %1$s ] wurde installiert!', "</span><strong>".strtoupper($value)."</strong>");
    $my_text .= '<br />';
    
}
$this->_success_text['Module:'] = $my_text;

}

return $this->_success_text;	
}


    
}


?>    
