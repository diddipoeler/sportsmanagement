<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage agegroups
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

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
//$this->saveOrder = $this->sortColumn == 'ordering';
//echo 'sortColumn<pre>'.print_r($this->sortColumn,true).'</pre>';
//echo 'sortDirection<pre>'.print_r($this->sortDirection,true).'</pre>';
//echo 'saveOrder<pre>'.print_r($this->saveOrder,true).'</pre>';

?>
<div id="editcell">
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
        <tr>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="20">
                <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_AGEGROUPS_NAME', 'obj.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_AGEGROUP_AGE_FROM', 'obj.age_from', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_AGEGROUP_AGE_TO', 'obj.age_to', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_AGEGROUP_SHORT_DEADLINE_DAY', 'obj.deadline_day', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="10%">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_AGEGROUP_COUNTRY', 'obj.country', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE'); ?>
            </th>
            <th class="title">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_AGEGROUP_SPORTSTYPE', 'obj.sportstype_id', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="" class="nowrap center">
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 'obj.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="10%">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'obj.ordering', $this->sortDirection, $this->sortColumn);
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'agegroups.saveorder');
				?>
            </th>
            <th width="20">
				<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'obj.id', $this->sortDirection, $this->sortColumn); ?>
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
        <tbody>
		<?php
		$k = 0;
		for ($i = 0, $n = count($this->items); $i < $n; $i++)
		{
			$row        = &$this->items[$i];
			$link       = Route::_('index.php?option=com_sportsmanagement&task=agegroup.edit&id=' . $row->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'agegroups.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.agegroup.' . $row->id) && $canCheckin;
			?>
            <tr class="<?php echo "row$k"; ?>">
                <td class="center">
					<?php
					echo $this->pagination->getRowOffset($i);
					?>
                </td>
                <td class="center">
					<?php
					echo HTMLHelper::_('grid.id', $i, $row->id);
					?>
                </td>
				<?php
				$inputappend = '';
				?>
                <td class="center">
					<?php if ($row->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'agegroups.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=agegroup.edit&id=' . (int) $row->id); ?>">
							<?php echo $this->escape($row->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($row->name); ?>
					<?php endif; ?>
                    <input tabindex="2" type="hidden" size="30" maxlength="64" class="form-control form-control-inline"
                           name="name<?php echo $row->id; ?>" value="<?php echo $row->name; ?>"
                           onchange="document.getElementById('cb<?php echo $i; ?>').checked = true"/>


					<?php //echo $checked;  ?>

					<?php //echo $row->name;  ?>
                    <p class="smallsub">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias)); ?></p>
                </td>
				<?php ?>


                <td><?php echo $row->age_from; ?></td>
                <td><?php echo $row->age_to; ?></td>
                <td><?php echo $row->deadline_day; ?></td>
                <td class="center"><?php echo JSMCountries::getCountryFlag($row->country); ?></td>

                <td class="center">
					<?php
					if (empty($row->picture) || !File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . $row->picture))
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE') . $row->picture;
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/delete.png', $imageTitle, $image_attributes);
					}
                    elseif ($row->picture == sportsmanagementHelper::getDefaultPlaceholder("player"))
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_DEFAULT_IMAGE');
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/information.png', $imageTitle, $image_attributes);
					}
					else
					{
						//$playerName = sportsmanagementHelper::formatName(null ,$row->firstname, $row->nickname, $row->lastname, 0);
						//echo sportsmanagementHelper::getPictureThumb($row->picture, $playerName, 0, 21, 4);
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

                <td class=""><?php echo Text::_($row->sportstype); ?></td>
                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $row->published, $i, 'agegroups.', $canChange, 'cb'); ?>
						<?php
						// Create dropdown items and render the dropdown list.
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'agegroups');
							HTMLHelper::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'agegroups');
							echo HTMLHelper::_('actionsdropdown.render', $this->escape($row->name));
						}
						?>
                    </div>
                </td>
                <td class="order">
                <?php if ($this->saveOrder) : ?>
                	<?php if ($this->sortDirection == 'asc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, @$this->items[$i - 1]->ordering == $row->ordering, 'agegroup.orderup', 'JLIB_HTML_MOVE_UP', $this->ordering); ?></span>
								<span><?php echo $this->pagination->orderDownIcon($i, $n, @$this->items[$i + 1]->ordering == $row->ordering, 'agegroup.orderdown', 'JLIB_HTML_MOVE_DOWN', $this->ordering); ?></span>
							<?php elseif ($this->sortDirection == 'desc') : ?>
								<span><?php echo $this->pagination->orderUpIcon($i, @$this->items[$i - 1]->ordering == $row->ordering, 'agegroup.orderdown', 'JLIB_HTML_MOVE_UP', $this->ordering); ?></span>
								<span><?php echo $this->pagination->orderDownIcon($i, $n, @$this->items[$i + 1]->ordering == $row->ordering, 'agegroup.orderup', 'JLIB_HTML_MOVE_DOWN', $this->ordering); ?></span>
							<?php endif; ?>
                <?php endif; ?>
                        <?php $disabled = $this->saveOrder ? '' : 'disabled="disabled"'; ?>
                    <input type="text" name="order[]" size="5"
                           value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?>
                           class="form-control form-control-inline" style="text-align: center"/>
                </td>
                <td class="center"><?php echo $row->id; ?></td>
            </tr>
			<?php
			$k = 1 - $k;
		}
		?>
        </tbody>
    </table>
</div>
