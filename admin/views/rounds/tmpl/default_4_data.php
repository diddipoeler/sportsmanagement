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
HTMLHelper::_('sortablelist.sortable', $this->view.'list', 'adminForm', strtolower($this->sortDirection), $saveOrderingUrl,$this->saveOrderButton);    
}
}
$columns = 6;
if ($this->templateConfig == null)
{
	$this->templateConfig = array('show_published_check' => 1, 'show_result_check' => 1, 'show_tournament_round' => 1, 'show_ordering' => 1, 'show_id' => 1);
}
if ($this->templateConfig['show_published_check'] == 1) $columns++;
if ($this->templateConfig['show_result_check'] == 1) $columns++;
if ($this->templateConfig['show_tournament_round'] == 1) $columns++;
if ($this->templateConfig['show_ordering'] == 1) $columns++;
if ($this->templateConfig['show_id'] == 1) $columns++;
?>

<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha512-MoRNloxbStBcD8z3M/2BmnT+rg4IsMxPkXaGh2zD6LGNNFE80W3onsAhRcMAMrSoyWL9xD7Ert0men7vR8LUZg==" crossorigin="anonymous" /> -->
<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css" />
<!-- <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css"> -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.css" crossorigin="anonymous" />
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment-with-locales.min.js"></script> -->
  
  
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js" integrity="sha512-ubuT8Z88WxezgSqf3RLuNi5lmjstiJcyezx34yIU2gAHonIi27Na7atqzUZCOoY4CExaoFumzOsFQ2Ch+I/HCw==" crossorigin="anonymous"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha512-M5KW3ztuIICmVIhjSqXe01oV2bpe248gOxqmlcYrEzAvws7Pw3z6BK0iGbrwvdrUQUhi3eXgtxp5I8PDo9YfjQ==" crossorigin="anonymous"></script> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.0/moment-with-locales.min.js" integrity="sha512-EATaemfsDRVs6gs1pHbvhc6+rKFGv8+w4Wnxk4LmkC0fzdVoyWb+Xtexfrszd1YuUMBEhucNuorkf8LpFBhj6w==" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.31/moment-timezone-with-data-10-year-range.min.js" integrity="sha512-Rb9RCtecTEK3SdnnQhrZx4GM1ascb2CNHybgugRDTriP/b1As79OemxeIT5qs6RMJ/fCpeJrDjtpASh7I7EKMQ==" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.js" crossorigin="anonymous"></script>
  
<div class="table-responsive" id="editcell">
    <legend><?php echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_LEGEND', '<i>' . $this->project->name . '</i>'); ?></legend>
    <table class="<?php echo $this->table_data_class; ?>" id="<?php echo $this->view; ?>list">
        <thead>
        <tr>
            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th><?php echo HTMLHelper::_('grid.checkall'); ?></th>
            <!--    <th width="20">&nbsp;</th> -->
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ROUND_NR', 'r.roundcode', $this->sortDirection, $this->sortColumn); ?></th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ROUND_TITLE', 'r.name', $this->sortDirection, $this->sortColumn); ?></th>

            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_STARTDATE', 'r.round_date_first', $this->sortDirection, $this->sortColumn); ?></th>

            <th>&nbsp;</th>
            <th><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_ENDDATE', 'r.round_date_last', $this->sortDirection, $this->sortColumn); ?></th>

            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_EDIT_MATCHES'); ?></th>
            <?php if ($this->templateConfig['show_published_check'] == 1) { ?>
    	        <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_PUBLISHED_CHECK'); ?></th>
			<?php } ?>
		    <?php if ($this->templateConfig['show_result_check'] == 1) { ?>
	            <th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_RESULT_CHECK'); ?></th>
			<?php } ?>
            <?php if ($this->templateConfig['show_tournament_round'] == 1) { ?>
				<th><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_ROUNDS_TOURNEMENT'); ?></th>
			<?php } ?>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 'r.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <?php if ($this->templateConfig['show_ordering'] == 1) { ?>
				<th>
					<?php
					echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'r.ordering', $this->sortDirection, $this->sortColumn);
					echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'rounds.saveorder');
					?>
				</th>		
			<?php } ?>
			<?php if ($this->templateConfig['show_id'] == 1) { ?>
		        <th><?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'r.id', $this->sortDirection, $this->sortColumn); ?></th>
			<?php } ?>
        </tr>
        </thead>

        <tfoot>
        <tr>
            <td colspan="<?php echo $columns; ?>"><?php echo $this->pagination->getListFooter(); ?></td>
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
                    <input tabindex="2" type="text" size="20" maxlength="64" class="form-control form-control-inline"
                           name="name<?php echo $this->item->id; ?>" value="<?php echo $this->item->name; ?>"
                           onchange="document.getElementById('cb<?php echo $this->count_i; ?>').checked=true"/>
                    <p class="smallsub">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($this->item->alias)); ?></p>
                </td>
                <td class="center" id="round_date_first4">
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

/*
					$attribs = array(
						'onChange' => "document.getElementById('cb" . $this->count_i . "').checked=true",
					);
			*/
$attribs['class'] = 'input-large';
$attribs['size'] = '10';
$attribs['maxlength'] = '10';
$attribs['onChange'] = "document.getElementById('cb" . $this->count_i . "').checked=true";			
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
                <td class="center" id="round_date_last4">
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
				<?php if ($this->templateConfig['show_published_check'] == 1) { ?>
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
				<?php }  ?>
				<?php if ($this->templateConfig['show_result_check'] == 1) { ?>
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
				<?php } ?>
				<?php if ($this->templateConfig['show_tournament_round'] == 1) { ?>
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
				<?php
				}
				?>

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
				<?php if ($this->templateConfig['show_ordering'] == 1) { ?>
					<td class="order" id="defaultdataorder">
					<?php
					echo $this->loadTemplate('data_order');
					?>
					</td>		    
				<?php } ?>
				<?php if ($this->templateConfig['show_published_check'] == 1) { ?>
	                <td class="center"><?php echo $this->item->id; ?></td>
				<?php } ?>
            </tr>
			<?php
			
		}
		?>
        </tbody>
    </table>
</div>
  
