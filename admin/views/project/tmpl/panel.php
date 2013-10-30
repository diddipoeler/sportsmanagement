<?php defined('_JEXEC') or die('Restricted access');

$path='/administrator/components/com_joomleague/assets/images/';
$user = JFactory::getUser();
JToolBarHelper::title(JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_CONTROL_PANEL_TITLE'));
//load navigation menu
//$this->addTemplatePath(JPATH_COMPONENT.DS.'views'.DS.'joomleague');
?>
			<div id="element-box">
				<div class="m">
					<div class="adminform">
						<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_CONTROL_PANEL_LEGEND','<i>'.$this->project->name.'</i>'); ?></legend>
						<div class="cpanel" style="height:100px;padding:15px">
							<?php
	 						$link=JRoute::_('index.php?option=com_sportsmanagement&task=project.edit&id='.$this->project->id);
							$text=JText::_('COM_SPORTSMANAGEMENT_P_PANEL_PSETTINGS');
							$imageFile='icon-48-ProjectSettings.png';
							$linkParams="<span>$text</span>&nbsp;";
							$image=JHTML::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
							?>
							<div class="icon-wrapper"><div class="icon"><?php echo JHTML::link($link,$image); ?></div></div>									
							<?php
	 						$link=JRoute::_('index.php?option=com_sportsmanagement&view=templates&pid='.$this->project->id);
							$text=JText::_('COM_SPORTSMANAGEMENT_P_PANEL_FES');
							$imageFile='icon-48-FrontendSettings.png';
							$linkParams="<span>$text</span>&nbsp;";
							$image=JHTML::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
							?>
							<div class="icon-wrapper"><div class="icon"><?php echo JHTML::link($link,$image); ?></div></div>
							<?php
							if ((isset($this->project->project_type)) &&
								 (($this->project->project_type == PROJECT_DIVISIONS) ||
								   ($this->project->project_type == 'DIVISIONS_LEAGUE')))
							{
								$link=JRoute::_('index.php?option=com_sportsmanagement&view=divisions&pid='.$this->project->id);
								$text=JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_DIVISIONS', $this->count_projectdivisions);
								$imageFile='icon-48-Divisions.png';
								$linkParams="<span>$text</span>&nbsp;";
								$image=JHTML::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
								?>
								<div class="icon-wrapper"><div class="icon"><?php echo JHTML::link($link,$image); ?></div></div>
								<?php
							}
							if ((isset($this->project->project_type)) &&
								(($this->project->project_type == 'TOURNAMENT_MODE') ||
								($this->project->project_type == 'DIVISIONS_LEAGUE')))
							{
								$link=JRoute::_('index.php?option=com_sportsmanagement&view=treetos&pid='.$this->project->id);
								$text=JText::_('COM_SPORTSMANAGEMENT_P_PANEL_TREE');
								$imageFile='icon-48-Tree.png';
								$linkParams="<span>$text</span>&nbsp;";
								$image=JHTML::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
								?>
								<div class="icon-wrapper"><div class="icon"><?php echo JHTML::link($link,$image); ?></div></div>
							<?php
							}
							$link=JRoute::_('index.php?option=com_sportsmanagement&view=projectposition&pid='.$this->project->id);
							$text=JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_POSITIONS', $this->count_projectpositions);
							$imageFile='icon-48-Positions.png';
							$linkParams="<span>$text</span>&nbsp;";
							$image=JHTML::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
							?>
							<div class="icon-wrapper"><div class="icon"><?php echo JHTML::link($link,$image); ?></div></div>
							<?php
							$link=JRoute::_('index.php?option=com_sportsmanagement&view=projectreferees&pid='.$this->project->id);
							$text=JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_REFEREES', $this->count_projectreferees);
							$imageFile='icon-48-Referees.png';
							$linkParams="<span>$text</span>&nbsp;";
							$image=JHTML::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
							?>
							<div class="icon-wrapper"><div class="icon"><?php echo JHTML::link($link,$image); ?></div></div>
							<?php
	 						$link=JRoute::_('index.php?option=com_sportsmanagement&view=projectteams&pid='.$this->project->id);
							$text=JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_TEAMS', $this->count_projectteams);
							$imageFile='icon-48-Teams.png';
							$linkParams="<span>$text</span>&nbsp;";
							$image=JHTML::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
							?>
							<div class="icon-wrapper"><div class="icon"><?php echo JHTML::link($link,$image); ?></div></div>
							<?php
	 						$link=JRoute::_('index.php?option=com_sportsmanagement&view=rounds&pid='.$this->project->id);
							$text=JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_MATCHDAYS', $this->count_matchdays);
							$imageFile='icon-48-Matchdays.png';
							$linkParams="<span>$text</span>&nbsp;";
							$image=JHTML::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
							?>
							<div class="icon-wrapper"><div class="icon"><?php echo JHTML::link($link,$image); ?></div></div>
							<?php
	 						$link=JRoute::_('index.php?option=com_sportsmanagement&view=jlxmlexports&pid='.$this->project->id);
							$text=JText::_('COM_SPORTSMANAGEMENT_P_PANEL_XML_EXPORT');
							$imageFile='icon-48-XMLExportData.png';
							$linkParams="<span>$text</span>&nbsp;";
							$image=JHTML::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
							?>
							<div class="icon-wrapper"><div class="icon"><?php echo JHTML::link($link,$image); ?></div></div>
						</div>
					</div>
				</div>
			</div>
			<div id="element-box">
				<div class="m">
					<div class="adminform">
						<div class="cpanel"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_CONTROL_PANEL_HINT'); ?></div>
					</div>
				</div>
			</div>
			<!-- bottom close main table opened in default_admin -->
		</td>
	</tr>
</table>