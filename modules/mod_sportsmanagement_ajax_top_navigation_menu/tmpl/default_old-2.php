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
echo JHtml::_('sliders.start','menueslidername', array('useCookie'=>0, 'show'=>0, 'display'=>0, 'startOffset'=>-1));
//echo JHtml::_('sliders.start','menueslidername'), array('show'=>0,'display'=>0, 'startOffset'=>-1);
echo JHtml::_('sliders.panel', JText::_('Federations Auswahlmenü'), 'menue-params');





// tabs anzeigen
$idxTab = 100;
echo JHTML::_('tabs.start','tabs_ajaxtopmenu', array('useCookie'=>1));
echo JHTML::_('tabs.panel', JText::_('AFC'), 'panelmenue'.($idxTab++));
?>

<div id="jlajaxtopmenu-afc<?php echo $module->id ?>">
<!--jlajaxtopmenuafc-<?php echo $module->id?> start-->
<p><img style="float: left;" src="images/dfs_kl_afc.gif" alt="AFC" width="144" height="" />
<table>
<tr>
<td>
<?PHP
echo JHTML::_('select.genericlist', $federationselect['afc'], 'jlamtopfederationafc'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewcountries('.$module->id.',\'afc\');"',  'value', 'text', $country_id);
?>
</td>
</tr>
<tr>
<td>
<?PHP
if ( $countryassocselect['assocs'] )
{
echo JHTML::_('select.genericlist', $countryassocselect['assocs'], 'jlamtopassocafc'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubassoc('.$module->id.',\'afc\');"',  'value', 'text', $assoc_id);
}
?>
</td>
</tr>
<tr>
<td>
<?PHP
if ( $countrysubassocselect['assocs'] )
{
echo JHTML::_('select.genericlist', $countrysubassocselect['assocs'], 'jlamtopsubassocafc'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubassoc('.$module->id.',\'afc\');"',  'value', 'text', $subassoc_id);
}
?>
</td>
</tr>
</table>
</p>
<!--jlajaxtopmenuafc-<?php echo $module->id?> end-->
</div>

<?PHP
echo JHTML::_('tabs.panel', JText::_('CAF'), 'panelmenue'.($idxTab++));
?>
<div id="jlajaxtopmenu-caf<?php echo $module->id ?>">
<!--jlajaxtopmenucaf-<?php echo $module->id?> start-->
<p><img style="float: left;" src="images/dfs_kl_caf.gif" alt="CAF" width="144" height="" />
<table>
<tr>
<td>
<?PHP
echo JHTML::_('select.genericlist', $federationselect['caf'], 'jlamtopfederationcaf'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewcountries('.$module->id.',\'caf\');"',  'value', 'text', $country_id);
?>
</td>
</tr>
<tr>
<td>
<?PHP
if ( $countryassocselect['assocs'] )
{
echo JHTML::_('select.genericlist', $countryassocselect['assocs'], 'jlamtopassoccaf'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubassoc('.$module->id.',\'caf\');"',  'value', 'text', $assoc_id);
}
?>
</td>
</tr>
<tr>
<td>
<?PHP
if ( $countrysubassocselect['assocs'] )
{
echo JHTML::_('select.genericlist', $countrysubassocselect['assocs'], 'jlamtopsubassoccaf'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubassoc('.$module->id.',\'caf\');"',  'value', 'text', $subassoc_id);
}
?>
</td>
</tr>
</table>
</p>
<!--jlajaxtopmenucaf-<?php echo $module->id?> end-->
</div>
<?PHP
echo JHTML::_('tabs.panel', JText::_('CONMEBOL'), 'panelmenue'.($idxTab++));
?>
<div id="jlajaxtopmenu-conmebol<?php echo $module->id ?>">
<!--jlajaxtopmenuconmebol-<?php echo $module->id?> start-->
<p><img style="float: left;" src="images/dfs_kl_conmebol.gif" alt="CONMEBAL" width="144" height="" />
<table>
<tr>
<td>
<?PHP
echo JHTML::_('select.genericlist', $federationselect['conmebol'], 'jlamtopfederationconmebol'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewcountries('.$module->id.',\'conmebol\');"',  'value', 'text', $country_id);
?>
</td>
</tr>
<tr>
<td>
<?PHP
if ( $countryassocselect['assocs'] )
{
echo JHTML::_('select.genericlist', $countryassocselect['assocs'], 'jlamtopassocconmebol'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubassoc('.$module->id.',\'conmebol\');"',  'value', 'text', $assoc_id);
}
?>
</td>
</tr>
<tr>
<td>
<?PHP
if ( $countrysubassocselect['assocs'] )
{
echo JHTML::_('select.genericlist', $countrysubassocselect['assocs'], 'jlamtopsubassocconmebol'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubassoc('.$module->id.',\'conmebol\');"',  'value', 'text', $subassoc_id);
}
?>
</td>
</tr>
</table>
</p>
<!--jlajaxtopmenuconmebol-<?php echo $module->id?> end-->
</div>
<?PHP
echo JHTML::_('tabs.panel', JText::_('CONCACAF'), 'panelmenue'.($idxTab++));
?>
<div id="jlajaxtopmenu-concacaf<?php echo $module->id ?>">
<!--jlajaxtopmenuconcacaf-<?php echo $module->id?> start-->
<p><img style="float: left;" src="images/dfs_kl_concacaf.gif" alt="CONCACAF" width="144" height="" />
<table>
<tr>
<td>
<?PHP
echo JHTML::_('select.genericlist', $federationselect['concacaf'], 'jlamtopfederationconcacaf'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewcountries('.$module->id.',\'concacaf\');"',  'value', 'text', $country_id);
?>
</td>
</tr>
<tr>
<td>
<?PHP
if ( $countryassocselect['assocs'] )
{
echo JHTML::_('select.genericlist', $countryassocselect['assocs'], 'jlamtopassocconcacaf'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubassoc('.$module->id.',\'concacaf\');"',  'value', 'text', $assoc_id);
}
?>
</td>
</tr>
<tr>
<td>
<?PHP
if ( $countrysubassocselect['assocs'] )
{
echo JHTML::_('select.genericlist', $countrysubassocselect['assocs'], 'jlamtopsubassocconcacaf'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubassoc('.$module->id.',\'concacaf\');"',  'value', 'text', $subassoc_id);
}
?>
</td>
</tr>
</table>
</p>
<!--jlajaxtopmenuconcacaf-<?php echo $module->id?> end-->
</div>
<?PHP
echo JHTML::_('tabs.panel', JText::_('OFC'), 'panelmenue'.($idxTab++));
?>
<div id="jlajaxtopmenu-ofc<?php echo $module->id ?>">
<!--jlajaxtopmenuofc-<?php echo $module->id?> start-->
<p><img style="float: left;" src="images/dfs_kl_ofc.gif" alt="OFC" width="144" height="" />
<table>
<tr>
<td>
<?PHP
echo JHTML::_('select.genericlist', $federationselect['ofc'], 'jlamtopfederationofc'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewcountries('.$module->id.',\'ofc\');"',  'value', 'text', $country_id);
?>
</td>
</tr>
<tr>
<td>
<?PHP
if ( $countryassocselect['assocs'] )
{
echo JHTML::_('select.genericlist', $countryassocselect['assocs'], 'jlamtopassocofc'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubassoc('.$module->id.',\'ofc\');"',  'value', 'text', $assoc_id);
}
?>
</td>
</tr>
<tr>
<td>
<?PHP
if ( $countrysubassocselect['assocs'] )
{
echo JHTML::_('select.genericlist', $countrysubassocselect['assocs'], 'jlamtopsubassocofc'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubassoc('.$module->id.',\'ofc\');"',  'value', 'text', $subassoc_id);
}
?>
</td>
</tr>
</table>
</p>
<!--jlajaxtopmenuofc-<?php echo $module->id?> end-->
</div>
<?PHP
echo JHTML::_('tabs.panel', JText::_('UEFA'), 'panelmenue'.($idxTab++));
?>
<div id="jlajaxtopmenu-uefa<?php echo $module->id ?>">
<!--jlajaxtopmenuuefa-<?php echo $module->id?> start-->
<p><img style="float: left;" src="images/dfs_kl_uefa.gif" alt="UEFA" width="144" height="" />
<table>
<tr>
<td>
<?PHP
echo JHTML::_('select.genericlist', $federationselect['uefa'], 'jlamtopfederationuefa'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewcountries('.$module->id.',\'uefa\');"',  'value', 'text', $country_id);
?>
</td>
</tr>

<tr>
<td>
<?PHP
if ( $countryassocselect['assocs'] )
{
echo JHTML::_('select.genericlist', $countryassocselect['assocs'], 'jlamtopassocuefa'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubassoc('.$module->id.',\'uefa\');"',  'value', 'text', $assoc_id);
}
?>
</td>
</tr>
<tr>
<td>
<?PHP
if ( $countrysubassocselect['assocs'] )
{
echo JHTML::_('select.genericlist', $countrysubassocselect['assocs'], 'jlamtopsubassocuefa'.$module->id, 'class="inputbox" style="width:100%;visibility:show;" size="1" onchange="javascript:jlamtopnewsubassoc('.$module->id.',\'uefa\');"',  'value', 'text', $subassoc_id);
}
?>
</td>
</tr>

</table>
</p>
<!--jlajaxtopmenuuefa-<?php echo $module->id?> end-->
</div>
<?PHP
echo JHTML::_('tabs.end');


echo JHTML::_('sliders.end');
?>







<?php
if($ajax && $ajaxmod==$module->id){ exit(); } ?>