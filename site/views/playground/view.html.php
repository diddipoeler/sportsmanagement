<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @subpackage playground
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
	$this->games = $this->model->getNextGames($this->jinput->getInt( "p", 0 ),$this->jinput->getInt( "pgid", 0 ),0);
	$this->playedgames = $this->model->getNextGames($this->jinput->getInt( "p", 0 ),$this->jinput->getInt( "pgid", 0 ),1);	
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
