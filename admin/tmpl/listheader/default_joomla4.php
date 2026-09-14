<?php
/**
 * Native Joomla 5/6 administrator list header layout.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage listheader
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$view = $this->jinput->getCmd('view', 'cpanel');
?>
<div class="row">
<?php
require JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/tmpl/listheader/default_4_start_menu.php';
?>

<div class="col-md-12">
<div id="j-main-container" class="j-main-container">
<?php if ($this->jsmmessage) : ?>
    <?php echo $this->loadTemplate('info_message'); ?>
<?php endif; ?>

<?php
switch ($view) {
    case 'agegroups':
    case 'clubs':
    case 'divisions':
    case 'eventtypes':
    case 'jlextcountries':
    case 'jlextassociations':
    case 'jlextfederations':
    case 'leagues':
    case 'playgrounds':
    case 'predictiongames':
    case 'predictionmembers':
    case 'predictiongroups':
    case 'predictiontemplates':
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
    case 'templates':
    case 'rosterpositions':
    case 'clubnames':
    case 'extrafields':
        echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
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
            <label for="filter_search" class="element-invisible">
                <?php echo Text::_('JSEARCH_FILTER_LABEL'); ?>
            </label>
            <input
                type="text"
                name="filter_search"
                id="filter_search"
                placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>"
                value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                class="hasTooltip"
                title="<?php echo HTMLHelper::tooltipText('JGLOBAL_LOOKING_FOR'); ?>"
            >
        </div>
        <div class="btn-group pull-left">
            <button
                type="submit"
                class="btn hasTooltip"
                title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"
            >
                <i class="icon-search"></i>
            </button>
            <button
                type="button"
                class="btn hasTooltip"
                title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>"
                onclick="const input = document.getElementById('filter_search'); if (input) { input.value = ''; } this.form.submit();"
            >
                <i class="icon-remove"></i>
            </button>
        </div>
        <div class="btn-group pull-right hidden-phone">
            <label for="limit" class="element-invisible">
                <?php echo Text::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?>
            </label>
            <?php echo $this->pagination->getLimitBox(); ?>
        </div>
        <?php
        break;
}
