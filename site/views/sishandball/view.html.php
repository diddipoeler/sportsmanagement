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


jimport( 'joomla.application.component.view');

class sportsmanagementViewsishandball extends JViewLegacy 
{ 

	function display($tpl = null) 
    {
		$option = JRequest::getCmd('option');
		//global $mainframe;
		$app = JFactory::getApplication();
		$params = $app->getParams();
        $paramscomponent = JComponentHelper::getParams( $option );
		$model = $this->getModel();
		//$model = &$this->getModelitem();
		
        /*	
		$vereinsnummer = $params->get( 'sis_meinevereinsnummer');
		$vereinspasswort = $params->get( 'sis_meinvereinspasswort');
		$sis_xmllink = $params->get( 'sis_xmllink');
        */
        
        $sis_xmllink	= $paramscomponent->get( 'sis_xmllink' );
        $vereinsnummer	= $paramscomponent->get( 'sis_meinevereinsnummer' );
        $vereinspasswort	= $paramscomponent->get( 'sis_meinvereinspasswort' );
        
        $liganummer = $params->get( 'sis_liganummer');
		$sis_art = $params->get( 'sis_art');
		
			
		if($sis_art == "x" || $sis_art == "1a") 
        {
			$sis_art = 1;
		}
			
		$sis_getxmldatei = $params->get( 'sis_getxmldatei');
		//$sis_getschiedsrichter = $params->get( 'sis_getschiedsrichter');
		//$sis_getspielort = $params->get( 'sis_getspielort');
		
		
		$linkresults = $model->getLink($vereinsnummer,$vereinspasswort,$liganummer,$sis_art,$sis_xmllink);
				
		//echo $linkresults.'<br>';
		
		$this->assignRef( 'sis_getxmldatei',	$sis_getxmldatei );
		$this->assignRef( 'sis_getschiedsrichter',	$sis_getschiedsrichter );
		$this->assignRef( 'sis_getspielort',	$sis_getspielort );		
		$this->assignRef( 'linkresults',	$linkresults );
		$this->assignRef('params',		$params);
        $this->assignRef('paramscomponent',		$paramscomponent);
		
		$this->assignRef('sis_art',		$sis_art);
		$this->assignRef('vereinsnummer',		$vereinsnummer);
		$this->assignRef('liganummer',		$liganummer);
		
		// tabellenanzeige ist gewünscht		
		if ( $sis_art == 4 || $sis_art == 6 || $sis_art == 7 ) 
        {
			$tabelle = $model->getTabelle($linkresults,$liganummer,$sis_art);
			$this->assignRef('tabelle',		$tabelle);
		}

		// spielplan ist gewünscht
		if ( $sis_art == 1 || $sis_art == "1a" || $sis_art == 2 || $sis_art == 3 || $sis_art == 10 || $sis_art == 11 || $sis_art == "x") 
        {
			$spielplan = $model->getSpielplan($linkresults,$liganummer,$sis_art);
			$this->assignRef('spielplan', $spielplan);
		}
	
		// statistikanzeige ist gewünscht		
		if ( $sis_art == 12 || $sis_art == "12a" ) 
        {
			$statistik = $model->getStatistik($linkresults,$liganummer);
			$this->assignRef('statistik', $statistik);
		}

		parent::display($tpl);
	}
}
?>
