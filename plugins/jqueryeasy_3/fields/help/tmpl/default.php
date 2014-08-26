<?php
/**
 * @copyright	Copyright (C) 2011 Simplify Your Web, Inc. All rights reserved.
 * @license		GNU General Public License version 3 or later; see LICENSE.txt
 */

// no direct access
defined( '_JEXEC' ) or die;

$plugin_documentation_link = 'http://www.simplifyyourweb.com/index.php/downloads/category/8-loading-jquery#downloads';
$plugin_forum = 'http://www.simplifyyourweb.com/index.php/forum/19-jquery-easy';

$solvingissues_article_link = 'http://www.simplifyyourweb.com/index.php/developers-corner/90-solving-jquery-jquery-and-jquery-mootools-conflict-issues-with-the-jquery-easy-plugin';
$casestudy_article_link = 'http://www.simplifyyourweb.com/index.php/developers-corner/110-solving-jquery-issues-with-jquery-easy-a-case-study';

$jquery_link = 'http://jquery.com/';
$jqueryui_link = 'http://jqueryui.com/';
$themeroller_link = 'http://jqueryui.com/themeroller/';
$noconflict_api_link = 'http://api.jquery.com/jQuery.noConflict/';
$learnjquery_link = 'http://learn.jquery.com/';
$learnjquery_libraryconflicts_link = 'http://learn.jquery.com/using-jquery-core/avoid-conflicts-other-libraries/';
?>

<style>
	.help ul {
		list-style: none;
	}
	.help li {
		line-height: 24px;
		list-style: square outside none;
	}
</style>

<div class="help">
	<span><?php echo JText::sprintf('PLG_SYSTEM_JQUERYEASY_HELP_DOCUMENTATION', $plugin_documentation_link); ?></span><br />
	<span><?php echo JText::sprintf('PLG_SYSTEM_JQUERYEASY_HELP_FORUM', $plugin_forum); ?></span>
	
	<h2><?php echo JText::_('PLG_SYSTEM_JQUERYEASY_HELP_ARTICLESUTILES'); ?></h2>
	
	<ul>
		<li><?php echo JText::sprintf('PLG_SYSTEM_JQUERYEASY_HELP_ARTICLESOLVING', $solvingissues_article_link); ?></li>
		<li><?php echo JText::sprintf('PLG_SYSTEM_JQUERYEASY_HELP_ARTICLEUSECASE', $casestudy_article_link); ?></li>
	</ul>
	
	<h2><?php echo JText::_('PLG_SYSTEM_JQUERYEASY_HELP_LIENSJQUERYUTILES'); ?></h2>
	
	<ul>
		<li><img style="width: 24px; height: 24px; padding-right: 5px;" src="../plugins/system/jqueryeasy/images/jquery.png"><a href="<?php echo $jquery_link; ?>" target="_blank">jQuery.com</a></li>
		<li><img style="width: 24px; height: 24px; padding-right: 5px;" src="../plugins/system/jqueryeasy/images/jqueryui.png"><a href="<?php echo $jqueryui_link; ?>" target="_blank">jQueryUI.com</a></li>
		<li><a href="<?php echo $themeroller_link; ?>" target="_blank">ThemeRoller</a></li>
		<li><a href="<?php echo $noconflict_api_link; ?>" target="_blank">noConflict API</a></li>
		<li><a href="<?php echo $learnjquery_link; ?>" target="_blank">Learn jQuery</a></li>
		<li><a href="<?php echo $learnjquery_libraryconflicts_link; ?>" target="_blank">Learn jQuery: avoiding conflicts with other libraries</a></li>
	</ul>
</div>