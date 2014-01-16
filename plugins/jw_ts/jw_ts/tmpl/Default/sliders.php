<?php
/**
 * @version		2.7
 * @package		Tabs & Sliders (plugin)
 * @author    JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2012 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Please note that this is the override for each slider only! Any change you do in the html below will reflect on all slider panes across your entire site articles.

?>

<div class="jwts_toggleControlContainer">
	<a href="#" class="jwts_toggleControl" title="<?php echo JText::_('JW_PLG_TS_CLICK_TO_OPEN'); ?>">
		<span class="jwts_togglePlus">+</span>
		<span class="jwts_toggleMinus">-</span>
		<span class="jwts_toggleControlTitle">{SLIDER_TITLE}</span>
		<span class="jwts_toggleControlNotice"><?php echo JText::_('JW_PLG_TS_CLICK_TO_COLLAPSE'); ?></span>
		<span class="jwts_clr"></span>
	</a>
</div>
<div class="jwts_toggleContent">
	<div class="jwts_content">
		{SLIDER_CONTENT}
	</div>
</div>
