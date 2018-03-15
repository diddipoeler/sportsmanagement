<?php 
/** SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage templates
 */

defined('_JEXEC') or die('Restricted access');
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
JHtml::_('behavior.tooltip');JHtml::_('behavior.modal');
?>
<script>
	function searchTemplate(val,key)
	{
		var f = $('adminForm');
		if(f){
			f.elements['search'].value=val;
			f.elements['search_mode'].value= 'matchfirst';
			f.submit();
		}
	}
</script>

		
	
		<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm"  name="adminForm">
<?PHP
if(version_compare(JVERSION,'3.0.0','ge')) 
{
echo $this->loadTemplate('joomla3');
}
else
{
echo $this->loadTemplate('joomla2');    
}

echo $this->loadTemplate('data');
?>	
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
			<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
<input type="hidden" name="pid" value="<?php echo $this->projectws->id; ?>" />
			<?php echo JHtml::_('form.token')."\n"; ?>
		</form>

<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
?>   
