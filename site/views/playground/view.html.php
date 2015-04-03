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
class sportsmanagementViewPlayground extends JViewLegacy
{
	/**
	 * sportsmanagementViewPlayground::display()
	 * 
	 * @param mixed $tpl
	 * @return
	 */
	function display( $tpl = null )
	{
		// Reference global application object
        $app = JFactory::getApplication();
        // JInput object
        $jinput = $app->input;
        $option = $jinput->getCmd('option');
        // Get a refrence of the page instance in joomla
		$document= JFactory::getDocument();
        
        //$document->addScript ( JUri::root(true).'/components/'.$option.'/assets/js/smsportsmanagement.js' );

		$model = $this->getModel();
        sportsmanagementModelProject::setProjectID($jinput->getInt( "p", 0 ),$jinput->getInt('cfg_which_database',0));
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' getName<br><pre>'.print_r($this->getName(),true).'</pre>'),'');
        
		$config = sportsmanagementModelProject::getTemplateConfig($this->getName(),$jinput->getInt('cfg_which_database',0));

		$this->assign('project', sportsmanagementModelProject::getProject($jinput->getInt('cfg_which_database',0)) );
		$this->assign('overallconfig', sportsmanagementModelProject::getOverallConfig($jinput->getInt('cfg_which_database',0)) );
		$this->assignRef('config', $config );

		//$model = $this->getModel();
		$games = $model->getNextGames($jinput->getInt( "p", 0 ));
		$gamesteams = sportsmanagementModelTeams::getTeamsFromMatches( $games );
		$this->assign('playground',  $model->getPlayground($jinput->getInt( "pgid", 0 )),1 );
        $this->assign('address_string', $model->getAddressString() );
		$this->assign('teams', sportsmanagementModelTeams::getTeams($this->playground->id) );
		$this->assignRef('games', $games );
		$this->assignRef('gamesteams', $gamesteams );
        
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' playground<br><pre>'.print_r($this->playground,true).'</pre>'),'');
        //$app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' config<br><pre>'.print_r($this->config,true).'</pre>'),'');

		//$this->assignRef( 'mapconfig', $model->getMapConfig() );
		

		//$this->assignRef( 'gmap', $model->getGoogleMap( $this->mapconfig, $this->address_string ) );
        
		// $gm = $this->getModel( 'googlemap' );
		// $this->assignRef('gm', $gm->getGoogleMap( $model->getMapConfig(), $model->getAddressString() ) );
        
        // diddipoeler
        $this->geo = new JSMsimpleGMapGeocoder();
        $this->geo->genkml3file($this->playground->id,$this->address_string,'playground',$this->playground->picture,$this->playground->name,$this->playground->latitude,$this->playground->longitude);

		$extended = sportsmanagementHelper::getExtended($this->playground->extended, 'playground');
		$this->assignRef( 'extended', $extended );
		// Set page title
		$pageTitle = JText::_( 'COM_SPORTSMANAGEMENT_PLAYGROUND_PAGE_TITLE' );
		if ( isset( $this->playground->name ) )
		{
			$pageTitle .= ' - ' . $this->playground->name;
		}
        // Set page title
		$document->setTitle( $pageTitle );
		$document->addCustomTag( '<meta property="og:title" content="' . $this->playground->name .'"/>' );
		$document->addCustomTag( '<meta property="og:street-address" content="' . $this->address_string .'"/>' );
        
        $view = $jinput->getVar( "view") ;
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'components/'.$option.'/assets/css/'.$view.'.css'.'" type="text/css" />' ."\n";
        $document->addCustomTag($stylelink);
        
        $this->headertitle = $this->playground->name;
        
		parent::display( $tpl );
	}
}
?>