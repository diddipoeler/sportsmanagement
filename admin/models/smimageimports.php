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


/**
 * sportsmanagementModelsmimageimports
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
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
    //Define header array for cURL requestes
    $header = array('Contect-Type:application/xml');
    curl_setopt($curl, CURLOPT_URL, $datei);
    curl_setopt($curl, CURLOPT_VERBOSE, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    //curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER , $header);
    
    
    if (curl_errno($curl)) 
    {
        // moving to display page to display curl errors
          //echo curl_errno($curl) ;
          //echo curl_error($curl);
          //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__. '<br><pre>'.print_r(curl_errno($curl),true).'</pre>'),'Error');
          //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__. '<br><pre>'.print_r(curl_error($curl),true).'</pre>'),'Error');
          
          
    }
    else
    {
        $content = curl_exec($curl);
        //print_r($content);
        curl_close($curl);
    } 
    
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__. '<br><pre>'.print_r($content,true).'</pre>'),'');
}
else if (file_get_contents(__FILE__) && ini_get('allow_url_fopen'))
{
    $content = file_get_contents($datei);
    //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' '.__LINE__. '<br><pre>'.print_r($content,true).'</pre>'),'');
}
else
{
    //echo 'Sie haben weder cURL installiert, noch allow_url_fopen aktiviert. Bitte aktivieren/installieren allow_url_fopen oder Curl!';
    $mainframe->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'),'Error');
}
		
		
		if ( $content )
        {
		//Parsen
		$doc = DOMDocument::loadXML($content);
        
        //$mainframe->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.'<br><pre>'.print_r($doc,true).'</pre>'),'');
        
        $doc->save(JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'xml_files'.DS.'pictures.xml');
        }
    
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