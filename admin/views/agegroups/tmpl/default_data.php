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
use Joomla\CMS\Session\Session;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
//$this->saveOrder = $this->sortColumn == 'ordering';
//echo 'sortColumn<pre>'.print_r($this->sortColumn,true).'</pre>';
//echo 'sortDirection<pre>'.print_r($this->sortDirection,true).'</pre>';
//echo 'saveOrder<pre>'.print_r($this->saveOrder,true).'</pre>';
$this->dragable_group = '';
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
?>    
<script>
//function saveorder(n, task) {
//console.warn('window.saveorder() is deprecated without a replacement!');
//console.warn('n ' + n);
//console.warn('task ' + task);
//checkAll_button( n, task );
//}
//
//function checkAll_button(n, task) {
//console.warn('window.checkAll_button() is deprecated without a replacement!');
//		task = task ? task : 'saveorder';
//		var j, box;
//		for ( j = 0; j <= n; j++ ) {
//			box = document.adminForm[ 'cb' + j ];
//			if ( box ) {
//				box.checked = true;
//			} else {
//				alert( "You cannot change the order of items, as an item in the list is `Checked Out`" );
//				return;
//			}
//		}
//		Joomla.submitform( task );
//}
</script>    
    
<?php    
if ($this->saveOrder && !empty($this->items))
{
$saveOrderingUrl = 'index.php?option=com_sportsmanagement&task=agegroups.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';    
HTMLHelper::_('draggablelist.draggable');
$this->dragable_group = 'data-dragable-group="<?php echo $item->catid; ?>"';
}    
}    
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
        <tbody <?php if ( $this->saveOrder && version_compare(substr(JVERSION, 0, 3), '4.0', 'ge') ) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->sortDirection); ?>" data-nested="true"<?php endif; ?>>
		<?php
		$k = 0;
		for ($i = 0, $n = count($this->items); $i < $n; $i++)
		{
			$this->datarow        = &$this->items[$i];
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
$this->dragable_group = 'data-dragable-group="'.$this->datarow->ordering.'"';
} 
			$link       = Route::_('index.php?option=com_sportsmanagement&task=agegroup.edit&id=' . $this->datarow->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->datarow->checked_out == $this->user->get('id') || $this->datarow->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $this->datarow->checked_out_time, 'agegroups.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.agegroup.' . $this->datarow->id) && $canCheckin;
			?>
            <tr class="<?php echo "row$k"; ?>" <?php echo $this->dragable_group; ?>>
                <td class="center">
					<?php
					echo $this->pagination->getRowOffset($i);
					?>
                </td>
                <td class="center">
					<?php
					echo HTMLHelper::_('grid.id', $i, $this->datarow->id);
					?>
                </td>
				<?php
				$inputappend = '';
				?>
                <td class="center">
					<?php if ($this->datarow->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $this->datarow->editor, $this->datarow->checked_out_time, 'agegroups.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=agegroup.edit&id=' . (int) $this->datarow->id); ?>">
							<?php echo $this->escape($this->datarow->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($this->datarow->name); ?>
					<?php endif; ?>
                    <input tabindex="2" type="hidden" size="30" maxlength="64" class="form-control form-control-inline"
                           name="name<?php echo $this->datarow->id; ?>" value="<?php echo $this->datarow->name; ?>"
                           onchange="document.getElementById('cb<?php echo $i; ?>').checked = true"/>


					<?php //echo $checked;  ?>

					<?php //echo $this->datarow->name;  ?>
                    <p class="smallsub">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($this->datarow->alias)); ?></p>
                </td>
				<?php ?>


                <td><?php echo $this->datarow->age_from; ?></td>
                <td><?php echo $this->datarow->age_to; ?></td>
                <td><?php echo $this->datarow->deadline_day; ?></td>
                <td class="center"><?php echo JSMCountries::getCountryFlag($this->datarow->country); ?></td>

                <td class="center">
					<?php
					if (empty($this->datarow->picture) || !File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . $this->datarow->picture))
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE') . $this->datarow->picture;
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/delete.png', $imageTitle, $image_attributes);
					}
                    elseif ($this->datarow->picture == sportsmanagementHelper::getDefaultPlaceholder("player"))
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_DEFAULT_IMAGE');
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/information.png', $imageTitle, $image_attributes);
					}
					else
					{
						//$playerName = sportsmanagementHelper::formatName(null ,$this->datarow->firstname, $this->datarow->nickname, $this->datarow->lastname, 0);
						//echo sportsmanagementHelper::getPictureThumb($this->datarow->picture, $playerName, 0, 21, 4);
						?>
                        <a href="<?php echo Uri::root() . $this->datarow->picture; ?>" title="<?php echo $this->datarow->name; ?>"
                           class="modal">
                            <img src="<?php echo Uri::root() . $this->datarow->picture; ?>" alt="<?php echo $this->datarow->name; ?>"
                                 width="20"/>
                        </a>
						<?PHP
					}
					?>
                </td>

                <td class=""><?php echo Text::_($this->datarow->sportstype); ?></td>
                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $this->datarow->published, $i, 'agegroups.', $canChange, 'cb'); ?>
						<?php
						// Create dropdown items and render the dropdown list.
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $this->datarow->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'agegroups');
							HTMLHelper::_('actionsdropdown.' . ((int) $this->datarow->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'agegroups');
							echo HTMLHelper::_('actionsdropdown.render', $this->escape($this->datarow->name));
						}
						?>
                    </div>
                </td>
                <td class="order">
<?php
echo $this->loadTemplate('data_order');
?>

                </td>
                <td class="center"><?php echo $this->datarow->id; ?></td>
            </tr>
			<?php
			$k = 1 - $k;
		}
		?>
        </tbody>
    </table>
</div>
