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
use Joomla\CMS\Session\Session;

if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
    
if ($this->saveOrder && !empty($this->items))
{
$saveOrderingUrl = 'index.php?option=com_sportsmanagement&task=agegroups.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';    
HTMLHelper::_('draggablelist.draggable');
}
    
}
else
{
    $this->dragable_group = '';
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
    <tbody <?php if ( $this->saveOrder && version_compare(substr(JVERSION, 0, 3), '4.0', 'ge') ) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->sortDirection); ?>" data-nested="true"<?php endif; ?>>
	<?php

    foreach ($this->items as $i => $this->item)
	{
$this->count_i = $i;
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
$this->dragable_group = 'data-dragable-group="'.$this->item->country.'"';
}         
		$link       = Route::_('index.php?option=com_sportsmanagement&task=league.edit&id=' . $this->item->id);
		$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
		$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
		$checked    = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'leagues.', $canCheckin);
		$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.league.' . $this->item->id) && $canCheckin;
		?>
        <tr class="row<?php echo $i % 2; ?>" <?php echo $this->dragable_group; ?>>
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
					<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->item->editor, $this->item->checked_out_time, 'leagues.', $canCheckin); ?>
				<?php endif; ?>
				<?php if ($canEdit) : ?>
                    <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=league.edit&id=' . (int) $this->item->id); ?>">
						<?php echo $this->escape($this->item->name); ?></a>
				<?php else : ?>
					<?php echo $this->escape($this->item->name); ?>
				<?php endif; ?>
                <div class="small">
					<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($this->item->alias)); ?>
                </div>
            </td>
            <td><?php echo $this->item->short_name; ?></td>
            <td class="center">
				<?php
				echo JSMCountries::getCountryFlag($this->item->country);
				$append = ' onchange="document.getElementById(\'cb' . $this->count_i . '\').checked=true" style="width:150px;background-color:#bbffff" size="1" ';
				echo HTMLHelper::_('select.genericlist', $this->lists['nation'], 'country' . $this->item->id, 'class="form-control form-control-inline" ' . $append, 'value', 'text', $this->item->country);
				?>
            </td>
            <td class="center"><?php echo Text::_($this->item->sportstype); ?></td>
            <td class="center">
				<?php
				$inputappend = '';
				$append      = ' style="background-color:#bbffff"';
				echo HTMLHelper::_(
					'select.genericlist', $this->lists['agegroup'], 'agegroup' . $this->item->id, $inputappend . 'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
					$this->count_i . '\').checked=true"' . $append, 'value', 'text', $this->item->agegroup_id
				);
				?>
            </td>
            <td class="center">
				<?php

				$imageTitle = '';
				$append     = ' onchange="document.getElementById(\'cb' . $this->count_i . '\').checked=true" style="background-color:#bbffff"';
				if (isset($this->lists['association'][$this->item->country]))
				{
					echo HTMLHelper::_('select.genericlist', $this->lists['association'][$this->item->country], 'association' . $this->item->id, 'class="form-control form-control-inline" size="1"' . $append, 'value', 'text', $this->item->associations);
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
				if (empty($this->item->picture))
				{
					$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE') . COM_SPORTSMANAGEMENT_PICTURE_SERVER . $this->item->picture;
                    $image_attributes['title'] = $imageTitle;
					echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/delete.png', $imageTitle, $image_attributes);
				}
                elseif ($this->item->picture == sportsmanagementHelper::getDefaultPlaceholder("player"))
				{
					$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_DEFAULT_IMAGE');
                    $image_attributes['title'] = $imageTitle;
					echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/information.png', $imageTitle, $image_attributes);
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
            <td class="center">
				<?php

				$class   = "btn-group btn-group-yesno";
				$options = array(
					HTMLHelper::_('select.option', '0', Text::_('JNO')),
					HTMLHelper::_('select.option', '1', Text::_('JYES'))
				);

				$html   = array();
				$html[] = '<fieldset id="published_act_season' . $this->item->id . '" class="' . $class . '" >';
				foreach ($options as $in => $option)
				{
					$checked = ($option->value == $this->item->published_act_season) ? ' checked="checked"' : '';
					$btn     = ($option->value == $this->item->published_act_season && $this->item->published_act_season) ? ' active btn-success' : ' ';
					$btn     = ($option->value == $this->item->published_act_season && !$this->item->published_act_season) ? ' active btn-danger' : $btn;

					$onchange = ' onchange="document.getElementById(\'cb' . $this->count_i . '\').checked=true"';
					$html[]   = '<input type="radio" style="display:none;" id="published_act_season' . $this->item->id . $in . '" name="published_act_season' . $this->item->id . '" value="'
						. $option->value . '"' . $onchange . ' />';

					$html[] = '<label for="published_act_season' . $this->item->id . $in . '"' . $checked . ' class="btn' . $btn . '" >'
						. Text::_($option->text) . '</label>';

				}

				echo implode($html);
				?>
            </td>
            <td class="center">
                <div class="btn-group">
					<?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'leagues.', $canChange, 'cb'); ?>
					<?php
					/**  Create dropdown items and render the dropdown list. */
					if ($canChange)
					{
						HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'leagues');
						HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'leagues');
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
