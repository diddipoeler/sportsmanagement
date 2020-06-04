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

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
if ($this->assign)
{
	$this->readonly = ' readonly ';
}
else
{
	$this->readonly = '';
}
?>
<!--	<div id="editcell"> -->
<div class="table-responsive">
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
        <tr>
            <th width="1%" class="center"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="1%" class="center">
                <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
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
        <tbody>
		<?php
		$k = 0;
		for ($i = 0, $n = count($this->items); $i < $n; $i++)
		{
			$row        = &$this->items[$i];
			$link       = Route::_('index.php?option=com_sportsmanagement&task=team.edit&id=' . $row->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'teams.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.team.' . $row->id) && $canCheckin;
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
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'teams.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canEdit && !$this->assign) : ?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=team.edit&id=' . (int) $row->id); ?>">
							<?php echo $this->escape($row->name); ?></a>
					<?php else : ?>
						<?php echo $this->escape($row->name); ?>
					<?php endif; ?>
                    <div class="small">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias)); ?>
                    </div>
                </td>
				<?php ?>

                <td><?php echo (empty($row->clubname)) ? '<span style="color:red;">' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NO_CLUB') . '</span>' : $row->clubname; ?></td>

                <td class="center"><?php echo JSMCountries::getCountryFlag($row->country); ?></td>

                <td class="center">
					<?php
					if ($row->website != '')
					{
						echo '<a href="' . $row->website . '" target="_blank"><span class="label label-success" title="' . $row->website . '">' . Text::_('JYES') . '</span></a>';
					}
					else
					{
						echo '<span class="label">' . Text::_('JNO') . '</span>';
					}
					?>
                </td>

                <td class="center">
					<?php
					if ($row->email != '')
					{
						echo '<a href="mailto:' . $row->email . '"><span class="label label-success" title="' . $row->email . '">' . Text::_('JYES') . '</span></a>';
					}
					else
					{
						echo '<span class="label">' . Text::_('JNO') . '</span>';
					}
					?>
                </td>

                <td class="center"><?php echo $row->short_name; ?></td>


                <td class="center">
					<?php
					$inputappend = $this->readonly;
					$append      = ' style="background-color:#bbffff"';
					echo HTMLHelper::_(
						'select.genericlist', $this->lists['agegroup'], 'agegroup' . $row->id, $inputappend . 'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
						$i . '\').checked=true"' . $append, 'value', 'text', $row->agegroup_id
					);
					?>
                    </br>
					<?php echo $row->info; ?>
                </td>

                <td class="center">
					<?php
					$append = ' onchange="document.getElementById(\'cb' . $i . '\').checked=true" style="max-width: 100px;background-color:#bbffff"';
					echo HTMLHelper::_('select.genericlist', $this->lists['sportstype'], 'sportstype' . $row->id, $inputappend . 'class="form-control form-control-inline" size="1"' . $append, 'id', 'name', $row->sports_type_id);
					?>
                </td>
                <td class="center">
					<?php
					if ($row->picture == '')
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_NO_IMAGE');
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/error.png', $imageTitle, $image_attributes);
					}
                    elseif ($row->picture == sportsmanagementHelper::getDefaultPlaceholder("team"))
					{
						$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_DEFAULT_IMAGE');
                        $image_attributes['title'] = $imageTitle;
						echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/information.png', $imageTitle, $image_attributes);
						?>
                        <a href="<?php echo Uri::root() . $row->picture; ?>" title="<?php echo $imageTitle; ?>"
                           class="modal">
                            <img src="<?php echo Uri::root() . $row->picture; ?>" alt="<?php echo $imageTitle; ?>"
                                 width="20"/>
                        </a>
						<?PHP
					}
					else
					{
						if (File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . $row->picture))
						{
							$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TEAMS_CUSTOM_IMAGE');
                            $image_attributes['title'] = $imageTitle;
							echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/ok.png', $imageTitle, $image_attributes);
							?>
                            <a href="<?php echo Uri::root() . $row->picture; ?>" title="<?php echo $imageTitle; ?>"
                               class="modal">
                                <img src="<?php echo Uri::root() . $row->picture; ?>" alt="<?php echo $imageTitle; ?>"
                                     width="20"/>
                            </a>
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
						<?php echo HTMLHelper::_('jgrid.published', $row->published, $i, 'seasons.', $canChange, 'cb'); ?>
						<?php
						// Create dropdown items and render the dropdown list.
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'seasons');
							HTMLHelper::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'seasons');
							echo HTMLHelper::_('actionsdropdown.render', $this->escape($row->name));
						}
						?>
                    </div>
                </td>
                <td class="order">
                        <span>
                            <?php echo $this->pagination->orderUpIcon($i, $i > 0, 'teams.orderup', 'JLIB_HTML_MOVE_UP', true); ?>
                        </span>
                    <span>
                            <?php echo $this->pagination->orderDownIcon($i, $n, $i < $n, 'teams.orderdown', 'JLIB_HTML_MOVE_DOWN', true); ?>
                            <?php $disabled = true ? '' : 'disabled="disabled"'; ?>
                        </span>
                    <input type="text" name="order[]" size="5"
                           value="<?php echo $row->ordering; ?>" <?php echo $disabled; ?>
                           class="form-control form-control-inline"
                           style="text-align: center" <?php echo $this->readonly; ?> />
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

