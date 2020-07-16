<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage clubs
 * @file       default_3_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

$params     = ComponentHelper::getParams('com_sportsmanagement');
$joomlaicon = $params->get('show_joomla_icons');
?>
<div class="table-responsive" id="editcell">
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
        <tr>
            <th width="1%">
				<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?>
            </th>
            <th width="1%">
                <input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);"/>
            </th>
            <th width="1%" class="title">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBS_NAME_OF_CLUB', 'a.name', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <th width="1%">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBS_WEBSITE', 'a.website', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <th width="1%">
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_EMAIL'); ?>
            </th>
            <th width="1%">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUB_UNIQUE_ID', 'a.unique_id', $this->sortDirection, $this->sortColumn); ?>
                <br/>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_NEW_CLIB_ID'); ?></th>
            </th>
            <th width="1%">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBS_L_LOGO', 'a.logo_big', $this->sortDirection, $this->sortColumn); ?>
            </th>
	    <!--
            <th width="1%">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBS_M_LOGO', 'a.logo_middle', $this->sortDirection, $this->sortColumn); ?>
            </th>
	    -->
	    <!--
            <th width="1%">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBS_S_LOGO', 'a.logo_small', $this->sortDirection, $this->sortColumn); ?>
            </th>
	    -->
            <th width="1%">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUB_POSTAL_CODE', 'a.zipcode', $this->sortDirection, $this->sortColumn); ?>
                <br/>
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBS_CITY', 'a.location', $this->sortDirection, $this->sortColumn); ?>
                <br/>
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUB_ADDRESS', 'a.address', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <th width="1%">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBS_COUNTRY', 'a.country', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <th width="1%" class="nowrap center">
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 's.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="1%">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'a.ordering', $this->sortDirection, $this->sortColumn);
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'clubs.saveorder');
				?>
            </th>
            <th width="1%">
				<?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $this->sortDirection, $this->sortColumn); ?>
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
			$link       = Route::_('index.php?option=com_sportsmanagement&task=club.edit&id=' . $row->id);
			$link2      = Route::_('index.php?option=com_sportsmanagement&view=teams&club_id=' . $row->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'clubs.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.club.' . $row->id) && $canCheckin;
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
					<?php
					?>
                    <a href="<?php echo $link2; ?>">
						<?php
						if ($joomlaicon)
						{
							echo '<span class="icon-star-2 large-icon"> </span>';
						}
						else
						{
							$imageTitle       = Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_SHOW_TEAMS');
							$attribs['title'] = $imageTitle;
							echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/icon-16-Teams.png', $imageTitle, $attribs);
						}
						?>
                    </a>

					<?php if ($row->checked_out)
						:
						?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $row->editor, $row->checked_out_time, 'clubs.', $canCheckin); ?>
					<?php endif; ?>
					<?php
					if ($canEdit)
						:
						?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=club.edit&id=' . (int) $row->id); ?>">
							<?php echo $this->escape($row->name); ?></a>
					<?php else

						:
						?>
						<?php echo $this->escape($row->name); ?>
					<?php endif;
					?>
					<?php // Echo $checked;
					?>
					<?php // echo $row->name;
					?>
                    <div class="small">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias)); ?></div>
                </td>
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
					if ($row->email)
					{
						echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/mail.png', '', '');
					}

					if ($row->facebook)
					{
						?>
                        <br>
						<?php
						echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/facebook.png', '', '');
					}

					?>
                </td>
                <td>
                    <input<?php echo $inputappend; ?>
                            type="text" size="10" class="form-control form-control-inline"
                            name="unique_id<?php echo $row->id; ?>"
                            value="<?php echo $row->unique_id; ?>"
                            onchange="document.getElementById('cb<?php echo $i; ?>').checked = true"/>
                    <br/>
                    <input<?php echo $inputappend; ?>
                            type="text" size="10" class="form-control form-control-inline"
                            name="new_club_id<?php echo $row->id; ?>"
                            value="<?php echo $row->new_club_id; ?>"
                            onchange="document.getElementById('cb<?php echo $i; ?>').checked = true"/>
                    <br/>
					<?php echo $this->escape($row->state); ?>
                </td>
                <td class="center">
					<?php
					$picture    = ($row->logo_big == sportsmanagementHelper::getDefaultPlaceholder("clublogobig")) ? 'information.png' : 'ok.png';
					$imageTitle = ($row->logo_big == sportsmanagementHelper::getDefaultPlaceholder("clublogobig")) ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_DEFAULT_IMAGE') : Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_CUSTOM_IMAGE');
					echo HTMLHelper::_(
						'image', 'administrator/components/com_sportsmanagement/assets/images/' . $picture,
						$imageTitle, 'title= "' . $imageTitle . '"'
					);
					echo sportsmanagementHelper::getBootstrapModalImage('collapseModallogo_big' . $row->id, Uri::root() . $row->logo_big, $imageTitle, '20', Uri::root() . $row->logo_big);
					?>
                </td>
                <td class="center">
					<?php
					$picture    = ($row->logo_middle == sportsmanagementHelper::getDefaultPlaceholder("clublogomedium")) ? 'information.png' : 'ok.png';
					$imageTitle = ($row->logo_middle == sportsmanagementHelper::getDefaultPlaceholder("clublogomedium")) ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_DEFAULT_IMAGE') : Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_CUSTOM_IMAGE');
					echo HTMLHelper::_(
						'image', 'administrator/components/com_sportsmanagement/assets/images/' . $picture,
						$imageTitle, 'title= "' . $imageTitle . '"'
					);
					echo sportsmanagementHelper::getBootstrapModalImage('collapseModallogo_middle' . $row->id, Uri::root() . $row->logo_middle, $imageTitle, '20', Uri::root() . $row->logo_middle);
					?>
                </td>
                <td class="center">
					<?php
					$picture    = ($row->logo_small == sportsmanagementHelper::getDefaultPlaceholder("clublogosmall")) ? 'information.png' : 'ok.png';
					$imageTitle = ($row->logo_small == sportsmanagementHelper::getDefaultPlaceholder("clublogosmall")) ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_DEFAULT_IMAGE') : Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_CUSTOM_IMAGE');
					echo HTMLHelper::_(
						'image', 'administrator/components/com_sportsmanagement/assets/images/' . $picture,
						$imageTitle, 'title= "' . $imageTitle . '"'
					);
					echo sportsmanagementHelper::getBootstrapModalImage('collapseModallogo_small' . $row->id, Uri::root() . $row->logo_small, $imageTitle, '20', Uri::root() . $row->logo_small);
					?>
                </td>
                <td class="">
                    <input<?php echo $inputappend; ?>
                            type="text" size="10" class="form-control form-control-inline"
                            name="zipcode<?php echo $row->id; ?>"
                            value="<?php echo $row->zipcode; ?>"
                            onchange="document.getElementById('cb<?php echo $i; ?>').checked = true"/>
                    <br/>
                    <input<?php echo $inputappend; ?>
                            type="text" size="30" class="form-control form-control-inline"
                            name="location<?php echo $row->id; ?>"
                            value="<?php echo $row->location; ?>"
                            onchange="document.getElementById('cb<?php echo $i; ?>').checked = true"/>
                    <br/>
                    <input<?php echo $inputappend; ?>
                            type="text" size="30" class="form-control form-control-inline"
                            name="address<?php echo $row->id; ?>"
                            value="<?php echo $row->address; ?>"
                            onchange="document.getElementById('cb<?php echo $i; ?>').checked = true"/>
                </td>
                <td class="center">
					<?php
					echo JSMCountries::getCountryFlag($row->country);
					$append = ' onchange="document.getElementById(\'cb' . $i . '\').checked=true" ';
					echo HTMLHelper::_('select.genericlist', $this->lists['nation'], 'country' . $row->id, 'style="width:150px" class="form-control form-control-inline" size="1"' . $append, 'value', 'text', $row->country);
					?>
                </td>
                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $row->published, $i, 'clubs.', $canChange, 'cb'); ?>
						<?php
						// Create dropdown items and render the dropdown list.
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'clubs');
							HTMLHelper::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'clubs');
							echo HTMLHelper::_('actionsdropdown.render', $this->escape($row->name));
						}
						?>
                    </div>
                </td>
                <td class="order">
						<span>
							<?php echo $this->pagination->orderUpIcon($i, $i > 0, 'clubs.orderup', 'JLIB_HTML_MOVE_UP', true); ?>
						</span>
                    <span>
							<?php
							echo $this->pagination->orderDownIcon($i, $n, $i < $n, 'clubs.orderdown', 'JLIB_HTML_MOVE_DOWN', true);
							$disabled = true ? '' : 'disabled="disabled"';
							?>
						</span>
                    <input type="text" name="order[]" size="5"
                           value="<?php echo $row->ordering; ?>"
						<?php echo $disabled; ?>
                           class="form-control form-control-inline"
                           style="text-align: center"/>
                </td>
                <td class="center">
					<?php echo $row->id; ?>
                </td>
            </tr>
			<?php
			$k = 1 - $k;
		}
		?>
        </tbody>
    </table>
</div>
