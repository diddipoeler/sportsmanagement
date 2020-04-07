<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 *
 * @version    1.0.05
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage projectteams
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
jimport('joomla.filesystem.file');


$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<?PHP
// Welche joomla version
if (version_compare(JVERSION, '3.0.0', 'ge'))
{
	echo $this->loadTemplate('joomla3');
}
else
{
	echo $this->loadTemplate('joomla2');
}
?>
<div>
<?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_PROJECTTEAMS_QUICKADD_DESCR'); ?>
</div>
<div>
<script type="text/javascript">
var leaguepicture = new Array;
<?php
foreach ($this->projectsbyleagueseason as $key => $value)
{
	echo 'leaguepicture[' . ($value->value) . ']=\'' . $value->picture . "';\n";
}
?>
  
var teampicture = new Array;
<?php
foreach ($this->lists['country_teams_picture'] as $key => $value)
{
	if (!$value)
	{
		$value = sportsmanagementHelper::getDefaultPlaceholder("clublogobig");
	}

	echo 'teampicture[' . ($key) . ']=\'' . $value . "';\n";
}
?>
</script>
<?PHP
// Some CSS
$this->document->addStyleDeclaration(
	'
img.item {
    padding-right: 10px;
    vertical-align: middle;
}
img.car {
    height: 25px;
}'
);

// String $opt - second parameter of formbehavior2::select2
// for details http://ivaynberg.github.io/select2/
$opt = ' allowClear: true,
   width: "50%",

   formatResult: function format(state)
   {
   var originalOption = state.element;
   var picture;
   picture = teampicture[state.id];
   if (!state.id)
   return state.text;
   return "<img class=\'item car\' src=\'' . Uri::root() . '" + picture + "\' />" + state.text;
   },
 
   escapeMarkup: function(m) { return m; }
';

$optproject = ' allowClear: true,
   width: "100%",

   formatResult: function format(state)
   {
   var originalOption2 = state.element;
   var picture2;
   picture2 = leaguepicture[state.id];
   if (!state.id)
   return state.text;
   return "<img class=\'item car\' src=\'' . Uri::root() . '" + picture2 + "\' />" + state.text;
   },
   escapeMarkup: function(m1) { return m1; }
';

$append = '';
HTMLHelper::_('formbehavior2.select2', '.test1', $opt);

if (isset($this->lists['country_teams']))
{
	echo HTMLHelper::_(
		'select.genericlist', $this->lists['country_teams'], 'team_id',
		'style="width:225px;" class="test1" size="6"' . $append, 'value', 'text', 0
	);

?>
<input class="btn" type="submit" name="addteam" id="addteam" value="<?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_ADD');?>" />
<?php
}


if (ComponentHelper::getParams($this->jinput->getCmd('option'))->get('show_option_projectteam_change', ''))
{
	HTMLHelper::_('formbehavior2.select2', '.optproject', $optproject);
	echo HTMLHelper::_(
		'select.genericlist', $this->projectsbyleagueseason, 'all_project_id',
		'style="width:225px;" class="optproject" size="1" ' . '', 'value', 'text', $this->project_id
	);
}


?>
</div>
<?PHP
if ($this->project_art_id != 3)
{
	if ($this->projectteam)
	{
		// Ordering allowed ?
		$ordering = ($this->sortColumn == 't.name');
		echo $this->loadTemplate('teams');
	}
	else
	{
		echo '<div class="alert alert-no-items">';
		echo Text::_('JGLOBAL_NO_MATCHING_RESULTS');
		echo '</div>';
	}
}
else
{
	if ($this->projectteam)
	{
		// Ordering allowed ?
		$ordering = ($this->sortColumn == 't.lastname');
		echo $this->loadTemplate('persons');
	}
	else
	{
		echo '<div class="alert alert-no-items">';
		echo Text::_('JGLOBAL_NO_MATCHING_RESULTS');
		echo '</div>';
	}
}

?>
<input type="hidden" name="task" value="" />
<input type="hidden" name="pid" value="<?php echo $this->project_id; ?>" />
<input type="hidden" name="season_id" value="<?php echo $this->project->season_id; ?>" />
<input type="hidden" name="boxchecked" value="0" />
<input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
<input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>" />
<input type="hidden" name="search_mode" value="<?php echo $this->lists['search_mode']; ?>" />
<?php echo HTMLHelper::_('form.token') . "\n"; ?>
</form>
<div>
<?PHP
echo $this->loadTemplate('footer');
?> 
</div>	
