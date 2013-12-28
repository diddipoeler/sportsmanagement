<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                jlextsisimport.php
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
defined( '_JEXEC' ) or die( 'Restricted access' );
$option = JRequest::getCmd('option');

$maxImportTime=JComponentHelper::getParams($option)->get('max_import_time',0);
if (empty($maxImportTime))
{
	$maxImportTime=480;
}
if ((int)ini_get('max_execution_time') < $maxImportTime){@set_time_limit($maxImportTime);}

$maxImportMemory=JComponentHelper::getParams($option)->get('max_import_memory',0);
if (empty($maxImportMemory))
{
	$maxImportMemory='150M';
}
if ((int)ini_get('memory_limit') < (int)$maxImportMemory){@ini_set('memory_limit',$maxImportMemory);}


jimport( 'joomla.application.component.model' );
jimport('joomla.html.pane');

require_once( JPATH_ADMINISTRATOR . DS. 'components'.DS.$option. DS. 'helpers' . DS . 'csvhelper.php' );
require_once( JPATH_ADMINISTRATOR . DS. 'components'.DS.$option. DS. 'helpers' . DS . 'ical.php' );
require_once(JPATH_ROOT.DS.'components'.DS.$option.DS. 'helpers' . DS . 'countries.php');


// import JArrayHelper
jimport( 'joomla.utilities.array' );
jimport( 'joomla.utilities.arrayhelper' ) ;

// import JFile
jimport('joomla.filesystem.file');
jimport( 'joomla.utilities.utility' );



class sportsmanagementModeljlextsisimport extends JModel
{

var $_datas=array();
var $_league_id=0;
var $_season_id=0;
var $_sportstype_id=0;
var $import_version='';
var $debug_info = false;
var $_project_id = 0;
var $_sis_art = 1;

function getData()
	{
  //global $mainframe, $option;
  $option = JRequest::getCmd('option');
  $mainframe = JFactory::getApplication();
  $document	= JFactory::getDocument();

$params = JComponentHelper::getParams( $option );
        $sis_xmllink	= $params->get( 'sis_xmllink' );
        $sis_nummer	= $params->get( 'sis_meinevereinsnummer' );
        $sis_passwort	= $params->get( 'sis_meinvereinspasswort' );
		$mainframe->enqueueMessage(JText::_('sis_xmllink<br><pre>'.print_r($sis_xmllink,true).'</pre>'   ),'');
        $mainframe->enqueueMessage(JText::_('sis_meinevereinsnummer<br><pre>'.print_r($sis_nummer,true).'</pre>'   ),'');
        $mainframe->enqueueMessage(JText::_('sis_meinvereinspasswort<br><pre>'.print_r($sis_passwort,true).'</pre>'   ),'');
        $liganummer = '001514505501506501000000000000000003000';
        
        $linkresults = self::getLink($sis_nummer,$sis_passwort,$liganummer,$this->_sis_art,$sis_xmllink);
        $mainframe->enqueueMessage(JText::_('linkresults<br><pre>'.print_r($linkresults,true).'</pre>'   ),'');
        
        
        $linkspielplan = self::getSpielplan($linkresults,$liganummer,$this->_sis_art);
        $mainframe->enqueueMessage(JText::_('linkspielplan<br><pre>'.print_r($linkspielplan,true).'</pre>'   ),'');



}  

	//get sis link
	function getLink($vereinsnummer,$vereinspasswort,$liganummer,$sis_art,$sis_xmllink) 
    {
		$sislink = $sis_xmllink.'/xmlexport/xml_dyn.aspx?user=%s&pass=%s&art=%s&auf=%s';
		$link = sprintf($sislink, $vereinsnummer, $vereinspasswort, $sis_art, $liganummer );	
		return $link;
	}
    
    
    //get sis spielplan
	function getSpielplan($linkresults,$liganummer,$sis_art) 
    {
        $option = JRequest::getCmd('option');
  $mainframe = JFactory::getApplication();
		// XML File
		$filepath='components/'.$option.'/sisdata/';
        
        $mainframe->enqueueMessage(JText::_('filepath<br><pre>'.print_r($filepath,true).'</pre>'   ),'');
        
		//File laden
		$datei = ($filepath.'sp_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
		if (file_exists($datei)) 
        {
			$LetzteAenderung = filemtime($datei);
			if ( (time() - $LetzteAenderung) > 1800) {
				if(file_get_contents($linkresults)) {
			 		//Laden
					$content = file_get_contents($linkresults);
					//Parsen
					$doc = DOMDocument::loadXML($content);
					//Altes File löschen
					unlink($datei);
					//Speichern
					$doc->save($filepath.'sp_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
				}
			}
		} 
        else 
        {
			//Laden
			$content = file_get_contents($linkresults);
            //$mainframe->enqueueMessage(JText::_('content<br><pre>'.print_r($content,true).'</pre>'   ),'');
            
			//Parsen
			$doc = DOMDocument::loadXML($content);
            //$mainframe->enqueueMessage(JText::_('doc<br><pre>'.print_r($doc,true).'</pre>'   ),'');
			//Speichern
			$doc->save($filepath.'sp_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
		}
		$result = simplexml_load_file($datei);
		// XML File end
		foreach ($result->Spiel as $temp) 
        {
			$nummer = substr( $temp->Liga , -3);
			$datum = substr( $temp->SpielVon , 0, 10);
			$datum_en = date("d.m.Y", strToTime($datum));
			$temp->Datum = $datum_en;
			$temp->vonUhrzeit = substr( $temp->SpielVon , 11, 8);
			$temp->bisUhrzeit = substr( $temp->SpielBis , 11, 8);
		}
		return $result;
	}
    



}

?>