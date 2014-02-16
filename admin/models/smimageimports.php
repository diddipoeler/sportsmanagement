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
		//$content = file_get_contents('https://raw2.github.com/diddipoeler/sportsmanagement/master/admin/helpers/xml_files/pictures.xml');
		
		$datei = "https://raw2.github.com/diddipoeler/sportsmanagement/master/admin/helpers/xml_files/pictures.xml";
if (function_exists('curl_version'))
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $datei);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    curl_close($curl);
}
else if (file_get_contents(__FILE__) && ini_get('allow_url_fopen'))
{
    $content = file_get_contents($datei);
}
else
{
    //echo 'Sie haben weder cURL installiert, noch allow_url_fopen aktiviert. Bitte aktivieren/installieren allow_url_fopen oder Curl!';
    $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'),'Error');
}
		
		
		
		//Parsen
		$doc = DOMDocument::loadXML($content);
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($doc,true).'</pre>'),'');
        
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