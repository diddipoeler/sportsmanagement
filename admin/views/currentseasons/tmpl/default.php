<?php 
defined('_JEXEC') or die(JText::_('Restricted access'));
JHtml::_('behavior.tooltip');


//echo 'this->items<br /><pre>~' . print_r($this->items,true) . '~</pre><br />';

$path='/administrator/components/com_sportsmanagement/assets/images/';

foreach ($this->items as $item)
	{
	echo JHtml::_('sliders.start','sliders',array(
										'allowAllClose' => true,
										'startTransition' => true,
										true));
			echo JHtml::_('sliders.panel',$item->name,'panel-'.$item->name);
?>

			<div id="element-box">
				<div class="t"><div class="t"><div class="t">&nbsp;</div></div></div>
				<div class="m">
					<fieldset class="adminform">
						<legend><?php echo JText::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_CONTROL_PANEL_LEGEND','<i>'.$item->name.'</i>'); ?></legend>
						<table border='0'>
							<tr>
								
									<div id="cpanel">
										<?php
				 						$link=JRoute::_('index.php?option=com_sportsmanagement&task=project.edit&id='.$item->id.'&pid='.$item->id  );
										$text=JText::_('COM_SPORTSMANAGEMENT_P_PANEL_PSETTINGS');
										$imageFile='icon-48-ProjectSettings.png';
										$linkParams="<span>$text</span>&nbsp;";
										$image=JHtml::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
										?>
										<div class="icon-wrapper"><div class="icon"><?php echo JHtml::link($link,$image); ?></div></div>		
										<?php
				 						$link=JRoute::_('index.php?option=com_sportsmanagement&view=templates&pid='.$item->id);
										$text=JText::_('COM_SPORTSMANAGEMENT_P_PANEL_FES');
										$imageFile='icon-48-FrontendSettings.png';
										$linkParams="<span>$text</span>&nbsp;";
										$image=JHtml::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
										?>
										<div class="icon-wrapper"><div class="icon"><?php echo JHtml::link($link,$image); ?></div></div>
										<?php
										if ((isset($item->project_type)) &&
											($item->project_type == 'DIVISIONS_LEAGUE'))
										{
											$link=JRoute::_('index.php?option=com_sportsmanagement&view=divisions&pid='.$item->id);
											
                                            $text=JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_DIVISIONS', $item->count_projectdivisions);
											$imageFile='icon-48-Divisions.png';
											$linkParams="<span>$text</span>&nbsp;";
											$image=JHtml::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
										?>
											<div class="icon-wrapper"><div class="icon"><?php echo JHtml::link($link,$image); ?></div></div>
										<?php
										}
										if ((isset($item->project_type)) &&
											(($item->project_type == 'TOURNAMENT_MODE') ||
											 ($item->project_type == 'DIVISIONS_LEAGUE')))
										{
											$link=JRoute::_('index.php?option=com_sportsmanagement&view=treetos&pid='.$item->id);
											$text=JText::_('COM_SPORTSMANAGEMENT_P_PANEL_TREE');
											$imageFile='icon-48-Tree.png';
											$linkParams="<span>$text</span>&nbsp;";
											$image=JHtml::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
											?>
											<div class="icon-wrapper"><div class="icon"><?php echo JHtml::link($link,$image); ?></div></div>
										<?php
										}
										$link=JRoute::_('index.php?option=com_sportsmanagement&view=projectposition&pid='.$item->id);
										
                                        $text=JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_POSITIONS', $item->count_projectpositions);
										$imageFile='icon-48-Positions.png';
										$linkParams="<span>$text</span>&nbsp;";
										$image=JHtml::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
										?>
										<div class="icon-wrapper"><div class="icon"><?php echo JHtml::link($link,$image); ?></div></div>
										<?php
										$link=JRoute::_('index.php?option=com_sportsmanagement&view=projectreferees&pid='.$item->id);
										
                                        $text=JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_REFEREES', $item->count_projectreferees);
										$imageFile='icon-48-Referees.png';
										$linkParams="<span>$text</span>&nbsp;";
										$image=JHtml::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
										?>
										<div class="icon-wrapper"><div class="icon"><?php echo JHtml::link($link,$image); ?></div></div>
										<?php
				 						$link=JRoute::_('index.php?option=com_sportsmanagement&view=projectteams&pid='.$item->id);
										
                                        $text=JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_TEAMS', $item->count_projectteams);
										$imageFile='icon-48-Teams.png';
										$linkParams="<span>$text</span>&nbsp;";
										$image=JHtml::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
										?>
										<div class="icon-wrapper"><div class="icon"><?php echo JHtml::link($link,$image); ?></div></div>
										<?php
				 						$link=JRoute::_('index.php?option=com_sportsmanagement&view=rounds&pid='.$item->id);
										
                                        $text=JText::plural('COM_SPORTSMANAGEMENT_P_PANEL_MATCHDAYS', $item->count_matchdays);
										$imageFile='icon-48-Matchdays.png';
										$linkParams="<span>$text</span>&nbsp;";
										$image=JHtml::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
										?>
										<div class="icon-wrapper"><div class="icon"><?php echo JHtml::link($link,$image); ?></div></div>
										<?php
				 						$link=JRoute::_('index.php?option=com_sportsmanagement&view=jlxmlexports&pid='.$item->id);
										$text=JText::_('COM_SPORTSMANAGEMENT_P_PANEL_XML_EXPORT');
										$imageFile='icon-48-XMLExportData.png';
										$linkParams="<span>$text</span>&nbsp;";
										$image=JHtml::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
										?>
										<div class="icon-wrapper"><div class="icon"><?php echo JHtml::link($link,$image); ?></div></div>
									</div>
								
							</tr>
						</table>
					</fieldset>
				</div>
				<div class="m">
					<fieldset class="adminform">
						<table><tr><td><div id="cpanel"><?php echo JText::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_CONTROL_PANEL_HINT'); ?></div></td></tr></table>
					</fieldset>
				</div>
				<div class="b"><div class="b"><div class="b"></div></div></div>
			</div>
			<!-- bottom close main table opened in default_admin -->
<?PHP                        
            echo JHtml::_('sliders.end');   
    }   

?>