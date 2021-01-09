<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage jlextassociastions
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

$this->saveOrder = $this->sortColumn == 'objassoc.ordering';
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
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
        <tr>
            <th width="5" style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="20" style="vertical-align: top; ">
                <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);"/>
            </th>

            <th class="title" nowrap="nowrap" style="vertical-align: top; ">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_NAME', 'objassoc.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th class="title" nowrap="nowrap" style="vertical-align: top; ">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_SHORT_NAME', 'objassoc.short_name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="10%" class="title" style="vertical-align: top; ">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_COUNTRY', 'objassoc.country', $this->sortDirection, $this->sortColumn);
				?>
            </th>

            <th width="5"
                style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_FLAG'); ?></th>
            <th width="5" style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_ICON'); ?></th>

            <th width="" class="title">
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 'objassoc.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>

            <th width="85" nowrap="nowrap" style="vertical-align: top; ">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'objassoc.ordering', $this->sortDirection, $this->sortColumn);
				echo '<br />';
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'jlextassociations.saveorder');
				?>
            </th>
            <th width="20" style="vertical-align: top; ">
				<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'objassoc.id', $this->sortDirection, $this->sortColumn); ?>
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
			$link       = Route::_('index.php?option=com_sportsmanagement&task=jlextassociation.edit&id=' . $this->item->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'jlextassociations.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.jlextassociation.' . $this->item->id) && $canCheckin;
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
                <td style="text-align:center; ">
					<?php if ($this->item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->item->editor, $this->item->checked_out_time, 'jlextassociations.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=jlextassociation.edit&id=' . (int) $this->item->id); ?>">
							<?php echo $this->escape($this->item->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($this->item->name); ?>
					<?php endif; ?>



					<?php
					if ($this->item->website != '')
					{
						echo '<a href="' . $this->item->website . '" target="_blank"><span class="label label-success" title="' . $this->item->website . '">' . Text::_('JYES') . '</span></a>';
					}
					else
					{
						echo '<span class="label">' . Text::_('JNO') . '</span>';
					}
					?>

					<?php
					?>
                    <p class="smallsub">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($this->item->alias)); ?></p>
                </td>
				<?php

				?>

                <td><?php echo $this->item->short_name; ?></td>
                <td style="text-align:center; "><?php echo JSMCountries::getCountryFlag($this->item->country); ?></td>
                <td style="text-align:center; ">
					<?php
					if (empty($this->item->assocflag) || !File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . $this->item->assocflag))
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE') . $this->item->assocflag;
						echo HTMLHelper::_(
							'image', 'administrator/components/com_sportsmanagement/assets/images/delete.png',
							$imageTitle, 'title= "' . $imageTitle . '"'
						);
					}
					else
					{
						?>
                        <a href="<?php echo Uri::root() . $this->item->assocflag; ?>" title="<?php echo $this->item->name; ?>"
                           class="modal">
                            <img src="<?php echo Uri::root() . $this->item->assocflag; ?>" alt="<?php echo $this->item->name; ?>"
                                 width="20"/>
                        </a>
						<?PHP
					}
					?>
                </td>
                <td style="text-align:center; ">
					<?php
					if (empty($this->item->picture) || !File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . $this->item->picture))
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE') . $this->item->picture;
						echo HTMLHelper::_(
							'image', 'administrator/components/com_sportsmanagement/assets/images/delete.png',
							$imageTitle, 'title= "' . $imageTitle . '"'
						);
					}
					else
					{
						?>
                        <a href="<?php echo Uri::root() . $this->item->picture; ?>" title="<?php echo $this->item->name; ?>"
                           class="modal">
                            <img src="<?php echo Uri::root() . $this->item->picture; ?>" alt="<?php echo $this->item->name; ?>"
                                 width="20"/>
                        </a>
						<?PHP
					}
					?>
                </td>
                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'jlextassociations.', $canChange, 'cb'); ?>
						<?php 
                        /** Create dropdown items and render the dropdown list. */
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'jlextassociations');
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'jlextassociations');
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
                <td style="text-align:center; "><?php echo $this->item->id; ?></td>
            </tr>
			<?php
			$k = 1 - $k;
		}
		?>
        </tbody>
    </table>
</div>
  
