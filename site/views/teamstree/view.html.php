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

}
?>
