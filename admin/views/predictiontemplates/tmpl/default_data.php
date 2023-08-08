<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage predictiontemplates
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$this->saveOrder = $this->sortColumn == 'tmpl.ordering';
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

<!--	<div id='editcell'> -->
<!--		<fieldset class='adminform'> -->
<legend>
	<?php
	if ($this->pred_id > 0)
	{
		$outputStr = Text::sprintf(
			'COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_TITLE2',
			'<i>' . $this->predictiongame->name . '</i>',
			' ' . $this->predictiongame->id . ' '
		);
	}
	else
	{
		$outputStr = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_DESCR');
	}

	echo $outputStr;
	?>
</legend>
<?php
if (($this->pred_id > 0) && ($this->predictiongame->master_template))
{
	echo $this->loadTemplate('import');
}


if ($this->pred_id > 0)
{
	?>
    
    <div class="table-responsive" id="editcell_predictiongames">
    <table class="<?php echo $this->table_data_class; ?>">
        <thead>
        <tr>
            <th class='title' width='5'>
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM');
				?>
            </th>
            <th class='title' width='20'>
                <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>
            <th class='title' width='20'>
                &nbsp;
            </th>
            <th class='title' nowrap='nowrap'>
				<?php
				echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_TMPL_FILE'), 'tmpl.template', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th class='title' nowrap='nowrap'>
				<?php
				echo HTMLHelper::_('grid.sort', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_TITLE3'), 'tmpl.title', $this->sortDirection, $this->sortColumn);
				?>
            </th>

            <th class='title' width='20' nowrap='nowrap'>
				<?php
				echo HTMLHelper::_('grid.sort', Text::_('JGRID_HEADING_ID'), 'tmpl.id', $this->sortDirection, $this->sortColumn);
				?>
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
            <td colspan='6'>
				<?php
				echo $this->pagination->getListFooter();
				?>
            </td>
            <td colspan="2"><?php echo $this->pagination->getResultsCounter(); ?>
            </td>
        </tr>
        </tfoot>
        <tbody <?php if ( $this->saveOrder && version_compare(substr(JVERSION, 0, 3), '4.0', 'ge') ) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->sortDirection); ?>" <?php endif; ?>>
		<?php
		$k = 0;

			foreach ($this->items as $this->count_i => $this->item)
		{

if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{
$this->dragable_group = 'data-dragable-group="none"';
}    
			//$this->item =& $this->items[$i];

			$link       = Route::_('index.php?option=com_sportsmanagement&task=predictiontemplate.edit&id=' . $this->item->id . '&predid=' . $this->prediction_id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'predictiontemplates.', $canCheckin);
			?>
            <tr class="row<?php echo $this->count_i % 2; ?>" <?php echo $this->dragable_group; ?>>
                <td>
					<?php
					echo $this->pagination->getRowOffset($this->count_i);
					?>
                </td>
                <td>
					<?php
					echo HTMLHelper::_('grid.id', $this->count_i, $this->item->id);
					?>
                </td>
                <td style='text-align:center; '>
					<?php
					if ($this->item->checked_out)
						:
						?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'predictiontemplates.', $canCheckin); ?>
					<?php endif; ?>
                    <a href='<?php echo $link; ?>'>
						<?php
						echo HTMLHelper::_(
							'image',
							'administrator/components/com_sportsmanagement/assets/images/edit.png',
							Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_EDIT_SETTINGS'),
							'title= "' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_PTMPLS_EDIT_SETTINGS') . '"'
						);
						?>
                    </a>
                </td>
                <td style='text-align:left; ' nowrap='nowrap'>
					<?php
					echo $this->item->template;
					?>
                </td>
                <td style='text-align:left; ' nowrap='nowrap'>
					<?php
					echo Text::_($this->item->title);
					?>
                </td>
                <td style='text-align:center; '>
					<?php
					echo $this->item->id;
					?>
                </td>
                <td><?php echo $this->item->modified; ?></td>
                <td><?php echo $this->item->username; ?></td>
            </tr>
			<?php
			$k = 1 - $k;
		}
		?>
        </tbody>
    </table>
    </div>
	<?php
}
?>
<!--		</fieldset> -->
<!--	</div> -->

