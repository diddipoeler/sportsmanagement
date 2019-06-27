<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      infofield.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage joomleagueimports
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">

<table class="<?php echo $this->table_data_class; ?>">
<tr>
<td class="nowrap" align="center">
<img src= "<?php echo Uri::base( true ) ?>/components/com_sportsmanagement/assets/icons/jl.png" width="180" height="auto" >
</td>
<td class="nowrap" align="center">
<div id="delayMsg"></div>
<table class="<?php echo $this->table_data_class; ?>">
<?PHP
$i = 0;
foreach( $this->get_info_fields as $key => $value )
{
?>
<tr>
<td class="nowrap" align="center">
<?PHP    
$inputappend = '';
$append = ' style="background-color:#bbffff"';
echo $value->info;
?>
</td>
<td class="nowrap" align="center">
<?PHP
echo HTMLHelper::_(	'select.genericlist',
$this->lists['agegroup'],
'agegroup['.$value->info.']',
$inputappend.'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
$i.'\').checked=true"'.$append,
'value','text',$value->agegroup_id);
echo '<br>';
?>
</td>
</tr>
<?PHP
}
?>
</table>                                                    
</td>
<td class="nowrap" align="center">
<img src= "<?php echo Uri::base( true ) ?>/components/com_sportsmanagement/assets/icons/logo_transparent.png" width="180" height="auto" >
</td>
</tr>
</table>

<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order" value="" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
<input type="hidden" name="jl_table_import_step" value="<?php echo $this->jl_table_import_step; ?>" />

<?php echo HTMLHelper::_('form.token')."\n"; ?>
</form>

<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>