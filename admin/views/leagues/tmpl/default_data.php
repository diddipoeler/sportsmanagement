<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage leagues
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
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
        <th width="5">
			<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?>
        </th>
        <th width="20">
            <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
        </th>
        <th>
			<?php
			echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_LEAGUES_NAME', 'obj.name', $this->sortDirection, $this->sortColumn);
			?>
        </th>
        <th>
			<?php
			echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_LEAGUES_SHORT_NAME', 'obj.short_name', $this->sortDirection, $this->sortColumn);
			?>
        </th>
        <th width="10%">
			<?php
			echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_LEAGUES_COUNTRY', 'obj.country', $this->sortDirection, $this->sortColumn);
			?>
        </th>
        <th class="title">
			<?php
			echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE', 'st.name', $this->sortDirection, $this->sortColumn);
			?>
        </th>
        <th class="title">
			<?php
			echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP', 'ag.name', $this->sortDirection, $this->sortColumn);
			?>
        </th>
        <th class="title">
			<?php
			echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ASSOCIATIONS_NAME', 'fed.name', $this->sortDirection, $this->sortColumn);
			?>
        </th>
        <th>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE'); ?>
        </th>
        <th>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_ACT_SEASON_MOD'); ?>
        </th>
        <th width="" class="nowrap center">
			<?php
			echo HTMLHelper::_('grid.sort', 'JSTATUS', 'objassoc.published', $this->sortDirection, $this->sortColumn);
			?>
        </th>
        <th width="10%">
			<?php
			echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'obj.ordering', $this->sortDirection, $this->sortColumn);
			echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'leagues.saveorder');
			?>
        </th>
        <th width="20">
			<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'obj.id', $this->sortDirection, $this->sortColumn); ?>
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
    <tbody>
	<?php
	$k = 0;
	for ($i = 0, $n = count($this->items); $i < $n; $i++)
	{
		$row        = &$this->items[$i];
		$link       = Route::_('index.php?option=com_sportsmanagement&task=league.edit&id=' . $row->id);
		$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
		$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
		$checked    = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'leagues.', $canCheckin);
		$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.league.' . $row->id) && $canCheckin;
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
					<?php echo HTMLHelper::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'leagues.', $canCheckin); ?>
				<?php endif; ?>
				<?php if ($canEdit) : ?>
                    <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=league.edit&id=' . (int) $row->id); ?>">
						<?php echo $this->escape($row->name); ?></a>
				<?php else : ?>
					<?php echo $this->escape($row->name); ?>
				<?php endif; ?>
                <div class="small">
					<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias)); ?>
                </div>
            </td>
            <td><?php echo $row->short_name; ?></td>
            <td class="center">
				<?php
				echo JSMCountries::getCountryFlag($row->country);
				$append = ' onchange="document.getElementById(\'cb' . $i . '\').checked=true" style="width:150px;background-color:#bbffff" size="1" ';
				echo HTMLHelper::_('select.genericlist', $this->lists['nation'], 'country' . $row->id, 'class="form-control form-control-inline" ' . $append, 'value', 'text', $row->country);
				?>
            </td>
            <td class="center"><?php echo Text::_($row->sportstype); ?></td>
            <td class="center">
				<?php
				$inputappend = '';
				$append      = ' style="background-color:#bbffff"';
				echo HTMLHelper::_(
					'select.genericlist', $this->lists['agegroup'], 'agegroup' . $row->id, $inputappend . 'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
					$i . '\').checked=true"' . $append, 'value', 'text', $row->agegroup_id
				);
				?>
            </td>
            <td class="center">
				<?php

				$imageTitle = '';
				$append     = ' onchange="document.getElementById(\'cb' . $i . '\').checked=true" style="background-color:#bbffff"';
				if (isset($this->lists['association'][$row->country]))
				{
					echo HTMLHelper::_('select.genericlist', $this->lists['association'][$row->country], 'association' . $row->id, 'class="form-control form-control-inline" size="1"' . $append, 'value', 'text', $row->associations);
				}
				else
				{
				    $image_attributes['title'] = $imageTitle;
					echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/delete.png', $imageTitle, $image_attributes);
				}
				?>
            </td>
            <td class="center">
				<?php
				if (empty($row->picture))
				{
					$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE') . COM_SPORTSMANAGEMENT_PICTURE_SERVER . $row->picture;
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
					?>
                    <a href="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER . $row->picture; ?>"
                       title="<?php echo $row->name; ?>" class="modal">
                        <img src="<?php echo COM_SPORTSMANAGEMENT_PICTURE_SERVER . $row->picture; ?>"
                             alt="<?php echo $row->name; ?>" width="20"/>
                    </a>
					<?PHP
				}
				?>
            </td>
            <td class="center">
				<?php

				$class   = "btn-group btn-group-yesno";
				$options = array(
					HTMLHelper::_('select.option', '0', Text::_('JNO')),
					HTMLHelper::_('select.option', '1', Text::_('JYES'))
				);

				$html   = array();
				$html[] = '<fieldset id="published_act_season' . $row->id . '" class="' . $class . '" >';
				foreach ($options as $in => $option)
				{
					$checked = ($option->value == $row->published_act_season) ? ' checked="checked"' : '';
					$btn     = ($option->value == $row->published_act_season && $row->published_act_season) ? ' active btn-success' : ' ';
					$btn     = ($option->value == $row->published_act_season && !$row->published_act_season) ? ' active btn-danger' : $btn;

					$onchange = ' onchange="document.getElementById(\'cb' . $i . '\').checked=true"';
					$html[]   = '<input type="radio" style="display:none;" id="published_act_season' . $row->id . $in . '" name="published_act_season' . $row->id . '" value="'
						. $option->value . '"' . $onchange . ' />';

					$html[] = '<label for="published_act_season' . $row->id . $in . '"' . $checked . ' class="btn' . $btn . '" >'
						. Text::_($option->text) . '</label>';

				}

				echo implode($html);
				?>
            </td>
            <td class="center">
                <div class="btn-group">
					<?php echo HTMLHelper::_('jgrid.published', $row->published, $i, 'leagues.', $canChange, 'cb'); ?>
					<?php
					/**  Create dropdown items and render the dropdown list. */
					if ($canChange)
					{
						HTMLHelper::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'leagues');
						HTMLHelper::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'leagues');
						echo HTMLHelper::_('actionsdropdown.render', $this->escape($row->name));
					}
					?>
                </div>
            </td>
<td class="order" id="defaultdataorder">
<?php
echo $this->loadTemplate('data_order');
?>
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
