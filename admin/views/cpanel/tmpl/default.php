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
		<td width="55%" valign="top">
			<div id="cpanel">
				<?php echo $this->addIcon('icon-48-clubs.png','index.php?option=com_sportsmanagement&view=clubs', JText::_('COM_SPORTSMANAGEMENT_D_MENU_CLUBS'));?>
				<?php echo $this->addIcon('icon-48-teams.png','index.php?option=com_sportsmanagement&view=users', JText::_('COM_SPORTSMANAGEMENT_D_MENU_TEAMS'));?>
				<?php echo $this->addIcon('icon-48-leagues.png','index.php?option=com_sportsmanagement&view=multiprofile', JText::_('COM_SPORTSMANAGEMENT_D_MENU_LEAGUES'));?>
				<?php echo $this->addIcon('icon-48-seasons.png','index.php?option=com_sportsmanagement&view=profiles', JText::_('COM_SPORTSMANAGEMENT_D_MENU_SEASONS'));?>
				<?php echo $this->addIcon('icon-48-playground.png','index.php?option=com_sportsmanagement&view=groups', JText::_('COM_SPORTSMANAGEMENT_D_MENU_VENUES'));?>
				<?php echo $this->addIcon('icon-48-positions.png','index.php?option=com_sportsmanagement&view=groupcategories', JText::_('COM_SPORTSMANAGEMENT_D_MENU_POSITIONS'));?>
				<?php echo $this->addIcon('icon-48-statistics.png','index.php?option=com_sportsmanagement&view=videoscategories', JText::_('COM_SPORTSMANAGEMENT_D_MENU_STATISTICS'));?>
				<?php echo $this->addIcon('icon-48-sportstypes.png','index.php?option=com_sportsmanagement&view=templates', JText::_('COM_SPORTSMANAGEMENT_D_MENU_SPORTSTYPES'));?>
				<?php echo $this->addIcon('icon-48-events.png','index.php?option=com_sportsmanagement&view=applications', JText::_('COM_SPORTSMANAGEMENT_D_MENU_EVENTS'));?>
                <?php echo $this->addIcon('groups.gif','index.php?option=com_sportsmanagement&view=applications', JText::_('COM_SPORTSMANAGEMENT_D_MENU_PERSONS'));?>
				
				
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
                
				echo $this->pane->startPanel( JText::_('COM_SPORTSMANAGEMENT_GITHUB_REQUESTS') , 'GITHUBREQUESTS' );
			?>
			<table class="adminlist">
				<tr>
					<td>

<ul class="GH-commit<?php echo $moduleclass_sfx;?>">
	<?php foreach ($this->githubrequest  as $o) { ?>
	<li><?php echo $o->commit->message.$o->commit->author;
	if (isset($o->commit->committer)) {
		echo $o->commit->committer;
	}
	echo $o->commit->time; ?>
	</li>
	<?php } ?>
</ul>
						
					</td>
				</tr>
			</table>
			<?php
				echo $this->pane->endPanel();	
				echo $this->pane->startPanel( JText::_('COM_SPORTSMANAGEMENT_WELCOME_TO_FORUM') , 'FORUM' );
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
