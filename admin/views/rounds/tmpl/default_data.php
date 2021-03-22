<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rounds
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

$this->saveOrder = $this->sortColumn == 'r.ordering';
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
    <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_LEGEND', '<i>' . $this->project->name . '</i>'); ?></legend>
    <table class="<?php echo $this->table_data_class; ?>" id="<?php echo $this->view; ?>list">
        <thead>
        <tr>
            <th width="1%"><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="1%">
			<?php echo HTMLHelper::_('grid.checkall'); ?>
			</th>
            <!--    <th width="20">&nbsp;</th> -->
            <th width="20"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ROUND_NR', 'r.roundcode', $this->sortDirection, $this->sortColumn); ?></th>
            <th width="20"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ROUND_TITLE', 'r.name', $this->sortDirection, $this->sortColumn); ?></th>

            <th width="20"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_STARTDATE', 'r.round_date_first', $this->sortDirection, $this->sortColumn); ?></th>

            <th width="1%">&nbsp;</th>
            <th width="20"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ENDDATE', 'r.round_date_last', $this->sortDirection, $this->sortColumn); ?></th>

            <th width="10%"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_EDIT_MATCHES'); ?></th>
            <th width="20"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_PUBLISHED_CHECK'); ?></th>
            <th width="20"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_RESULT_CHECK'); ?></th>
            <th width="20"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_TOURNEMENT'); ?></th>
            <th width="5%" class="title">
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 'r.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>
<th width="10%">
			<?php
			echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'r.ordering', $this->sortDirection, $this->sortColumn);
			echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'rounds.saveorder');
			?>
        </th>		
            <th width="5%"><?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'r.id', $this->sortDirection, $this->sortColumn); ?></th>
        </tr>
        </thead>

        <tfoot>
        <tr>
            <td colspan="11"><?php echo $this->pagination->getListFooter(); ?></td>
            <td colspan="3"><?php echo $this->pagination->getResultsCounter(); ?></td>
        </tr>
        </tfoot>

         <tbody <?php if ( $this->saveOrder && version_compare(substr(JVERSION, 0, 3), '4.0', 'ge') ) :?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($this->sortDirection); ?>" <?php endif; ?>>
	<?php
		foreach ($this->items as $this->count_i => $this->item)
		{
			$link1      = Route::_('index.php?option=com_sportsmanagement&task=round.edit&id=' . $this->item->id . '&pid=' . $this->project->id);
			$link2      = Route::_('index.php?option=com_sportsmanagement&view=matches&rid=' . $this->item->id . '&pid=' . $this->project->id);
			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
			$checked    = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'rounds.', $canCheckin);
			$canChange  = $this->user->authorise('core.edit.state', 'com_sportsmanagement.round.' . $this->item->id) && $canCheckin;
			?>
            <tr class="row<?php echo $this->count_i % 2; ?>" <?php echo $this->dragable_group; ?>>
                <td class="center">
					<?php
					echo $this->pagination->getRowOffset($this->count_i); ?>
                </td>
                <td class="center">
					<?php echo HTMLHelper::_('grid.id', $this->count_i, $this->item->id); ?>
                    <!--  </td> -->
                    <!--    <td class="center"> -->

					<?php
					if ($this->item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'rounds.', $canCheckin); ?>
					<?php endif; ?>
					<?php
					if ($canEdit && !$this->item->checked_out) :
						$imageTitle  = Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_EDIT_DETAILS');
						$imageFile   = 'administrator/components/com_sportsmanagement/assets/images/edit.png';
						$imageParams['title'] = $imageTitle;
						echo HTMLHelper::link($link1, HTMLHelper::_('image',$imageFile, $imageTitle, $imageParams));
					endif;
					?>
                </td>
                <td class="center">
                    <input tabindex="1" type="text" style="text-align: center" size="5"
                           class="form-control form-control-inline" name="roundcode<?php echo $this->item->id; ?>"
                           value="<?php echo $this->item->roundcode; ?>"
                           onchange="document.getElementById('cb<?php echo $this->count_i; ?>').checked=true"/>
                </td>
                <td class="center">
                    <input tabindex="2" type="text" size="30" maxlength="64" class="form-control form-control-inline"
                           name="name<?php echo $this->item->id; ?>" value="<?php echo $this->item->name; ?>"
                           onchange="document.getElementById('cb<?php echo $this->count_i; ?>').checked=true"/>
                    <p class="smallsub">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($this->item->alias)); ?></p>
                </td>
                <td class="center">
					<?php

					/**
					 * das wurde beim kalender geändert
					 * $attribs = array(
					 * 'onChange' => "alert('it works')",
					 * "showTime" => 'false',
					 * "todayBtn" => 'true',
					 * "weekNumbers" => 'false',
					 * "fillTable" => 'true',
					 * "singleHeader" => 'false',
					 * );
					 * echo HTMLHelper::_('calendar', Factory::getDate()->format('Y-m-d'), 'date', 'date', '%Y-%m-%d', $attribs); ?>
					 */


					$attribs = array(
						'onChange' => "document.getElementById('cb" . $this->count_i . "').checked=true",
					);
					$date1   = sportsmanagementHelper::convertDate($this->item->round_date_first, 1);
					$append  = '';
					if (($date1 == '00-00-0000') || ($date1 == ''))
					{
						$append = ' style="background-color:#FFCCCC;" ';
						$date1  = '';
					}
					echo HTMLHelper::calendar(
						$date1,
						'round_date_first' . $this->item->id,
						'round_date_first' . $this->item->id,
						'%d-%m-%Y',
						$attribs
					);
					?>
                </td>
                <td class="center">&nbsp;-&nbsp;</td>
                <td class="center">
					<?php
					$date2  = sportsmanagementHelper::convertDate($this->item->round_date_last, 1);
					$append = '';
					if (($date2 == '00-00-0000') || ($date2 == ''))
					{
						$append = ' style="background-color:#FFCCCC;"';
						$date2  = '';
					}
					echo HTMLHelper::calendar(
						$date2,
						'round_date_last' . $this->item->id,
						'round_date_last' . $this->item->id,
						'%d-%m-%Y',
						$attribs
					);
					?>
                </td>
                <td class="center" class="nowrap">
					<?php
					$link2Title  = Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_EDIT_MATCHES_LINK');
					$link2Params = "title='$link2Title'";
					echo HTMLHelper::link($link2, $link2Title, $link2Params);
					?>
                </td>
                <td class="center" class="nowrap">
					<?php
					if (($this->item->countUnPublished == 0) && ($this->item->countMatches > 0))
					{
						$imageTitle  = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ALL_PUBLISHED', $this->item->countMatches);
						$imageFile   = 'administrator/components/com_sportsmanagement/assets/images/ok.png';
						$imageParams['title'] = $imageTitle;
						echo HTMLHelper::_('image',$imageFile, $imageTitle, $imageParams);
					}
					else
					{
						if ($this->item->countMatches == 0)
						{
							$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ANY_MATCHES');
						}
						else
						{
							$imageTitle = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_PUBLISHED_NR', $this->item->countUnPublished);
						}
						$imageFile   = 'administrator/components/com_sportsmanagement/assets/images/error.png';
						$imageParams['title'] = $imageTitle;
						echo HTMLHelper::_('image',$imageFile, $imageTitle, $imageParams);
					}
					?>
                </td>
                <td class="center" class="nowrap">
					<?php
					if (($this->item->countNoResults == 0) && ($this->item->countMatches > 0))
					{
						$imageTitle  = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ALL_RESULTS', $this->item->countMatches);
						$imageFile   = 'administrator/components/com_sportsmanagement/assets/images/ok.png';
						$imageParams['title'] = $imageTitle;
						echo HTMLHelper::_('image',$imageFile, $imageTitle, $imageParams);
					}
					else
					{
						if ($this->item->countMatches == 0)
						{
							$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ANY_MATCHES');
						}
						else
						{
							$imageTitle = Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_RESULTS_MISSING', $this->item->countNoResults);
						}
						$imageFile   = 'administrator/components/com_sportsmanagement/assets/images/error.png';
						$imageParams['title'] = $imageTitle;
						echo HTMLHelper::_('image',$imageFile, $imageTitle, $imageParams);
					}
					?>
                </td>

                <td class="center">
					<?php
					$append = ' style="background-color:#bbffff"';
					echo HTMLHelper::_(
						'select.genericlist',
						$this->lists['tournementround'],
						'tournementround' . $this->item->id,
						'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
						$this->count_i . '\').checked=true"' . $append,
						'value', 'text', $this->item->tournement
					);
					?>
                </td>

                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'rounds.', $canChange, 'cb'); ?>
						<?php
						// Create dropdown items and render the dropdown list.
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'rounds');
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'rounds');
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
  
