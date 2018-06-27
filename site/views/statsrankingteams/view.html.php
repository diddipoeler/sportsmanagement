<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage statsrankingteams
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
//jimport( 'joomla.application.component.view' );
/**
 * sportsmanagementViewStatsRanking
 * 
 * @package 
 * @author diddi
 * @copyright 2014
 * @version $Id$
 * @access public
 */
class sportsmanagementViewStatsRankingTeams extends sportsmanagementView
{
  
function init()
	{  
  	$this->document->addScript(JUri::root(true) . '/components/' . $this->option . '/assets/js/smsportsmanagement.js');
        sportsmanagementModelProject::setProjectID($this->jinput->getInt('p',0),$this->cfg_which_database);
  
  $teams = sportsmanagementModelProject::getTeamsIndexedById(0,'name',$this->cfg_which_database);
  $this->teams = $teams;
$this->stats = $this->model->getProjectUniqueStats();
$this->playersstats = $this->model->getPlayersStats();
	
	
	
$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' playersstats<br><pre>'.print_r($this->playersstats,true).'</pre>'),'');	
}
  
}
?>
