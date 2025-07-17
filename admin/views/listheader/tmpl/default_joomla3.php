<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage listheader
 * @file       default_joomla3.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Layout\LayoutHelper;

$app    = Factory::getApplication();
$jinput = $app->input;
$option = $jinput->getCmd('option');
$view   = $jinput->getCmd('view', 'cpanel');

/**
 * retrieve the value of the state variable. If no value is specified,
 * the specified default value will be returned.
 * function syntax is getUserState( $key, $default );
 */
$project_id = $app->getUserState("$option.pid", '0');


$buttons = array(
	array(
		'link'   => Route::_('index.php?option=com_sportsmanagement&view=sportstypes'),
		'image'  => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'icon'   => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'text'   => Text::_('COM_SPORTSMANAGEMENT_D_MENU_SPORTSTYPES'),
		'access' => array('core.manage', 'com_sportsmanagement'),
		'group'  => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA'
	),
	array(
		'link'   => Route::_('index.php?option=com_sportsmanagement&view=seasons'),
		'image'  => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'icon'   => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'text'   => Text::_('COM_SPORTSMANAGEMENT_D_MENU_SEASONS'),
		'access' => array('core.manage', 'com_sportsmanagement'),
		'group'  => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA'
	),
	array(
		'link'   => Route::_('index.php?option=com_sportsmanagement&view=leagues'),
		'image'  => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'icon'   => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'text'   => Text::_('COM_SPORTSMANAGEMENT_D_MENU_LEAGUES'),
		'access' => array('core.manage', 'com_sportsmanagement'),
		'group'  => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA'
	),
	array(
		'link'   => Route::_('index.php?option=com_sportsmanagement&view=jlextfederations'),
		'image'  => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'icon'   => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'text'   => Text::_('COM_SPORTSMANAGEMENT_D_MENU_FEDERATIONS'),
		'access' => array('core.manage', 'com_sportsmanagement'),
		'group'  => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA'
	),
	array(
		'link'   => Route::_('index.php?option=com_sportsmanagement&view=jlextcountries'),
		'image'  => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'icon'   => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'text'   => Text::_('COM_SPORTSMANAGEMENT_D_MENU_COUNTRIES'),
		'access' => array('core.manage', 'com_sportsmanagement'),
		'group'  => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA'
	),
	array(
		'link'   => Route::_('index.php?option=com_sportsmanagement&view=jlextassociations'),
		'image'  => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'icon'   => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'text'   => Text::_('COM_SPORTSMANAGEMENT_D_MENU_ASSOCIATIONS'),
		'access' => array('core.manage', 'com_sportsmanagement'),
		'group'  => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA'
	),
	array(
		'link'   => Route::_('index.php?option=com_sportsmanagement&view=positions'),
		'image'  => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'icon'   => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'text'   => Text::_('COM_SPORTSMANAGEMENT_D_MENU_POSITIONS'),
		'access' => array('core.manage', 'com_sportsmanagement'),
		'group'  => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA'
	),
	array(
		'link'   => Route::_('index.php?option=com_sportsmanagement&view=eventtypes'),
		'image'  => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'icon'   => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'text'   => Text::_('COM_SPORTSMANAGEMENT_D_MENU_EVENTS'),
		'access' => array('core.manage', 'com_sportsmanagement'),
		'group'  => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA'
	),
	array(
		'link'   => Route::_('index.php?option=com_sportsmanagement&view=agegroups'),
		'image'  => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'icon'   => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
		'text'   => Text::_('COM_SPORTSMANAGEMENT_D_MENU_AGEGROUPS'),
		'access' => array('core.manage', 'com_sportsmanagement'),
		'group'  => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA'
	)
);


$buttonsproject = array(array('link'   => Route::_('index.php?option=com_sportsmanagement&view=project&layout=panel&id=' . $project_id),
                              'image'  => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
                              'icon'   => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
                              'text'   => Text::_('COM_SPORTSMANAGEMENT_P_SIDEBAR_PROJECT'),
                              'access' => array('core.manage', 'com_sportsmanagement'),
                              'group'  => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA_PROJECT'
),

	array('link'   => Route::_('index.php?option=com_sportsmanagement&view=templates&pid=' . $project_id),
	      'image'  => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
	      'icon'   => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
	      'text'   => Text::_('COM_SPORTSMANAGEMENT_P_SIDEBAR_TEMPLATES'),
	      'access' => array('core.manage', 'com_sportsmanagement'),
	      'group'  => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA_PROJECT'
	),
	array('link'   => Route::_('index.php?option=com_sportsmanagement&view=projectpositions&pid=' . $project_id),
	      'image'  => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
	      'icon'   => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
	      'text'   => Text::_('COM_SPORTSMANAGEMENT_P_SIDEBAR_POSITIONS'),
	      'access' => array('core.manage', 'com_sportsmanagement'),
	      'group'  => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA_PROJECT'
	),
	array('link'   => Route::_('index.php?option=com_sportsmanagement&view=projectreferees&persontype=3&pid=' . $project_id),
	      'image'  => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
	      'icon'   => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
	      'text'   => Text::_('COM_SPORTSMANAGEMENT_P_SIDEBAR_REFEREES'),
	      'access' => array('core.manage', 'com_sportsmanagement'),
	      'group'  => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA_PROJECT'
	),
	array('link'   => Route::_('index.php?option=com_sportsmanagement&view=projectteams&pid=' . $project_id),
	      'image'  => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
	      'icon'   => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
	      'text'   => Text::_('COM_SPORTSMANAGEMENT_P_SIDEBAR_TEAMS'),
	      'access' => array('core.manage', 'com_sportsmanagement'),
	      'group'  => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA_PROJECT'
	),
	array('link'   => Route::_('index.php?option=com_sportsmanagement&view=rounds&pid=' . $project_id),
	      'image'  => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
	      'icon'   => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
	      'text'   => Text::_('COM_SPORTSMANAGEMENT_P_SIDEBAR_ROUNDS'),
	      'access' => array('core.manage', 'com_sportsmanagement'),
	      'group'  => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA_PROJECT'
	),
	array('link'   => Route::_('index.php?option=com_sportsmanagement&view=divisions&pid=' . $project_id),
	      'image'  => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
	      'icon'   => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
	      'text'   => Text::_('COM_SPORTSMANAGEMENT_P_SIDEBAR_DIVISIONS'),
	      'access' => array('core.manage', 'com_sportsmanagement'),
	      'group'  => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA_PROJECT'
	),
	array('link'   => Route::_('index.php?option=com_sportsmanagement&view=treetos&pid=' . $project_id),
	      'image'  => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
	      'icon'   => 'com_sportsmanagement/assets/icons/transparent_schrift_48.png',
	      'text'   => Text::_('COM_SPORTSMANAGEMENT_P_PANEL_TREE'),
	      'access' => array('core.manage', 'com_sportsmanagement'),
	      'group'  => 'COM_SPORTSMANAGEMENT_D_HEADING_BASIS_DATA_PROJECT'
	)


);


$groupedButtons = array();

if ($project_id)
{
	foreach ($buttonsproject as $button)
	{
		$groupedButtons[$button['group']][] = $button;
	}
}
else
{
	foreach ($buttons as $button)
	{
		$groupedButtons[$button['group']][] = $button;
	}
}

$html = HTMLHelper::_('links.linksgroups', $groupedButtons);

?>
<!--
<?php if (!empty($this->sidebar)) : ?>

<div id="j-sidebar-container" class="span2">
    <div class="sidebar-nav quick-icons">
		<?php echo $html; ?>
    </div>
	<?php echo $this->sidebar; ?>

</div>
<div id="j-main-container" class="span10">
	<?php else : ?>
    <div id="j-main-container">
		<?php endif; ?>
-->
<?php
require(JPATH_COMPONENT_ADMINISTRATOR . '/views/listheader/tmpl/default_3_start_menu.php');   

switch ($view)
{
case 'agegroups':
case 'clubs':
case 'divisions':
case 'eventtypes':
case 'jlextcountries':
case 'jlextassociations':
case 'jlextfederations':
case 'leagues':
case 'playgrounds':
case 'extrafields':    		
case 'players':
case 'positions':
case 'projectreferees':
case 'projectteams':		
case 'projects':
case 'rounds':
case 'seasons':
case 'smquotes':
case 'teamplayers':				
case 'teams':		
case 'sportstypes':
case 'rosterpositions':
case 'clubnames':
echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
break;
case 'githubinstall':
case 'updates':
case 'databasetools':
case 'treetonodes':
case 'treetomatchs':
break;
default:
?>
<div class="js-stools clearfix">
    <div class="clearfix">
		<div class="filter-search btn-group pull-left">
			<label for="filter_search" class="element-invisible"><?php echo Text::_('JSEARCH_FILTER_LABEL'); ?></label>
                            <input type="text" name="filter_search" id="filter_search"
                                 placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>"
                                   value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                                   class="hasTooltip"
                                   title="<?php echo HTMLHelper::tooltipText('JGLOBAL_LOOKING_FOR'); ?>"/>
                        </div>
                        <div class="btn-group pull-left">
                            <button type="submit" class="btn hasTooltip"
                                    title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><i
                                        class="icon-search"></i></button>
                            <button type="button" class="btn hasTooltip"
                                    title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>"
                                    onclick="document.id('filter_search').value='';this.form.submit();"><i
                                        class="icon-remove"></i></button>

        </div>
		<div class="btn-group pull-right hidden-phone">
                            <label for="limit"
                                   class="element-invisible"><?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
							<?php echo $this->pagination->getLimitBox(); ?>
		</div>
	</div>
</div>
<?php
break;
}

?>
	    
<?PHP
?>

