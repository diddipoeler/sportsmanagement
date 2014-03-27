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
JHtml::_('behavior.tooltip');
?>


<?PHP

/*
$options_slider = array(
    'onActive' => 'function(title, description){
        description.setStyle("display", "block");
        title.addClass("open").removeClass("closed");
    }',
    'onBackground' => 'function(title, description){
        description.setStyle("display", "none");
        title.addClass("closed").removeClass("open");
    }',
    'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
    'useCookie' => true, // this must not be a string. Don't use quotes.
);

array('useCookie'=>0, 'show'=>0, 'display'=>0, 'startOffset'=>-1)
*/



if ( $project_id )
{
$options_slider = array(
    'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
    'display'=> 1,
    'show'=> 1,
    'useCookie' => true, // this must not be a string. Don't use quotes.
);
}
else
{
$options_slider = array(
    'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
    'display'=> 0,
    'show'=> 0,
    'useCookie' => true, // this must not be a string. Don't use quotes.
);

}


echo JHtml::_('sliders.start','menueslidername', $options_slider );
//echo JHtml::_('sliders.start','menueslidername'), array('show'=>0,'display'=>0, 'startOffset'=>-1);
echo JHtml::_('sliders.panel', JText::_('Ligen Auswahlmenü von Fussball in Europa'), 'menue-params');

// tabs anzeigen
$idxTab = 100;
echo JHTML::_('tabs.start','tabs_ajaxtopmenu', array('useCookie'=>false, 'startOffset' => $startoffset ));

foreach ( $tab_points as $key => $value  )
{
$fed_array = strtoupper($value);

echo JHTML::_('tabs.panel', JText::_( strtoupper($value) ), 'panelmenue'.($idxTab++));
?>

<div id="jlajaxtopmenu-<?php echo $value?><?php echo $module->id ?>">

<!--jlajaxtopmenu<?php echo $value?>-<?php echo $module->id?> start-->

<?PHP
if ( $show_debug_info )
{
echo 'startoffset=> <pre>'.print_r($startoffset, true).'</pre><br>';
echo 'country_federation=> <pre>'.print_r($country_federation, true).'</pre><br>';
echo 'queryvalues => <pre>'.print_r($queryvalues, true).'</pre><br>';

echo 'jlamtopcountry => '.$country_id.'<br>';
echo 'jlamtopassocid => '.$assoc_id.'<br>';
echo 'jlamtopsubassocid => '.$subassoc_id.'<br>';
echo 'jlamtopsubsubassocid => '.$subsubassoc_id.'<br>';


echo 'jlamtopseason => '.$season_id.'<br>';
echo 'jlamtopleague => '.$league_id.'<br>';
echo 'jlamtopproject => '.$project_id.'<br>';
echo 'jlamtopteam => '.$team_id.'<br>';
echo 'post => <pre>'.print_r($_POST, true).'</pre><br>';
}
?>

<p>
<img style="float: left;" src="images/dfs_kl_<?php echo $value?>.gif" alt="<?php echo $value?>" width="144" height="" />

<?PHP
if ( $country_id )
{
?>
<img style="float: right;" src="images/laender_karten/<?php echo strtolower($country_id) ?>.gif" alt="<?php echo $country_id?>" width="144" height="" />
<?PHP
}
?>

<table>
<tr>
<td>

<table>
<tr>
<td>
<?PHP
echo JHTML::_('select.genericlist', $federationselect[$value], 'jlamtopfederation'.$value.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewcountries('.$module->id.',\''.$value.'\');"',  'value', 'text', $country_id);
?>
</td>
</tr>


<?PHP
if ( $countryassocselect[$fed_array]['assocs'] )
{
?>
<tr>
<td>
<?PHP
echo JHTML::_('select.genericlist', $countryassocselect[$fed_array]['assocs'], 'jlamtopassoc'.$value.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubassoc('.$module->id.',\''.$value.'\');"',  'value', 'text', $assoc_id);
?>
</td>
</tr>
<?PHP
}
?>



<?PHP
if ( $countrysubassocselect[$fed_array]['assocs'] )
{
?>
<tr>
<td>
<?PHP
echo JHTML::_('select.genericlist', $countrysubassocselect[$fed_array]['assocs'], 'jlamtopsubassoc'.$value.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubsubassoc('.$module->id.',\''.$value.'\');"',  'value', 'text', $subassoc_id);
?>
</td>
</tr>
<?PHP
}
?>



<?PHP
if ( $countrysubsubassocselect[$fed_array]['subassocs'] )
{
?>
<tr>
<td>
<?PHP
echo JHTML::_('select.genericlist', $countrysubsubassocselect[$fed_array]['subassocs'], 'jlamtopsubsubassoc'.$value.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubsubsubassoc('.$module->id.',\''.$value.'\');"',  'value', 'text', $subsubassoc_id);
?>
</td>
</tr>
<?PHP
}
?>



<?PHP
if ( $countrysubsubsubassocselect[$fed_array]['subsubassocs'] )
{
?>
<tr>
<td>
<?PHP
echo JHTML::_('select.genericlist', $countrysubsubsubassocselect[$fed_array]['subsubassocs'], 'jlamtopsubsubsubassoc'.$value.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubsubsubassoc('.$module->id.',\''.$value.'\');"',  'value', 'text', $subsubsubassoc_id);
?>
</td>
</tr>
<?PHP
}
?>



<?PHP
if ( $leagueselect[$fed_array]['leagues'] )
{
?>
<tr>
<td>
<?PHP
echo JHTML::_('select.genericlist', $leagueselect[$fed_array]['leagues'], 'jlamtopleagues'.$value.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewprojects('.$module->id.',\''.$value.'\');"',  'value', 'text', $league_id);
?>
</td>
</tr>
<?PHP
}
?>



<?PHP
if ( $projectselect[$fed_array]['projects'] )
{
?>
<tr>
<td>
<?PHP
echo JHTML::_('select.genericlist', $projectselect[$fed_array]['projects'], 'jlamtopprojects'.$value.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewdivisions('.$module->id.',\''.$value.'\');"',  'value', 'text', $project_id);
?>
</td>
</tr>
<?PHP
}
?>


<?PHP
if ( $projectselect[$fed_array]['teams'] )
{
?>
<tr>
<td>
<?PHP
echo JHTML::_('select.genericlist', $projectselect[$fed_array]['teams'], 'jlamtopteams'.$value.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewteams('.$module->id.',\''.$value.'\');"',  'value', 'text', $team_id);
?>
</td>
</tr>
<?PHP
}
?>



</table>
</td>

<td>
<table>
<tr>
<td>
<?php if ( $project_id ) { ?>
<div style="margin: 0 auto;">
<fieldset class="">

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
</td>
</tr>


<tr>
<td>
<?php if ( $team_id ) { ?>
<div style="margin: 0 auto;">
<fieldset class="">

<ul class="nav-list">
<?php if ($params->get('show_nav_links')): ?>
	
		<?php for ($i = 17; $i < 23; $i++): ?>
			<?php if ($params->get('navpointct'.$i) && $link = $helper->getLink($params->get('navpointct'.$i))): ?>
				<li class="nav-item"><?php echo JHTML::link(JRoute::_($link), $params->get('navpointct_label'.$i)); ?></li>
			<?php elseif ($params->get('navpointct'.$i) == "separator"): ?>
				<li class="nav-item separator"><?php echo $params->get('navpointct_label'.$i); ?></li>
			<?php endif; ?>
		<?php endfor; ?>
    
    
    
        
     <?php
     
    
    endif; 
    ?>
</ul> 
</fieldset>	   
</div>
<?php } ?>
</td>
</tr>


</table>
</td>
</tr>
</table>
</p>

<!--jlajaxtopmenu<?php echo $value?>-<?php echo $module->id?> end-->
</div>

<?PHP
}

echo JHTML::_('tabs.end');

echo JHTML::_('sliders.end');
?>

<?php
if($ajax && $ajaxmod==$module->id){ exit(); } ?>