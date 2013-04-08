<?php
/**
 * @category	Core
 * @package		JomSocial
 * @copyright (C) 2008 by Slashes & Dots Sdn Bhd - All rights reserved!
 * @license		GNU/GPL, see LICENSE.php
 */
 
;##################################################################
;/* CBE
;* Modified by Joomla-CBE Team
;* http://www.joomla-cbe.de.com 
;* email: info@joomla-cbe.de
;* date: 2012
;* Release: 1.7
;* License : http://www.gnu.org/copyleft/gpl.html GNU/GPL 
;*/
################################################################### 
 
// Disallow direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<table width="100%" border="0">
	<tr>
		<td width="55%" valign="top">
			<div id="cpanel">
				<?php echo $this->addIcon('configuration.gif','index.php?option=com_cbe&view=configuration', JText::_('COM_CBE_CONFIGURATION'));?>
				<?php echo $this->addIcon('edit-user.gif','index.php?option=com_cbe&view=users', JText::_('COM_CBE_USERS'));?>
				<?php echo $this->addIcon('multiprofile.gif','index.php?option=com_cbe&view=multiprofile', JText::_('COM_CBE_CONFIGURATION_MULTIPROFILES'));?>
				<?php echo $this->addIcon('profiles.gif','index.php?option=com_cbe&view=profiles', JText::_('COM_CBE_CUSTOM_PROFILES'));?>
				<?php echo $this->addIcon('groups.gif','index.php?option=com_cbe&view=groups', JText::_('COM_CBE_GROUPS'));?>
				<?php echo $this->addIcon('groupcategories.gif','index.php?option=com_cbe&view=groupcategories', JText::_('COM_CBE_GROUP_CATEGORIES'));?>
				<?php echo $this->addIcon('videos.gif','index.php?option=com_cbe&view=videoscategories', JText::_('COM_CBE_VIDEO_CATEGORIES'));?>
				<?php echo $this->addIcon('templates.gif','index.php?option=com_cbe&view=templates', JText::_('COM_CBE_TEMPLATES'));?>
				<?php echo $this->addIcon('applications.gif','index.php?option=com_cbe&view=applications', JText::_('COM_CBE_APPLICATIONS'));?>
				<?php echo $this->addIcon('event.gif','index.php?option=com_cbe&view=events', JText::_('COM_CBE_EVENTS'));?>
				<?php echo $this->addIcon('eventcategories.gif','index.php?option=com_cbe&view=eventcategories', JText::_('COM_CBE_EVENT_CATEGORIES'));?>
				<?php echo $this->addIcon('mailq.gif','index.php?option=com_cbe&view=mailqueue', JText::_('COM_CBE_MAIL_QUEUE'));?>
				<?php echo $this->addIcon('reports.gif','index.php?option=com_cbe&view=reports', JText::_('COM_CBE_REPORTINGS')); ?>
				<?php echo $this->addIcon('userpoints.gif','index.php?option=com_cbe&view=userpoints', JText::_('COM_CBE_USERPOINTS')); ?>
				<?php echo $this->addIcon('message.gif','index.php?option=com_cbe&view=messaging', JText::_('COM_CBE_MASSMESSAGING')); ?>
				<?php echo $this->addIcon('activities.gif','index.php?option=com_cbe&view=activities', JText::_('COM_CBE_ACTIVITIES')); ?>
				<?php echo $this->addIcon('memberlist.gif','index.php?option=com_cbe&view=memberlist', JText::_('COM_CBE_MEMBERLIST')); ?>
				<?php echo $this->addIcon('about.gif','index.php?option=com_cbe&view=about', JText::_('COM_CBE_ABOUT')); ?>
				<?php echo $this->addIcon('help.gif','http://www.joomla-cbe.de/cbe-online-doukumentation.html', JText::_('COM_CBE_HELP'), true ); ?>
			</div>
		</td>
		<td width="45%" valign="top">
		
			<div style=" top:1px;  width:auto; background-color:#FFFFE0; border:4px ridge #46CD29; padding:10px; margin:5px">
				<table width="99%">
					<tr align="center">
						<td width="50%" >
							<div style="text-align: center;">
							<?php echo $this->addIcon('cbehome.png','http://www.joomla-cbe.de', JText::_('COM_CBE_DASHBOARD_CBESITE_LINK'), true); ?>
							</div>
						</td>
						<td width="50%" >
							<div style="text-align: center;">
							<?php echo $this->addIcon('cbehelp.png','http://www.joomla-cbe.de/cbe-online-doukumentation.html', JText::_('COM_CBE_DASHBOARD_HELP_LINK'), true); ?>
							</div>
						</td>					
					</tr>
				</table>
			</div>			

		
			<?php
				echo $this->pane->startPane( 'stat-pane' );
				echo $this->pane->startPanel( JText::_('COM_CBE_WELCOME_TO_CBECOMMUNITY') , 'welcome' );
			?>
			<table class="adminlist">
				<tr>
					<td>
						<div style="font-weight:700;">
							<?php echo JText::_('COM_CBE_GREAT_COMPONENT_MSG');?>
						</div>
						<p>
							If you require professional support just head on to the forums at 
							<a href="http://www.joomla-cbe.de/forum/" target="_blank">
							http://www.joomla-cbe.de/forum/
							</a>
							For developers, you can browse through the documentations at 
							<a href="http://www.joomla-cbe.de/cbe-online-doukumentation.html" target="_blank">http://www.joomla-cbe.de/cbe-online-doukumentation.html</a>
						</p>
						<p>
							If you found any bugs, just drop us an email at bugs@joomla-cbe.de
						</p>
					</td>
				</tr>
			</table>
			<?php
				echo $this->pane->endPanel();
				echo $this->pane->startPanel( JText::_('COM_CBE_STATISTICS') , 'cbe' );
			?>
				<table class="adminlist">
					<tr>
						<td>
							<?php echo JText::_( 'COM_CBE_TOTAL_USERS' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->cbe->total; ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'COM_CBE_TOTAL_BLOCKED_USERS' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->cbe->blocked; ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'COM_CBE_TOTAL_APPLICATIONS_INSTALLED' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->cbe->applications; ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'COM_CBE_TOTAL_ACTIVITY_UPDATES' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->cbe->updates; ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'COM_CBE_PHOTOS_TOTAL' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->cbe->photos; ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'COM_CBE_VIDEOS_TOTAL' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->cbe->videos; ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'COM_CBE_GROUPS_T0TAL_DISCUSSIONS' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->cbe->groupDiscussion; ?></strong>
						</td>
					</tr>
				</table>

			<?php
				echo $this->pane->endPanel();
				echo $this->pane->startPanel( JText::_('COM_CBE_GROUPS_STATISTICS'), 'groups' );
			?>
				<table class="adminlist">
					<tr>
						<td>
							<?php echo JText::_( 'COM_CBE_GROUPS_PUBLISHED' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->groups->published; ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'COM_CBE_GROUPS_UNPUBLISHED' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->groups->unpublished; ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'COM_CBE_GROUP_CATEGORIES' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->groups->categories; ?></strong>
						</td>
					</tr>
				</table>
			<?php
				echo $this->pane->endPanel();
				echo $this->pane->startPanel( JText::_('COM_CBE_WELCOME_TO_CBENEWS') , 'NEWS' );
			?>
			<table class="adminlist">
				<tr>
					<td>

					coming soon
						
					</td>
				</tr>
			</table>
			<?php
				echo $this->pane->endPanel();	
				echo $this->pane->startPanel( JText::_('COM_CBE_WELCOME_TO_CBEFORUM') , 'FORUM' );
			?>
			<table class="adminlist">
				<tr>
					<td>

					coming soon
						
					</td>
				</tr>
			</table>
			<?php
				echo $this->pane->endPanel();	
								
				echo $this->pane->endPane();
			?>
		</td>
	</tr>
	<!-- FOOTER INFO DASHBOARD TODO ALL PAGES -->
	<tr>
		<td style="text-align: left; width: 33%;">
			<a href="http://www.facebook.com/pages/Joomla-CBE/115372148571797" target="_blank"><?php echo JText::_( "COM_CBE_FACEBOOK_FOLLOW" ); ?></a>
			<br/>
			<a href="http://twitter.com/Joomla_CBE" target="_blank"><?php echo JText::_( "COM_CBE_TWITTER_FOLLOW" ); ?></a>
			<br/>				
			<a href="http://gplus.to/JoomlaCBE" target="_blank"><?php echo JText::_( "COM_CBE_GPLUS_FOLLOW" ); ?></a>
			<br/>
			<a href="http://extensions.joomla.org/extensions/owner/JoomlaCBE" target="_blank"><?php echo JText::_( "COM_CBE_JED_FEEDBACK" ); ?></a>
		</td>
		<td style="text-align: center; width: 33%;">
			<?php echo JText::_( "COM_CBE_CBE_DESC" ); ?>
			<br/>
			<?php echo JText::_( "COM_CBE_COPYRIGHT" ); ?>: &copy; <a href="http://www.joomla-cbe.de" target="_blank">Joomla-CBE</a>
			<br/>
			<?php echo JText::_( "COM_CBE_VERSION" ); ?>: <?php echo JText::sprintf( 'Version: %1$s', $this->version ); ?>
		</td>
		<td style="text-align: right; width: 33%;">
		</td>
	</tr>
</table>
