<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage divisions
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

$this->saveOrder = $this->sortColumn == 'dv.ordering';
if ($this->saveOrder && !empty($this->items))
{
$saveOrderingUrl = 'index.php?option=com_sportsmanagement&task='.$this->view.'.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{    
HTMLHelper::_('draggablelist.draggable');
}
else
{
JHtml::_('sortablelist.sortable', $this->view.'list', 'adminForm', strtolower($this->sortDirection), $saveOrderingUrl,$this->saveOrderButton);    
}
}
?>
<legend>
	<?php
	echo Text::sprintf(
		'COM_SPORTSMANAGEMENT_ADMIN_DIVS_TITLE2',
		'<i>' . $this->projectws->name . '</i>'
	);
	?>
</legend>

<div class="table-responsive" id="editcell">
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
            <th class="title" style="vertical-align: top; ">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_DIVS_NAME', 'dv.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th class="title" style="vertical-align: top; ">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_DIVS_S_NAME', 'dv.shortname', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th class="title" style="vertical-align: top; ">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_DIVS_PARENT_NAME', 'parent_name', $this->sortDirection, $this->sortColumn);
				?>
            </th>

            <th class="title" style="vertical-align: top; ">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE', 'dv.picture', $this->sortDirection, $this->sortColumn);
				?>
            </th>

            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 'dv.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="85" style="vertical-align: top; ">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'dv.ordering', $this->sortDirection, $this->sortColumn);
				echo '<br />';
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'divisions.saveorder');
				?>
            </th>

            <th style="vertical-align: top; ">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'dv.id', $this->sortDirection, $this->sortColumn);
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
            //$this->count_i = $i;
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
$this->dragable_group = 'data-dragable-group="none"';
} 
			$link       = Route::_('index.php?option=com_sportsmanagement&task=division.edit&id=' . $this->item->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'divisions.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.division.' . $this->item->id) && $canCheckin;
			?>
            <tr class="row<?php echo $this->count_i % 2; ?>" <?php echo $this->dragable_group; ?>>
                <td style="text-align:center; ">
					<?php echo $this->pagination->getRowOffset($i); ?>
                </td>
                <td style="text-align:center; ">
					<?php echo HTMLHelper::_('grid.id', $i, $this->item->id); ?>
                </td>
				<?php

				$inputappend = '';
				?>
                <td style="text-align:center; ">
					<?php
					if ($row->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'divisions.', $canCheckin); ?>
					<?php else: ?>
                        <a href="<?php echo $link; ?>">
							<?php
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_DIVS_EDIT_DETAILS');
$image_attributes['title'] = $imageTitle;
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/edit.png',$imageTitle,$image_attributes);
							?>
                        </a>
					<?php endif; ?>
                </td>
				<?php

				?>
                <td>
                    <input tabindex="2" type="text" size="30" maxlength="64" class="form-control form-control-inline"
                           name="name<?php echo $this->item->id; ?>" value="<?php echo $this->item->name; ?>"
                           onchange="document.getElementById('cb<?php echo $this->count_i; ?>').checked=true"/>
					<?php
					//echo $row->name;
					?>
                    <p class="smallsub">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($this->item->alias)); ?></p>
                </td>
                <td>
					<?php
					echo $this->item->shortname;
					?>
                </td>
                <td>
					<?php
					echo $this->item->parent_name;
					?>
                </td>
                <td>
					<?php
					if (empty($row->picture) || !File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . $this->item->picture))
					{
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE') . $row->picture;
$image_attributes['title'] = $imageTitle;
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/delete.png',$imageTitle,$image_attributes);
					}
                    elseif ($row->picture == sportsmanagementHelper::getDefaultPlaceholder("player"))
					{
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_DEFAULT_IMAGE');
$image_attributes['title'] = $imageTitle;
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/information.png',$imageTitle,$image_attributes);
					}
					else
					{
						?>
                        <a href="<?php echo Uri::root() . $row->picture; ?>" title="<?php echo $row->name; ?>"
                           class="modal">
                            <img src="<?php echo Uri::root() . $row->picture; ?>" alt="<?php echo $row->name; ?>"
                                 width="20"/>
                        </a>
						<?PHP
					}
					?>
                </td>

                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'divisions.', $canChange, 'cb'); ?>
						<?php
						/** Create dropdown items and render the dropdown list. */
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'divisions');
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'divisions');
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

