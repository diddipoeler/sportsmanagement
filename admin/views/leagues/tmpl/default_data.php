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
use Joomla\CMS\Uri\Uri;

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
            <br />
            <?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_LEVEL'); ?>
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
        <th>
			<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_CHAMPIONS_COMPLETE'); ?>
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
    <tbody <?php if ( $this->saveOrder && version_compare(substr(JVERSION, 0, 3), '4.0', 'ge') ) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->sortDirection); ?>" <?php endif; ?>>
	<?php

    foreach ($this->items as $this->count_i => $this->item)
	{

if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
$this->dragable_group = 'data-dragable-group="none"';
}         
		$link       = Route::_('index.php?option=com_sportsmanagement&task=league.edit&id=' . $this->item->id);
		$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
		$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
		$checked    = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'leagues.', $canCheckin);
		$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.league.' . $this->item->id) && $canCheckin;
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
                <br />
                <?php echo $this->item->league_level; ?></td>
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
                echo sportsmanagementHelper::getBootstrapModalImage('collapseModallogo_picture' . $this->item->id, Uri::root() . $this->item->picture, $imageTitle, '20', Uri::root() . $this->item->picture);
 				?>
            </td>
            <td class="center">
				<?php


$this->switcher_onchange = ' onchange="document.getElementById(\'cb' . $this->count_i . '\').checked=true"';
$this->switcher_options = array(
						HTMLHelper::_('select.option', '0', Text::_('JNO')),
						HTMLHelper::_('select.option', '1', Text::_('JYES'))
					);
                    
$this->switcher_value = $this->item->published_act_season;    
$this->switcher_name = 'published_act_season' . $this->item->id;    
$this->switcher_attr = 'id="' . $this->item->id . '"'; 
$this->switcher_item_id = $this->item->id;           
/** welche joomla version ? */
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
echo $this->loadTemplate('switcher4');    
}
elseif (version_compare(substr(JVERSION, 0, 3), '3.0', 'ge'))
{    
echo $this->loadTemplate('switcher3');
}   

$this->switcher_value = $this->item->champions_complete;    
$this->switcher_name = 'champions_complete' . $this->item->id;
/** welche joomla version ? */
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
echo $this->loadTemplate('switcher4');    
}
elseif (version_compare(substr(JVERSION, 0, 3), '3.0', 'ge'))
{    
echo $this->loadTemplate('switcher3');
}  






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
