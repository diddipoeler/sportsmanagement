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
				<?php echo $this->addIcon('vereine.png','index.php?option=com_sportsmanagement&view=clubs', JText::_('COM_SPORTSMANAGEMENT_D_MENU_CLUBS'));?>
				<?php echo $this->addIcon('mannschaften.png','index.php?option=com_sportsmanagement&view=teams', JText::_('COM_SPORTSMANAGEMENT_D_MENU_TEAMS'));?>
				<?php echo $this->addIcon('ligen.png','index.php?option=com_sportsmanagement&view=leagues', JText::_('COM_SPORTSMANAGEMENT_D_MENU_LEAGUES'));?>
				<?php echo $this->addIcon('saisons.png','index.php?option=com_sportsmanagement&view=seasons', JText::_('COM_SPORTSMANAGEMENT_D_MENU_SEASONS'));?>
				<?php echo $this->addIcon('spielorte.png','index.php?option=com_sportsmanagement&view=playgrounds', JText::_('COM_SPORTSMANAGEMENT_D_MENU_VENUES'));?>
				<?php echo $this->addIcon('positionen.png','index.php?option=com_sportsmanagement&view=positions', JText::_('COM_SPORTSMANAGEMENT_D_MENU_POSITIONS'));?>
				<?php echo $this->addIcon('statistik.png','index.php?option=com_sportsmanagement&view=statistics', JText::_('COM_SPORTSMANAGEMENT_D_MENU_STATISTICS'));?>
				<?php echo $this->addIcon('sportarten.png','index.php?option=com_sportsmanagement&view=sportstypes', JText::_('COM_SPORTSMANAGEMENT_D_MENU_SPORTSTYPES'));?>
				<?php echo $this->addIcon('ereignisse.png','index.php?option=com_sportsmanagement&view=eventtypes', JText::_('COM_SPORTSMANAGEMENT_D_MENU_EVENTS'));?>
        <?php echo $this->addIcon('personen.png','index.php?option=com_sportsmanagement&view=persons', JText::_('COM_SPORTSMANAGEMENT_D_MENU_PERSONS'));?>
        <?php echo $this->addIcon('altersklassen.png','index.php?option=com_sportsmanagement&view=agegroups', JText::_('COM_SPORTSMANAGEMENT_D_MENU_AGEGROUPS'));?>
        <?php echo $this->addIcon('laender.png','index.php?option=com_sportsmanagement&view=jlextcountries', JText::_('COM_SPORTSMANAGEMENT_D_MENU_COUNTRIES'));?>
        <?php echo $this->addIcon('landesverbaende.png','index.php?option=com_sportsmanagement&view=jlextassociations', JText::_('COM_SPORTSMANAGEMENT_D_MENU_ASSOCIATIONS'));?>
        <?php echo $this->addIcon('extrafelder.png','index.php?option=com_sportsmanagement&view=extrafields', JText::_('COM_SPORTSMANAGEMENT_D_MENU_EXTRAFIELDS'));?>
				
		<?php echo $this->addIcon('xmlimport.png','index.php?option=com_sportsmanagement&view=jlxmlimports&layout=default', JText::_('COM_SPORTSMANAGEMENT_D_MENU_XML_IMPORT'));?>
                <?php echo $this->addIcon('datenbanktools.png','index.php?option=com_sportsmanagement&view=databasetools', JText::_('COM_SPORTSMANAGEMENT_D_MENU_TOOLS'));?>
                <?php echo $this->addIcon('updates.png','index.php?option=com_sportsmanagement&view=updates', JText::_('COM_SPORTSMANAGEMENT_D_MENU_UPDATES'));?>
                <?php echo $this->addIcon('spielfeldpositionen.png','index.php?option=com_sportsmanagement&view=rosterpositions', JText::_('COM_SPORTSMANAGEMENT_D_MENU_ROSTER_POSITION'));?>
                
<?php echo $this->addIcon('xmleditor.png','index.php?option=com_sportsmanagement&view=smextxmleditors&layout=default', JText::_('COM_SPORTSMANAGEMENT_D_MENU_XML_EDITOR'));?>
                		
			</div>
		</td>
		<td width="45%" valign="top">
		
			<div style=" top:1px;  width:auto; background-color:#FFFFE0; border:4px ridge #46CD29; padding:10px; margin:5px">
				<table width="99%">
					<tr align="center">
						<td width="50%" >
							<div style="text-align: center;">
							<?php echo $this->addIcon('logo_transparent.png','http://www.fussballineuropa.de', JText::_('COM_SPORTSMANAGEMENT_SITE_LINK'), true); ?>
							</div>
						</td>
                        
						<td width="50%" >
							<div style="text-align: center;">
							<?php echo $this->addIcon('cbehelp.png','http://joomleaguewiki.grammatikas-grill.de/index.php/Hauptseite', JText::_('COM_SPORTSMANAGEMENT_HELP_LINK'), true); ?>
							</div>
						</td>					
					</tr>
				</table>
			</div>			
		
			<?php
				echo $this->pane->startPane( 'stat-pane' );
				
                                echo $this->pane->startPanel( JText::_('COM_SPORTSMANAGEMENT_DEVELOPER') , 'developer' );
			?>
			<table class="adminlist">
				<tr>
				<td width="5" >
							<div style="text-align: center;">
							<?php echo $this->addIcon('ploeger_dieter.jpg','http://www.fussballineuropa.de', JText::_('COM_SPORTSMANAGEMENT_SITE_LINK'), true); ?>
							</div>
                            
                            <div style="text-align: center;">
							<?php echo $this->addIcon('galun-siegfried02.png','http://www.esv-knittelfeld.at', JText::_('COM_SPORTSMANAGEMENT_SITE_LINK'), true); ?>
							</div>
                            
                            <div style="text-align: center;">
							<?php echo $this->addIcon('prochnow_hauke.jpg','http://svdoerpum.de/', JText::_('COM_SPORTSMANAGEMENT_SITE_LINK'), true); ?>
							</div>
						</td>	
				</tr>
			</table>
			<?php
				echo $this->pane->endPanel();
                
                
                echo $this->pane->startPanel( JText::_('COM_SPORTSMANAGEMENT_WELCOME_TO_CBECOMMUNITY') , 'welcome' );
			?>
			<table class="adminlist">
				<tr>
					<td>
						<div style="font-weight:700;">
							<?php echo JText::_('COM_SPORTSMANAGEMENT_GREAT_COMPONENT_MSG');?>
						</div>
						<p>
							
						</p>
						<p>
							
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
		<td style="width: 33%;">
			<a href="http://www.facebook.com/pages/Sportsmanager/558711710835555" target="_blank"><img src="/administrator/components/com_sportsmanagement/assets/icons/facebook.png" alt="facebook" /><?php echo JText::_('COM_SPORTSMANAGEMENT_FACEBOOK_FOLLOW'); ?></a>
			
			<a href="https://github.com/diddipoeler/sportsmanagement" target="_blank"><img src="/administrator/components/com_sportsmanagement/assets/icons/github.png" alt="github" /><?php echo JText::_( "COM_SPORTSMANAGEMENT_GITHUB_FOLLOW" ); ?></a>
			
		</td>
		<td style="text-align: center; width: 33%;">
			<?php echo JText::_( "COM_SPORTSMANAGEMENT_DESC" ); ?>
			<br/>
			<?php echo JText::_( "COM_SPORTSMANAGEMENT_COPYRIGHT" ); ?>: &copy; <a href="http://www.fussballineuropa.de" target="_blank">Fussball in Europa</a>
			<br/>
			<?php echo JText::_( "COM_SPORTSMANAGEMENT_VERSION" ); ?>: <?php echo JText::sprintf( 'Version: %1$s', $this->version ); ?>
		</td>
		<td style="text-align: right; width: 33%;">
		</td>
	</tr>
</table>
