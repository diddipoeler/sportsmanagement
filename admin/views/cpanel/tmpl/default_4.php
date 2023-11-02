<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage cpanel
 * @file       default_4.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="row">
<?php
echo $this->loadTemplate('start_menu');
?>
	<!--
    <div id="j-sidebar-container" class="col-md-2">
		<?php echo $this->sidebar; ?>
    </div>
-->
    <div class="col-md-10">
    <?php
            if ( ComponentHelper::getParams('com_sportsmanagement')->get('start_install_helper') && !$this->sporttypes )
            {
            echo $this->loadTemplate('startinstallhelper');    
                
            }
            ?>

		<div class="col-md-12 quickicons-for-site_quickicon module-wrapper" style="grid-row-end: span 30;">
			<div class="card mb-3">
				<div class="card-header">                
					<h4>
					<span class="fa fa-2x fa-lightbulb" aria-hidden="true"></span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA') ?>
					</h4>
				</div>
				<nav class="quick-icons px-3 pb-3" aria-label="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA') ?>">
					<ul class="nav flex-wrap" style="grid-gap: 0.5rem; grid-template-columns: repeat(auto-fit,minmax(120px,1fr));">
						<li class="quickicon quickicon-single">
							<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_SPORTSTYPES') ?>" 
							href="index.php?option=com_sportsmanagement&view=sportstypes">
								<div class="quickicon-icon">
									<img src="components/com_sportsmanagement/assets/icons/sportarten.png" style="background-color:white;"
										alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_SPORTSTYPES') ?>"/>
								</div>
								<div class="quickicon-name d-flex align-items-end">
									<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_SPORTSTYPES') ?>
								</div>
							</a>
						</li>
						<li class="quickicon quickicon-single">
							<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_SEASONS') ?>" 
							href="index.php?option=com_sportsmanagement&view=seasons">
								<div class="quickicon-icon">
									<img src="components/com_sportsmanagement/assets/icons/saisons.png" style="background-color:white;"
										alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_SEASONS') ?>"/>
								</div>
								<div class="quickicon-name d-flex align-items-end">
									<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_SEASONS') ?>
								</div>
							</a>
						</li>
						<li class="quickicon quickicon-single">
							<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_LEAGUES') ?>" 
							href="index.php?option=com_sportsmanagement&view=leagues">
								<div class="quickicon-icon">
									<img src="components/com_sportsmanagement/assets/icons/ligen.png" style="background-color:white;"
										alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_LEAGUES') ?>"/>
								</div>
								<div class="quickicon-name d-flex align-items-end">
									<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_LEAGUES') ?>
								</div>
							</a>
						</li>
						<?PHP
						if ($this->params->get('show_option_federation', 1))
						{
						?>
							<li class="quickicon quickicon-single">
								<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_FEDERATIONS') ?>" 
								href="index.php?option=com_sportsmanagement&view=jlextfederations">
									<div class="quickicon-icon">
										<img src="components/com_sportsmanagement/assets/icons/federation.png" style="background-color:white;"
											alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_FEDERATIONS') ?>"/>
									</div>
									<div class="quickicon-name d-flex align-items-end">
										<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_FEDERATIONS') ?>
									</div>
								</a>
							</li>
						<?PHP
						}
						?>
						<li class="quickicon quickicon-single">
							<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_COUNTRIES') ?>" 
							href="index.php?option=com_sportsmanagement&view=jlextcountries">
								<div class="quickicon-icon">
									<img src="components/com_sportsmanagement/assets/icons/laender.png" style="background-color:white;"
										alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_COUNTRIES') ?>"/>
								</div>
								<div class="quickicon-name d-flex align-items-end">
									<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_COUNTRIES') ?>
								</div>
							</a>
						</li>
						<?PHP
						if ($this->params->get('show_option_association', 1))
						{
							?>
							<li class="quickicon quickicon-single">
								<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_ASSOCIATIONS') ?>" 
								href="index.php?option=com_sportsmanagement&view=jlextassociations">
									<div class="quickicon-icon">
										<img src="components/com_sportsmanagement/assets/icons/landesverbaende.png" style="background-color:white;"
											alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_ASSOCIATIONS') ?>"/>
									</div>
									<div class="quickicon-name d-flex align-items-end">
										<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_ASSOCIATIONS') ?>
									</div>
								</a>
							</li>
						<?PHP
						}
						if ($this->params->get('show_option_position', 1))
						{
						?>										
							<li class="quickicon quickicon-single">
								<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_POSITIONS') ?>" 
								href="index.php?option=com_sportsmanagement&view=positions">
									<div class="quickicon-icon">
										<img src="components/com_sportsmanagement/assets/icons/positionen.png" style="background-color:white;"
											alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_POSITIONS') ?>"/>
									</div>
									<div class="quickicon-name d-flex align-items-end">
										<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_POSITIONS') ?>
									</div>
								</a>
							</li>
						<?PHP
						}
						if ($this->params->get('show_option_eventtypes', 1))
						{
						?>
							<li class="quickicon quickicon-single">
								<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_EVENTS') ?>" 
								href="index.php?option=com_sportsmanagement&view=eventtypes">
									<div class="quickicon-icon">
										<img src="components/com_sportsmanagement/assets/icons/ereignisse.png" style="background-color:white;"
											alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_EVENTS') ?>"/>
									</div>
									<div class="quickicon-name d-flex align-items-end">
										<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_EVENTS') ?>
									</div>
								</a>
							</li>
						<?PHP
						}
						if ($this->params->get('show_option_agegroup', 1))
						{
						?>
							<li class="quickicon quickicon-single">
								<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_AGEGROUPS') ?>" 
								href="index.php?option=com_sportsmanagement&view=agegroups">
									<div class="quickicon-icon">
										<img src="components/com_sportsmanagement/assets/icons/altersklassen.png" style="background-color:white;"
											alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_AGEGROUPS') ?>"/>
									</div>
									<div class="quickicon-name d-flex align-items-end">
										<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_AGEGROUPS') ?>
									</div>
								</a>
							</li>
						<?PHP
						}
						?>
					</ul>
				</nav>
			</div>
		</div>

		<div class="col-md-12 quickicons-for-site_quickicon module-wrapper" style="grid-row-end: span 30;">
			<div class="card mb-3">
				<div class="card-header">                
					<h4>
					<span class="fa fa-2x fa-users" aria-hidden="true"></span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_PERSONAL_DATA') ?>
					</h4>
				</div>
				<nav class="quick-icons px-3 pb-3" aria-label="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_PERSONAL_DATA') ?>">
					<ul class="nav flex-wrap" style="grid-gap: 0.5rem; grid-template-columns: repeat(auto-fit,minmax(120px,1fr));">
						<li class="quickicon quickicon-single">
							<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_CLUBS') ?>" 
							href="index.php?option=com_sportsmanagement&view=clubs">
								<div class="quickicon-icon">
									<img src="components/com_sportsmanagement/assets/icons/vereine.png" style="background-color:white;"
										alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_CLUBS') ?>"/>
								</div>
								<div class="quickicon-name d-flex align-items-end">
									<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_CLUBS') ?>
								</div>
							</a>
						</li>
						<li class="quickicon quickicon-single">
							<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TEAMS') ?>" 
							href="index.php?option=com_sportsmanagement&view=teams">
								<div class="quickicon-icon">
									<img src="components/com_sportsmanagement/assets/icons/mannschaften.png" style="background-color:white;"
										alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TEAMS') ?>"/>
								</div>
								<div class="quickicon-name d-flex align-items-end">
									<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TEAMS') ?>
								</div>
							</a>
						</li>
						<?PHP
						if ($this->params->get('show_option_person', 1))
						{
						?>
							<li class="quickicon quickicon-single">
								<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_PERSONS') ?>" 
								href="index.php?option=com_sportsmanagement&view=players">
									<div class="quickicon-icon">
										<img src="components/com_sportsmanagement/assets/icons/personen.png" style="background-color:white;"
											alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_PERSONS') ?>"/>
									</div>
									<div class="quickicon-name d-flex align-items-end">
										<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_PERSONS') ?>
									</div>
								</a>
							</li>
						<?PHP
						}
						if ($this->params->get('show_option_playground', 1))
						{
							?>
							<li class="quickicon quickicon-single">
								<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_VENUES') ?>" 
								href="index.php?option=com_sportsmanagement&view=playgrounds">
									<div class="quickicon-icon">
										<img src="components/com_sportsmanagement/assets/icons/spielorte.png" style="background-color:white;"
											alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_VENUES') ?>"/>
									</div>
									<div class="quickicon-name d-flex align-items-end">
										<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_VENUES') ?>
									</div>
								</a>
							</li>
						<?PHP
						}
						if ($this->params->get('show_option_rosterposition', 1))
						{
						?>										
							<li class="quickicon quickicon-single">
								<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_ROSTER_POSITION') ?>" 
								href="index.php?option=com_sportsmanagement&view=rosterpositions">
									<div class="quickicon-icon">
										<img src="components/com_sportsmanagement/assets/icons/spielfeldpositionen.png" style="background-color:white;"
											alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_ROSTER_POSITION') ?>"/>
									</div>
									<div class="quickicon-name d-flex align-items-end">
										<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_ROSTER_POSITION') ?>
									</div>
								</a>
							</li>
						<?PHP
						}
						?>
					</ul>
				</nav>
			</div>
		</div>

		<div class="col-md-12 quickicons-for-site_quickicon module-wrapper" style="grid-row-end: span 30;">
			<div class="card mb-3">
				<div class="card-header">                
					<h4>
					<span class="fa fa-2x fa-cubes" aria-hidden="true"></span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_SPECIAL_FUNCTION') ?>
					</h4>
				</div>
				<nav class="quick-icons px-3 pb-3" aria-label="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_SPECIAL_FUNCTION') ?>">
					<ul class="nav flex-wrap" style="grid-gap: 0.5rem; grid-template-columns: repeat(auto-fit,minmax(120px,1fr));">
					<?PHP
						if ($this->params->get('show_option_extrafields', 1))
						{
						?>
							<li class="quickicon quickicon-single">
								<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_EXTRAFIELDS') ?>" 
								href="index.php?option=com_sportsmanagement&view=extrafields">
									<div class="quickicon-icon">
										<img src="components/com_sportsmanagement/assets/icons/extrafelder.png" style="background-color:white;"
											alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_EXTRAFIELDS') ?>"/>
									</div>
									<div class="quickicon-name d-flex align-items-end">
										<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_EXTRAFIELDS') ?>
									</div>
								</a>
							</li>
						<?PHP
						}
						if ($this->params->get('show_option_statistics', 1))
						{
						?>
							<li class="quickicon quickicon-single">
								<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_STATISTICS') ?>" 
								href="index.php?option=com_sportsmanagement&view=statistics">
									<div class="quickicon-icon">
										<img src="components/com_sportsmanagement/assets/icons/statistik.png" style="background-color:white;"
											alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_STATISTICS') ?>"/>
									</div>
									<div class="quickicon-name d-flex align-items-end">
										<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_STATISTICS') ?>
									</div>
								</a>
							</li>
						<?PHP
						}
						if ($this->params->get('show_option_clubnames', 1))
						{
							?>
							<li class="quickicon quickicon-single">
								<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_CLUBNAMES') ?>" 
								href="index.php?option=com_sportsmanagement&view=clubnames">
									<div class="quickicon-icon">
										<img src="components/com_sportsmanagement/assets/icons/statistik.png" style="background-color:white;"
											alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_CLUBNAMES') ?>"/>
									</div>
									<div class="quickicon-name d-flex align-items-end">
										<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_CLUBNAMES') ?>
									</div>
								</a>
							</li>
						<?PHP
						}
						if ($this->params->get('show_option_github', 1))
						{
						?>										
							<li class="quickicon quickicon-single">
								<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_GITHUB') ?>" 
								href="index.php?option=com_sportsmanagement&view=github">
									<div class="quickicon-icon">
										<img src="components/com_sportsmanagement/assets/icons/github.png" style="background-color:white;"
											alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_GITHUB') ?>"/>
									</div>
									<div class="quickicon-name d-flex align-items-end">
										<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_GITHUB') ?>
									</div>
								</a>
							</li>
						<?PHP
						}
						?>
						<li class="quickicon quickicon-single">
							<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TRANSIFEX') ?>" 
							href="index.php?option=com_sportsmanagement&view=transifex">
								<div class="quickicon-icon">
									<img src="components/com_sportsmanagement/assets/icons/transifex.png" style="height:48px;background-color:white;"
										alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TRANSIFEX') ?>"/>
								</div>
								<div class="quickicon-name d-flex align-items-end">
									<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TRANSIFEX') ?>
								</div>
							</a>
						</li>
					</ul>
				</nav>
			</div>
		</div>

		<div class="col-md-12 quickicons-for-site_quickicon module-wrapper" style="grid-row-end: span 30;">
			<div class="card mb-3">
				<div class="card-header">                
					<h4>
					<span class="fa fa-2x fa-compress" aria-hidden="true"></span><span class="fa fa-2x fa-expand" aria-hidden="true"></span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_IMPORT_EXPORT_FUNCTION') ?>
					</h4>
				</div>
				<nav class="quick-icons px-3 pb-3" aria-label="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_IMPORT_EXPORT_FUNCTION') ?>">
					<ul class="nav flex-wrap" style="grid-gap: 0.5rem; grid-template-columns: repeat(auto-fit,minmax(120px,1fr));">
						<li class="quickicon quickicon-single">
							<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_XML_IMPORT') ?>" 
							href="index.php?option=com_sportsmanagement&view=jlxmlimports&layout=default">
								<div class="quickicon-icon">
									<img src="components/com_sportsmanagement/assets/icons/xmlimport.png" style="background-color:white;"
										alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_XML_IMPORT') ?>"/>
								</div>
								<div class="quickicon-name d-flex align-items-end">
									<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_XML_IMPORT') ?>
								</div>
							</a>
						</li>
						<!-- <li class="quickicon quickicon-single">
							<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_XML_EDITOR') ?>" 
							href="index.php?option=com_sportsmanagement&view=smextxmleditors&layout=default">
								<div class="quickicon-icon">
									<img src="components/com_sportsmanagement/assets/icons/xmleditor.png" style="background-color:white;"
										alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_XML_EDITOR') ?>"/>
								</div>
								<div class="quickicon-name d-flex align-items-end">
								<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_XML_EDITOR') ?>
								</div>
							</a>
						</li> -->
						<li class="quickicon quickicon-single">
							<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_IMAGE_IMPORT') ?>" 
							href="index.php?option=com_sportsmanagement&view=smimageimports&layout=default">
								<div class="quickicon-icon">
									<img src="components/com_sportsmanagement/assets/icons/imageimport.png" style="background-color:white;"
										alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_IMAGE_IMPORT') ?>"/>
								</div>
								<div class="quickicon-name d-flex align-items-end">
									<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_IMAGE_IMPORT') ?>
								</div>
							</a>
						</li>
						<li class="quickicon quickicon-single">
							<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_JOOMLEAGUE_IMPORT') ?>" 
							href="index.php?option=com_sportsmanagement&view=joomleagueimports&layout=default">
								<div class="quickicon-icon">
									<img src="components/com_sportsmanagement/assets/icons/joomleague.png" style="background-color:white;"
										alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_JOOMLEAGUE_IMPORT') ?>"/>
								</div>
								<div class="quickicon-name d-flex align-items-end">
									<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_JOOMLEAGUE_IMPORT') ?>
								</div>
							</a>
						</li>
					</ul>
				</nav>
			</div>
		</div>

		<div class="col-md-12 quickicons-for-site_quickicon module-wrapper" style="grid-row-end: span 30;">
			<div class="card mb-3">
				<div class="card-header">                
					<h4>
					<span class="fa fa-2x fa-wrench" aria-hidden="true"></span><?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_INSTALL_TOOLS') ?>
					</h4>
				</div>
				<nav class="quick-icons px-3 pb-3" aria-label="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_HEADING_INSTALL_TOOLS') ?>">
					<ul class="nav flex-wrap" style="grid-gap: 0.5rem; grid-template-columns: repeat(auto-fit,minmax(120px,1fr));">
						<li class="quickicon quickicon-single">
							<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_UPDATES') ?>" 
							href="index.php?option=com_sportsmanagement&view=updates">
								<div class="quickicon-icon">
									<img src="components/com_sportsmanagement/assets/icons/updates.png" style="background-color:white;"
										alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_UPDATES') ?>"/>
								</div>
								<div class="quickicon-name d-flex align-items-end">
									<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_UPDATES') ?>
								</div>
							</a>
						</li>
						<li class="quickicon quickicon-single">
							<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TOOLS') ?>" 
							href="index.php?option=com_sportsmanagement&view=databasetools">
								<div class="quickicon-icon">
									<img src="components/com_sportsmanagement/assets/icons/datenbanktools.png" style="background-color:white;"
										alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TOOLS') ?>"/>
								</div>
								<div class="quickicon-name d-flex align-items-end">
								<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TOOLS') ?>
								</div>
							</a>
						</li>
						<?PHP
						if ($this->params->get('show_option_smquotes', 1))
						{
						?>
							<li class="quickicon quickicon-single">
								<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_QUOTES') ?>" 
								href="index.php?option=com_sportsmanagement&view=smquotes">
									<div class="quickicon-icon">
										<img src="components/com_sportsmanagement/assets/icons/zitate.png" style="background-color:white;"
											alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_QUOTES') ?>"/>
									</div>
									<div class="quickicon-name d-flex align-items-end">
										<?php echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_QUOTES') ?>
									</div>
								</a>
							</li>
						<?PHP
						}
						?>
						<li class="quickicon quickicon-single">
							<a title="<?php echo Text::_('JComments for Joomla 4') ?>" 
							href="index.php?option=com_sportsmanagement&view=update&task=update.save&file_name=jsm_install_jcomments.php">
								<div class="quickicon-icon">
									<img src="components/com_sportsmanagement/assets/icons/jcomments_j4_256.png" width="48px"
										alt="<?php echo Text::_('JComments for Joomla 4') ?>"/>
								</div>
								<div class="quickicon-name d-flex align-items-end">
									<?php echo Text::_('JComments for Joomla 4') ?>
								</div>
							</a>
						</li>
						<li class="quickicon quickicon-single">
							<a title="<?php echo Text::_('JComments for Joomla 3') ?>" 
							href="index.php?option=com_sportsmanagement&view=update&task=update.save&file_name=jsm_install_jcomments_j3.php">
								<div class="quickicon-icon">
									<img src="components/com_sportsmanagement/assets/icons/jcomments_j4_256.png" width="48px"
										alt="<?php echo Text::_('JComments for Joomla 3') ?>"/>
								</div>
								<div class="quickicon-name d-flex align-items-end">
									<?php echo Text::_('JComments for Joomla 3') ?>
								</div>
							</a>
						</li>
					</ul>
				</nav>
			</div>
		</div>

		<div class="">
            <div class="center">
				<?PHP
				$start = 1;
				/**
				 * Define slides options
				 */
				$slidesOptions = array(
					"active" => "slide1_id" // It is the ID of the active tab.
				);
				/**
				 * Define tabs options for version of Joomla! 3.0
				 */
				$tabsOptions = array(
					"active" => "tab1_id" // It is the ID of the active tab.
				);

				echo HTMLHelper::_('bootstrap.startAccordion', 'slide-group-id', $slidesOptions);


				if (is_array($this->importData))
				{
					foreach ($this->importData as $key => $value)
					{
						echo HTMLHelper::_('bootstrap.addSlide', 'slide-group-id', Text::_($key), 'slide' . $start . '_id');
						echo $value;
						echo HTMLHelper::_('bootstrap.endSlide');
						$start++;
					}
				}


				if (is_array($this->importData2))
				{
					foreach ($this->importData2 as $key => $value)
					{
						echo HTMLHelper::_('bootstrap.addSlide', 'slide-group-id', Text::_($key), 'slide' . $start . '_id');
						echo $value;
						echo HTMLHelper::_('bootstrap.endSlide');
						$start++;
					}
				}

				echo HTMLHelper::_('bootstrap.endAccordion');
				?>
            </div>
        </div>
    </div>
    <div class="col-md-2">
		<?php sportsmanagementHelper::jsminfo(); ?>

    </div>
</div>
</div>
