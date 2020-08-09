<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage positions
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

$this->saveOrder = $this->sortColumn == 'po.ordering';
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
    
if ($this->saveOrder && !empty($this->items))
{
$saveOrderingUrl = 'index.php?option=com_sportsmanagement&task='.$this->view.'.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';    
HTMLHelper::_('draggablelist.draggable');
}    
}
else
{
$saveOrderingUrl = 'index.php?option=com_sportsmanagement&task='.$this->view.'.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';    
JHtml::_('sortablelist.sortable', $this->view.'list', 'adminForm', strtolower($this->sortDirection), $saveOrderingUrl,null);
}   
?>
<div class="table-responsive" id="editcell">
<table class="<?php echo $this->table_data_class; ?>" id="<?php echo $this->view; ?>list">
        <thead>
        <tr>
            <th width="5">
				<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?>
            </th>
            <th width="20">
                <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_STANDARD_NAME_OF_POSITION', 'po.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_TRANSLATION'); ?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_IMAGE', 'po.picture', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_PARENTNAME', 'po.parent_id', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_SPORTSTYPE', 'po.sports_type_id', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_PERSON_TYPE', 'po.persontype', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="5%">
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_HAS_EVENTS'); ?>
            </th>
            <th width="5%">
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_HAS_STATS'); ?>
            </th>
            <th width="5%">
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 'po.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="10%">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'po.ordering', $this->sortDirection, $this->sortColumn);
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'positions.saveorder');
				?>
            </th>
            <th width="5%">
				<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'po.id', $this->sortDirection, $this->sortColumn); ?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="10">
				<?php echo $this->pagination->getListFooter(); ?>
            </td>
            <td colspan="4">
				<?php echo $this->pagination->getResultsCounter(); ?>
            </td>
        </tr>
        </tfoot>
        <tbody <?php if ( $this->saveOrder && version_compare(substr(JVERSION, 0, 3), '4.0', 'ge') ) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->sortDirection); ?>" data-nested="true"<?php endif; ?>>
		<?php
 foreach ($this->items as $this->count_i => $this->item)
		{
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
$this->dragable_group = 'data-dragable-group="none"';
} 
			$link       = Route::_('index.php?option=com_sportsmanagement&task=position.edit&id=' . $this->item->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'positions.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.position.' . $this->item->id) && $canCheckin;
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
						<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->item->editor, $this->item->checked_out_time, 'positions.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=position.edit&id=' . (int) $this->item->id); ?>">
							<?php echo $this->escape($this->item->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($this->item->name); ?>
					<?php endif; ?>
                    <div class="small">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($this->item->alias)); ?>
                    </div>
                </td>
                <td>
					<?php
					if ($this->item->name == Text::_($this->item->name))
					{
						echo '&nbsp;';
					}
					else
					{
						echo Text::_($this->item->name);
					}
					?>
                </td>
                <td width="5%" class="center">
					<?php
					if ($this->item->picture == '')
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_NO_IMAGE');
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', Uri::base() . '/components/com_sportsmanagement/assets/images/delete.png', $imageTitle, $image_attributes);
					}
                    elseif ($this->item->picture == sportsmanagementHelper::getDefaultPlaceholder("icon"))
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_DEFAULT_IMAGE');
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', Uri::base() . '/components/com_sportsmanagement/assets/images/information.png', $imageTitle, $image_attributes);
						?>
                        <a href="<?php echo Uri::root() . $this->item->picture; ?>" title="<?php echo $imageTitle; ?>"
                           class="modal">
                            <img src="<?php echo Uri::root() . $this->item->picture; ?>" alt="<?php echo $imageTitle; ?>"
                                 width="20"/>
                        </a>
						<?PHP
					}
                    elseif ($this->item->picture !== '')
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PLAYGROUNDS_CUSTOM_IMAGE');
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', Uri::base() . '/components/com_sportsmanagement/assets/images/ok.png', $imageTitle, $image_attributes['title']);
						?>
                        <a href="<?php echo Uri::root() . $this->item->picture; ?>" title="<?php echo $imageTitle; ?>"
                           class="modal">
                            <img src="<?php echo Uri::root() . $this->item->picture; ?>" alt="<?php echo $imageTitle; ?>"
                                 width="20"/>
                        </a>
						<?PHP
					}
					?>
                </td>

                <td>
					<?php
					echo HTMLHelper::_('select.genericlist', $this->lists['parent_id'], 'parent_id' . $this->item->id, '' . 'style="background-color:#bbffff" class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' . $i . '\').checked=true"', 'value', 'text', $this->item->parent_id);
					?>
                </td>
                <td class="center"><?php echo Text::_(sportsmanagementHelper::getSportsTypeName($this->item->sports_type_id)); ?></td>
                <td class="center"><?php echo Text::_(sportsmanagementHelper::getPosPersonTypeName($this->item->persontype)); ?></td>
                <td class="center">
					<?php
					if ($this->item->countEvents == 0)
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_NO_EVENTS');
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/error.png', $imageTitle, $image_attributes);
					}
					else
					{
						$imageTitle = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_NR_EVENTS', $this->item->countEvents);
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/ok.png', $imageTitle, $image_attributes);
					}
					?>
                </td>
                <td class="center">
					<?php
					if ($this->item->countStats == 0)
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_NO_STATISTICS');
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/error.png', $imageTitle, $image_attributes);
					}
					else
					{
						$imageTitle = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_POSITIONS_NR_STATISTICS', $this->item->countStats);
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/ok.png', $imageTitle, $image_attributes);
					}
					?>
                </td>
                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'positions.', $canChange, 'cb'); ?>
						<?php
						// Create dropdown items and render the dropdown list.
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'positions');
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'positions');
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
                <td align="center"><?php echo $this->item->id; ?></td>
            </tr>
			<?php
			
		}
		?>
        </tbody>
    </table>
</div>
