<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage sportstypes
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

$this->saveOrder = $this->sortColumn == 's.ordering';
if ($this->saveOrder && !empty($this->items))
{
$saveOrderingUrl = 'index.php?option=com_sportsmanagement&task='.$this->view.'.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{    
HTMLHelper::_('draggablelist.draggable');
}
else
{
HTMLHelper::_('sortablelist.sortable', $this->view.'list', 'adminForm', strtolower($this->sortDirection), $saveOrderingUrl,$this->saveOrderButton);    
}
}
?>
<script>
var last_value;
var current_value;
var attribute_cbnummer;
var id_cbnummer;
jQuery(document).ready(function() {
    jQuery("tr").click(function(){
jQuery(this).children("td:even").addClass("row-selected").end().children("td:odd").addClass("row-selected");
            		
       //alert("Click! "+ jQuery(this).find('td').html());
    });
});

jQuery(document).on("click","select",function(){
    last_value = $(this).val();
	attribute_cbnummer = $(this).attr('cbnummer');
	id_cbnummer = $(this).attr('id');
});

jQuery(document).on("change","select",function(){
	//last_value = $(this).val();
	//attribute_cbnummer = $(this).attr('cbnummer');
	//id_cbnummer = $(this).attr('id');
current_value = $(this).val();

    console.log('last value - '+last_value);
    console.log('current value - '+current_value);
	console.log('attribute_cbnummer - '+attribute_cbnummer);
	console.log('id_cbnummer - '+id_cbnummer);
$( "#"+attribute_cbnummer).prop( "checked", true );

});
</script>
<div class="table-responsive" id="editcell_sportstypes">
<table class="<?php echo $this->table_data_class; ?>" id="<?php echo $this->view; ?>list">
        <thead>
        <tr>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="20">
                <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>

            <th class="title">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_SPORTSTYPES_NAME', 's.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_EVENTS_TRANSLATION'); ?>
            </th>
            <th width="10%" class="title">
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_SPORTSTYPES_ICON'); ?>
            </th>
            <th width="10%" class="title">
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_SPORTSTYPES_SPORTSART'); ?>
            </th>

            <th width="" class="nowrap center">
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 's.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>

            <th width="10%">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 's.ordering', $this->sortDirection, $this->sortColumn);
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'sportstypes.saveorder');
				?>
            </th>
            <th>
				<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 's.id', $this->sortDirection, $this->sortColumn); ?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="100%" class="center">
				<?php echo $this->pagination->getListFooter(); ?>
				<?php echo $this->pagination->getResultsCounter(); ?>
            </td>
        </tr>
        </tfoot>
        <tbody <?php if ( $this->saveOrder && version_compare(substr(JVERSION, 0, 3), '4.0', 'ge') ) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->sortDirection); ?>" <?php endif; ?>>
		<?php
		foreach ($this->items as $this->count_i => $this->item) 
			{
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
$this->dragable_group = 'data-dragable-group="none"';
}                     
			$link       = Route::_('index.php?option=com_sportsmanagement&task=sportstype.edit&id=' . $this->item->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'sportstypes.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.sportstype.' . $this->item->id) && $canCheckin;
			?>
            <tr class="row<?php echo $this->count_i % 2; ?>" <?php echo $this->dragable_group; ?>>
                <td class="center">
					<?php
					echo $this->pagination->getRowOffset($this->count_i);
					?>
                </td>
                <td class="center">
					<?php
					echo HTMLHelper::_('grid.id', $this->count_i, $this->item->id);
					?>
                </td>

				<?php
				$inputappend = '';
				?>
                <td class="center">
					<?php if ($this->item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->item->editor, $this->item->checked_out_time, 'sportstypes.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=sportstype.edit&id=' . (int) $this->item->id); ?>">
							<?php echo $this->escape($this->item->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($this->item->name); ?>
					<?php endif; ?>



					<?php //echo $checked;  ?>
                </td>
				<?php ?>
                <td><?php echo Text::_($this->item->name); ?></td>

                <td class="center">
					<?php

					$desc    = Text::_($this->item->name);
echo sportsmanagementHelper::getBootstrapModalImage('collapseModallogo_icon' . $this->item->id, Uri::root() . $this->item->icon, $desc, '20', Uri::root() . $this->item->icon);
					?>

					<?PHP
					?>
                </td>
                <td>
<?php
$append = ' cbnummer="cb' . $this->count_i . '" style="background-color:#bbffff"';
echo HTMLHelper::_('select.genericlist', $this->lists['sportart'], 'sportstype_id' . $this->item->id, 'class="form-control form-control-inline" size="1"' . $append, 'value', 'text', $this->item->sportsart);
?>
			
                </td>
                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'sportstypes.', $canChange, 'cb'); ?>
						<?php
						// Create dropdown items and render the dropdown list.
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'sportstypes');
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'sportstypes');
							echo HTMLHelper::_('actionsdropdown.render', $this->escape($this->item->name));
						}
						?>
                    </div>
                </td>

<td class="order" id="defaultdataorder">
<?php
echo $this->loadTemplate('data_order');
?>
</td> 
                <td class="center"><?php echo $this->item->id; ?></td>
            </tr>
			<?php

		}
		?>
        </tbody>
    </table>
</div>
