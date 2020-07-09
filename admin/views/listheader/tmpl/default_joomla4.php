<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage listheader
 * @file       default_joomla4.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Layout\LayoutHelper;

HTMLHelper::_('behavior.multiselect');

$app = Factory::getApplication();
$jinput = $app->input;
$option = $jinput->getCmd('option');
$view = $jinput->getCmd('view', 'cpanel');

/**
 * retrieve the value of the state variable. If no value is specified,
 * the specified default value will be returned.
 * function syntax is getUserState( $key, $default );
 */

$project_id = $app->getUserState("$option.pid", '0');

?>

<div class="row">
<div id="j-sidebar-container" class="col-md-2">
<?php echo $this->sidebar; ?>
</div>
<div class="col-md-10">
<div id="j-main-container" class="j-main-container">
<?php

if ($this->jsmmessage)
{
	echo $this->loadTemplate('info_message');
}
?>
<!-- <div id="filter-bar" class="btn-toolbar"> -->
<?php
switch ($view)
{
case 'clubs':
case 'playgrounds':
case 'positions':	
case 'players':		
case 'projects':		
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
<?php
break;
}

?>
	    
<?PHP
?>
<!-- </div> -->







