<?php 
/** SportsManagement ein Programm zur Verwaltung f?r alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
* @copyright        Copyright: ? 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
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
* SportsManagement ist Freie Software: Sie k?nnen es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder sp?teren
* ver?ffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es n?tzlich sein wird, aber
* OHNE JEDE GEW?HELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gew?hrleistung der MARKTF?HIGKEIT oder EIGNUNG F?R EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License f?r weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * sportsmanagementViewPlayground
 * 
 * @package   
 * @author 
 * @copyright diddi
 * @version 2014
 * @access public
 */
class sportsmanagementViewPlayground extends sportsmanagementView
{
    
	/**
	 * sportsmanagementViewPlayground::init()
	 * 
	 * @return void
	 */
	function init()
	{
	
        sportsmanagementModelProject::setProjectID($this->jinput->getInt( "p", 0 ),$this->jinput->getInt('cfg_which_database',0));
        $mdlJSMTeams = JModelLegacy::getInstance("teams", "sportsmanagementModel");
    	$this->playground = sportsmanagementModelPlayground::getPlayground($this->jinput->getInt( "pgid", 0 ),1);
        $this->address_string = $this->model->getAddressString();
	$this->teams = $mdlJSMTeams->getTeams($this->playground->id);
	$this->games = $this->model->getNextGames($this->jinput->getInt( "p", 0 ),$this->jinput->getInt( "pgid", 0 ));
	$this->gamesteams = $mdlJSMTeams->getTeamsFromMatches( $this->games );
        
        //$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' playground<br><pre>'.print_r($this->playground,true).'</pre>'   ),'');
        
        // diddipoeler
        $this->geo = new JSMsimpleGMapGeocoder();
        $this->geo->genkml3file($this->playground->id,$this->address_string,'playground',$this->playground->picture,$this->playground->name,$this->playground->latitude,$this->playground->longitude);

		$this->extended = sportsmanagementHelper::getExtended($this->playground->extended, 'playground');
		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PLAYGROUND_PAGE_TITLE' );
		if ( isset( $this->playground->name ) )
		{
			$pageTitle .= ' - ' . $this->playground->name;
		}
        // Set page title
		$this->document->setTitle( $pageTitle );
		$this->document->addCustomTag( '<meta property="og:title" content="' . $this->playground->name .'"/>' );
		$this->document->addCustomTag( '<meta property="og:street-address" content="' . $this->address_string .'"/>' );
        
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$this->option.'/assets/css/'.$this->view.'.css'.'" type="text/css" />' ."\n";
        $this->document->addCustomTag($stylelink);
        
        $this->headertitle = $this->playground->name;
        
	}
}
?>
