<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage cpanel
 * @file       deafult.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div>
    <div class="cpanel-left">
        <div style="clear:both">
            <legend>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA'); ?>
            </legend>
        </div>
        <br>

        <div class="cpanel">
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('sportarten.png', 'index.php?option=com_sportsmanagement&view=sportstypes', Text::_('COM_SPORTSMANAGEMENT_D_MENU_SPORTSTYPES')); ?>
                </div>
            </div>
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('saisons.png', 'index.php?option=com_sportsmanagement&view=seasons', Text::_('COM_SPORTSMANAGEMENT_D_MENU_SEASONS')); ?>
                </div>
            </div>
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('ligen.png', 'index.php?option=com_sportsmanagement&view=leagues', Text::_('COM_SPORTSMANAGEMENT_D_MENU_LEAGUES')); ?>
                </div>
            </div>

            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('federation.png', 'index.php?option=com_sportsmanagement&view=jlextfederations', Text::_('COM_SPORTSMANAGEMENT_D_MENU_FEDERATIONS')); ?>
                </div>
            </div>
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('laender.png', 'index.php?option=com_sportsmanagement&view=jlextcountries', Text::_('COM_SPORTSMANAGEMENT_D_MENU_COUNTRIES')); ?>
                </div>
            </div>
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('landesverbaende.png', 'index.php?option=com_sportsmanagement&view=jlextassociations', Text::_('COM_SPORTSMANAGEMENT_D_MENU_ASSOCIATIONS')); ?>
                </div>
            </div>

            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('positionen.png', 'index.php?option=com_sportsmanagement&view=positions', Text::_('COM_SPORTSMANAGEMENT_D_MENU_POSITIONS')); ?>
                </div>
            </div>
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('ereignisse.png', 'index.php?option=com_sportsmanagement&view=eventtypes', Text::_('COM_SPORTSMANAGEMENT_D_MENU_EVENTS')); ?>
                </div>
            </div>
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('altersklassen.png', 'index.php?option=com_sportsmanagement&view=agegroups', Text::_('COM_SPORTSMANAGEMENT_D_MENU_AGEGROUPS')); ?>
                </div>
            </div>
        </div>
        <br>
        <div style="clear:both">
            <legend>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_PERSONAL_DATA'); ?>
            </legend>
        </div>
        <br>
        <div class="cpanel">
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('vereine.png', 'index.php?option=com_sportsmanagement&view=clubs', Text::_('COM_SPORTSMANAGEMENT_D_MENU_CLUBS')); ?>
                </div>
            </div>
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('mannschaften.png', 'index.php?option=com_sportsmanagement&view=teams', Text::_('COM_SPORTSMANAGEMENT_D_MENU_TEAMS')); ?>
                </div>
            </div>
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('personen.png', 'index.php?option=com_sportsmanagement&view=jsmpersons', Text::_('COM_SPORTSMANAGEMENT_D_MENU_PERSONS')); ?>
                </div>
            </div>
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('spielorte.png', 'index.php?option=com_sportsmanagement&view=playgrounds', Text::_('COM_SPORTSMANAGEMENT_D_MENU_VENUES')); ?>
                </div>
            </div>
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('spielfeldpositionen.png', 'index.php?option=com_sportsmanagement&view=rosterpositions', Text::_('COM_SPORTSMANAGEMENT_D_MENU_ROSTER_POSITION')); ?>
                </div>
            </div>
        </div>
        <br>
        <div style="clear:both">
            <legend>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_SPECIAL_FUNCTION'); ?>
            </legend>
        </div>
        <br>
        <div class="cpanel">
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('extrafelder.png', 'index.php?option=com_sportsmanagement&view=extrafields', Text::_('COM_SPORTSMANAGEMENT_D_MENU_EXTRAFIELDS')); ?>
                </div>
            </div>
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('statistik.png', 'index.php?option=com_sportsmanagement&view=statistics', Text::_('COM_SPORTSMANAGEMENT_D_MENU_STATISTICS')); ?>
                </div>
            </div>


            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('github.png', 'index.php?option=com_sportsmanagement&view=github', Text::_('COM_SPORTSMANAGEMENT_D_MENU_GITHUB')); ?>
                </div>
            </div>

        </div>
        <br>
        <div style="clear:both">
            <legend>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_IMPORT_EXPORT_FUNCTION'); ?>
            </legend>
        </div>
        <br>
        <div class="cpanel">
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('xmlimport.png', 'index.php?option=com_sportsmanagement&view=jlxmlimports&layout=default', Text::_('COM_SPORTSMANAGEMENT_D_MENU_XML_IMPORT')); ?>
                </div>
            </div>
            <div class="icon-wrapper">
                <!-- <div id="icon">
				<?php echo $this->addIcon('xmleditor.png', 'index.php?option=com_sportsmanagement&view=smextxmleditors&layout=default', Text::_('COM_SPORTSMANAGEMENT_D_MENU_XML_EDITOR')); ?>
			</div>    -->
            </div>

            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('imageimport.png', 'index.php?option=com_sportsmanagement&view=smimageimports&layout=default', Text::_('COM_SPORTSMANAGEMENT_D_MENU_IMAGE_IMPORT')); ?>
                </div>
            </div>

            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('joomleague.png', 'index.php?option=com_sportsmanagement&view=joomleagueimports&layout=default', Text::_('COM_SPORTSMANAGEMENT_D_MENU_JOOMLEAGUE_IMPORT')); ?>
                </div>
            </div>

        </div>
        <br>
        <div style="clear:both">
            <legend>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_INSTALL_TOOLS'); ?>
            </legend>
        </div>
        <br>
        <div class="cpanel">
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('updates.png', 'index.php?option=com_sportsmanagement&view=updates', Text::_('COM_SPORTSMANAGEMENT_D_MENU_UPDATES')); ?>
                </div>
            </div>
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('datenbanktools.png', 'index.php?option=com_sportsmanagement&view=databasetools', Text::_('COM_SPORTSMANAGEMENT_D_MENU_TOOLS')); ?>
                </div>
            </div>
            <div class="icon-wrapper">
                <div id="icon">
					<?php echo $this->addIcon('zitate.png', 'index.php?option=com_sportsmanagement&view=smquotes', Text::_('COM_SPORTSMANAGEMENT_D_MENU_QUOTES')); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="cpanel-right">
        <div style=" top:1px; background-color:#cccccc; border:4px ridge #0033ff; padding:10px; margin:5px;">
            <div style="width:48px; float:right">
                <div style="margin-bottom:5px">
                    <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_FACEBOOK_FOLLOW') ?>" target="_blank"
                       href="https://www.facebook.com/joomlasportsmanagement">
                        <img src="<?php echo Uri::base(true) ?>/components/com_sportsmanagement/assets/icons/facebook.png"
                             width="48px" height="48px"></a>
                </div>
                <div style="margin-bottom:5px">
                    <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_GITHUB_FOLLOW') ?>" target="_blank"
                       href="https://www.github.com/diddipoeler/sportsmanagement">
                        <img src="<?php echo Uri::base(true) ?>/components/com_sportsmanagement/assets/icons/github.png"
                             width="48px" height="48px"></a>
                </div>
                <div style="margin-bottom:5px">
                    <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_HELP_LINK') ?>" target="_blank"
                       href="https://jsm-wiki.diddipoeler.de/index.php/Hauptseite">
                        <img src="<?php echo Uri::base(true) ?>/components/com_sportsmanagement/assets/icons/hilfe.png"
                             width="48px" height="48px"></a>
                </div>
            </div>
            <div style="width:80%">
                <div>
                    <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SITE_LINK') ?>" target="_blank"
                       href="http://www.fussballineuropa.de">
                        <img src="<?php echo Uri::base(true) ?>/components/com_sportsmanagement/assets/icons/boxklein.png"
                             width="100%" height="auto" max-width="100%"></a>
                </div>
            </div>
        </div>
        <div style="float:left">
        </div>
		<?php
		echo $this->pane->startPane('stat-pane');
		echo $this->pane->startPanel(Text::_('COM_SPORTSMANAGEMENT_INFORMATION'), 'information');

		if (is_array($this->importData))
		{
			foreach ($this->importData as $key => $value)
			{
				?>
                <fieldset>
                    <legend><?php echo Text::_($key); ?></legend>
                    <table class='adminlist'>
                        <tr>
                            <td><?php echo $value; ?></td>
                        </tr>
                    </table>
                </fieldset>
				<?php
			}
		}

		if (is_array($this->importData2))
		{
			foreach ($this->importData2 as $key => $value)
			{
				?>
                <fieldset>
                    <legend><?php echo Text::_($key); ?></legend>
                    <table class='adminlist'>
                        <tr>
                            <td><?php echo $value; ?></td>
                        </tr>
                    </table>
                </fieldset>
				<?php
			}
		}

		echo $this->pane->endPanel();
		?>
		<?php // Echo $this->pane->startPane( 'stat-pane' );
		echo $this->pane->startPanel(Text::_('COM_SPORTSMANAGEMENT_DEVELOPER'), 'developer'); ?>
        <br/>
        <br/>
        <div style="text-align: center">
            <div style="text-align: center; width: 142px;height: 190px;float:left;">
                <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SITE_LINK') ?>" target="_blank"
                   href="http://www.fussballineuropa.de">
                    <img src="<?php echo Uri::base(true) ?>/components/com_sportsmanagement/assets/icons/ploeger_dieter.jpg"></a>
            </div>
            <div style="text-align: center; width:142px;height: 190px;float:left;">
                <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SITE_LINK') ?>" target="_blank"
                   href="http://www.esv-knittelfeld.at">
                    <img src="<?php echo Uri::base(true) ?>/components/com_sportsmanagement/assets/icons/galun-siegfried02.png"></a>
            </div>
            <div style="text-align: center; width:142px;height: 190px;float:left;">
                <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SITE_LINK') ?>" target="_blank"
                   href="http://svdoerpum.de/">
                    <img src="<?php echo Uri::base(true) ?>/components/com_sportsmanagement/assets/icons/prochnow_hauke.jpg"></a>
            </div>
            <div style="text-align: center; width:142px;height: 190px;float:left;">
                <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SITE_LINK') ?>" target="_blank" href="">
                    <img src="<?php echo Uri::base(true) ?>/components/com_sportsmanagement/assets/icons/appu-konrad.jpg"></a>
            </div>

            <div style="text-align: center; width:142px;height: 190px;float:left;">
                <a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_SITE_LINK') ?>" target="_blank" href="">
                    <img src="<?php echo Uri::base(true) ?>/components/com_sportsmanagement/assets/icons/keller-jens.jpg"></a>
            </div>
        </div>
        <br/>
		<?php echo $this->pane->endPanel();
		echo $this->pane->startPanel(Text::_('COM_SPORTSMANAGEMENT_WELCOME_TO_CBECOMMUNITY'), 'welcome'); ?>
        <div style="font-weight:700;">
			<?php echo Text::_('COM_SPORTSMANAGEMENT_GREAT_COMPONENT_MSG'); ?>
        </div>

		<?php echo $this->pane->endPanel();
		echo $this->pane->startPanel(Text::_('COM_SPORTSMANAGEMENT_STATISTICS'), 'cbe'); ?>
		<?php
		if (isset($this->cbe))
		{
			echo Text::_('COM_SPORTSMANAGEMENT_TOTAL_USERS') . ': '; ?><strong>
			<?php echo $this->cbe->total; ?></strong>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_TOTAL_BLOCKED_USERS') . ': '; ?>        <strong>
			<?php echo $this->cbe->blocked; ?></strong>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_TOTAL_APPLICATIONS_INSTALLED') . ': '; ?>        <strong>
			<?php echo $this->cbe->applications; ?></strong>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_TOTAL_ACTIVITY_UPDATES') . ': '; ?>        <strong>
			<?php echo $this->cbe->updates; ?></strong>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_PHOTOS_TOTAL') . ': '; ?>        <strong>
			<?php echo $this->cbe->photos; ?></strong>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_VIDEOS_TOTAL') . ': '; ?>        <strong>
			<?php echo $this->cbe->videos; ?></strong>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_GROUPS_T0TAL_DISCUSSIONS') . ': '; ?>        <strong>
			<?php echo $this->cbe->groupDiscussion; ?></strong>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_TOTAL_USERS') . ': '; ?>        <strong>
			<?php echo $this->cbe->total; ?></strong>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_TOTAL_BLOCKED_USERS') . ': '; ?>        <strong>
			<?php echo $this->cbe->blocked; ?></strong>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_TOTAL_APPLICATIONS_INSTALLED') . ': '; ?>        <strong>
			<?php echo $this->cbe->applications; ?></strong>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_TOTAL_ACTIVITY_UPDATES') . ': '; ?>        <strong>
			<?php echo $this->cbe->updates; ?></strong>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_PHOTOS_TOTAL') . ': '; ?>        <strong>
			<?php echo $this->cbe->photos; ?></strong>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_VIDEOS_TOTAL') . ': '; ?>        <strong>
			<?php echo $this->cbe->videos; ?></strong>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_GROUPS_T0TAL_DISCUSSIONS') . ': '; ?>        <strong>
			<?php echo $this->cbe->groupDiscussion; ?>
        </strong>
			<?php
		}

		echo $this->pane->endPanel();
		echo $this->pane->startPanel(Text::_('COM_SPORTSMANAGEMENT_GROUPS_STATISTICS'), 'groups');

		if (isset($this->groups))
		{
			?>
            <table class="adminlist">
                <tr>
					<?php echo Text::_('COM_SPORTSMANAGEMENT_GROUPS_PUBLISHED') . ': '; ?>
                    <td align="center"><strong>
							<?php echo $this->groups->published; ?></strong></td>
                </tr>
                <tr>
					<?php echo Text::_('COM_SPORTSMANAGEMENT_GROUPS_UNPUBLISHED') . ': '; ?>
                    <td align="center"><strong>
							<?php echo $this->groups->unpublished; ?></strong></td>
                </tr>
                <tr>
					<?php echo Text::_('COM_SPORTSMANAGEMENT_GROUP_CATEGORIES') . ': '; ?>
                    <td align="center"><strong>
							<?php echo $this->groups->categories; ?></strong></td>
                </tr>
            </table>
			<?PHP
		}
		?>
		<?php echo $this->pane->endPanel();
		echo $this->pane->startPanel(Text::_('COM_SPORTSMANAGEMENT_GITHUB_REQUESTS'), 'GITHUBREQUESTS'); ?>
        <table class="adminlist">
            <tr>
                <td>
                    <ul class="GH-commit <?php echo $moduleclass_sfx; ?>">
						<?php
						foreach ($this->githubrequest as $o)
						{
							?>
                            <li>
								<?php echo $o->commit->message . $o->commit->author;

								if (isset($o->commit->committer))
								{
									echo $o->commit->committer;
								}
								echo $o->commit->time; ?>
                            </li>
						<?php } ?>
                    </ul>
                </td>
            </tr>
        </table>
		<?php echo $this->pane->endPanel();
		echo $this->pane->startPanel(Text::_('COM_SPORTSMANAGEMENT_WELCOME_TO_FORUM'), 'FORUM'); ?>
        <table class="adminlist">
            <tr>
                <td> coming soon</td>
            </tr>
        </table>
		<?php echo $this->pane->endPanel();
		echo $this->pane->endPane(); ?>
    </div>
</div>
<!-- FOOTER INFO DASHBOARD TODO ALL PAGES -->

<div>
	<?PHP
	echo $this->loadTemplate('footer');
	?>
</div>
