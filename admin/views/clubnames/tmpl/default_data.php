<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage clubnames
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

$this->saveOrder = $this->sortColumn == 'obj.ordering';
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
<div class="table-responsive" id="editcell_clubnames">
<table class="<?php echo $this->table_data_class; ?>" id="<?php echo $this->view; ?>list">
        <thead>
        <tr>
            <th width="5" style="vertical-align: top; ">
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_RESET');
				?>
            </th>
            <th width="20" style="vertical-align: top; ">
                <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>
            <th width="20" style="vertical-align: top; ">
                &nbsp;
            </th>
            <th style="vertical-align: top; ">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBNAMES_NAME', 'obj.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th style="vertical-align: top; ">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBNAMES_L_NAME', 'obj.name_long', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_LEAGUES_COUNTRY', 'obj.country', $this->sortDirection, $this->sortColumn);
				?>
            </th>


            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 'obj.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="85" style="vertical-align: top; ">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'obj.ordering', $this->sortDirection, $this->sortColumn);
				echo '<br />';
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'clubnames.saveorder');
				?>
            </th>

            <th style="vertical-align: top; ">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'obj.id', $this->sortDirection, $this->sortColumn);
				?>
            </th>
        </tr>
        </thead>

        <tfoot>
        <tr>
            <td colspan="8">
				<?php
				echo $this->pagination->getListFooter();
				?>
            </td>
            <td colspan='2'>
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

			$link       = Route::_('index.php?option=com_sportsmanagement&task=clubname.edit&id=' . $this->item->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'clubnames.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.clubname.' . $this->item->id) && $canCheckin;
			?>
            <tr class="row<?php echo $this->count_i % 2; ?>" <?php echo $this->dragable_group; ?>>
            
                <td style="text-align:center; ">
					<?php echo $this->pagination->getRowOffset($this->count_i); ?>
                </td>
                <td style="text-align:center; ">
					<?php echo HTMLHelper::_('grid.id', $this->count_i, $this->item->id); ?>
                </td>
				<?php

				$inputappend = '';
				?>
                <td style="text-align:center; ">
					<?php
					if ($this->item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'clubnames.', $canCheckin); ?>
					<?php else: ?>
                        <a href="<?php echo $link; ?>">
							<?php
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBNAME_EDIT_DETAILS');
$image_attributes['title'] = $imageTitle;
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/edit.png',$imageTitle,$image_attributes);
							?>
                        </a>
					<?php endif; ?>
                </td>
				<?php

				?>
                <td>
					<?php
					echo $this->item->name;
					?>

                </td>
                <td>
					<?php
					echo $this->item->name_long;
					?>
                </td>
                <td class="center">
					<?php
					echo JSMCountries::getCountryFlag($this->item->country);
					$append = ' onchange="document.getElementById(\'cb' . $this->count_i . '\').checked=true" ';
					echo HTMLHelper::_(
						'select.genericlist', $this->lists['nation'], 'country' . $this->item->id,
						'class="form-control form-control-inline" size="1"' . $append, 'value', 'text', $this->item->country
					);
					?>
                </td>


                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'clubnames.', $canChange, 'cb'); ?>
						<?php
						// Create dropdown items and render the dropdown list.
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'clubnames');
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'clubnames');
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
                <td style="text-align:center; ">
					<?php
					echo $this->item->id;
					?>
                </td>
            </tr>
			<?php
			//$k = 1 - $k;
		}
		?>
        </tbody>
    </table>
</div>
<!--	</fieldset> -->
