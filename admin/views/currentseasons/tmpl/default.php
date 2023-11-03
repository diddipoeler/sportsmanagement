<?php
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage currentseasons
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

jimport('joomla.html.html.bootstrap');

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

$count = 0;
?>

<?PHP
if ($this->items)
{
	foreach ($this->items as $item)
	{
		if (!$count)
		{
			// Define slides options
			$slidesOptions = array(
				"active" => "slide" . $item->id . "_id" // It is the ID of the active tab.
			);

			// Define tabs options for version of Joomla! 3.0
			$tabsOptions = array(
				"active" => "tab" . $item->id . "_id" // It is the ID of the active tab.
			);
		}
?>

<?PHP
		$count++;
	}
}
?>


<div class="row">
	<?php 
/**
if (!empty($this->sidebar)) :
	?>
		<div id="j-sidebar-container" class="col-md-2">
			<?php echo $this->sidebar; ?>
		</div>
		<div class="col-md-10">
		<?php else :
		?>
			<div class="col-md-12">
			<?php endif; 
*/
?>

<?php
require(JPATH_COMPONENT_ADMINISTRATOR . '/views/listheader/tmpl/default_4_start_menu.php');   
?>    				
							<div class="col-md-12">
			<div id="j-main-container">

				<div id="jsm" class="admin override">

					<div id="j-main-container" class="span10">
						<section class="content-block" role="main">


							<?php // This renders the beginning of the slides code. 
							?>
							<?php echo HTMLHelper::_('bootstrap.startAccordion', 'slide-group-id', $slidesOptions); ?>

							<?PHP
							if ($this->items)
							{
								foreach ($this->items as $item)
								{
									// Open the first slide
									echo HTMLHelper::_('bootstrap.addSlide', 'slide-group-id', JSMCountries::getCountryFlag($item->country) . ' ' . $item->name, 'slide' . $item->id . '_id');
							?>
									<nav class="quick-icons px-3 py-3">
										<ul class="nav flex-wrap" style="grid-gap: 0.5rem; grid-template-columns: repeat(auto-fit,minmax(180px,1fr));">
											<li class="quickicon quickicon-single">
												<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_PSETTINGS') ?>"
												href="index.php?option=com_sportsmanagement&task=project.edit&id=<?PHP echo $item->id; ?>">
													<div class="quickicon-icon">
														<img src="components/com_sportsmanagement/assets/icons/projekte.png" style="background-color:white;"
															alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_PSETTINGS') ?>"/>
													</div>
													<div class="quickicon-name d-flex align-items-end">
														<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_PSETTINGS') ?>
													</div>
												</a>
											</li>
											<li class="quickicon quickicon-single">
												<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_FES') ?>"
												href="index.php?option=com_sportsmanagement&view=templates&pid=<?PHP echo $item->id; ?>">
													<div class="quickicon-icon">
														<img src="components/com_sportsmanagement/assets/icons/templates.png" style="background-color:white;"
															alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_FES') ?>"/>
													</div>
													<div class="quickicon-name d-flex align-items-end">
														<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_FES') ?>
													</div>
												</a>
											</li>
											<?php
											if ((isset($item->project_type))
												&& (($item->project_type == 'PROJECT_DIVISIONS')
													|| ($item->project_type == 'DIVISIONS_LEAGUE'))
											)
											{
											?>
												<li class="quickicon quickicon-single">
													<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_DIVISIONS') ?>"
													href="index.php?option=com_sportsmanagement&view=divisions&pid=<?PHP echo $item->id; ?>">
														<div class="quickicon-icon">
															<img src="components/com_sportsmanagement/assets/icons/divisionen.png" style="background-color:white;"
																alt="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_DIVISIONS', $item->count_projectdivisions) ?>"/>
														</div>
														<div class="quickicon-name d-flex align-items-end">
															<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_DIVISIONS', $item->count_projectdivisions) ?>
														</div>
													</a>
												</li>
											<?php
											}

											if ((isset($item->project_type))
												&& (($item->project_type == 'TOURNAMENT_MODE')
													|| ($item->project_type == 'DIVISIONS_LEAGUE'))
											)
											{
											?>
												<li class="quickicon quickicon-single">
													<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_TREE') ?>"
													href="index.php?option=com_sportsmanagement&view=treetos&pid=<?PHP echo $item->id; ?>">
														<div class="quickicon-icon">
															<img src="components/com_sportsmanagement/assets/icons/turnierbaum.png" style="background-color:white;"
																alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_TREE') ?>"/>
														</div>
														<div class="quickicon-name d-flex align-items-end">
															<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_TREE') ?>
														</div>
													</a>
												</li>
											<?PHP
											}

											if ($item->project_art_id != 3)
											{
											?>
												<li class="quickicon quickicon-single">
													<a title="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_POSITIONS', $item->count_projectpositions) ?>"
													href="index.php?option=com_sportsmanagement&view=projectpositions&pid=<?PHP echo $item->id; ?>">
														<div class="quickicon-icon">
															<img src="components/com_sportsmanagement/assets/icons/positionen.png" style="background-color:white;"
															alt="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_POSITIONS', $item->count_projectpositions) ?>"/>
														</div>
														<div class="quickicon-name d-flex align-items-end">
															<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_POSITIONS', $item->count_projectpositions) ?>
														</div>
													</a>
												</li>
											<?PHP
											}
											if (isset($item->teams_as_referees) && $item->teams_as_referees == 0 )
											{
											?>
												<li class="quickicon quickicon-single">
													<a title="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_REFEREES', $item->count_projectreferees) ?>"
													href="index.php?option=com_sportsmanagement&view=projectreferees&persontype=3&pid=<?PHP echo $item->id; ?>">
														<div class="quickicon-icon">
															<img src="components/com_sportsmanagement/assets/icons/projektschiedsrichter.png" style="background-color:white;"
																alt="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_REFEREES', $item->count_projectreferees) ?>"/>
														</div>
														<div class="quickicon-name d-flex align-items-end">
															<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_REFEREES', $item->count_projectreferees) ?>
														</div>
													</a>
												</li>
											<?PHP
											}
											?>
											<li class="quickicon quickicon-single">
												<a title="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_TEAMS', $item->count_projectteams) ?>"
												href="index.php?option=com_sportsmanagement&view=projectteams&pid=<?PHP echo $item->id; ?>">
													<div class="quickicon-icon">
														<img src="components/com_sportsmanagement/assets/icons/mannschaften.png" style="background-color:white;"
															alt="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_TEAMS', $item->count_projectteams) ?>"/>
													</div>
													<div class="quickicon-name d-flex align-items-end">
														<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_TEAMS', $item->count_projectteams) ?>
													</div>
												</a>
											</li>
											<li class="quickicon quickicon-single">
												<a title="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_MATCHDAYS', $item->count_matchdays) ?>"
												href="index.php?option=com_sportsmanagement&view=rounds&pid=<?PHP echo $item->id; ?>">
													<div class="quickicon-icon">
														<img src="components/com_sportsmanagement/assets/icons/spieltage.png" style="background-color:white;"
															alt="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_MATCHDAYS', $item->count_matchdays) ?>"/>
													</div>
													<div class="quickicon-name d-flex align-items-end">
														<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_MATCHDAYS', $item->count_matchdays) ?>
													</div>
												</a>
											</li>
											<li class="quickicon quickicon-single">
												<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_XML_EXPORT') ?>"
												href="index.php?option=com_sportsmanagement&view=jlxmlexports&pid=<?PHP echo $item->id; ?>">
													<div class="quickicon-icon">
														<img src="components/com_sportsmanagement/assets/icons/xmlexport.png" style="background-color:white;"
															alt="<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_XML_EXPORT') ?>"/>
													</div>
													<div class="quickicon-name d-flex align-items-end">
														<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_XML_EXPORT') ?>
													</div>
												</a>
											</li>

										</ul>
									</nav>
							<?PHP
									// This is the closing tag of the first slide
									echo HTMLHelper::_('bootstrap.endSlide');
								}
							}


							?>
							<?php // This renders the end part of the slides code. 
							?>
							<?php echo HTMLHelper::_('bootstrap.endAccordion'); ?>


						</section>
					</div>
				</div>
			</div>
			<?php echo $this->table_data_div;
