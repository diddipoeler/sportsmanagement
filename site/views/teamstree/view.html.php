<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      view.html.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@arcor.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage teamstree
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view' );


/**
 * sportsmanagementViewTeamsTree
 * 
 * @package 
 * @author Dieter Plöger
 * @copyright 2018
 * @version $Id$
 * @access public
 */
class sportsmanagementViewTeamsTree extends sportsmanagementView
{

	/**
	 * sportsmanagementViewTeamsTree::init()
	 * 
	 * @return void
	 */
	function init()
	{

$this->teams = sportsmanagementModelProject::getTeams($this->jinput->getInt( "division", 0 ),'name',$this->jinput->getInt('cfg_which_database',0));
//$this->config['show_bootstrap_tree'] = 0;
foreach( $this->teams as $rowclub )
{
    
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_id<br><pre>'.print_r($rowclub->club_id,true).'</pre>'),'');
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_id<br><pre>'.print_r($rowclub->club_name,true).'</pre>'),'');

$mdlClubInfo = JModelLegacy::getInstance("ClubInfo", "sportsmanagementModel");
$mdlClubInfo::$tree_fusion = '';
$mdlClubInfo::$historyhtmltree = '';
$mdlClubInfo::$first_club_id = 0;
/**
 * ist das die erste club_id in der kette des stammbaumes ?
 */
if ( $rowclub->new_club_id )
{
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' club_name<br><pre>'.print_r($rowclub->club_name,true).'</pre>'),'');    
$this->firstclubid = $mdlClubInfo::getFirstClubId($rowclub->club_id,$rowclub->new_club_id);
$firstclubid = $mdlClubInfo::$first_club_id; 
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' firstclubid<br><pre>'.print_r($this->firstclubid,true).'</pre>'),'');
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' firstclubid<br><pre>'.print_r($firstclubid,true).'</pre>'),'');
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' new_club_id<br><pre>'.print_r($rowclub->new_club_id,true).'</pre>'),'');
}
$this->clubhistory = $mdlClubInfo::getClubHistory($rowclub->club_id);
$this->clubhistoryhtml = $mdlClubInfo::getClubHistoryHTML($rowclub->club_id);
$this->clubhistoryfamilytree = $mdlClubInfo::fbTreeRecurse($rowclub->club_id, '', array (),$mdlClubInfo::$tree_fusion, 10, 0, 1);
$this->genfamilytree = $mdlClubInfo::generateTree($rowclub->club_id,$this->config['show_bootstrap_tree']);
$this->familytree = $mdlClubInfo::$historyhtmltree;
//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' familytree<br><pre>'.print_r($this->familytree,true).'</pre>'),'');  
$this->familyteamstree[$rowclub->club_id] = $this->familytree;  
$this->familyclub[$rowclub->club_id] = $rowclub;
}

	if ( $this->config['show_bootstrap_tree'] )
	{	
        $this->document->addStyleSheet(JURI::base().'components/'.$this->option.'/assets/css/bootstrap-familytree.css');
	}
	else
	{
$javascript = "\n";	
$javascript .= "
jQuery(function ($) {
    $('.tree li:has(ul)').addClass('parent_li').find(' > span').attr('title', 'Collapse this branch');
    $('.tree li.parent_li > span').on('click', function (e) {
        var children = $(this).parent('li.parent_li').find(' > ul > li');
        if (children.is(\":visible\")) {
            children.hide('fast');
            $(this).attr('title', 'Expand this branch').find(' > i').addClass('icon-plus-sign').removeClass('icon-minus-sign');
        } else {
            children.show('fast');
            $(this).attr('title', 'Collapse this branch').find(' > i').addClass('icon-minus-sign').removeClass('icon-plus-sign');
        }
        e.stopPropagation();
    });
});


";	
	
$this->document->addScriptDeclaration( $javascript );
		$this->document->addStyleSheet(JURI::base().'components/'.$this->option.'/assets/css/bootstrap-tree2.css');	
	}

$this->document->setTitle( JText::_( 'COM_SPORTSMANAGEMENT_TEAMSTREE_PAGE_TITLE' ) );

//$this->app->enqueueMessage(JText::_(__METHOD__.' '.__LINE__.' teams<br><pre>'.print_r($this->teams,true).'</pre>'),'');
        
}

}
?>
