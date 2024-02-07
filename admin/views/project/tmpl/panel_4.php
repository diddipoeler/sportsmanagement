<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.60
 * @file      panel_4.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
    <div class="row">
<?php
require(JPATH_COMPONENT_ADMINISTRATOR . '/views/listheader/tmpl/default_4_start_menu.php');   
?>

<?php 
/**
if (!empty($this->sidebar))
	:
	?>
    <div id="j-sidebar-container" class="col-md-2">
		<?php echo $this->sidebar; ?>
    </div>
    <div class="col-md-8">
	<?php else

	:
	?>
    <div class="col-md-10">
<?php endif; 
*/
?>
<div class="col-md-10">
	    
<?php
echo $this->loadTemplate('jsm_warnings');
echo $this->loadTemplate('jsm_notes');			    
echo $this->loadTemplate('jsm_tips');			    
?>		    
    <div class="card mb-3">
		<nav class="quick-icons px-3 py-3">
			<ul class="nav flex-wrap" style="grid-gap: 0.5rem; grid-template-columns: repeat(auto-fit,minmax(180px,1fr));">
				<li class="quickicon quickicon-single">
					<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_PSETTINGS') ?>"
					href="index.php?option=com_sportsmanagement&task=project.edit&id=<?PHP echo $this->project->id; ?>">
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
					href="index.php?option=com_sportsmanagement&view=templates&pid=<?PHP echo $this->project->id; ?>">
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
				if ((isset($this->project->project_type))
					&& (($this->project->project_type == 'PROJECT_DIVISIONS')
						|| ($this->project->project_type == 'DIVISIONS_LEAGUE'))
				)
				{
				?>
					<li class="quickicon quickicon-single">
						<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_DIVISIONS') ?>"
						href="index.php?option=com_sportsmanagement&view=divisions&pid=<?PHP echo $this->project->id; ?>">
							<div class="quickicon-icon">
								<img src="components/com_sportsmanagement/assets/icons/divisionen.png" style="background-color:white;"
									alt="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_DIVISIONS', $this->count_projectdivisions) ?>"/>
							</div>
							<div class="quickicon-name d-flex align-items-end">
								<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_DIVISIONS', $this->count_projectdivisions) ?>
							</div>
						</a>
					</li>
				<?php
				}

				if ((isset($this->project->project_type))
					&& (($this->project->project_type == 'TOURNAMENT_MODE')
						|| ($this->project->project_type == 'DIVISIONS_LEAGUE'))
				)
				{
				?>
					<li class="quickicon quickicon-single">
						<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_TREE') ?>"
						href="index.php?option=com_sportsmanagement&view=treetos&pid=<?PHP echo $this->project->id; ?>">
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

				if ($this->project->project_art_id != 3)
				{
				?>
					<li class="quickicon quickicon-single">
						<a title="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_POSITIONS', $this->count_projectpositions) ?>"
						href="index.php?option=com_sportsmanagement&view=projectpositions&pid=<?PHP echo $this->project->id; ?>">
							<div class="quickicon-icon">
								<img src="components/com_sportsmanagement/assets/icons/positionen.png" style="background-color:white;"
								alt="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_POSITIONS', $this->count_projectpositions) ?>"/>
							</div>
							<div class="quickicon-name d-flex align-items-end">
								<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_POSITIONS', $this->count_projectpositions) ?>
							</div>
						</a>
					</li>
				<?PHP
				}
				if (isset($this->project->teams_as_referees) && $this->project->teams_as_referees == 0 )
				{
				?>
				<li class="quickicon quickicon-single">
					<a title="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_REFEREES', $this->count_projectreferees) ?>"
					href="index.php?option=com_sportsmanagement&view=projectreferees&persontype=3&pid=<?PHP echo $this->project->id; ?>">
						<div class="quickicon-icon">
							<img src="components/com_sportsmanagement/assets/icons/projektschiedsrichter.png" style="background-color:white;"
								alt="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_REFEREES', $this->count_projectreferees) ?>"/>
						</div>
						<div class="quickicon-name d-flex align-items-end">
							<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_REFEREES', $this->count_projectreferees) ?>
						</div>
					</a>
				</li>
				<?PHP
				}
				?>
				<li class="quickicon quickicon-single">
					<a title="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_TEAMS', $this->count_projectteams) ?>"
					href="index.php?option=com_sportsmanagement&view=projectteams&pid=<?PHP echo $this->project->id; ?>">
						<div class="quickicon-icon">
							<img src="components/com_sportsmanagement/assets/icons/mannschaften.png" style="background-color:white;"
								alt="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_TEAMS', $this->count_projectteams) ?>"/>
						</div>
						<div class="quickicon-name d-flex align-items-end">
							<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_TEAMS', $this->count_projectteams) ?>
						</div>
					</a>
				</li>
				<li class="quickicon quickicon-single">
					<a title="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_MATCHDAYS', $this->count_matchdays) ?>"
					href="index.php?option=com_sportsmanagement&view=rounds&pid=<?PHP echo $this->project->id; ?>">
						<div class="quickicon-icon">
							<img src="components/com_sportsmanagement/assets/icons/spieltage.png" style="background-color:white;"
								alt="<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_MATCHDAYS', $this->count_matchdays) ?>"/>
						</div>
						<div class="quickicon-name d-flex align-items-end">
							<?php echo Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_MATCHDAYS', $this->count_matchdays) ?>
						</div>
					</a>
				</li>
				<li class="quickicon quickicon-single">
					<a title="<?php echo Text::_('COM_SPORTSMANAGEMENT_P_PANEL_XML_EXPORT') ?>"
					href="index.php?option=com_sportsmanagement&view=jlxmlexports&pid=<?PHP echo $this->project->id; ?>">
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
    </div>
    </div>
    <div class="col-md-2">
		<?php sportsmanagementHelper::jsminfo(); ?>
    </div>
    </div>
<?php echo $this->table_data_div;
