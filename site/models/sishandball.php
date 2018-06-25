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


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');
//jimport('joomla.application.component.modelitem');


/**
 * sportsmanagementModelsishandball
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementModelsishandball extends JModelLegacy 
{


	
	/**
	 * sportsmanagementModelsishandball::getLink()
	 * 
	 * @param mixed $vereinsnummer
	 * @param mixed $vereinspasswort
	 * @param mixed $liganummer
	 * @param mixed $sis_art
	 * @param mixed $sis_xmllink
	 * @return
	 */
	function getLink($vereinsnummer,$vereinspasswort,$liganummer,$sis_art,$sis_xmllink) 
    {
        $option = JFactory::getApplication()->input->getCmd('option');
        $app = JFactory::getApplication();
		$sislink = $sis_xmllink.'/xmlexport/xml_dyn.aspx?user=%s&pass=%s&art=%s&auf=%s';
		$link = sprintf($sislink, $vereinsnummer, $vereinspasswort, $sis_art, $liganummer );	
		return $link;
	}

	
	/**
	 * sportsmanagementModelsishandball::getTabelle()
	 * 
	 * @param mixed $linkresults
	 * @param mixed $liganummer
	 * @param mixed $sis_art
	 * @return
	 */
	function getTabelle($linkresults,$liganummer,$sis_art) 
    {
        $option = JFactory::getApplication()->input->getCmd('option');
        $app = JFactory::getApplication();
		// XML File
		$filepath='components/'.$option.'/data/';
		//File laden
		$datei = ($filepath.'tab_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
		if (file_exists($datei)) {
			$LetzteAenderung = filemtime($datei);
			if ( (time() - $LetzteAenderung) > 1800) 
      {
				//if(file_get_contents($linkresults)) 
        //{
					//Laden
					//$content = file_get_contents($linkresults);
					if (function_exists('curl_version'))
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $linkresults);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    curl_close($curl);
}
else if (file_get_contents(__FILE__) && ini_get('allow_url_fopen'))
{
    $content = file_get_contents($linkresults);
}
else
{
    //echo 'Sie haben weder cURL installiert, noch allow_url_fopen aktiviert. Bitte aktivieren/installieren allow_url_fopen oder Curl!';
    $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'),'Error');
}

					//Parsen
					$doc = DOMDocument::loadXML($content);
					//Altes File löschen
					unlink($datei);
					//Speichern
					$doc->save($filepath.'tab_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
				//}
			}
		} else {
			//Laden
			//$content = file_get_contents($linkresults);
			if (function_exists('curl_version'))
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $linkresults);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    curl_close($curl);
}
else if (file_get_contents(__FILE__) && ini_get('allow_url_fopen'))
{
    $content = file_get_contents($linkresults);
}
else
{
    //echo 'Sie haben weder cURL installiert, noch allow_url_fopen aktiviert. Bitte aktivieren/installieren allow_url_fopen oder Curl!';
    $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'),'Error');
}
			//Parsen
			$doc = DOMDocument::loadXML($content);
			//Speichern
			$doc->save($filepath.'tab_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
		}
		$result = simplexml_load_file($datei);
		// XML File end
		return $result;
	}
	
	
	/**
	 * sportsmanagementModelsishandball::getStatistik()
	 * 
	 * @param mixed $linkresults
	 * @param mixed $liganummer
	 * @return
	 */
	function getStatistik($linkresults,$liganummer) 
    {
        $option = JFactory::getApplication()->input->getCmd('option');
        $app = JFactory::getApplication();
		// XML File
		$filepath='components/'.$option.'/data/';
		//File laden
		$datei = ($filepath.'stat_'.$liganummer.'.xml');
		if (file_exists($datei)) {
			$LetzteAenderung = filemtime($datei);
			if ( (time() - $LetzteAenderung) > 1800) 
            {
				//unlink($datei);
				//if(file_get_contents($linkresults)) 
                //{
					//Laden
					//$content = file_get_contents($linkresults);
                    if (function_exists('curl_version'))
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $linkresults);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    curl_close($curl);
}
else if (file_get_contents(__FILE__) && ini_get('allow_url_fopen'))
{
    $content = file_get_contents($linkresults);
}
else
{
    //echo 'Sie haben weder cURL installiert, noch allow_url_fopen aktiviert. Bitte aktivieren/installieren allow_url_fopen oder Curl!';
    $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'),'Error');
}

					//Parsen
					$doc = DOMDocument::loadXML($content);
					//Altes File löschen
					unlink($datei);
					//Speichern
					$doc->save($filepath.'stat_'.$liganummer.'.xml');
				//}
			}
		} else {
			//Laden
			//$content = file_get_contents($linkresults);
            if (function_exists('curl_version'))
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $linkresults);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    curl_close($curl);
}
else if (file_get_contents(__FILE__) && ini_get('allow_url_fopen'))
{
    $content = file_get_contents($linkresults);
}
else
{
    //echo 'Sie haben weder cURL installiert, noch allow_url_fopen aktiviert. Bitte aktivieren/installieren allow_url_fopen oder Curl!';
    $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'),'Error');
}
			//Parsen
			$doc = DOMDocument::loadXML($content);
			//Speichern
			$doc->save($filepath.'stat_'.$liganummer.'.xml');
		}
		$result = simplexml_load_file($datei);
		// XML File end
		return $result;
	}
	//Statistik, Tore end

	
	
	/**
	 * sportsmanagementModelsishandball::getSpielplan()
	 * 
	 * @param mixed $linkresults
	 * @param mixed $liganummer
	 * @param mixed $sis_art
	 * @return
	 */
	function getSpielplan($linkresults,$liganummer,$sis_art) 
    {
        $option = JFactory::getApplication()->input->getCmd('option');
        $app = JFactory::getApplication();
		// XML File
		$filepath='components/'.$option.'/data/';
		//File laden
		$datei = ($filepath.'sp_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
		if (file_exists($datei)) {
			$LetzteAenderung = filemtime($datei);
			if ( (time() - $LetzteAenderung) > 1800) 
            {
				//if(file_get_contents($linkresults)) 
                //{
			 		//Laden
					//$content = file_get_contents($linkresults);
                    if (function_exists('curl_version'))
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $linkresults);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    curl_close($curl);
}
else if (file_get_contents(__FILE__) && ini_get('allow_url_fopen'))
{
    $content = file_get_contents($linkresults);
}
else
{
    //echo 'Sie haben weder cURL installiert, noch allow_url_fopen aktiviert. Bitte aktivieren/installieren allow_url_fopen oder Curl!';
    $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'),'Error');
}
					//Parsen
					$doc = DOMDocument::loadXML($content);
					//Altes File löschen
					unlink($datei);
					//Speichern
					$doc->save($filepath.'sp_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
				//}
			}
		} else {
			//Laden
			//$content = file_get_contents($linkresults);
            if (function_exists('curl_version'))
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $linkresults);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($curl);
    curl_close($curl);
}
else if (file_get_contents(__FILE__) && ini_get('allow_url_fopen'))
{
    $content = file_get_contents($linkresults);
}
else
{
    //echo 'Sie haben weder cURL installiert, noch allow_url_fopen aktiviert. Bitte aktivieren/installieren allow_url_fopen oder Curl!';
    $app->enqueueMessage(JText::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'),'Error');
}
			//Parsen
			$doc = DOMDocument::loadXML($content);
			//Speichern
			$doc->save($filepath.'sp_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
		}
		$result = simplexml_load_file($datei);
		// XML File end
		foreach ($result->Spiel as $temp) {
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