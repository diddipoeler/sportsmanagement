<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage currentseasons
 */

defined('_JEXEC') or die(Text::_('Restricted access'));
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.tooltip');
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
// Get a refrence of the page instance in joomla
        $document = JFactory::getDocument();
        $stylelink = '<link rel="stylesheet" href="'.JURI::root().'administrator/components/com_sportsmanagement/assets/css/jlextusericons.css'.'" type="text/css" />' ."\n";
//echo 'this->items<br /><pre>~' . print_r($this->items,true) . '~</pre><br />';

$path='/administrator/components/com_sportsmanagement/assets/icons/';

foreach ($this->items as $item)
	{
	echo HTMLHelper::_('sliders.start','sliders',array(
										'allowAllClose' => true,
										'startTransition' => true,
										true));
			echo HTMLHelper::_('sliders.panel',$item->name,'panel-'.$item->name);
?>

			<div id="element-box">
				<div class="t"><div class="t"><div class="t">&nbsp;</div></div></div>
				<div class="m">
					<fieldset class="adminform">
						<legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_CONTROL_PANEL_LEGEND','<i>'.$item->name.'</i>'); ?></legend>
						<table border='0'>
							<tr>
								
									<div id="cpanel">
										<?php
				 						$link=JRoute::_('index.php?option=com_sportsmanagement&task=project.edit&id='.$item->id.'&pid='.$item->id  );
										$text=Text::_('COM_SPORTSMANAGEMENT_P_PANEL_PSETTINGS');
										$imageFile='projekte.png';
										$linkParams="<span>$text</span>&nbsp;";
										$image=HTMLHelper::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
										?>
										<div class="icon-wrapper"><div class="icon"><?php echo HTMLHelper::link($link,$image); ?></div></div>		
										<?php
				 						$link=JRoute::_('index.php?option=com_sportsmanagement&view=templates&pid='.$item->id);
										$text=Text::_('COM_SPORTSMANAGEMENT_P_PANEL_FES');
										$imageFile='templates.png';
										$linkParams="<span>$text</span>&nbsp;";
										$image=HTMLHelper::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
										?>
										<div class="icon-wrapper"><div class="icon"><?php echo HTMLHelper::link($link,$image); ?></div></div>
										<?php
										if ((isset($item->project_type)) &&
											($item->project_type == 'DIVISIONS_LEAGUE'))
										{
											$link=JRoute::_('index.php?option=com_sportsmanagement&view=divisions&pid='.$item->id);
											
                                            $text=Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_DIVISIONS', $item->count_projectdivisions);
											$imageFile='divisionen.png';
											$linkParams="<span>$text</span>&nbsp;";
											$image=HTMLHelper::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
										?>
											<div class="icon-wrapper"><div class="icon"><?php echo HTMLHelper::link($link,$image); ?></div></div>
										<?php
										}
										if ((isset($item->project_type)) &&
											(($item->project_type == 'TOURNAMENT_MODE') ||
											 ($item->project_type == 'DIVISIONS_LEAGUE')))
										{
											$link=JRoute::_('index.php?option=com_sportsmanagement&view=treetos&pid='.$item->id);
											$text=Text::_('COM_SPORTSMANAGEMENT_P_PANEL_TREE');
											$imageFile='turnierbaum.png';
											$linkParams="<span>$text</span>&nbsp;";
											$image=HTMLHelper::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
											?>
											<div class="icon-wrapper"><div class="icon"><?php echo HTMLHelper::link($link,$image); ?></div></div>
										<?php
										}
										$link=JRoute::_('index.php?option=com_sportsmanagement&view=projectposition&pid='.$item->id);
										
                                        $text=Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_POSITIONS', $item->count_projectpositions);
										$imageFile='positionen.png';
										$linkParams="<span>$text</span>&nbsp;";
										$image=HTMLHelper::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
										?>
										<div class="icon-wrapper"><div class="icon"><?php echo HTMLHelper::link($link,$image); ?></div></div>
										<?php
										$link=JRoute::_('index.php?option=com_sportsmanagement&view=projectreferees&pid='.$item->id);
										
                                        $text=Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_REFEREES', $item->count_projectreferees);
										$imageFile='projektschiedsrichter.png';
										$linkParams="<span>$text</span>&nbsp;";
										$image=HTMLHelper::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
										?>
										<div class="icon-wrapper"><div class="icon"><?php echo HTMLHelper::link($link,$image); ?></div></div>
										<?php
				 						$link=JRoute::_('index.php?option=com_sportsmanagement&view=projectteams&pid='.$item->id);
										
                                        $text=Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_TEAMS', $item->count_projectteams);
										$imageFile='mannschaften.png';
										$linkParams="<span>$text</span>&nbsp;";
										$image=HTMLHelper::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
										?>
										<div class="icon-wrapper"><div class="icon"><?php echo HTMLHelper::link($link,$image); ?></div></div>
										<?php
				 						$link=JRoute::_('index.php?option=com_sportsmanagement&view=rounds&pid='.$item->id);
										
                                        $text=Text::plural('COM_SPORTSMANAGEMENT_P_PANEL_MATCHDAYS', $item->count_matchdays);
										$imageFile='spieltage.png';
										$linkParams="<span>$text</span>&nbsp;";
										$image=HTMLHelper::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
										?>
										<div class="icon-wrapper"><div class="icon"><?php echo HTMLHelper::link($link,$image); ?></div></div>
										<?php
				 						$link=JRoute::_('index.php?option=com_sportsmanagement&view=jlxmlexports&pid='.$item->id);
										$text=Text::_('COM_SPORTSMANAGEMENT_P_PANEL_XML_EXPORT');
										$imageFile='xmlexport.png';
										$linkParams="<span>$text</span>&nbsp;";
										$image=HTMLHelper::_('image.administrator',$imageFile,$path,NULL,NULL,$text).$linkParams;
										?>
										<div class="icon-wrapper"><div class="icon"><?php echo HTMLHelper::link($link,$image); ?></div></div>
									</div>
								
							</tr>
						</table>
					</fieldset>
				</div>
				<div class="m">
					<fieldset class="adminform">
						<table><tr><td><div id="cpanel"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_CONTROL_PANEL_HINT'); ?></div></td></tr></table>
					</fieldset>
				</div>
				<div class="b"><div class="b"><div class="b"></div></div></div>
			</div>
			<!-- bottom close main table opened in default_admin -->
<?PHP                        
            echo HTMLHelper::_('sliders.end');   
    }   

?>
