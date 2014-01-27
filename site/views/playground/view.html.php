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

class sportsmanagementViewPlayground extends JView
{
	function display( $tpl = null )
	{
		// Get a refrence of the page instance in joomla
		$document= JFactory::getDocument();

		// Set page title
		//$document->setTitle( JText::_( 'COM_SPORTSMANAGEMENT_PLAYGROUND_TITLE' ) );

		$model = $this->getModel();
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName());

		$this->assign( 'project', sportsmanagementModelProject::getProject() );
		$this->assign( 'overallconfig', sportsmanagementModelProject::getOverallConfig() );
		$this->assignRef( 'config', $config );

		//$model = $this->getModel();
		$games = $model->getNextGames();
		$gamesteams = sportsmanagementModelTeams::getTeamsFromMatches( $games );
		$this->assign( 'playground',  $model->getPlayground() );
		$this->assign( 'teams', sportsmanagementModelTeams::getTeams($this->playground->id) );
		$this->assignRef( 'games', $games );
		$this->assignRef( 'gamesteams', $gamesteams );

		//$this->assignRef( 'mapconfig', $model->getMapConfig() );
		//$this->assignRef( 'address_string', $model->getAddressString() );

		//$this->assignRef( 'gmap', $model->getGoogleMap( $this->mapconfig, $this->address_string ) );
        
		// $gm = $this->getModel( 'googlemap' );
		// $this->assignRef('gm', $gm->getGoogleMap( $model->getMapConfig(), $model->getAddressString() ) );
        
        // diddipoeler
        //$this->geo = new simpleGMapGeocoder();
        //$this->geo->genkml3file($this->playground->id,$this->address_string,'playground',$this->playground->picture,$this->playground->name);

		$extended = sportsmanagementHelper::getExtended($this->playground->extended, 'playground');
		$this->assignRef( 'extended', $extended );
		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PLAYGROUND_PAGE_TITLE' );
		if ( isset( $this->playground->name ) )
		{
			$pageTitle .= ' - ' . $this->playground->name;
		}
		$document->setTitle( $pageTitle );
		$document->addCustomTag( '<meta property="og:title" content="' . $this->playground->name .'"/>' );
		$document->addCustomTag( '<meta property="og:street-address" content="' . $this->address_string .'"/>' );
        
        $view = JRequest::getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
		parent::display( $tpl );
	}
}
?>