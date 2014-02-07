<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
// import Joomla modelform library
jimport('joomla.application.component.model');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.archive'); 

class sportsmanagementModelsmimageimport extends JModel
{


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
            
            $file['name'] = $file;
            $filename = $file;
            $filepath = $base_Dir . $filename;
            
//            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'file<br><pre>'.print_r($file,true).'</pre>'),'Notice');
//            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'filename<br><pre>'.print_r($filename,true).'</pre>'),'Notice');
//            $mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'filepath<br><pre>'.print_r($filepath,true).'</pre>'),'Notice');
            
            if ( !copy($servercopy,$filepath) )
            {
            }
            else
            {
                $extractdir = JPATH_SITE.DS.'images'.DS.'com_sportsmanagement'.DS.'database'.DS.$directory;
                $dest = JPATH_SITE.DS.'tmp'.DS.$file['name'];
                $result = JArchive::extract($dest,$extractdir);
            }    
            
            
            
            
        }

}

}

?>