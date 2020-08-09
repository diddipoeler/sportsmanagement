<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage clubs
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
use Joomla\CMS\Component\ComponentHelper;

$params     = ComponentHelper::getParams('com_sportsmanagement');
$joomlaicon = $params->get('show_joomla_icons');

$this->saveOrder = $this->sortColumn == 'a.ordering';
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
JHtml::_('sortablelist.sortable', $this->view.'list', 'adminForm', strtolower($this->sortDirection), $saveOrderingUrl);
}   
?>
<div class="table-responsive" id="editcell">
<table class="<?php echo $this->table_data_class; ?>" id="<?php echo $this->view; ?>list">
        <thead>

        <tr>
            <th width="5"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="5">
            <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>

            <th class="title">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBS_NAME_OF_CLUB', 'a.name', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <th>
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBS_WEBSITE', 'a.website', $this->sortDirection, $this->sortColumn); ?>
            </th>

            <th>
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUB_UNIQUE_ID', 'a.unique_id', $this->sortDirection, $this->sortColumn); ?>
                <br/>
				<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB_NEW_CLIB_ID'); ?></th>
            </th>

            <th width="20">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBS_L_LOGO', 'a.logo_big', $this->sortDirection, $this->sortColumn); ?>
            </th>
   
            <th width="">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUB_POSTAL_CODE', 'a.zipcode', $this->sortDirection, $this->sortColumn); ?>
                <br/>
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBS_CITY', 'a.location', $this->sortDirection, $this->sortColumn); ?>
                <br/>
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUB_ADDRESS', 'a.address', $this->sortDirection, $this->sortColumn); ?>
            </th>


            <th width="">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBS_LATITUDE', 'a.latitude', $this->sortDirection, $this->sortColumn); ?>
                <br/>
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBS_LONGITUDE', 'a.longitude', $this->sortDirection, $this->sortColumn); ?>
            </th>


            <th width="">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBS_COUNTRY', 'a.country', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <th width="">
				<?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_CLUBS_COUNTRY', 'a.country', $this->sortDirection, $this->sortColumn); ?>
            </th>
            <th width="" class="nowrap center">
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 's.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="">
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
            <td colspan="10">
				<?php echo $this->pagination->getListFooter(); ?>
            </td>
            <td colspan='8'>
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
			$link       = Route::_('index.php?option=com_sportsmanagement&task=club.edit&id=' . $this->item->id);
			$link2      = Route::_('index.php?option=com_sportsmanagement&view=teams&club_id=' . $this->item->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'clubs.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.club.' . $this->item->id) && $canCheckin;
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

					<?php if ($this->item->checked_out)
						:
						?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->item->editor, $this->item->checked_out_time, 'clubs.', $canCheckin); ?>
					<?php endif; ?>
					<?php
					if ($canEdit)
						:
						?>
                        <a href="<?php echo Route::_('index.php?option=com_sportsmanagement&task=club.edit&id=' . (int) $this->item->id); ?>">
							<?php echo $this->escape($this->item->name); ?></a>
					<?php else
						:
						?>
						<?php echo $this->escape($this->item->name); ?>
					<?php endif; ?>

                    <p class="smallsub">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($this->item->alias)); ?></p>
                </td>
				<?php

				?>


                </td>
                <td>
					<?php
					if ($this->item->website != '')
					{
						echo '<a href="' . $this->item->website . '" target="_blank">';
					}

					echo $this->item->website;

					if ($this->item->website != '')
					{
						echo '</a>';
					}
					?>
                </td>
                <td>
					<?php
					// Echo $row->unique_id;
					?>
                    <input<?php echo $inputappend; ?> type="text" size="10" class="form-control form-control-inline"
                                                      name="unique_id<?php echo $this->item->id; ?>"
                                                      value="<?php echo $this->item->unique_id; ?>"
                                                      onchange="document.getElementById('cb<?php echo $this->count_i; ?>').checked=true"/>

                    <br/>
                    <input<?php echo $inputappend; ?> type="text" size="10" class="form-control form-control-inline"
                                                      name="new_club_id<?php echo $this->item->id; ?>"
                                                      value="<?php echo $this->item->new_club_id; ?>"
                                                      onchange="document.getElementById('cb<?php echo $this->count_i; ?>').checked=true"/>
                </td>
                <td class="center">
		<?php
		$picture    = ($this->item->logo_big == sportsmanagementHelper::getDefaultPlaceholder("clublogobig")) ? 'information.png' : 'ok.png';
		$imageTitle = ($this->item->logo_big == sportsmanagementHelper::getDefaultPlaceholder("clublogobig")) ? Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_DEFAULT_IMAGE') : Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUBS_CUSTOM_IMAGE');
		$image_attributes['title'] = $imageTitle;
		echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/'.$picture,$imageTitle,$image_attributes);
		echo sportsmanagementHelper::getBootstrapModalImage('collapseModallogo_big' . $this->item->id, Uri::root() . $this->item->logo_big, $imageTitle, '20', Uri::root() . $this->item->logo_big);
		?>
                </td>

                <td class="">
                    <input<?php echo $inputappend; ?> type="text" size="10" class="form-control form-control-inline"
                                                      name="zipcode<?php echo $this->item->id; ?>"
                                                      value="<?php echo $this->item->zipcode; ?>"
                                                      onchange="document.getElementById('cb<?php echo $this->count_i; ?>').checked=true"/>
                    <br/>
                    <input<?php echo $inputappend; ?> type="text" size="30" class="form-control form-control-inline"
                                                      name="location<?php echo $this->item->id; ?>"
                                                      value="<?php echo $this->item->location; ?>"
                                                      onchange="document.getElementById('cb<?php echo $this->count_i; ?>').checked=true"/>
                    <br/>
                    <input<?php echo $inputappend; ?> type="text" size="30" class="form-control form-control-inline"
                                                      name="address<?php echo $this->item->id; ?>"
                                                      value="<?php echo $this->item->address; ?>"
                                                      onchange="document.getElementById('cb<?php echo $this->count_i; ?>').checked=true"/>
                </td>

                <td class="">
					<?php echo $this->item->latitude; ?>
                    <br/>
					<?php echo $this->item->longitude; ?>
                </td>
                <td class="center">
					<?php
					$append = ' onchange="document.getElementById(\'cb' . $this->count_i . '\').checked=true" style="background-color:#bbffff"';
					echo HTMLHelper::_(
						'select.genericlist', $this->lists['nation'], 'country' . $this->item->id,
						'class="form-control form-control-inline" size="1"' . $append, 'value', 'text', $this->item->country
					);
					?>
                </td>
                <td class="center"><?php echo JSMCountries::getCountryFlag($this->item->country); ?></td>
                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'clubs.', $canChange, 'cb'); ?>
						<?php
						// Create dropdown items and render the dropdown list.
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'clubs');
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'clubs');
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
  
