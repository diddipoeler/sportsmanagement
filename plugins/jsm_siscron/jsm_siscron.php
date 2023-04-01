<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jsm_siscron
 * @file       jsm_siscron.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
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


defined('_JEXEC') or die();
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

jimport('joomla.plugin.plugin');
jimport('joomla.html.parameter');

/**
 * PlgSystemjsm_siscron
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class PlgSystemjsm_siscron extends CMSPlugin
{
    var $_sis_art = 1;

    public function PlgSystemjsm_siscron(&$subject, $params)
    {
        parent::__construct($subject, $params);
        // load language file for frontend
        CMSPlugin::loadLanguage('plg_jsm_siscron', JPATH_ADMINISTRATOR);
    }
  
  
    public function onBeforeRender()
    {
        $db = Factory::getDBO();
        $app = Factory::getApplication();
        $projectid = Factory::getApplication()->input->getInt('p', 0);
      
      
      
        $params = ComponentHelper::getParams('com_sportsmanagement');
        $show_debug_info = $params->get('show_debug_info');
        $sis_xmllink = $params->get('sis_xmllink');
        $sis_nummer    = $params->get('sis_meinevereinsnummer');
        $sis_passwort = $params->get('sis_meinvereinspasswort');
        switch ($sis_xmllink)
        {
        case 'http://www.sis-handball.de':
            $country = 'DEU';
            break;
        case 'http://www.sis-handball.at':
            $country = 'AUT';
            break;
        }
      
        if ($show_debug_info ) {

        }
      
        $query = $db->getQuery(true);
        $query->select('p.staffel_id,p.sports_type_id,st.name');  
        $query->from('#__sportsmanagement_project as p');
        $query->join('INNER', '#__sportsmanagement_sports_type as st on st.id = p.sports_type_id');
        $query->where('p.id = '.$projectid);
        $db->setQuery($query);
        $result = $db->loadObject();
      
        if ($result ) {
            $teamart = substr($result->staffel_id, 17, 4);
      
            if ($show_debug_info ) {

            }
      
            if ($result->name == 'COM_SPORTSMANAGEMENT_ST_HANDBALL'  ) {
                $linkresults = self::getLink($sis_nummer, $sis_passwort, $result->staffel_id, $this->_sis_art, $sis_xmllink);
                $linkspielplan = self::getSpielplan($linkresults, $result->staffel_id, $this->_sis_art);
            }
      
        }
      
    }
  
    public function onAfterRender()
    {

    }
  
    public function onAfterRoute()
    {

    }
  
    public function onAfterDispatch()
    {

    }
  
    public function onAfterInitialise()
    {

    }
  
    //get sis link
    function getLink($vereinsnummer,$vereinspasswort,$liganummer,$sis_art,$sis_xmllink)
    {
        $sislink = $sis_xmllink.'/xmlexport/xml_dyn.aspx?user=%s&pass=%s&art=%s&auf=%s';
        $link = sprintf($sislink, $vereinsnummer, $vereinspasswort, $sis_art, $liganummer);  
        return $link;
    }
  
    //get sis spielplan
    function getSpielplan($linkresults,$liganummer,$sis_art)
    {
        $option = Factory::getApplication()->input->getCmd('option');
        $app = Factory::getApplication();
        // XML File
        $filepath='components/'.$option.'/sisdata/';
     
        //File laden
        $datei = ($filepath.'sp_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
        if (file_exists($datei)) {
            $LetzteAenderung = filemtime($datei);
            if ((time() - $LetzteAenderung) > 1800) {
                //if(file_get_contents($linkresults))
                //{
                  //Laden
                 //$content = file_get_contents($linkresults);
                if (function_exists('curl_version')) {
                    $curl = curl_init();
                    //Define header array for cURL requestes
                    $header = array('Contect-Type:application/xml');
                    curl_setopt($curl, CURLOPT_URL, $linkresults);
                    curl_setopt($curl, CURLOPT_VERBOSE, 1);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                    //curl_setopt($curl, CURLOPT_POST, 1);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
                    $content = curl_exec($curl);
                    curl_close($curl);
                }
                else if (file_get_contents(__FILE__) && ini_get('allow_url_fopen')) {
                    $content = file_get_contents($linkresults);
                }
                else
                {
                    //echo 'Sie haben weder cURL installiert, noch allow_url_fopen aktiviert. Bitte aktivieren/installieren allow_url_fopen oder Curl!';
                    $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'), 'Error');
                }

                //Parsen
                $doc = DOMDocument::loadXML($content);
                //Altes File löschen
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
            if (function_exists('curl_version')) {
                $curl = curl_init();
                //Define header array for cURL requestes
                $header = array('Contect-Type:application/xml');
                curl_setopt($curl, CURLOPT_URL, $linkresults);
                curl_setopt($curl, CURLOPT_VERBOSE, 1);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                //curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
                $content = curl_exec($curl);
                curl_close($curl);
            }
            else if (file_get_contents(__FILE__) && ini_get('allow_url_fopen')) {
                $content = file_get_contents($linkresults);
            }
            else
            {
                $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_ADMIN_GLOBAL_ERROR_ALLOW_URL_FOPEN'), 'Error');
            }
          
            //Parsen
            $doc = DOMDocument::loadXML($content);
            //Speichern
            $doc->save($filepath.'sp_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
        }
        $result = simplexml_load_file($datei);
        // XML File end
        foreach ($result->Spiel as $temp)
        {
            $nummer = substr($temp->Liga, -3);
            $datum = substr($temp->SpielVon, 0, 10);
            $datum_en = date("d.m.Y", strToTime($datum));
            $temp->Date = $datum;
            $temp->Nummer = $nummer;
            $temp->Datum = $datum_en;
            $temp->vonUhrzeit = substr($temp->SpielVon, 11, 8);
            $temp->bisUhrzeit = substr($temp->SpielBis, 11, 8);
        }
        return $result;
    }
  

}  

?>
