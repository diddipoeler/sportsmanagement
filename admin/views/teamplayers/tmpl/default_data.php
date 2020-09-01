<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teamplayers
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

$this->saveOrder = $this->sortColumn == 'ppl.ordering';
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

/** welche joomla version */
/*
if (version_compare(substr(JVERSION, 0, 5), '4.0.0', 'ge'))
{
}	
elseif (version_compare(substr(JVERSION, 0, 5), '3.0.0', 'ge'))
{
	HTMLHelper::_('behavior.framework', true);
}
else
{
	HTMLHelper::_('behavior.mootools');
}
*/
?>
<div class="table-responsive" id="editcell">
<table class="<?php echo $this->table_data_class; ?>" id="<?php echo $this->view; ?>list">
        <thead>
        <tr>
            <th width="">
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM');
				?>
            </th>
            <th width="">
                <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>
            <th width="">
                &nbsp;
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_NAME', 'ppl.lastname', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NATIONALITY', 'pl.country', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'PID', 'tp.person_id', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_IMAGE', 'ppl.picture', $this->sortDirection, $this->sortColumn);
				?>
            </th>
			<?PHP
			if ($this->_persontype == 1)
			{
				?>
                <th width="">
					<?php
					echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_MARKET_VALUE', 'tp.market_value', $this->sortDirection, $this->sortColumn);
					?>
                </th>
                <th width="">
					<?php
					echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_SHIRTNR', 'tp.jerseynumber', $this->sortDirection, $this->sortColumn);
					?>
                </th>
				<?PHP
			}
			?>
            <th width="">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_POS', 'ppl.position_id', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th>
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_STATUS');
				?>
            </th>
            <th>
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_STATUS_PROJECT');
				?>
            </th>
            <th>
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 'ppl.published', $this->sortDirection, $this->sortColumn);
				?></th>
            <th width="">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'ppl.ordering', $this->sortDirection, $this->sortColumn);
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'teamplayers.saveorder');
				?>
            </th>
            <th width="">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'ppl.id', $this->sortDirection, $this->sortColumn);
				?>
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="9">
				<?php
				echo $this->pagination->getListFooter();
				?>
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
			$link        = Route::_(
				'index.php?option=com_sportsmanagement&task=teamplayer.edit&project_team_id=' .
				$this->item->projectteam_id . '&person_id=' . $this->item->id . '&id=' . $this->item->tpid . '&pid=' . $this->project->id . '&team_id=' . $this->team_id . '&persontype=' . $this->_persontype
			);
			$canEdit     = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin  = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;
			$checked     = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'teamplayers.', $canCheckin);
			$inputappend = '';
			$canChange   = $this->user->authorise('core.edit.state', 'com_sportsmanagement.teamplayer.' . $this->item->id) && $canCheckin;
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

                <td>
					<?php if ($this->item->checked_out)
						:
						?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->item->editor, $this->item->checked_out_time, 'teamplayers.', $canCheckin); ?>
					<?php endif; ?>

					<?php if ($canEdit && !$this->item->checked_out)
						:
						?>


                        <a href="<?php echo $link; ?>">
							<?php
$imageFile = 'administrator/components/com_sportsmanagement/assets/images/edit.png';
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_EDIT_DETAILS');
$image_attributes['title'] = $imageTitle;
$image = HTMLHelper::_('image',$imageFile,$imageTitle,$image_attributes);
echo HTMLHelper::link($link, $image);
			
							
							?>
                        </a>


					<?php else

						:
						?>
						<?php // Echo $this->escape($row->name);
						?>
					<?php endif; ?>
                </td>


				<?php


				?>
                <td>
					<?php echo sportsmanagementHelper::formatName(null, $this->item->firstname, $this->item->nickname, $this->item->lastname, 0) ?>
                </td>

                <td class="nowrap" class="center">
					<?php
					$append      = '';
					$inputappend = '';

					if (empty($this->item->country))
					{
						$append = ' background-color:#FFCCCC;';
					}

					echo JHtmlSelect::genericlist(
						$this->lists['nation'],
						'country' . $this->item->person_id,
						$inputappend . ' class="form-control form-control-inline" style="width:140px; ' . $append . '" onchange="document.getElementById(\'cb' . $this->count_i . '\').checked=true"',
						'value',
						'text',
						$this->item->country
					);
					?>
                </td>


                <td class="center">
					<?php
					echo $this->item->person_id;
					?>
                </td>
                <td class="center">
					<?php
					if ($this->item->season_picture == '')
					{
$imageFile = 'administrator/components/com_sportsmanagement/assets/images/delete.png';
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_NO_IMAGE');
$image_attributes['title'] = $imageTitle;
$image = HTMLHelper::_('image',$imageFile,$imageTitle,$image_attributes);
echo $image;					

					}
                    elseif ($this->item->season_picture == sportsmanagementHelper::getDefaultPlaceholder("player"))
					{
$imageFile = 'administrator/components/com_sportsmanagement/assets/images/information.png';
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_DEFAULT_IMAGE');
$image_attributes['title'] = $imageTitle;
$image = HTMLHelper::_('image',$imageFile,$imageTitle,$image_attributes);
echo $image;
echo sportsmanagementHelper::getBootstrapModalImage('season_picture' . $this->item->id, Uri::root() . $this->item->season_picture, $imageTitle, '20', Uri::root() . $this->item->season_picture);			    			    
}
elseif ($this->item->season_picture == !'')
{
$playerName = sportsmanagementHelper::formatName(null, $this->item->firstname, $this->item->nickname, $this->item->lastname, 0);
echo sportsmanagementHelper::getBootstrapModalImage('season_picture' . $this->item->id, Uri::root() . $this->item->season_picture, $playerName, '20', Uri::root() . $this->item->season_picture);			    
}
?>
<br />
 <?php
$link2 = 'index.php?option=com_sportsmanagement&view=imagelist' .'&teamplayer_id='.$this->item->id.
'&imagelist=1&asset=com_sportsmanagement&folder=teamplayers' . '&author=&fieldid=' . '&tmpl=component&type=teamplayers'.'&fieldname=picture';
echo sportsmanagementHelper::getBootstrapModalImage('select', '', Text::_('JLIB_FORM_MEDIA_PREVIEW_SELECTED_IMAGE').' ', '20', Uri::base() . $link2, $this->modalwidth, $this->modalheight);        
        ?>
                </td>
				<?PHP
				if ($this->_persontype == 1)
				{
					?>
                    <td class="center">
                        <input<?php echo $inputappend; ?> type="text" size="4" class="form-control form-control-inline"
                                                          name="market_value<?php echo $this->item->id; ?>"
                                                          value="<?php echo $this->item->market_value; ?>"
                                                          onchange="document.getElementById('cb<?php echo $this->count_i; ?>').checked=true"/>
                    </td>
                    <td class="center">
                        <input<?php echo $inputappend; ?> type="text" size="4" class="form-control form-control-inline"
                                                          name="jerseynumber<?php echo $this->item->id; ?>"
                                                          value="<?php echo $this->item->jerseynumber; ?>"
                                                          onchange="document.getElementById('cb<?php echo $this->count_i; ?>').checked=true"/>
                    </td>
					<?PHP
				}
				?>
                <td class="nowrap" class="center">
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
			<!--
                        <script language="javascript">document.getElementById('cb<?php echo $this->count_i; ?>').checked = true;</script>
			-->
						<?php
					}


					if ($this->item->project_position_id == 0)
					{
						$append = ' style="background-color:#FFCCCC"';

						/**
						 *
						 * einen vorschlag generieren
						 */
						$mdlPerson      = BaseDatabaseModel::getInstance("player", "sportsmanagementModel");
						$project_person = $mdlPerson->getPerson($this->item->person_id);
						$position_id    = $project_person->position_id;
						/**
						 *
						 * build the html options for position
						 */
						$position_ids          = array();
						$mdlPositions          = BaseDatabaseModel::getInstance('Positions', 'sportsmanagementModel');
						$project_ref_positions = $mdlPositions->getProjectPositions($this->project_id, $this->_persontype);

						if ($project_ref_positions)
						{
							$position_ids = array_merge($position_ids, $project_ref_positions);
						}

						foreach ($position_ids as $items => $item)
						{
							if ($item->position_id == $position_id)
							{
								$selectedvalue = $item->value;
							}
						}
					}

					echo HTMLHelper::_('select.genericlist', $this->lists['project_position_id'], 'project_position_id' . $this->item->id, $inputappend . 'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' . $this->count_i . '\').checked=true"' . $append, 'value', 'text', $selectedvalue);

					?>
                    <input type="hidden" name="position_id<?php echo $this->item->id; ?>"
                           value="<?php echo $this->item->position_id; ?>"/>
                    <input type="hidden" name="person_id<?php echo $this->item->id; ?>" value="<?php echo $this->item->tpid; ?>"/>
                    <input type="hidden" name="tpid[<?php echo $this->item->id; ?>]" value="<?php echo $this->item->tpid; ?>"/>

                </td>
                <td class="nowrap" class="center">
					<?php
					// $row->injury = 1;
					// $row->suspension = 1;
					// $row->away = 1;
					if ($this->item->injury > 0)
					{
$imageFile = 'administrator/components/com_sportsmanagement/assets/images/injured.gif';
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_INJURED');
$image_attributes['title'] = $imageTitle;
$image = HTMLHelper::_('image',$imageFile,$imageTitle,$image_attributes);
echo $image;						

					}


					if ($this->item->suspension > 0)
					{
$imageFile = 'administrator/components/com_sportsmanagement/assets/images/suspension.gif';
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_SUSPENDED');
$image_attributes['title'] = $imageTitle;
$image = HTMLHelper::_('image',$imageFile,$imageTitle,$image_attributes);
echo $image;						
					}


					if ($this->item->away > 0)
					{
$imageFile = 'administrator/components/com_sportsmanagement/assets/images/away.gif';
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS_AWAY');
$image_attributes['title'] = $imageTitle;
$image = HTMLHelper::_('image',$imageFile,$imageTitle,$image_attributes);
echo $image;						
					}


					if (!$this->item->injury
						&& !$this->item->suspension
						&& !$this->item->away
					)
					{
$imageFile = 'administrator/components/com_sportsmanagement/assets/images/players.png';
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_TPLAYERS');
$image_attributes['title'] = $imageTitle;
$image = HTMLHelper::_('image',$imageFile,$imageTitle,$image_attributes);
echo $image;						
					}
					?>
                    &nbsp;
                </td>
                <td class="center">
					<?php

					$class   = "btn-group btn-group-yesno";
					$options = array(
						HTMLHelper::_('select.option', '0', Text::_('JNO')),
						HTMLHelper::_('select.option', '1', Text::_('JYES'))
					);

					$html   = array();
					$html[] = '<fieldset id="project_published' . $this->item->id . '" class="' . $class . '" >';

					foreach ($options as $in => $option)
					{
						$checked = ($option->value == $this->item->project_published) ? ' checked="checked"' : '';
						$btn     = ($option->value == $this->item->project_published && $this->item->project_published) ? ' active btn-success' : ' ';
						$btn     = ($option->value == $this->item->project_published && !$this->item->project_published) ? ' active btn-danger' : $btn;

						$onchange = ' onchange="document.getElementById(\'cb' . $this->count_i . '\').checked=true"';
						$html[]   = '<input type="radio" style="display:none;" id="project_published' . $this->item->id . $in . '" name="project_published' . $this->item->id . '" value="'
							. $option->value . '"' . $onchange . ' />';

						$html[] = '<label for="project_published' . $this->item->id . $in . '"' . $checked . ' class="btn' . $btn . '" >'
							. Text::_($option->text) . '</label>';
					}

					echo implode($html);
					?>
                </td>
                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'teamplayers.', $canChange, 'cb');
						?>
						<?php // Create dropdown items and render the dropdown list.
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'teamplayers');
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'teamplayers');
							echo HTMLHelper::_('actionsdropdown.render', $this->escape($this->item->firstname . ' ' . $this->item->lastname));
						}
						?>
                    </div>
                </td>
                <td class="order" id="defaultdataorder">
<?php
echo $this->loadTemplate('data_order');
?>
</td>
                <td class="center">
					<?php
					echo $this->item->tpid;
					?>
                </td>
            </tr>
			<?php
			//$k = 1 - $k;
		}
		?>
        </tbody>
    </table>
</div>
  
