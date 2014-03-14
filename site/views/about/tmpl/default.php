<?php 

defined('_JEXEC') or die('Restricted access');
/*
 * @copyright	Copyright (C) 2005-2013 JoomLeague.net. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * Default HTML layout for the Joomleague component
 *
 * @author Joomleague Team <www.JoomLeague.net>
 * @package   Joomleague
 * @since 0.1
*/

/*
<object type="application/x-shockwave-flash" data="media/com_sportsmanagement/jl_images/joomleague_logo.swf" id="movie" width="410" height="200">
<param name="movie" value="media/com_sportsmanagement/jl_images/joomleague_logo.swf" />
<param name="bgcolor" value="#FFFFFF" />
<param name="quality" value="high" />
<param name="loop" value="false" />
<param name="allowscriptaccess" value="samedomain" />
</object>
      
*/
?>
<table class="about">
	<tr>
		<td align="center">
<?PHP 
$option = JRequest::getCmd('option');
$backgroundimage = 'administrator/components/'.$option.'/assets/icons/sm-sports-manager.png';  

//echo $backgroundimage.'<br>';
     
echo "<img class=\"\" style=\"\" src=\"".$backgroundimage."\" alt=\"\" >";
?>		
		</td>
	</tr>
</table>
<br />
<div class="componentheading">
	<?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT');?>
</div>
<table class="about">
	<tr>
		<td><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_TEXT'); ?></td>
	</tr>
</table>
<br />

<div class="componentheading">
	<?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_DIDDIPOELER'); ?>
</div>
<table class="about">
<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_TEXT_DIDDIPOELER'); ?></b></td>
    <td><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_TEXT_DESC_DIDDIPOELER'); ?></td>
	</tr>
<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_WEBSITE_DIDDIPOELER'); ?></b></td>
		<td>
			<a href="<?php echo $this->about->diddipoelerpage; ?>" target="_blank">
				<?php echo $this->about->diddipoelerpage; ?>
			</a>
		</td>
	</tr>
<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_SUPPORT_FORUM_DIDDIPOELER'); ?></b></td>
		<td>
			<a href="<?php echo $this->about->diddipoelerforum; ?>" target="_blank">
				<?php echo "Fussballineuropa Joomleague 2.0 Forum"; ?></a>
		</td>
	</tr> 
  
<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_GITHUB_DIDDIPOELER'); ?></b></td>
		<td>
			<a href="<?php echo $this->about->github; ?>" target="_blank">
				<?php echo 'Github joomleague diddipoeler'; ?>
			</a>
		</td>
	</tr>
     
<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_SUPPORT_EMAIL_DIDDIPOELER'); ?></b></td>
		<td>
			<a href="mailto:<?php echo $this->about->diddipoeleremail; ?>" target="_blank">
				<?php echo $this->about->diddipoeleremail; ?></a>
		</td>
	</tr>
</table>



<div class="componentheading">
	<?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_DETAILS'); ?>
</div>
<table class="about">
<!--
	<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_TRANSLATIONS'); ?></b></td>
		<td><?php echo $this->about->translations; ?></td>
	</tr>
	<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_REPOSITORY'); ?></b></td>
		<td><?php echo $this->about->repository; ?></td>
	</tr>
	<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_VERSION'); ?></b></td>
		<td><?php echo $this->about->version; ?></td>
	</tr>
	<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_AUTHOR'); ?></b></td>
		<td><?php echo $this->about->author; ?></td>
	</tr>

	<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_WEBSITE'); ?></b></td>
		<td>
			<a href="<?php echo $this->about->page; ?>" target="_blank">
				<?php echo $this->about->page; ?>
			</a>
		</td>
	</tr>
	<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_SUPPORT_FORUM'); ?></b></td>
		<td>
			<a href="<?php echo $this->about->forum; ?>" target="_blank">
				<?php echo $this->about->forum; ?></a>
		</td>
	</tr>
	<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_BUGS'); ?></b></td>
		<td>
			<a href="<?php echo $this->about->bugs; ?>" target="_blank">
				<?php echo $this->about->bugs; ?></a>
		</td>
	</tr>
	<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_WIKI'); ?></b></td>
		<td>
			<a href="<?php echo $this->about->wiki; ?>" target="_blank">
				<?php echo $this->about->wiki; ?></a>
		</td>
	</tr>	
	<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_DEVELOPERS'); ?></b></td>
		<td><?php echo $this->about->developer; ?></td>
	</tr>

	<tr>
		<td><b><?php //echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_SUPPORTERS'); ?></b></td>
		<td><?php //echo $this->about->supporters; ?></td>
	</tr>
	<tr>
		<td><b><?php //echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_TRANSLATORS'); ?></b></td>
		<td><?php //echo $this->about->translator; ?></td>
	</tr>
-->
	<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_DESIGNER'); ?></b></td>
		<td><?php echo $this->about->designer; ?></td>
	</tr>
    <tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_DEVELOPERS'); ?></b></td>
		<td><?php echo $this->about->developer; ?></td>
	</tr>
    
    
<!--    
	<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_ICONS'); ?></b></td>
		<td><?php echo $this->about->icons; ?></td>
	</tr>
	<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_FLASH_STATISTICS'); ?></b></td>
		<td><?php echo $this->about->flash; ?></td>
	</tr>
	<tr>
		<td><b><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_PHPTHUMB'); ?></b></td>
		<td><?php echo $this->about->phpthumb; ?></td>
	</tr>	

	<tr>
		<td><b><?php //echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_GRAPHIC_LIBRARY'); ?></b></td>
		<td><?php //echo $this->about->graphic_library; ?></td>
	</tr>
-->
</table>
<br />
<div class="componentheading">
	<?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_LICENSE'); ?>
</div>
<table class="about">
	<tr>
		<td><?php echo JText::_('COM_SPORTSMANAGEMENT_ABOUT_LICENSE_TEXT'); ?></td>
	</tr>
</table>
<!-- backbutton -->
<?php

//	echo $this->loadTemplate('backbutton');

/*
if ($this->config['show_back_button'] > "0")
{
	echo $this->loadTemplate('backbutton');
}
?>
<!-- footer -->
<?php echo $this->loadTemplate('footer');
*/
?>