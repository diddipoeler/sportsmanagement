<?php
/**
 * @category	Core
 * @package		
 * @copyright (C) 2013
 * @license		GNU/GPL, see LICENSE.php
 */
 
;##################################################################
;/* CBE
;* Modified by 
;*  
;* email: info@joomla-cbe.de
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
		<td width="55%" valign="top">
			<div id="cpanel">
				<?php echo $this->addIcon('configuration.gif','index.php?option=com_sportsmanagement&view=configuration', JText::_('COM_SPORTSMANAGEMENT_CONFIGURATION'));?>
				<?php echo $this->addIcon('edit-user.gif','index.php?option=com_sportsmanagement&view=users', JText::_('COM_SPORTSMANAGEMENT_USERS'));?>
				<?php echo $this->addIcon('multiprofile.gif','index.php?option=com_sportsmanagement&view=multiprofile', JText::_('COM_SPORTSMANAGEMENT_CONFIGURATION_MULTIPROFILES'));?>
				<?php echo $this->addIcon('profiles.gif','index.php?option=com_sportsmanagement&view=profiles', JText::_('COM_SPORTSMANAGEMENT_CUSTOM_PROFILES'));?>
				<?php echo $this->addIcon('groups.gif','index.php?option=com_sportsmanagement&view=groups', JText::_('COM_SPORTSMANAGEMENT_GROUPS'));?>
				<?php echo $this->addIcon('groupcategories.gif','index.php?option=com_sportsmanagement&view=groupcategories', JText::_('COM_SPORTSMANAGEMENT_GROUP_CATEGORIES'));?>
				<?php echo $this->addIcon('videos.gif','index.php?option=com_sportsmanagement&view=videoscategories', JText::_('COM_SPORTSMANAGEMENT_VIDEO_CATEGORIES'));?>
				<?php echo $this->addIcon('templates.gif','index.php?option=com_sportsmanagement&view=templates', JText::_('COM_SPORTSMANAGEMENT_TEMPLATES'));?>
				<?php echo $this->addIcon('applications.gif','index.php?option=com_sportsmanagement&view=applications', JText::_('COM_SPORTSMANAGEMENT_APPLICATIONS'));?>
				<?php echo $this->addIcon('event.gif','index.php?option=com_sportsmanagement&view=events', JText::_('COM_SPORTSMANAGEMENT_EVENTS'));?>
				<?php echo $this->addIcon('eventcategories.gif','index.php?option=com_sportsmanagement&view=eventcategories', JText::_('COM_SPORTSMANAGEMENT_EVENT_CATEGORIES'));?>
				<?php echo $this->addIcon('mailq.gif','index.php?option=com_sportsmanagement&view=mailqueue', JText::_('COM_SPORTSMANAGEMENT_MAIL_QUEUE'));?>
				<?php echo $this->addIcon('reports.gif','index.php?option=com_sportsmanagement&view=reports', JText::_('COM_SPORTSMANAGEMENT_REPORTINGS')); ?>
				<?php echo $this->addIcon('userpoints.gif','index.php?option=com_sportsmanagement&view=userpoints', JText::_('COM_SPORTSMANAGEMENT_USERPOINTS')); ?>
				<?php echo $this->addIcon('message.gif','index.php?option=com_sportsmanagement&view=messaging', JText::_('COM_SPORTSMANAGEMENT_MASSMESSAGING')); ?>
				<?php echo $this->addIcon('activities.gif','index.php?option=com_sportsmanagement&view=activities', JText::_('COM_SPORTSMANAGEMENT_ACTIVITIES')); ?>
				<?php echo $this->addIcon('memberlist.gif','index.php?option=com_sportsmanagement&view=memberlist', JText::_('COM_SPORTSMANAGEMENT_MEMBERLIST')); ?>
				<?php echo $this->addIcon('about.gif','index.php?option=com_sportsmanagement&view=about', JText::_('COM_SPORTSMANAGEMENT_ABOUT')); ?>
				<?php echo $this->addIcon('help.gif','http://www.joomla-cbe.de/cbe-online-doukumentation.html', JText::_('COM_SPORTSMANAGEMENT_HELP'), true ); ?>
			</div>
		</td>
		<td width="45%" valign="top">
		
			<div style=" top:1px;  width:auto; background-color:#FFFFE0; border:4px ridge #46CD29; padding:10px; margin:5px">
				<table width="99%">
					<tr align="center">
						<td width="50%" >
							<div style="text-align: center;">
							<?php echo $this->addIcon('cbehome.png','http://www.joomla-cbe.de', JText::_('COM_SPORTSMANAGEMENT_DASHBOARD_CBESITE_LINK'), true); ?>
							</div>
						</td>
						<td width="50%" >
							<div style="text-align: center;">
							<?php echo $this->addIcon('cbehelp.png','http://www.joomla-cbe.de/cbe-online-doukumentation.html', JText::_('COM_SPORTSMANAGEMENT_DASHBOARD_HELP_LINK'), true); ?>
							</div>
						</td>					
					</tr>
				</table>
			</div>			

		
			<?php
				echo $this->pane->startPane( 'stat-pane' );
				echo $this->pane->startPanel( JText::_('COM_SPORTSMANAGEMENT_WELCOME_TO_CBECOMMUNITY') , 'welcome' );
			?>
			<table class="adminlist">
				<tr>
					<td>
						<div style="font-weight:700;">
							<?php echo JText::_('COM_SPORTSMANAGEMENT_GREAT_COMPONENT_MSG');?>
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
				echo $this->pane->startPanel( JText::_('COM_SPORTSMANAGEMENT_STATISTICS') , 'cbe' );
			?>
				<table class="adminlist">
					<tr>
						<td>
							<?php echo JText::_( 'COM_SPORTSMANAGEMENT_TOTAL_USERS' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->cbe->total; ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'COM_SPORTSMANAGEMENT_TOTAL_BLOCKED_USERS' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->cbe->blocked; ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'COM_SPORTSMANAGEMENT_TOTAL_APPLICATIONS_INSTALLED' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->cbe->applications; ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'COM_SPORTSMANAGEMENT_TOTAL_ACTIVITY_UPDATES' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->cbe->updates; ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'COM_SPORTSMANAGEMENT_PHOTOS_TOTAL' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->cbe->photos; ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'COM_SPORTSMANAGEMENT_VIDEOS_TOTAL' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->cbe->videos; ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'COM_SPORTSMANAGEMENT_GROUPS_T0TAL_DISCUSSIONS' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->cbe->groupDiscussion; ?></strong>
						</td>
					</tr>
				</table>

			<?php
				echo $this->pane->endPanel();
				echo $this->pane->startPanel( JText::_('COM_SPORTSMANAGEMENT_GROUPS_STATISTICS'), 'groups' );
			?>
				<table class="adminlist">
					<tr>
						<td>
							<?php echo JText::_( 'COM_SPORTSMANAGEMENT_GROUPS_PUBLISHED' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->groups->published; ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'COM_SPORTSMANAGEMENT_GROUPS_UNPUBLISHED' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->groups->unpublished; ?></strong>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_( 'COM_SPORTSMANAGEMENT_GROUP_CATEGORIES' ).': '; ?>
						</td>
						<td align="center">
							<strong><?php echo $this->groups->categories; ?></strong>
						</td>
					</tr>
				</table>
			<?php
				echo $this->pane->endPanel();
				echo $this->pane->startPanel( JText::_('COM_SPORTSMANAGEMENT_WELCOME_TO_CBENEWS') , 'NEWS' );
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
				echo $this->pane->startPanel( JText::_('COM_SPORTSMANAGEMENT_WELCOME_TO_CBEFORUM') , 'FORUM' );
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
			<a href="http://www.facebook.com/pages/Sportsmanager/558711710835555" target="_blank"><?php echo JText::_( "COM_SPORTSMANAGEMENT_FACEBOOK_FOLLOW" ); ?></a>
			<br/>
			<a href="https://github.com/diddipoeler/sportsmanagement" target="_blank"><?php echo JText::_( "COM_SPORTSMANAGEMENT_GITHUB_FOLLOW" ); ?></a>
			<br/>				
			<a href="http://gplus.to/JoomlaCBE" target="_blank"><?php echo JText::_( "COM_SPORTSMANAGEMENT_GPLUS_FOLLOW" ); ?></a>
			<br/>
			<a href="http://extensions.joomla.org/extensions/owner/JoomlaCBE" target="_blank"><?php echo JText::_( "COM_SPORTSMANAGEMENT_JED_FEEDBACK" ); ?></a>
		</td>
		<td style="text-align: center; width: 33%;">
			<?php echo JText::_( "COM_SPORTSMANAGEMENT_CBE_DESC" ); ?>
			<br/>
			<?php echo JText::_( "COM_SPORTSMANAGEMENT_COPYRIGHT" ); ?>: &copy; <a href="http://www.fussballineuropa.de" target="_blank">Fussball in Europa</a>
			<br/>
			<?php echo JText::_( "COM_SPORTSMANAGEMENT_VERSION" ); ?>: <?php echo JText::sprintf( 'Version: %1$s', $this->version ); ?>
		</td>
		<td style="text-align: right; width: 33%;">
		</td>
	</tr>
</table>
