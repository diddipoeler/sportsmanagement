<?php
/**
 * SportsManagement all clubs template for Joomla 5/6.
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.keepalive');

$templatesToLoad = ['globalviews'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<script>
function tableOrdering(order, dir) {
    const form = document.getElementById('adminForm');
    form.filter_order.value = order;
    form.filter_order_Dir.value = dir;
    form.submit();
}

function searchPerson(value) {
    const form = document.getElementById('adminForm');
    document.getElementById('filter_search').value = value;
    form.submit();
}
</script>
<div class="container-fluid">
    <form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars($this->uri->toString(), ENT_QUOTES, 'UTF-8'); ?>" method="post">
        <fieldset class="filters">
            <legend class="hidelabeltxt"><?php echo Text::_('JGLOBAL_FILTER_LABEL'); ?></legend>
            <div class="filter-search">
                <input type="text" name="filter_search" id="filter_search"
                    value="<?php echo $this->escape($this->filter); ?>" class="inputbox"
                    onchange="document.getElementById('adminForm').submit();">
                <button type="submit" class="btn" title="<?php echo $this->escape(Text::_('JGLOBAL_FILTER_BUTTON')); ?>">
                    <span class="icon-search" aria-hidden="true"></span><?php echo Text::_('JGLOBAL_FILTER_BUTTON'); ?>
                </button>
                <button type="button" class="btn"
                    onclick="document.getElementById('filter_search').value='';this.form.submit();">
                    <span class="icon-remove" aria-hidden="true"></span><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
                </button>

                <?php echo $this->lists['nation2'] . '&nbsp;&nbsp;'; ?>
                <?php
                $componentParams = ComponentHelper::getParams(Factory::getApplication()->getInput()->getCmd('option'));
                $startRange = (int) $componentParams->get('character_filter_start_hex', 0);
                $endRange = (int) $componentParams->get('character_filter_end_hex', 0);

                for ($i = $startRange; $i <= $endRange; $i++) {
                    $character = '&#' . $i . ';';
                    printf(
                        '<a href="javascript:searchPerson(\'%s\')">%s</a>&nbsp;&nbsp;&nbsp;&nbsp;',
                        $character,
                        $character
                    );
                }
                ?>
            </div>
            <input type="hidden" name="filter_order" value="<?php echo $this->escape($this->sortColumn); ?>">
            <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->sortDirection); ?>">
            <input type="hidden" name="limitstart" value="">
            <div class="display-limit">
                <?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>
                <?php echo $this->pagination->getLimitBox(); ?>
            </div>
        </fieldset>
        <?php
        echo $this->loadTemplate('items');
        echo $this->loadTemplate('jsminfo');
        ?>
    </form>
</div>
