<?php
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
* @version         1.0.05
* @file                agegroup.php
* @author                diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright        Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license                This file is part of SportsManagement.
*
* SportsManagement is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* SportsManagement is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with SportsManagement.  If not, see <http://www.gnu.org/licenses/>.
*
* Diese Datei ist Teil von SportsManagement.
*
* SportsManagement ist Freie Software: Sie können es unter den Bedingungen
* der GNU General Public License, wie von der Free Software Foundation,
* Version 3 der Lizenz oder (nach Ihrer Wahl) jeder späteren
* veröffentlichten Version, weiterverbreiten und/oder modifizieren.
*
* SportsManagement wird in der Hoffnung, dass es nützlich sein wird, aber
* OHNE JEDE GEWÄHELEISTUNG, bereitgestellt; sogar ohne die implizite
* Gewährleistung der MARKTFÄHIGKEIT oder EIGNUNG FÜR EINEN BESTIMMTEN ZWECK.
* Siehe die GNU General Public License für weitere Details.
*
* Sie sollten eine Kopie der GNU General Public License zusammen mit diesem
* Programm erhalten haben. Wenn nicht, siehe <http://www.gnu.org/licenses/>.
*
* Note : All ini files need to be saved as UTF-8 without BOM
*/

// no direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
?>
<script type="text/javascript">
var ajaxmenu_baseurl = '<?php echo JUri::base() ?>';
</script>

<?PHP

//echo 'tab_points=> <pre>'.print_r($tab_points, true).'</pre><br>';


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
echo JHtml::_('sliders.panel', JText::_('MOD_SPORTSMANAGEMENT_AJAX_TOP_NAVIGATION_MENU'), 'menue-params');

// tabs anzeigen
$idxTab = 100;
echo JHtml::_('tabs.start','tabs_ajaxtopmenu', array('useCookie'=>1, 'startOffset' => $startoffset ));

foreach ( $tab_points as $key => $value  )
{
$fed_array = strtoupper($value);

echo JHtml::_('tabs.panel', JText::_( strtoupper($value) ), 'panelmenue'.($idxTab++));
?>

<div id="jlajaxtopmenu-<?php echo $value?><?php echo $module->id ?>">

<!--jlajaxtopmenu<?php echo $value?>-<?php echo $module->id?> start-->

<?PHP
if ( COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO )
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

echo 'federationselect => <pre>'.print_r($federationselect[$value], true).'</pre><br>';
echo 'countryassocselect => <pre>'.print_r($countryassocselect[$fed_array]['assocs'], true).'</pre><br>';
echo 'countrysubassocselect => <pre>'.print_r($countrysubassocselect[$fed_array]['assocs'], true).'</pre><br>';
echo 'countrysubsubassocselect => <pre>'.print_r($countrysubsubassocselect[$fed_array]['subassocs'], true).'</pre><br>';
echo 'countrysubsubsubassocselect => <pre>'.print_r($countrysubsubsubassocselect[$fed_array]['subsubassocs'], true).'</pre><br>';
echo 'leagueselect => <pre>'.print_r($leagueselect[$fed_array]['leagues'], true).'</pre><br>';
echo 'projectselect => <pre>'.print_r($projectselect[$fed_array]['projects'], true).'</pre><br>';

echo 'getFederations => <pre>'.print_r(modSportsmanagementAjaxTopNavigationMenuHelper::$query_getFederations, true).'</pre><br>';
echo 'getFederationSelect => <pre>'.print_r(modSportsmanagementAjaxTopNavigationMenuHelper::$query_getFederationSelect, true).'</pre><br>';

echo 'getCountryAssocSelect => <pre>'.print_r(modSportsmanagementAjaxTopNavigationMenuHelper::$query_getCountryAssocSelect, true).'</pre><br>';
echo 'getCountryFederation => <pre>'.print_r(modSportsmanagementAjaxTopNavigationMenuHelper::$query_getCountryFederation, true).'</pre><br>';
echo 'getCountrySubAssocSelect => <pre>'.print_r(modSportsmanagementAjaxTopNavigationMenuHelper::$query_getCountrySubAssocSelect, true).'</pre><br>';
echo 'getCountrySubSubAssocSelect => <pre>'.print_r(modSportsmanagementAjaxTopNavigationMenuHelper::$query_getCountrySubSubAssocSelect, true).'</pre><br>';

echo 'getLeagueAssocId => <pre>'.print_r(modSportsmanagementAjaxTopNavigationMenuHelper::$query_getLeagueAssocId, true).'</pre><br>';
echo 'getLeagueSelect => <pre>'.print_r(modSportsmanagementAjaxTopNavigationMenuHelper::$query_getLeagueSelect, true).'</pre><br>';

}
?>

<p>
<img style="float: left;" src="images/com_sportsmanagement/database/laender_karten/dfs_kl_<?php echo strtolower($value)?>.gif" alt="<?php echo $value?>" width="144" height="" />

<?PHP

/*
echo 'value => '.$value.'<br>';
echo 'fed_array => '.$fed_array.'<br>';


echo 'jlamtopcountry / country_id => '.$country_id.'<br>';
echo 'country_federation => '.$country_federation.'<br>';

echo '_country_fed => <pre>'.print_r(modSportsmanagementAjaxTopNavigationMenuHelper::$_country_fed, true).'</pre><br>';

echo 'query_getFederations => <pre>'.print_r(modSportsmanagementAjaxTopNavigationMenuHelper::$query_getFederations, true).'</pre><br>';
echo 'query_getFederationSelect => <pre>'.print_r(modSportsmanagementAjaxTopNavigationMenuHelper::$query_getFederationSelect, true).'</pre><br>';


echo 'jlamtopassocid => '.$assoc_id.'<br>';
echo 'jlamtopsubassocid => '.$subassoc_id.'<br>';
echo 'jlamtopsubsubassocid => '.$subsubassoc_id.'<br>';


echo 'jlamtopseason => '.$season_id.'<br>';
echo 'jlamtopleague => '.$league_id.'<br>';
echo 'jlamtopproject => '.$project_id.'<br>';
echo 'jlamtopteam => '.$team_id.'<br>';

echo __METHOD__.' '.__LINE__.' leagueselect<br><pre>'.print_r($leagueselect,true).'</pre>';
echo __METHOD__.' '.__LINE__.' POST<br><pre>'.print_r($_POST,true).'</pre>';
echo __METHOD__.' '.__LINE__.' queryvalues<br><pre>'.print_r($queryvalues,true).'</pre>';
*/

if ( $country_id )
{
?>
<img style="float: right;" src="images/com_sportsmanagement/database/laender_karten/<?php echo strtolower($country_id) ?>.gif" alt="<?php echo $country_id?>" width="144" height="" />
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
if ( isset($countryassocselect[$fed_array]['assocs']) )
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
if ( isset($countrysubassocselect[$fed_array]['assocs']) )
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
if ( isset($countrysubsubassocselect[$fed_array]['subassocs']) )
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
if ( isset($countrysubsubsubassocselect[$fed_array]['subsubassocs']) )
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
if ( isset($leagueselect[$fed_array]['leagues']) )
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
if ( isset($projectselect[$fed_array]['projects']) )
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
if ( isset($projectselect[$fed_array]['teams']) )
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
    
    if ( $user_name == 'diddipoeler' )
        {
        $params_new = array(	"option" => "com_sportsmanagement",
				"view" => "jlusernewseason",
				"p" => $project_id);
	
		$query = sportsmanagementHelperRoute::buildQuery( $params_new );
		$link = JRoute::_( 'index.php?' . $query, false );
		    ?>		
<li class="nav-item"><?php echo JHTML::link(JRoute::_($link), 'neue Saison' ); ?></li>		
    <?php 
        }
    
    
    
    endif; 
    
    //if ( $user_name != '' )
    if ( $user_name == 'diddipoeler' )
    {
        $params_new = array(	"option" => "com_sportsmanagement",
				"view" => "jlxmlexports",
				"p" => $project_id);
	
		$query = sportsmanagementHelperRoute::buildQuery( $params_new );
		$link = JRoute::_( 'index.php?' . $query, false );
		    ?>		
<li class="nav-item"><?php echo JHTML::link(JRoute::_($link), 'XML Export' ); ?></li>		
    <?php 
        }
        
        
    ?>
</ul> 
</fieldset>	   
</div>
<?php } ?>
</td>

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

echo JHtml::_('tabs.end');

echo JHtml::_('sliders.end');
?>

<?php
if($ajax && $ajaxmod==$module->id){ exit(); } ?>