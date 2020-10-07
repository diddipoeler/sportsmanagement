<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage projectreferees
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

$this->saveOrder = $this->sortColumn == 'pref.ordering';
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
JHtml::_('sortablelist.sortable', $this->view.'list', 'adminForm', strtolower($this->sortDirection), $saveOrderingUrl,null);
} 
?>
<legend>
	<?php
	echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_PREF_TITLE2', '<i>' . $this->project->name . '</i>');
	?>
</legend>

<div class="table-responsive" id="editcell">
<table class="<?php echo $this->table_data_class; ?>" id="<?php echo $this->view; ?>list">
        <thead>
        <tr>
            <th width="5">
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM');
				?>
            </th>
            <th width="20">
                <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>
            <th width="20">
                &nbsp;
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PREF_NAME', 'p.lastname', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="20">
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_PID');
				?>
            </th>
            <th>
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_IMAGE');
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_POS'), 'pref.project_position_id', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 'pref.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="10%">
				<?php
				echo HTMLHelper::_('grid.sort', Text::_('JGRID_HEADING_ORDERING'), 'pref.ordering', $this->sortDirection, $this->sortColumn);
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'projectreferees.saveorder');
				?>
            </th>
            <th width="5%">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'p.id', $this->sortDirection, $this->sortColumn);
				?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan='8'>
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
//$this->count_i = $i;
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
$this->dragable_group = 'data-dragable-group="none"';
}        
			$link       = Route::_('index.php?option=com_sportsmanagement&task=projectreferee.edit&id=' . $this->item->id . '&pid=' . $this->item->project_id . '&person_id=' . $this->item->person_id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $this->item->checked_out_time, 'projectreferees.', $canCheckin);

			$inputappend = '';
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

                <td class="center">
					<?php
					if ($row->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'projectreferees.', $canCheckin); ?>
					<?php
					endif;
					if ($canEdit && !$this->item->checked_out) :
						?>
                        <a href="<?php echo $link; ?>">
							<?php
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_EDIT_DETAILS');
$image_attributes['title'] = $imageTitle;
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/edit.png',$imageTitle,$image_attributes);
							?>
                        </a>
					<?php
					endif;
					?>
                </td>
				<?php

				?>
                <td>
					<?php echo sportsmanagementHelper::formatName(null, $this->item->firstname, $this->item->nickname, $this->item->lastname, 1) ?>
                </td>
                <td class="center">
					<?php
					echo $this->item->person_id;
					?>
                </td>
                <td class="center">
					<?php
					if ($this->item->picture == '')
					{
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_NO_IMAGE');
$image_attributes['title'] = $imageTitle;
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/delete.png',$imageTitle,$image_attributes);

					}
                    elseif ($row->picture == sportsmanagementHelper::getDefaultPlaceholder("player"))
					{
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PREF_DEFAULT_IMAGE');
$image_attributes['title'] = $imageTitle;
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/information.png',$imageTitle,$image_attributes);
					}
					else
					{
						$playerName = sportsmanagementHelper::formatName(null, $this->item->firstname, $this->item->nickname, $this->item->lastname, 0);
						$picture    = Uri::root() . $this->item->picture;
						echo sportsmanagementHelper::getBootstrapModalImage('collapseModalplayerpicture' . $this->item->id, $picture, $playerName, '20', $picture);
					}
					?>
                </td>
                <td class="center">
					<?php
					if ($this->item->project_position_id != 0)
					{
						$selectedvalue = $this->item->project_position_id;
						$append        = '';
					}
					else
					{
						$selectedvalue = 0;
						$append        = ' style="background-color:#FFCCCC"';
					}

					if ($append != '')
					{
						?>
                        <script language="javascript">document.getElementById('cb<?php echo $this->count_i; ?>').checked = true;</script>
						<?php
					}

					if ($this->item->project_position_id == 0)
					{
						$append = ' style="background-color:#FFCCCC"';
					}

					echo HTMLHelper::_(
						'select.genericlist',
						$this->lists['project_position_id'], 'project_position_id' . $this->item->id,
						$inputappend . 'class="inputbox" size="1" onchange="document.getElementById(\'cb' . $this->count_i . '\').checked=true"' . $append,
						'value', 'text', $selectedvalue
					);
					?>
                </td>
                <td class="center">
					<?php
					echo HTMLHelper::_('grid.published', $this->item, $this->count_i, 'tick.png', 'publish_x.png', 'projectreferees.');
					?>
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
