<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
// import Joomla modelform library
jimport('joomla.application.component.model');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive'); 

/**
 * sportsmanagementModelsmimageimport
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelsmimageimport extends JModel
{


/**
 * sportsmanagementModelsmimageimport::import()
 * 
 * @return
 */
function import()
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $post = JRequest::get('post');
        
        $server = 'http://sportsmanagement.fussballineuropa.de/jdownloads/';
        
        $cid = $post['cid'];
        
//        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($post,true).'</pre>'),'');
//        $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($cid,true).'</pre>'),'');
        
        foreach ( $cid as $key => $value )
        {
            $name = $post['picture'][$value];
            $folder = $post['folder'][$value];
            $directory = $post['directory'][$value];
            $file = $post['file'][$value];
            
            $servercopy = $server.$folder.'/'.$file;
            
//            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($name,true).'</pre>'),'Notice');
//            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($folder,true).'</pre>'),'Notice');
//            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($directory,true).'</pre>'),'Notice');
//            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($file,true).'</pre>'),'Notice');
//            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($servercopy,true).'</pre>'),'Notice');
            
            //set the target directory
		    $base_Dir = JPATH_SITE . DS . 'tmp'. DS;
            //$file['name'] = basename($servercopy);
            //$filename = $file['name'];
            //$filepath = $base_Dir . $filename;
            
            //$file['name'] = $file;
            $filename = $file;
            $filepath = $base_Dir . $filename;
            
//            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'file<br><pre>'.print_r($file,true).'</pre>'),'Notice');
//            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'filename<br><pre>'.print_r($filename,true).'</pre>'),'Notice');
//            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'filepath<br><pre>'.print_r($filepath,true).'</pre>'),'Notice');
            
            if ( !copy($servercopy,$filepath) )
            {
                $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'file<br><pre>'.print_r($servercopy,true).'</pre>'),'Error');
            }
            else
            {
                
						  
                          
                          
                $extractdir = JPATH_SITE.DS.'images'.DS.'com_sportsmanagement'.DS.'database'.DS.$directory;
                $dest = JPATH_SITE.DS.'tmp'.DS.$filename;
                
                //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'dest<br>'.$dest.''),'Notice');
                
                if (strtolower(JFile::getExt($dest))=='zip')
						{
                $result = JArchive::extract($dest,$extractdir);
                if ($result === false)
							{
								JError::raiseError(500,JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_UNZIP_ERROR'));
								return false;
							}
                            else
                            {
                                JError::raiseNotice(0,JText::sprintf('Bilderdatei : %1$s endpackt.',$name) ) ;
                            }
                }
                else
                {
                    JError::raiseError(500,JText::_('COM_SPORTSMANAGEMENT_ADMIN_IMAGE_NO_ZIP_ERROR'));
								return false;
                    
                }
                
            }    
            
            
            
            
        }

}

}

?>