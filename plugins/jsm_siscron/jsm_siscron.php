<?php
/** SportsManagement ein Programm zur Verwaltung f�r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: � 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k�nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp�teren
* ver�ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n�tzlich sein wird, aber
* OHNE JEDE GEW�HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew�hrleistung der MARKTF�HIGKEIT oder EIGNUNG F�R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f�r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

/**
 * System plugin
 * 1) onBeforeRender()
 * 2) onAfterRender()
 * 3) onAfterRoute()
 * 4) onAfterDispatch()
 * These events are triggered in 'JAdministrator' class in file 'application.php' at location
 * 'Joomla_base\administrator\includes'.
 * 5) onAfterInitialise()
 * This event is triggered in 'JApplication' class in file 'application.php' at location
 * 'Joomla_base\libraries\joomla\application'.
 */


// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');

/**
 * PlgSystemjsm_siscron
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class PlgSystemjsm_siscron extends JPlugin
{
var $_sis_art = 1;

	public function PlgSystemjsm_siscron(&$subject, $params)
	{
		parent::__construct($subject, $params);
		// load language file for frontend
		JPlugin::loadLanguage( 'plg_jsm_siscron', JPATH_ADMINISTRATOR );
	}
    
    
	public function onBeforeRender()
	{
		$db = JFactory::getDBO();
        $app = JFactory::getApplication();
        $projectid = JRequest::getInt('p',0);
        
        
        
        $params = JComponentHelper::getParams( 'com_sportsmanagement' );
        $show_debug_info = $params->get( 'show_debug_info' ); 
        $sis_xmllink = $params->get( 'sis_xmllink' );
        $sis_nummer	= $params->get( 'sis_meinevereinsnummer' );
        $sis_passwort = $params->get( 'sis_meinvereinspasswort' );
        switch ($sis_xmllink)
        {
        case 'http://www.sis-handball.de':
        $country = 'DEU';
        break;
        case 'http://www.sis-handball.at':
        $country = 'AUT';
        break;
        }
        
        if ( $show_debug_info )
        {
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'   ),'');    
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' sis_xmllink<br><pre>'.print_r($sis_xmllink,true).'</pre>'   ),'');
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' sis_meinevereinsnummer<br><pre>'.print_r($sis_nummer,true).'</pre>'   ),'');
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' sis_meinvereinspasswort<br><pre>'.print_r($sis_passwort,true).'</pre>'   ),'');
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' country<br><pre>'.print_r($country,true).'</pre>'   ),'');
        }
        
        $query = $db->getQuery(true);
        $query->select('p.staffel_id,p.sports_type_id,st.name');    
        $query->from('#__sportsmanagement_project as p'); 
        $query->join('INNER', '#__sportsmanagement_sports_type as st on st.id = p.sports_type_id');
        $query->where('p.id = '.$projectid); 
        $db->setQuery($query);
		$result = $db->loadObject();
        
        if ( $result )
        {
        $teamart = substr( $result->staffel_id , 17, 4);
        
        if ( $show_debug_info )
        {
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' staffel_id<br><pre>'.print_r($result->staffel_id,true).'</pre>'   ),'');
        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' teamart<br><pre>'.print_r($teamart,true).'</pre>'   ),'');
        }
        
        if ( $result->name == 'COM_SPORTSMANAGEMENT_ST_HANDBALL'  )
        {
        $linkresults = self::getLink($sis_nummer,$sis_passwort,$result->staffel_id,$this->_sis_art,$sis_xmllink);
        $linkspielplan = self::getSpielplan($linkresults,$result->staffel_id,$this->_sis_art);
        }
        
        }
        
	}
    
    public function onAfterRender()
	{
		
//        $app = JFactory::getApplication();
//        $projectid = JRequest::getInt('p',0);
//        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'   ),'');
	}
    
    public function onAfterRoute()
	{
		
//        $app = JFactory::getApplication();
//        $projectid = JRequest::getInt('p',0);
//        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'   ),'');
	}
    
    public function onAfterDispatch()
	{
		
//        $app = JFactory::getApplication();
//        $projectid = JRequest::getInt('p',0);
//        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'   ),'');
	}
    
    public function onAfterInitialise()
	{
		
//        $app = JFactory::getApplication();
//        $projectid = JRequest::getInt('p',0);
//        $app->enqueueMessage(JText::_(get_class($this).' '.__FUNCTION__.' projectid<br><pre>'.print_r($projectid,true).'</pre>'   ),'');
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
  $app = JFactory::getApplication();
		// XML File
		$filepath='components/'.$option.'/sisdata/';
        
        //$app->enqueueMessage(JText::_('filepath<br><pre>'.print_r($filepath,true).'</pre>'   ),'');
        
		//File laden
		$datei = ($filepath.'sp_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
		if (file_exists($datei)) 
        {
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
    //Define header array for cURL requestes
    $header = array('Contect-Type:application/xml');
    curl_setopt($curl, CURLOPT_URL, $linkresults);
    curl_setopt($curl, CURLOPT_VERBOSE, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    //curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER , $header);
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
					//Altes File l�schen
					unlink($datei);
					//Speichern
					$doc->save($filepath.'sp_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
				//}
			}
		} 
        else 
        {
			//Laden
			//$content = file_get_contents($linkresults);
            if (function_exists('curl_version'))
{
    $curl = curl_init();
    //Define header array for cURL requestes
    $header = array('Contect-Type:application/xml');
    curl_setopt($curl, CURLOPT_URL, $linkresults);
    curl_setopt($curl, CURLOPT_VERBOSE, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    //curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER , $header);
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
            //$app->enqueueMessage(JText::_('content<br><pre>'.print_r($content,true).'</pre>'   ),'');
            
			//Parsen
			$doc = DOMDocument::loadXML($content);
            //$app->enqueueMessage(JText::_('doc<br><pre>'.print_r($doc,true).'</pre>'   ),'');
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
			$temp->Date = $datum;
            $temp->Nummer = $nummer;
            $temp->Datum = $datum_en;
			$temp->vonUhrzeit = substr( $temp->SpielVon , 11, 8);
			$temp->bisUhrzeit = substr( $temp->SpielBis , 11, 8);
		}
		return $result;
	}
    

}    

?>