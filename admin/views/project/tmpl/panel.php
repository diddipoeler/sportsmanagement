<?php defined('_JEXEC') or die('Restricted access');

$path='/administrator/components/com_joomleague/assets/images/';
$user = JFactory::getUser();
JToolBarHelper::title(JText::_('COM_JOOMLEAGUE_ADMIN_PROJECTS_CONTROL_PANEL_TITLE'));
//load navigation menu
$this->addTemplatePath(JPATH_COMPONENT.DS.'views'.DS.'joomleague');
?>
			<div id="element-box">
				<div class="m">
					<div class="adminform">
						<legend><?php echo JText::sprintf('COM_JOOMLEAGUE_ADMIN_PROJECTS_CONTROL_PANEL_LEGEND','<i>'.$this->project->name.'</i>'); ?></legend>
						<div class="cpanel" style="height:100px;padding:15px">
							<?php
	 						$link=JRoute::_('index.php?option=com_joomleague&&task=project.edit&cid[]='.$this->project->id);
							$text=JText::_('COM_JOOMLEAGUE_P_PANEL_PSETTINGS');
							$imageFile='icon-48-ProjectSettings.png';
							$linkParams="<span>$text</span>&nbsp;";
							$image=JHTML::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
							?>
							<div class="icon-wrapper"><div class="icon"><?php echo JHTML::link($link,$image); ?></div></div>									
							<?php
	 						$link=JRoute::_('index.php?option=com_joomleague&view=templates&task=template.display&pid[]='.$this->project->id);
							$text=JText::_('COM_JOOMLEAGUE_P_PANEL_FES');
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
								$link=JRoute::_('index.php?option=com_joomleague&view=divisions&task=division.display&pid[]='.$this->project->id);
								$text=JText::plural('COM_JOOMLEAGUE_P_PANEL_DIVISIONS', $this->count_projectdivisions);
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
								$link=JRoute::_('index.php?option=com_joomleague&view=treetos&task=treeto.display&pid[]='.$this->project->id);
								$text=JText::_('COM_JOOMLEAGUE_P_PANEL_TREE');
								$imageFile='icon-48-Tree.png';
								$linkParams="<span>$text</span>&nbsp;";
								$image=JHTML::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
								?>
								<div class="icon-wrapper"><div class="icon"><?php echo JHTML::link($link,$image); ?></div></div>
							<?php
							}
							$link=JRoute::_('index.php?option=com_joomleague&view=projectposition&task=projectposition.display&pid[]='.$this->project->id);
							$text=JText::plural('COM_JOOMLEAGUE_P_PANEL_POSITIONS', $this->count_projectpositions);
							$imageFile='icon-48-Positions.png';
							$linkParams="<span>$text</span>&nbsp;";
							$image=JHTML::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
							?>
							<div class="icon-wrapper"><div class="icon"><?php echo JHTML::link($link,$image); ?></div></div>
							<?php
							$link=JRoute::_('index.php?option=com_joomleague&view=projectreferees&task=projectreferee.display&pid[]='.$this->project->id);
							$text=JText::plural('COM_JOOMLEAGUE_P_PANEL_REFEREES', $this->count_projectreferees);
							$imageFile='icon-48-Referees.png';
							$linkParams="<span>$text</span>&nbsp;";
							$image=JHTML::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
							?>
							<div class="icon-wrapper"><div class="icon"><?php echo JHTML::link($link,$image); ?></div></div>
							<?php
	 						$link=JRoute::_('index.php?option=com_joomleague&view=projectteams&task=projectteam.display&pid[]='.$this->project->id);
							$text=JText::plural('COM_JOOMLEAGUE_P_PANEL_TEAMS', $this->count_projectteams);
							$imageFile='icon-48-Teams.png';
							$linkParams="<span>$text</span>&nbsp;";
							$image=JHTML::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
							?>
							<div class="icon-wrapper"><div class="icon"><?php echo JHTML::link($link,$image); ?></div></div>
							<?php
	 						$link=JRoute::_('index.php?option=com_joomleague&view=rounds&task=round.display&pid[]='.$this->project->id);
							$text=JText::plural('COM_JOOMLEAGUE_P_PANEL_MATCHDAYS', $this->count_matchdays);
							$imageFile='icon-48-Matchdays.png';
							$linkParams="<span>$text</span>&nbsp;";
							$image=JHTML::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
							?>
							<div class="icon-wrapper"><div class="icon"><?php echo JHTML::link($link,$image); ?></div></div>
							<?php
	 						$link=JRoute::_('index.php?option=com_joomleague&view=jlxmlexports&task=jlxmlexport.display&pid[]='.$this->project->id);
							$text=JText::_('COM_JOOMLEAGUE_P_PANEL_XML_EXPORT');
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
						<div class="cpanel"><?php echo JText::_('COM_JOOMLEAGUE_ADMIN_PROJECTS_CONTROL_PANEL_HINT'); ?></div>
					</div>
				</div>
			</div>
			<!-- bottom close main table opened in default_admin -->
		</td>
	</tr>
</table>