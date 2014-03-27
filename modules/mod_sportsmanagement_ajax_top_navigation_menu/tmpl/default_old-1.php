<?php
/**
* @version $Id: default.php 4905 2010-01-30 08:51:33Z and_one $
* @package Joomleague
* @subpackage navigation_menu
* @copyright Copyright (C) 2009  JoomLeague
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see _joomleague_license.txt
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<div
	id="jlajaxmenu-<?php echo $module->id ?>"><!--jlajaxmenu-<?php echo $module->id?> start-->

<?PHP
// echo 'season_id ->'.$season_id.'<br>';
// echo 'league_id ->'.$league_id.'<br>';
// echo 'project_id ->'.$project_id.'<br>';
// echo 'division_id ->'.$division_id.'<br>';
// echo 'team_id ->'.$team_id.'<br>';
// echo 'favteams -><pre>'.print_r($favteams,true).'</pre><br>';
?>

<div style="margin: 0 auto;">
<?php
if ( $params->get('show_season_dropdown') ) 
{
echo JHTML::_('select.genericlist', $seasonselect['seasons'], 'jlamseason'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamnewleagues('.$module->id.');"',  'value', 'text', $season_id);
}
?>
</div>

<?php if ( $leagueselect['leagues'] && $params->get('show_league_dropdown') ) { ?>
<div style="margin: 0 auto;">
<?php
echo JHTML::_('select.genericlist', $leagueselect['leagues'], 'jlamleague'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamnewprojects('.$module->id.');"',  'value', 'text', $league_id);
?>
</div>
<?php } ?>

<?php if ( $projectselect['projects'] && $params->get('show_project_dropdown') ) { ?>
<div style="margin: 0 auto;">
<?php
echo JHTML::_('select.genericlist', $projectselect['projects'], 'jlamproject'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamnewdivisions('.$module->id.');"',  'value', 'text', $project_id);
?>
</div>
<?php } ?>

<?php if ( $divisionsselect['divisions'] && $params->get('show_division_dropdown') ) { ?>
<div style="margin: 0 auto;">
<?php
echo JHTML::_('select.genericlist', $divisionsselect['divisions'], 'jlamdivisions'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamdivision('.$module->id.');"',  'value', 'text', $division_id);
?>
</div>
<?php } ?>

<?php if ( $projectselect['teams'] && $params->get('show_teams_dropdown') ) { ?>
<div style="margin: 0 auto;">
<?php
echo JHTML::_('select.genericlist', $projectselect['teams'], 'jlamteams'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamteam('.$module->id.');"',  'value', 'text', $team_id);
?>
</div>
<?php } ?>

<?php if ( $project_id ) { ?>
<div style="margin: 0 auto;">
<fieldset class="">
<legend><?php echo JText::_('MODULE_JLG_NAVIGATION_PROJECT_VIEWS'); ?>
	</legend>
<ul class="nav-list">
<?php if ($params->get('show_nav_links')): ?>
	
		<?php for ($i = 1; $i < 18; $i++): ?>
			<?php if ($params->get('navpoint'.$i) && $link = $helper->getLink($params->get('navpoint'.$i))): ?>
				<li class="nav-item"><?php echo JHTML::link(JRoute::_($link), $params->get('navpoint_label'.$i)); ?></li>
			<?php elseif ($params->get('navpoint'.$i) == "separator"): ?>
				<li class="nav-item separator"><?php echo $params->get('navpoint_label'.$i); ?></li>
			<?php endif; ?>
		<?php endfor; ?>
    
    
    
        <?php 
        if ($params->get('show_tournament_nav_links'))
        {
        $link = $helper->getLink('jltournamenttree')
        ?>		
<li class="nav-item"><?php echo JHTML::link(JRoute::_($link), $params->get('show_tournament_text') ); ?></li>		
    <?php 
    }
    
     if ($params->get('show_alltimetable_nav_links'))
        {
        $link = $helper->getLink('rankingalltime')
        ?>		
<li class="nav-item"><?php echo JHTML::link(JRoute::_($link), $params->get('show_alltimetable_text') ); ?></li>		
    <?php 
    }
    
    endif; 
    ?>
</ul> 
</fieldset>	   
</div>
<?php } ?>

<?php if ( $team_id ) { ?>
<div style="margin: 0 auto;">
<fieldset class="">
<legend><?php echo JText::_('MODULE_JLG_NAVIGATION_CLUB_TEAM_VIEWS'); ?>
	</legend>
<ul class="nav-list">
<?php if ($params->get('show_nav_links')): ?>
	
		<?php for ($i = 17; $i < 23; $i++): ?>
			<?php if ($params->get('navpointct'.$i) && $link = $helper->getLink($params->get('navpointct'.$i))): ?>
				<li class="nav-item"><?php echo JHTML::link(JRoute::_($link), $params->get('navpointct_label'.$i)); ?></li>
			<?php elseif ($params->get('navpointct'.$i) == "separator"): ?>
				<li class="nav-item separator"><?php echo $params->get('navpointct_label'.$i); ?></li>
			<?php endif; ?>
		<?php endfor; ?>		
		
	<?php endif; ?>
</ul> 
</fieldset>	   
</div>
<?php } ?>

<?php 
if ( $params->get('show_favteams_nav_links') && $project_id && $favteams ) 
{ 

foreach ( $favteams as $teams )
{
?>
<div style="margin: 0 auto;">
<fieldset class="">
<legend><?php echo $teams->name; ?>
	</legend>
<ul class="nav-list">
<?php for ($i = 17; $i < 23; $i++): ?>
			<?php if ($params->get('navpointct'.$i) && $link = $helper->getLinkFavTeam($params->get('navpointct'.$i),$teams->team_id ,$teams->club_id )): ?>
				<li class="nav-item"><?php echo JHTML::link(JRoute::_($link), $params->get('navpointct_label'.$i)); ?></li>
			<?php elseif ($params->get('navpointct'.$i) == "separator"): ?>
				<li class="nav-item separator"><?php echo $params->get('navpointct_label'.$i); ?></li>
			<?php endif; ?>
		<?php endfor; ?>	

</ul> 
</fieldset>	   
</div>
<?PHP
}

}
?>



<!--jlajaxmenu-<?php echo $module->id?> end-->
</div>

<?php
if($ajax && $ajaxmod==$module->id){ exit(); } ?>