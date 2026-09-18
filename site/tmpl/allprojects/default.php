<?php
/**
 * SportsManagement all projects template for Joomla 5/6.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$assets = $this->getDocument()->getWebAssetManager();
$assets->useScript('keepalive');
$assets->registerAndUseScript(
    'com_sportsmanagement.site.allprojects',
    'components/com_sportsmanagement/assets/js/allprojects.js',
    ['version' => 'auto'],
    ['defer' => true],
    ['core']
);
?>

<?php if ($this->params->get('show_page_heading', 1)) : ?>
    <h1 class="componentheading">
        <?php echo $this->escape($this->params->get('page_title')); ?>
    </h1>
<?php endif; ?>
<div class="<?php echo $this->escape($this->divclasscontainer); ?>" id="allprojects">
    <form name="adminForm" id="adminForm" data-jsm-allprojects-form
          action="<?php echo htmlspecialchars($this->uri->toString(), ENT_QUOTES, 'UTF-8'); ?>"
          method="post">
        <fieldset class="filters">
            <legend class="hidelabeltxt"><?php echo Text::_('JGLOBAL_FILTER_LABEL'); ?></legend>
            <div class="filter-search">
                <input type="text" name="filter_search" id="filter_search"
                       value="<?php echo $this->escape($this->filter); ?>" class="form-control w-auto"
                       data-jsm-auto-submit>

                <button type="submit" class="btn">
                    <span class="icon-search" aria-hidden="true"></span><?php echo Text::_('JGLOBAL_FILTER_BUTTON'); ?>
                </button>
                <button type="button" class="btn btn-outline-secondary" data-jsm-clear-filter>
                    <span class="icon-remove" aria-hidden="true"></span><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
                </button>

                <?php echo $this->lists['nation2'] . '&nbsp;&nbsp;'; ?>
                <?php echo $this->lists['leagues'] . '&nbsp;&nbsp;'; ?>
                <?php echo $this->lists['seasons'] . '&nbsp;&nbsp;'; ?>
            </div>

            <input type="hidden" name="filter_order" value="<?php echo $this->escape($this->sortColumn); ?>">
            <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->sortDirection); ?>">
            <input type="hidden" name="limitstart" value="">

            <div class="display-limit">
                <?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
                <?php echo $this->pagination->getLimitBox(); ?>
            </div>
        </fieldset>

        <?php echo $this->loadTemplate('items'); ?>
        <?php echo $this->loadTemplate('jsminfo'); ?>
    </form>
</div>
