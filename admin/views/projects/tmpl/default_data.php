<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
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

$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>
<div class="table-responsive">
<table class="<?php echo $this->table_data_class; ?>">
<thead>
<tr>
<th width=""><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
<th width="40" class="">
<input  type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this);" />
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
<tbody>
<?php
$k=0;
for ($i=0,$n=count($this->items); $i < $n; $i++)
{
    $row =& $this->items[$i];

    $link = Route::_('index.php?option=com_sportsmanagement&task=project.edit&id='.$row->id);
    $link2 = Route::_('index.php?option=com_sportsmanagement&view=projects&task=project.display&id='.$row->id);
    $link2panel = Route::_('index.php?option=com_sportsmanagement&task=project.edit&layout=panel&pid='.$row->id.'&stid='.$row->sports_type_id.'&id='.$row->id);
    $link2teams = Route::_('index.php?option=com_sportsmanagement&view=projectteams&pid='.$row->id.'&id='.$row->id);
                  
    $link2rounds = Route::_('index.php?option=com_sportsmanagement&view=rounds&pid='.$row->id);
    $link2divisions = Route::_('index.php?option=com_sportsmanagement&view=divisions&pid='.$row->id);
                  
    $canEdit = $this->user->authorise('core.edit', 'com_sportsmanagement');
    $canCheckin = $this->user->authorise('core.manage', 'com_checkin') || $row->checked_out == $this->user->get('id') || $row->checked_out == 0;

    $checked = HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'projects.', $canCheckin);
    $canChange = $this->user->authorise('core.edit.state', 'com_sportsmanagement.project.' . $row->id) && $canCheckin;
?>
<tr class="<?php echo "row$k"; ?>">
<td class="center"><?php echo $this->pagination->getRowOffset($i); ?></td>
<td width="40" class=""><?php echo HTMLHelper::_('grid.id', $i, $row->id); ?>
<?php
$inputappend = '';
if (( $row->checked_out != $this->user->get('id') ) && $row->checked_out ) : ?>
<?php echo HTMLHelper::_('jgrid.checkedout', $i, $this->user->get('id'), $row->checked_out_time, 'projects.', $canCheckin); ?>
<?php else: ?>
<a href="<?php echo $link; ?>">
<?php
$imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_EDIT_DETAILS');
echo HTMLHelper::_(
    'image', 'administrator/components/com_sportsmanagement/assets/images/edit.png',
    $imageTitle, 'title= "'.$imageTitle.'"'
);
?>
</a>
</br>	
<?php
endif;
$pcture_link = 'index.php?option=com_media&view=images&tmpl=component&asset=com_sportsmanagement&author=&folder=com_sportsmanagement/database/projectimages/' . $row->id;                  
echo sportsmanagementHelper::getBootstrapModalImage('projectimages' . $row->id, Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/link.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_MATCHES_EDIT_MATCHPICTURE'), '20', Uri::base() . $pcture_link, $this->modalwidth, $this->modalheight);                  
?>
</td>
                          
<td>
<?php

?>
<a href="<?php echo $link2panel; ?>"><?php echo $row->name; ?></a>
<?php
?>
<p class="smallsub">
<?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias));?></p>
</td>
<td>
<?php
if ($this->state->get('filter.search_nation') ) {
    $append = ' style="background-color:#bbffff"';
    echo HTMLHelper::_(
        'select.genericlist',
        $this->league,
        'league'.$row->id,
        $inputappend.'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
        $i.'\').checked=true"'.$append,
        'id', 'name', $row->league_id
    );
}                      
else
{
    echo $row->league.'<br>';
}
?>
</td>
<td class="center"><?php echo JSMCountries::getCountryFlag($row->country); ?></td>
<td class="center"><?php echo $row->season; ?>
    <br>
    <?php
    $picture = $this->model->existcurrentseason($this->season_ids, $row->league_id) ? 'ok.png' : 'error.png';
    echo HTMLHelper::_('image', 'administrator/components/com_sportsmanagement/assets/images/'.$picture, '', 'title= "' . '' . '"');  
    ?>
    </td>
<td class="center">
<?php
echo Text::_($row->sportstype);
?>
</td>
                      
<td class="center">
<?php
$inputappend = '';
$append = ' style="background-color:#bbffff"';
echo HTMLHelper::_(
    'select.genericlist',
    $this->lists['agegroup'],
    'agegroup'.$row->id,
    $inputappend.'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
    $i.'\').checked=true"'.$append,
    'value', 'text', $row->agegroup_id
);
?>
</td>
  
<td class="center">
<?php
$inputappend = '';
$append = ' style="background-color:#bbffff"';
echo HTMLHelper::_(
    'select.genericlist',
    $this->lists['project_type'],
    'project_type'.$row->id,
    $inputappend.'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
    $i.'\').checked=true"'.$append,
    'value', 'text', $row->project_type
);
?>
<br>
<?php   
echo HTMLHelper::_(
    'select.genericlist',
    $this->lists['mastertemplates'],
    'master_template'.$row->id,
    $inputappend.'class="form-control form-control-inline" size="1" onchange="document.getElementById(\'cb' .
    $i.'\').checked=true"'.$append,
    'value', 'text', $row->master_template
);
?>  
</td>
<td class="center">
<?php
if (empty($row->picture) || !File::exists(JPATH_SITE.DIRECTORY_SEPARATOR.$row->picture)) {
    $imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_NO_IMAGE').$row->picture;
    echo HTMLHelper::_(
        'image', 'administrator/components/com_sportsmanagement/assets/images/delete.png',
        $imageTitle, 'title= "'.$imageTitle.'"'
    );
}
elseif ($row->picture == sportsmanagementHelper::getDefaultPlaceholder("player")) {
    $imageTitle = Text::_('COM_SPORTSMANAGEMENT_ADMIN_PERSONS_DEFAULT_IMAGE');
    echo HTMLHelper::_(
        'image', 'administrator/components/com_sportsmanagement/assets/images/information.png',
        $imageTitle, 'title= "'.$imageTitle.'"'
    );
}
else
{
?>                                  
<a href="<?php echo Uri::root().$row->picture;?>" title="<?php echo $row->name;?>" class="modal">
<img src="<?php echo Uri::root().$row->picture;?>" alt="<?php echo $row->name;?>" width="20" />
</a>
<?PHP
}
?>
</td>
<td class="center">
<?php if ($row->current_round) : ?>
<?php echo HTMLHelper::link(
    'index.php?option=com_sportsmanagement&view=matches&pid='.$row->id.'&rid='. $row->current_round,
    HTMLHelper::image(Uri::root().'administrator/components/com_sportsmanagement/assets/images/icon-16-Matchdays.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTS_GAMES_DETAILS'))
); ?>
<?php endif; ?>
</td>
<td class="center">
<a href="<?php echo $link2teams; ?>"><?php echo $row->proteams; ?></a>
<br />
<?php echo $row->notassign; ?>
</td>
<td class="center">
<a href="<?php echo $link2rounds; ?>"><?php echo $this->modelround->getRoundsCount($row->id); ?></a>
<br>
<?php echo $this->modelmatches->getMatchesCount($row->id); ?>
</td>
<td class="center">
<a href="<?php echo $link2divisions; ?>"><?php echo $this->modeldivision->getProjectDivisionsCount($row->id); ?></a>
</td>
<td class="center">
<?php
echo $row->user_field;
$teile = explode("<br>", $row->user_field);
for ($a=0; $a < sizeof($teile); $a++ )
{
    echo HTMLHelper::link(
        'index.php?option=com_sportsmanagement&view='. $teile[$a].'&pid='.$row->id,
        HTMLHelper::image(Uri::root().'administrator/components/com_sportsmanagement/assets/images/information.png', Text::_($teile[$a]))
    ).'<br>';
}
if ($this->state->get('filter.userfields') ) {
?>
<input type="text" size="100" class="inputbox" name="user_field<?php echo $row->id; ?>" value="<?php echo $row->user_fieldvalue; ?>"
onchange="document.getElementById('cb<?php echo $i; ?>').checked=true" />                      
<input type="text" size="10" class="inputbox" name="user_field_id<?php echo $row->id; ?>" value="<?php echo $row->user_field_id; ?>" >
<?PHP
}
?>
</td>
<td class="center">
<div class="btn-group">
<?php echo HTMLHelper::_('jgrid.published', $row->published, $i, 'projects.', $canChange, 'cb'); ?>
<?php
// Create dropdown items and render the dropdown list.
if ($canChange) {
    HTMLHelper::_('actionsdropdown.' . ((int) $row->published === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'projects');
    HTMLHelper::_('actionsdropdown.' . ((int) $row->published === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'projects');
    echo HTMLHelper::_('actionsdropdown.render', $this->escape($row->name));
}
?>
</div>
</td>
<td class="order">
<span><?php echo $this->pagination->orderUpIcon($i, $i > 0, 'projects.orderup', 'JLIB_HTML_MOVE_UP', true); ?></span>
<span><?php echo $this->pagination->orderDownIcon($i, $n, $i < $n, 'projects.orderdown', 'JLIB_HTML_MOVE_DOWN', true); ?></span>
<?php $disabled=true ?  '' : 'disabled="disabled"';    ?>
<input type="text" name="order[]" size="5" value="<?php echo $row->ordering;?>" <?php echo $disabled ?> class="form-control form-control-inline" style="text-align: center" />
</td>
<td class="center"><?php echo $row->id; ?></td>
<td class="center"><?php echo $row->modified; ?></td>
<td class="center"><?php echo $row->username; ?></td>
</tr>
<?php
$k=1 - $k;
}
?>
</tbody>
</table>
</div>
