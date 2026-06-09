<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage templates
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;


$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

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
<script>
	function searchTemplate(val, key) {
		var f = $('adminForm');
		if (f) {
			f.elements['search'].value = val;
			f.elements['search_mode'].value = 'matchfirst';
			f.submit();
		}
	}
</script>
<legend>
	<?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_LEGEND', '<i>' . $this->projectws->name . '</i>'); ?>
</legend>
<table class="<?php echo $this->table_data_class; ?>" id="<?php echo $this->view; ?>list">
	<thead>
		<?php
		if ($this->projectws->master_template) {
			?>
			<tr>
				<td align="right" colspan="6">
					<?php echo $this->lists['mastertemplates']; ?>
				</td>
			</tr>
		<?php
		}
		?>
		<tr>
			<th width="5">
				<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?>
			</th>
			<th width="20">
				<?php echo HTMLHelper::_('grid.checkall'); ?>
			</th>
			<th width="20">&nbsp;</th>
			<th>
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_TEMPLATE', 'tmpl.template', $this->sortDirection, $this->sortColumn); ?>
			</th>
			<th>
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_DESCR', 'tmpl.title', $this->sortDirection, $this->sortColumn); ?>
			</th>
			<th>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_TYPE'); ?>
			</th>
			<th>
				<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'tmpl.id', $this->sortDirection, $this->sortColumn); ?>
			</th>

			<th width="" class="title">
				<?php
				echo Text::_('JGLOBAL_FIELD_MODIFIED_LABEL');
				?>
			</th>
			<th width="" class="title">
				<?php
				echo Text::_('JGLOBAL_FIELD_MODIFIED_BY_LABEL');
				?>
			</th>
		</tr>
	</thead>
    <tfoot>
    <tr>
        <td colspan="9">
			<?php echo $this->pagination->getListFooter(); ?>
        </td>
        <td colspan="4">
			<?php echo $this->pagination->getResultsCounter(); ?>
        </td>
    </tr>
    </tfoot>
	<tbody <?php if ( $this->saveOrder && version_compare(substr(JVERSION, 0, 3), '4.0', 'ge') ) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->sortDirection); ?>" <?php endif; ?>>
		<?php
		foreach ($this->templates as $this->count_i => $this->item)
        {
			$link1 = Route::_('index.php?option=com_sportsmanagement&task=template.edit&id=' . $this->item->id);
			$canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
			$checked = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'templates.', $canCheckin);
			?>
			<tr class="<?php echo ($this->count_i % 2 == 0) ? 'even' : 'odd'; ?>">
				<td class="center">
					<?php echo $this->pagination->getRowOffset($this->count_i); ?>
				</td>
				<td class="center">
					<?php echo HTMLHelper::_('grid.id', $this->count_i, $this->item->id); ?>
				</td>
				<td>
					<?php if ($this->item->checked_out != $this->user->get('id') && $this->item->checked_out): ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'templates.', $canCheckin); ?>
					<?php else: ?>
						<?php $image = HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/edit.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_EDIT_DETAILS'), ['title' => Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_EDIT_DETAILS')]); ?>
						<?php echo HTMLHelper::link($link1, $image); ?>
					<?php endif; ?>
				</td>
				<td>
					<?php echo $this->item->template; ?>
				</td>
				<td>
					<?php echo Text::_($this->item->title); ?>
				</td>
				<td>
					<?php
					$isMasterColor = ($this->item->isMaster) ? 'red' : 'green';
					$isMasterText = ($this->item->isMaster) ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_MASTER') : Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEMPLATES_INDEPENDENT');
					echo "<span style='font-weight:bold; color:$isMasterColor'>$isMasterText</span>";
					?>
				</td>
				<td class="center">
					<?php echo $this->item->id; ?>
					<input type='hidden' name='isMaster[<?php echo $this->item->id; ?>]' value='<?php echo $this->item->isMaster; ?>' />
				</td>
				<td>
					<?php echo $this->item->modified; ?>
				</td>
				<td>
					<?php echo $this->item->username; ?>
				</td>
			</tr>
		<?php } ?>
	</tbody>

</table>