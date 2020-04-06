<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       sishandball.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage models
 */


defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * sportsmanagementModelsishandball
 *
 * @package 
 * @author
 * @copyright diddi
 * @version   2014
 * @access    public
 */
class sportsmanagementModelsishandball extends BaseDatabaseModel
{


  
    /**
     * sportsmanagementModelsishandball::getLink()
     *
     * @param  mixed $vereinsnummer
     * @param  mixed $vereinspasswort
     * @param  mixed $liganummer
     * @param  mixed $sis_art
     * @param  mixed $sis_xmllink
     * @return
     */
    function getLink($vereinsnummer,$vereinspasswort,$liganummer,$sis_art,$sis_xmllink)
    {
        $option = Factory::getApplication()->input->getCmd('option');
        $app = Factory::getApplication();
        $sislink = $sis_xmllink.'/xmlexport/xml_dyn.aspx?user=%s&pass=%s&art=%s&auf=%s';
        $link = sprintf($sislink, $vereinsnummer, $vereinspasswort, $sis_art, $liganummer);  
        return $link;
    }

  
    /**
     * sportsmanagementModelsishandball::getTabelle()
     *
     * @param  mixed $linkresults
     * @param  mixed $liganummer
     * @param  mixed $sis_art
     * @return
     */
    function getTabelle($linkresults,$liganummer,$sis_art)
    {
        $option = Factory::getApplication()->input->getCmd('option');
        $app = Factory::getApplication();
        // XML File
        $filepath='components/'.$option.'/data/';
        //File laden
        $datei = ($filepath.'tab_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
        if (file_exists($datei)) {
            $LetzteAenderung = filemtime($datei);
            if ((time() - $LetzteAenderung) > 1800) {
                //if(file_get_contents($linkresults))
                 //{
                 //Laden
                 //$content = file_get_contents($linkresults);
                if (function_exists('curl_version')) {
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $linkresults);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
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
                $doc->save($filepath.'tab_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
                //}
            }
        } else {
            //Laden
            //$content = file_get_contents($linkresults);
            if (function_exists('curl_version')) {
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $linkresults);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
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
     * @param  mixed $linkresults
     * @param  mixed $liganummer
     * @return
     */
    function getStatistik($linkresults,$liganummer)
    {
        $option = Factory::getApplication()->input->getCmd('option');
        $app = Factory::getApplication();
        // XML File
        $filepath='components/'.$option.'/data/';
        //File laden
        $datei = ($filepath.'stat_'.$liganummer.'.xml');
        if (file_exists($datei)) {
            $LetzteAenderung = filemtime($datei);
            if ((time() - $LetzteAenderung) > 1800) {
                //unlink($datei);
                //if(file_get_contents($linkresults))
                //{
                 //Laden
                 //$content = file_get_contents($linkresults);
                if (function_exists('curl_version')) {
                    $curl = curl_init();
                    curl_setopt($curl, CURLOPT_URL, $linkresults);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
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
                $doc->save($filepath.'stat_'.$liganummer.'.xml');
                //}
            }
        } else {
            //Laden
            //$content = file_get_contents($linkresults);
            if (function_exists('curl_version')) {
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $linkresults);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
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
     * @param  mixed $linkresults
     * @param  mixed $liganummer
     * @param  mixed $sis_art
     * @return
     */
    function getSpielplan($linkresults,$liganummer,$sis_art)
    {
        $option = Factory::getApplication()->input->getCmd('option');
        $app = Factory::getApplication();
        // XML File
        $filepath='components/'.$option.'/data/';
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
                    curl_setopt($curl, CURLOPT_URL, $linkresults);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
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
        } else {
            //Laden
            //$content = file_get_contents($linkresults);
            if (function_exists('curl_version')) {
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $linkresults);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
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
            //Speichern
            $doc->save($filepath.'sp_sis_art_'.$sis_art.'_ln_'.$liganummer.'.xml');
        }
        $result = simplexml_load_file($datei);
        // XML File end
        foreach ($result->Spiel as $temp) {
            $nummer = substr($temp->Liga, -3);
            $datum = substr($temp->SpielVon, 0, 10);
            $datum_en = date("d.m.Y", strToTime($datum));
            $temp->Datum = $datum_en;
            $temp->vonUhrzeit = substr($temp->SpielVon, 11, 8);
            $temp->bisUhrzeit = substr($temp->SpielBis, 11, 8);
        }
        return $result;
    }

}


?>
