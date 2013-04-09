<?php
/**
 * @category	Core
 * @package		
 * @copyright (C) 2013
 * @license		GNU/GPL, see LICENSE.php
 */
 
;##################################################################
;/* 
;* Modified by 
;*  
;* email: 
;* date: 2013
;* Release: 1.0
;* License : http://www.gnu.org/copyleft/gpl.html GNU/GPL 
;*/
################################################################### 
 
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<table width="100%" border="0">
	<tr>
		<td width="100%" valign="top">
			<div id="cpanel">
				<?php echo $this->addIcon('dfb-key.jpg','index.php?option=com_sportsmanagement&view=configuration', JText::_('COM_SPORTSMANAGEMENT_EXT_DFBKEY'));?>
        		<?php echo $this->addIcon('tippspiel.png','index.php?option=com_sportsmanagement&view=configuration', JText::_('COM_SPORTSMANAGEMENT_EXT_PREDICTION'));?>
                <?php echo $this->addIcon('dbb.png','index.php?option=com_sportsmanagement&view=configuration', JText::_('COM_SPORTSMANAGEMENT_EXT_DBB_IMPORT'));?>
                <?php echo $this->addIcon('lmo.jpg','index.php?option=com_sportsmanagement&view=configuration', JText::_('COM_SPORTSMANAGEMENT_EXT_LMO_IMPORT'));?>
                <?php echo $this->addIcon('sis.jpg','index.php?option=com_sportsmanagement&view=configuration', JText::_('COM_SPORTSMANAGEMENT_EXT_SIS_IMPORT'));?>

			</div>
		</td>
		
	</tr>
	<!-- FOOTER INFO DASHBOARD TODO ALL PAGES -->
	<tr>
		<td style="text-align: left; width: 50%;">
			<a href="http://www.facebook.com/pages/Sportsmanager/558711710835555" target="_blank"><?php echo JText::_( "COM_SPORTSMANAGEMENT_FACEBOOK_FOLLOW" ); ?></a>
			<br/>
			<a href="https://github.com/diddipoeler/sportsmanagement" target="_blank"><?php echo JText::_( "COM_SPORTSMANAGEMENT_GITHUB_FOLLOW" ); ?></a>
			<br/>				
			<a href="http://gplus.to/JoomlaCBE" target="_blank"><?php echo JText::_( "COM_SPORTSMANAGEMENT_GPLUS_FOLLOW" ); ?></a>
			<br/>
			<a href="http://extensions.joomla.org/extensions/owner/JoomlaCBE" target="_blank"><?php echo JText::_( "COM_SPORTSMANAGEMENT_JED_FEEDBACK" ); ?></a>
		</td>
		<td style="text-align: left; width: 50%;">
			<?php echo JText::_( "COM_SPORTSMANAGEMENT_CBE_DESC" ); ?>
			<br/>
			<?php echo JText::_( "COM_SPORTSMANAGEMENT_COPYRIGHT" ); ?>: &copy; <a href="http://www.fussballineuropa.de" target="_blank">Fussball in Europa</a>
			<br/>
			<?php echo JText::_( "COM_SPORTSMANAGEMENT_VERSION" ); ?>: <?php echo JText::sprintf( 'Version: %1$s', $this->version ); ?>
		</td>
		
	</tr>
</table>
