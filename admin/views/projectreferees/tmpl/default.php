<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 * @package   sportsmanagement
 * @subpackage projectreferees
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<style>
.search-item {
    font:normal 11px tahoma,arial,helvetica,sans-serif;
    padding:3px 10px 3px 10px;
    border:1px solid #fff;
    border-bottom:1px solid #eeeeee;
    white-space:normal;
    color:#555;
}
.search-item h3 {
    display:block;
    font:inherit;
    font-weight:bold;
    color:#222;
}

.search-item h3 span {
    float: right;
    font-weight:normal;
    margin:0 0 5px 5px;
    width:100px;
    display:block;
    clear:none;
}
</style>
<script>

	var quickaddsearchurl = '<?php echo Uri::root();?>administrator/index.php?option=com_sportsmanagement&task=quickadd.searchreferee';

	function searchPlayer(val)
	{
        var s= document.getElementById("search");
        s.value = val;
        Joomla.submitform('', this.form)
	}
</script>
<?php
$uri=Uri::root();
?>

<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<?PHP
if(version_compare(JVERSION,'3.0.0','ge')) 
{
echo $this->loadTemplate('joomla3');
}
else
{
echo $this->loadTemplate('joomla2');    
}
?>
<!-- <fieldset class="adminform"> -->
	<legend>
	<?php
	echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTREFEREES_QUICKADD_REFEREE');
	?>
	</legend>
	<form id="quickaddForm" action="<?php echo Uri::root(); ?>administrator/index.php?option=com_sportsmanagement&task=quickadd.addreferee" method="post">
	<input type="hidden" id="cpersonid" name="cpersonid" value="">
	<table>
		<tr>
			<td><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTREFEREES_QUICKADD_DESCR');?>:</td>
			<td><input type="text" name="quickadd" id="quickadd"  size="50" /></td>
			<td><input type="submit" name="submit" id="submit" value="<?php echo Text::_('Add');?>" /></td>
		</tr>
	</table>
	<?php echo HTMLHelper::_('form.token'); ?>
	</form>
<!-- </fieldset> -->

<?PHP
if ( $this->items )
{
echo $this->loadTemplate('data');
}
else
{
echo '<div class="alert alert-no-items">';
echo Text::_('JGLOBAL_NO_MATCHING_RESULTS');
echo '</div>';    
}

?>
	<input type="hidden" name="search_mode"			value="<?php echo $this->lists['search_mode'];?>" id="search_mode" />
    <input type="hidden" name="pid" value="<?php echo $this->project_id; ?>" />
	<input type="hidden" name="task"				value="" />
	<input type="hidden" name="boxchecked"			value="0" />
	<input type="hidden" name="filter_order"		value="<?php echo $this->sortColumn; ?>" />
	<input type="hidden" name="filter_order_Dir"	value="<?php echo $this->sortDirection; ?>" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   
