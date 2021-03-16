<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teams
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

if ($this->assign)
{
	$this->readonly = ' readonly ';
}
else
{
	$this->readonly = '';
}
$this->saveOrder = $this->sortColumn == 't.ordering';
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
            <th width="1%" class="center"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th style="width:1%" class="text-center">
									<?php echo HTMLHelper::_('grid.checkall'); ?>
								</th>

            <th width="10%" class="left nowrap">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NAME', 't.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th class="left">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEAMS_CLUBNAME', 'c.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th class="left">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBS_COUNTRY', 'c.country', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="1%" class="center">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEAMS_WEBSITE', 't.website', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="1%" class="center">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEAMS_EMAIL', 't.email', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEAMS_ML_NAME', 't.middle_name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th class="title">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_AGEGROUP', 'ag.name', $this->sortDirection, $this->sortColumn);
				?>
                </br>
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAM_INFO');
				?>
            </th>
            <th class="title">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SPORTSTYPE', 'st.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TEAMS_PICTURE', 't.picture', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="" class="nowrap center">
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 's.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="10%">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 't.ordering', $this->sortDirection, $this->sortColumn);
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'teams.saveorder');
				?>
            </th>
            <th width="1%">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 't.id', $this->sortDirection, $this->sortColumn);
				?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td class="center" colspan="100%"><?php echo $this->pagination->getListFooter(); ?>
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
			$link       = Route::_('index.php?option=com_sportsmanagement&task=team.edit&id=' . $this->item->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'teams.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.team.' . $this->item->id) && $canCheckin;
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
						<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->item->editor, $this->item->checked_out_time, 'teams.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit && !$this->assign) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=team.edit&id=' . (int) $this->item->id); ?>">
							<?php echo $this->escape($this->item->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($this->item->name); ?>
					<?php endif; ?>
                    <div class="small">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($this->item->alias)); ?>
                    </div>
                </td>
				<?php ?>

                <td><?php echo (empty($this->item->clubname)) ? '<span style="color:red;">' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NO_CLUB') . '</span>' : $this->item->clubname; ?></td>

                <td class="center"><?php echo JSMCountries::getCountryFlag($this->item->country); ?></td>

                <td class="center">
					<?php
					if ($this->item->website != '')
					{
						echo '<a href="' . $row->website . '" target="_blank"><span class="label label-success" title="' . $this->item->website . '">' . Text::_('JYES') . '</span></a>';
					}
					else
					{
						echo '<span class="label">' . Text::_('JNO') . '</span>';
					}
					?>
                </td>

                <td class="center">
					<?php
					if ($this->item->email != '')
					{
						echo '<a href="mailto:' . $this->item->email . '"><span class="label label-success" title="' . $this->item->email . '">' . Text::_('JYES') . '</span></a>';
					}
					else
					{
						echo '<span class="label">' . Text::_('JNO') . '</span>';
					}
					?>
                </td>

                <td class="center"><?php echo $this->item->short_name; ?></td>


                <td class="center">
					<?php
					$inputappend = $this->readonly;
					$append      = ' style="background-color:#bbffff"';
					echo HTMLHelper::_(
						'select.genericlist', $this->lists['agegroup'], 'agegroup' . $this->item->id, $inputappend . 'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
						$this->count_i . '\').checked=true"' . $append, 'value', 'text', $this->item->agegroup_id
					);
					?>
                    </br>
					<?php echo $this->item->info; ?>
                </td>

                <td class="center">
					<?php
					$append = ' onchange="document.getElementById(\'cb' . $this->count_i . '\').checked=true" style="max-width: 100px;background-color:#bbffff"';
					echo HTMLHelper::_('select.genericlist', $this->lists['sportstype'], 'sportstype' . $this->item->id, $inputappend . 'class="form-control form-control-inline" size="1"' . $append, 'id', 'name', $this->item->sports_type_id);
					?>
                </td>
                <td class="center">
					<?php
					if ($this->item->picture == '')
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NO_IMAGE');
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/error.png', $imageTitle, $image_attributes);
					}
                    elseif ($this->item->picture == sportsmanagementHelper::getDefaultPlaceholder("team"))
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_DEFAULT_IMAGE');
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/information.png', $imageTitle, $image_attributes);
echo sportsmanagementHelper::getBootstrapModalImage('collapseModallogo_picture' . $this->item->id, Uri::root() . $this->item->picture, $imageTitle, '20', Uri::root() . $this->item->picture);                        
						?>

						<?PHP
					}
					else
					{
						if (File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . $this->item->picture))
						{
							$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_CUSTOM_IMAGE');
                            $image_attributes['title'] = $imageTitle;
							echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/ok.png', $imageTitle, $image_attributes);
echo sportsmanagementHelper::getBootstrapModalImage('collapseModallogo_picture' . $this->item->id, Uri::root() . $this->item->picture, $imageTitle, '20', Uri::root() . $this->item->picture);                            
							?>

							<?PHP
						}
						else
						{
							$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NO_IMAGE');
                            $image_attributes['title'] = $imageTitle;
							echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/delete.png', $imageTitle, $image_attributes);
						}
					}
					?>
                </td>
                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'seasons.', $canChange, 'cb'); ?>
						<?php
						/** Create dropdown items and render the dropdown list. */
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'seasons');
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'seasons');
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

