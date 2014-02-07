<?php


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');


class sportsmanagementModelsmimageimports extends JModel
{

function getimagesxml()
{
    $mainframe = JFactory::getApplication(); 
        $option = JRequest::getCmd('option');  
        // sind neue bilder pakete vorhanden ?
		$content = file_get_contents('https://raw2.github.com/diddipoeler/sportsmanagement/master/admin/helpers/xml_files/pictures.xml');
		//Parsen
		$doc = DOMDocument::loadXML($content);
        $doc->save(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'xml_files'.DS.'pictures.xml');
    
}
function getXMLFiles()
    {
        $mainframe = JFactory::getApplication();
        $option = JRequest::getCmd('option');
        $files = array();
        $path = JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'xml_files'.DS.'pictures.xml';
        $xml = JFactory::getXMLParser( 'Simple' );
       $xml->loadFile($path); 
       
       $i = 0;
       // schleife altersgruppen anfang
       foreach( $xml->document->pictures as $picture ) 
{
   $name = $picture->getElementByPath('picture');
   $attributes = $name->attributes();
   $picturedescription = $name->data();
   $folder = $attributes['folder'];
   $directory = $attributes['directory'];
   $file = $attributes['file'];
   
   $temp = new stdClass();
   $temp->id = $i;
   $temp->picture = $picturedescription;
   $temp->folder = $folder;
   $temp->directory = $directory;
   $temp->file = $file;
   $export[] = $temp;
   $files = array_merge($export);
   $i++;
   }
       
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($files,true).'</pre>'),'');   
        
        return $files;
        
    }	
    
}

?>