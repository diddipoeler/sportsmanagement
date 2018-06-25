<?php 
/** SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version   1.0.05
 * @file      default.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage allteams
 */

defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.framework');
JHtml::_('behavior.modal');

// Make sure that in case extensions are written for mentioned (common) views,
// that they are loaded i.s.o. of the template of this view
$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

if (version_compare(JSM_JVERSION, '4', 'eq')) {
    $uri = JUri::getInstance();   
} else {
    $uri = JFactory::getURI();
}
?>
<script language="javascript" type="text/javascript">
function tableOrdering( order, dir, task )
{
        var form = document.adminForm;

        form.filter_order.value = order;
        form.filter_order_Dir.value = dir;
        document.adminForm.submit( task );
}
function searchPerson(val)
	{
        var s= document.getElementById("filter_search");
        s.value = val;
        Joomla.submitform('', this.form)
	}
</script>
<div class="<?php echo COM_SPORTSMANAGEMENT_BOOTSTRAP_DIV_CLASS; ?>">
<form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars($uri->toString());?>" method="post">
	<fieldset class="filters">
	<legend class="hidelabeltxt"><?php echo JText::_('JGLOBAL_FILTER_LABEL'); ?></legend>
	<div class="filter-search">

		<!--label class="filter_search-lbl" for="filter_search"><!--?php echo JText::_('JSEARCH_FILTER_LABEL').':&#160;'; ?></label-->
		<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->escape($this->filter); ?>" class="inputbox" onchange="document.getElementById('adminForm').submit();" />

		<button type="submit" class="btn" title=""><i class="icon-search"></i></button>
		<button type="button" class="btn" title="" onclick="document.id('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
		<!--button type="submit" class="button"><!--?php echo JText::_('JGLOBAL_FILTER_BUTTON'); ?></button-->
		
        <!--button class="button" onclick="document.getElementById('filter_search').value='';this.form.submit(); ">
					<!--?php
					echo JText::_('JSEARCH_FILTER_CLEAR');
					?>
				</button-->
        <td nowrap='nowrap' align='right'><?php echo $this->lists['nation2'].'&nbsp;&nbsp;'; ?></td>
        
        <td align="center" colspan="4">
				<?php
                $startRange = JComponentHelper::getParams(JFactory::getApplication()->input->getCmd('option'))->get('character_filter_start_hex', '0');
		$endRange = JComponentHelper::getParams(JFactory::getApplication()->input->getCmd('option'))->get('character_filter_end_hex', '0');
		for ($i=$startRange; $i <= $endRange; $i++)
		{
			
            //printf("<a href=\"javascript:searchPerson('%s')\">%s</a>&nbsp;&nbsp;&nbsp;&nbsp;",chr($i),chr($i));
            printf("<a href=\"javascript:searchPerson('%s')\">%s</a>&nbsp;&nbsp;&nbsp;&nbsp;",'&#'.$i.';','&#'.$i.';');
			}
				
				?>
			</td>
	</div>

	
<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
<input type="hidden" name="limitstart" value="" />
        
    <div class="display-limit">
			<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
        
	</fieldset>

	<?php echo $this->loadTemplate('items'); 
    echo "<div>";
		echo $this->loadTemplate('backbutton');
		echo $this->loadTemplate('footer');
	echo "</div>";
    ?>
</form>
</div>

