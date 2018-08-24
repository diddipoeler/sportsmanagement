<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
* @version   1.0.05
* @file      default.php
* @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
* @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
* @license   This file is part of SportsManagement.
* @package   sportsmanagement
* @subpackage teams
*/

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<script>

	function searchTeam(val,key)
	{
		var f = $('adminForm');
		if(f)
		{
		f.elements['search'].value=val;
		
		f.submit();
		}
	}

</script>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">

<?PHP
if ( $this->assign )
	{
?>	
 <button type="button" onclick="Joomla.submitform('seasons.applyteams', this.form);">
<?php echo Text::_('JAPPLY');?></button>
<?php
echo $this->loadTemplate('data');
	}
	else
	{
echo $this->loadTemplate('joomla_version');
	}
?>	
<input type="hidden" name="search_mode" value="<?php echo $this->lists['search_mode']; ?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="season_id" value="<?php echo $this->season_id; ?>" />	
<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
<input type="hidden" name="club_id" value="<?php echo $this->club_id; ?>" />
<?php echo JHtml::_('form.token'); ?>
</form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   
