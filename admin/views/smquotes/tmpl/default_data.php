<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage smquotes
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
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
JHtml::_('sortablelist.sortable', $this->view.'list', 'adminForm', strtolower($this->sortDirection), $saveOrderingUrl,$this->saveOrderButton);    
}
}
?>
<div class="table-responsive" id="editcell">
    <table class="<?php echo $this->table_data_class; ?>" id="<?php echo $this->view; ?>list">
        <thead>
        <tr>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="20">
                <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>
            <th width="20">&nbsp;</th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'JAUTHOR', 'obj.author', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE'); ?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'JGLOBAL_ARTICLES', 'obj.quote', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'JCATEGORY', 'obj.catid', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="10%">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'obj.ordering', $this->sortDirection, $this->sortColumn);
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'smquotes.saveorder');
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 'obj.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="20">
				<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'obj.id', $this->sortDirection, $this->sortColumn); ?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="6"><?php echo $this->pagination->getListFooter(); ?>
            </td>
            <td colspan="3"><?php echo $this->pagination->getResultsCounter(); ?></td>
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
			$link       = Route::_('index.php?option=com_sportsmanagement&task=smquote.edit&id=' . $this->item->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->count_i->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $this->count_i->checked_out_time, 'smquotes.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.smquote.' . $this->item->id) && $canCheckin;
			?>
            <tr class="row<?php echo $this->count_i % 2; ?>" <?php echo $this->dragable_group; ?>>
                <td class="center"><?php echo $this->pagination->getRowOffset($i); ?></td>
                <td class="center"><?php echo HTMLHelper::_('grid.id', $this->count_i, $this->item->id); ?></td>
				<?php

				$inputappend = '';
				?>
                <td class="center">
					<?php
					if ($this->item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'smquotes.', $canCheckin); ?>
					<?php endif;

					if ($canEdit && !$this->item->checked_out) :
						?>
                        <a href="<?php echo $link; ?>">
							<?php
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_QUOTES_EDIT_DETAILS');
$image_attributes['title'] = $imageTitle;
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/edit.png',$imageTitle,$image_attributes);
							?>
                        </a>
					<?php
					endif;

					?>
                </td>
				<?php

				?>
                <td><?php echo $this->item->author; ?></td>

                <td>
					<?php
					if (empty($this->item->picture))
					{
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE') . COM_SPORTSMANAGEMENT_PICTURE_SERVER . $this->item->picture;
$image_attributes['title'] = $imageTitle;
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/delete.png',$imageTitle,$image_attributes);
					}
                    elseif ($this->count_i->picture == sportsmanagementHelper::getDefaultPlaceholder("player"))
					{
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_DEFAULT_IMAGE');
$image_attributes['title'] = $imageTitle;
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/information.png',$imageTitle,$image_attributes);
					}
					else
					{

						?>
                        <a href="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER . $this->item->picture; ?>"
                           title="<?php echo $this->item->name; ?>" class="modal">
                            <img src="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER . $this->item->picture; ?>"
                                 alt="<?php echo $this->item->name; ?>" width="20"/>
                        </a>
						<?PHP
					}
					?>
                </td>


                <td><?php echo $this->item->quote; ?></td>
                <td><?php echo $this->escape($this->item->category_title); ?></td>

<td class="order" id="defaultdataorder">
<?php
echo $this->loadTemplate('data_order');
?>
</td>                
                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'smquotes.', $canChange, 'cb'); ?>
						<?php
						// Create dropdown items and render the dropdown list.
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'smquotes');
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'smquotes');
							echo HTMLHelper::_('actionsdropdown.render', $this->escape($this->item->name));
						}
						?>
                    </div>


                </td>
                <td class="center"><?php echo $this->item->id; ?></td>
            </tr>
			<?php

		}
		?>
        </tbody>
    </table>
</div>
  
