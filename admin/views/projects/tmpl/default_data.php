<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage projects
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
use Joomla\CMS\Session\Session;

$this->saveOrder = $this->sortColumn == 'p.ordering';

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
            <th width=""><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th width="40" class="">
                <?php echo HTMLHelper::_('grid.checkall'); ?>
            </th>
            <th class="title">
	<?php
	echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_NAME_OF_PROJECT', 'p.name', $this->sortDirection, $this->sortColumn);
	?>
            </th>
            <th class="title">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_LEAGUE', 'l.name', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_LEAGUES_COUNTRY', 'l.country', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th class="title">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_SEASON', 's.name', $this->sortDirection, $this->sortColumn);
				?>
                <br>
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_FES_PARAM_LABEL_USE_CURRENT_SEASON');
				?>
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
	<br>
	<?php
	echo Text::_('COM_SPORTSMANAGEMENT_SETTINGS_PROJECTTEAMS_QUICKADD');
	?>	    
            </th>
            <th class="title">
				<?php
				echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_PROJECTTYPE', 'p.project_type', $this->sortDirection, $this->sortColumn);
				?>
                <br>
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECT_TEMPLATES');
				?>
            </th>
            <th>
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_IMAGE');
				?>
            </th>
            <th width="" class="title">
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_GAMES');
				?>
            </th>
            <th class="title">
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_D_MENU_TEAMS');
				?>
            </th>
            <th width="" class="title">
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_ROUND');
				?>
            </th>
            <th width="" class="title">
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_DIVISION');
				?>
            </th>
            <th width="" class="title">
				<?php
				echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_USER_FIELD');
				?>
            </th>
            <th width="" class="title">
				<?php
				echo HTMLHelper::_('grid.sort', 'JSTATUS', 'p.published', $this->sortDirection, $this->sortColumn);
				?>
            </th>
            <th width="" class="title">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ORDERING', 'p.ordering', $this->sortDirection, $this->sortColumn);
				echo HTMLHelper::_('grid.order', $this->items, 'filesave.png', 'projects.saveorder');
				?>
            </th>
            <th width="" class="title">
				<?php
				echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'p.id', $this->sortDirection, $this->sortColumn);
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
            <td colspan='15'><?php echo $this->pagination->getListFooter(); ?>
            </td>
            <td colspan="6"><?php echo $this->pagination->getResultsCounter(); ?>
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

			$link       = Route::_('index.php?option=com_sportsmanagement&task=project.edit&id=' . $this->item->id);
			$link2      = Route::_('index.php?option=com_sportsmanagement&view=projects&task=project.display&id=' . $this->item->id);
			$link2panel = Route::_('index.php?option=com_sportsmanagement&task=project.edit&layout=panel&pid=' . $this->item->id . '&stid=' . $this->item->sports_type_id . '&id=' . $this->item->id);
			$link2teams = Route::_('index.php?option=com_sportsmanagement&view=projectteams&pid=' . $this->item->id . '&id=' . $this->item->id);

			$link2rounds    = Route::_('index.php?option=com_sportsmanagement&view=rounds&pid=' . $this->item->id);
			$link2divisions = Route::_('index.php?option=com_sportsmanagement&view=divisions&pid=' . $this->item->id);

			$canEdit    = $this->user->authorise('core.edit', 'com_sportsmanagement');
			$canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $this->item->checked_out == $this->user->get('id') || $this->item->checked_out == 0;

			$checked   = HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'projects.', $canCheckin);
			$canChange = $this->user->authorise('core.edit.state', 'com_sportsmanagement.project.' . $this->item->id) && $canCheckin;
			?>
            <tr class="row<?php echo $this->count_i % 2; ?>" <?php echo $this->dragable_group; ?>>
                <td class="center"><?php echo $this->pagination->getRowOffset($this->count_i); ?></td>
                <td width="40" class=""><?php echo HTMLHelper::_('grid.id', $this->count_i, $this->item->id); ?>
					<?php
					$inputappend = '';
					if (($this->item->checked_out != $this->user->get('id')) && $this->item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $this->count_i, $this->user->get('id'), $this->item->checked_out_time, 'projects.', $canCheckin); ?>
					<?php else: ?>
                        <a href="<?php echo $link; ?>">
<?php
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_EDIT_DETAILS');
$image_attributes['title'] = $imageTitle;			
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/edit.png',$imageTitle,$image_attributes);
							?>
                        </a>
                        </br>
					<?php
					endif;
if (version_compare(substr(JVERSION, 0, 3), '4.0', 'ge'))
{			
$pcture_link = 'index.php?option=com_media&tmpl=component&asset=com_sportsmanagement&author=&path=local-images:/com_sportsmanagement/database/projectimages/'.$this->item->id;
}	
elseif (version_compare(substr(JVERSION, 0, 3), '3.0', 'ge'))
{
//$pcture_link = 'index.php?option=com_sportsmanagement&view=imagelist&tmpl=component&asset=com_sportsmanagement&author=&folder=images/com_sportsmanagement/database/projectimages/' . $row->id.'&pid='. $row->id;	
//$pcture_link = 'index.php?option=com_sportsmanagement&view=imagelist&tmpl=component&asset=com_sportsmanagement&author=&folder=projectimages/'.$row->id.'&pid='.$row->id;
$pcture_link = 'index.php?option=com_sportsmanagement&view=imagelist&tmpl=component&asset=com_sportsmanagement&author=&folder=projectimages'.'&pid='.$this->item->id;
}	
					echo sportsmanagementHelper::getBootstrapModalImage('projectimages' . $this->item->id, Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/link.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_PROJECTSPICTURE'), '20', Uri::base() . $pcture_link, $this->modalwidth, $this->modalheight);
					?>
                </td>

                <td>
					<?php

					?>
                    <a href="<?php echo $link2panel; ?>"><?php echo $this->item->name; ?></a>
					<?php
					?>
                    <p class="smallsub">
						<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($this->item->alias)); ?></p>
                </td>
                <td>
					<?php
					if ($this->state->get('filter.search_nation'))
					{
						$append = ' style="background-color:#bbffff"';
						echo HTMLHelper::_(
							'select.genericlist',
							$this->league,
							'league' . $this->item->id,
							$inputappend . 'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
							$this->count_i . '\').checked=true"' . $append,
							'id', 'name', $this->item->league_id
						);
					}
					else
					{
						echo $this->item->league . '<br>';
					}
					?>
                    <br />
                    <?php echo $this->item->league_id; ?>
                </td>
                <td class="center"><?php echo JSMCountries::getCountryFlag($this->item->country); ?></td>
                <td class="center"><?php echo $this->item->season; ?>
                <br />
<?php
$picture = $this->model->existcurrentseason($this->season_ids, $this->item->league_id) ? 'ok.png' : 'error.png';
$image_attributes['title'] = 'title= "' . '' . '"';			
echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/' . $picture, '', $image_attributes);
					?>
                </td>
                <td class="center">
					<?php
					echo Text::_($this->item->sportstype);
					?>
                </td>

                <td class="center">
					<?php
					$inputappend = '';
					$append      = ' style="background-color:#bbffff"';
					echo HTMLHelper::_(
						'select.genericlist',
						$this->lists['agegroup'],
						'agegroup' . $this->item->id,
						$inputappend . 'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
						$this->count_i . '\').checked=true"' . $append,
						'value', 'text', $this->item->agegroup_id
					);
					?>
                    <br>  
<?php

$this->switcher_onchange = ' onchange="document.getElementById(\'cb' . $this->count_i . '\').checked=true"';
$this->switcher_options = array(
						HTMLHelper::_('select.option', '0', Text::_('JNO')),
						HTMLHelper::_('select.option', '1', Text::_('JYES'))
					);
                    
$this->switcher_value = $this->item->fast_projektteam;    
$this->switcher_name = 'fast_projektteam' . $this->item->id;   
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
                    
					?>    			
                </td>

                <td class="center">
					<?php
					$inputappend = '';
					$append      = ' style="background-color:#bbffff"';
					echo HTMLHelper::_(
						'select.genericlist',
						$this->lists['project_type'],
						'project_type' . $this->item->id,
						$inputappend . 'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
						$this->count_i . '\').checked=true"' . $append,
						'value', 'text', $this->item->project_type
					);
					?>
                    <br>
					<?php
					echo HTMLHelper::_(
						'select.genericlist',
						$this->lists['mastertemplates'],
						'master_template' . $this->item->id,
						$inputappend . 'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
						$this->count_i . '\').checked=true"' . $append,
						'value', 'text', $this->item->master_template
					);
					?>
                </td>
                <td class="center">
					<?php
					if (empty($this->item->picture) || !File::exists(JPATH_SITE . DIRECTORY_SEPARATOR . $this->item->picture))
					{
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE') . $this->item->picture;
$image_attributes['title'] = $imageTitle;						
echo HTMLHelper::_('image','administrator/components/com_sportsmanagement/assets/images/delete.png',$imageTitle,$image_attributes);
					}
                    elseif ($this->item->picture == sportsmanagementHelper::getDefaultPlaceholder("player"))
					{
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_DEFAULT_IMAGE');
$image_attributes['title'] = $imageTitle;			    
echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/information.png',$imageTitle, $image_attributes);
					}
					else
					{
echo sportsmanagementHelper::getBootstrapModalImage('collapseModallogo_picture' . $this->item->id, Uri::root() . $this->item->picture, $this->item->name, '20', Uri::root() . $this->item->picture);					   
						?>

						<?PHP
					}
					?>
                </td>
                <td class="center">
					<?php if ($this->item->current_round) : ?>
						<?php echo HTMLHelper::link(
							'index.php?option=com_sportsmanagement&view=matches&pid=' . $this->item->id . '&rid=' . $this->item->current_round,
							HTMLHelper::_('image',Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/icon-16-Matchdays.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_GAMES_DETAILS'))
						); ?>
					<?php endif; ?>
                </td>
                <td class="center">
                    <a href="<?php echo $link2teams; ?>"><?php echo $this->item->proteams; ?></a>
                    <br/>
					<?php echo $this->item->notassign; ?>
                </td>
                <td class="center">
                    <a href="<?php echo $link2rounds; ?>"><?php echo $this->modelround->getRoundsCount($this->item->id); ?></a>
                    <br>
					<?php echo $this->modelmatches->getMatchesCount($this->item->id); ?>
                </td>
                <td class="center">
                    <a href="<?php echo $link2divisions; ?>"><?php echo $this->modeldivision->getProjectDivisionsCount($this->item->id); ?></a>
                </td>
                <td class="center">
					<?php
					echo $this->item->user_field;
					$teile = explode("<br>", $this->item->user_field);
					for ($a = 0; $a < sizeof($teile); $a++)
					{
						echo HTMLHelper::link(
								'index.php?option=com_sportsmanagement&view=' . $teile[$a] . '&pid=' . $this->item->id,
								HTMLHelper::_('image',Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/information.png', Text::_($teile[$a]))
							) . '<br>';
					}
					if ($this->state->get('filter.userfields'))
					{
						?>
                        <input type="text" size="100" class="inputbox" name="user_field<?php echo $this->item->id; ?>"
                               value="<?php echo $this->item->user_fieldvalue; ?>"
                               onchange="document.getElementById('cb<?php echo $this->count_i; ?>').checked=true"/>
                        <input type="text" size="10" class="inputbox" name="user_field_id<?php echo $this->item->id; ?>"
                               value="<?php echo $this->item->user_field_id; ?>">
						<?PHP
					}
					?>
                </td>
                <td class="center">
                    <div class="btn-group">
						<?php echo HTMLHelper::_('jgrid.published', $this->item->published, $this->count_i, 'projects.', $canChange, 'cb'); ?>
						<?php
						/** Create dropdown items and render the dropdown list. */
						if ($canChange)
						{
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === 2 ? 'un' : '') . 'archive', 'cb' . $this->count_i, 'projects');
							HTMLHelper::_('actionsdropdown.' . ((int) $this->item->published === -2 ? 'un' : '') . 'trash', 'cb' . $this->count_i, 'projects');
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
                <td class="center"><?php echo $this->item->modified; ?></td>
                <td class="center"><?php echo $this->item->username; ?></td>
            </tr>
			<?php
		
		}
		?>
        </tbody>
    </table>
</div>
