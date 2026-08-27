<?php
/**
 * SportsManagement all playgrounds template for Joomla 5/6.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.keepalive');
$this->getDocument()->getWebAssetManager()->useScript('jquery');

$startRange = (int) $this->params->get('character_filter_start_hex', 0);
$endRange = (int) $this->params->get('character_filter_end_hex', 0);
?>
<script>
function tableOrdering(order, dir) {
    const form = document.getElementById('adminForm');
    form.filter_order.value = order;
    form.filter_order_Dir.value = dir;
    form.submit();
}

function searchPerson(value) {
    document.getElementById('filter_search').value = value;
    document.getElementById('adminForm').submit();
}

function clearPlaygroundSearch() {
    document.getElementById('filter_search').value = '';
    document.getElementById('adminForm').submit();
}
</script>
<div class="<?php echo $this->escape($this->divclasscontainer); ?>" id="allplaygrounds">
    <form name="adminForm" id="adminForm"
          action="<?php echo htmlspecialchars($this->uri->toString(), ENT_QUOTES, 'UTF-8'); ?>"
          method="post">
        <fieldset class="filters">
            <legend class="hidelabeltxt"><?php echo Text::_('JGLOBAL_FILTER_LABEL'); ?></legend>
            <div class="filter-search">
                <input type="text" name="filter_search" id="filter_search"
                       value="<?php echo $this->escape($this->filter); ?>" class="inputbox"
                       onchange="this.form.submit();">
                <button type="submit" class="btn">
                    <span class="icon-search" aria-hidden="true"></span><?php echo Text::_('JGLOBAL_FILTER_BUTTON'); ?>
                </button>
                <button type="button" class="btn" onclick="clearPlaygroundSearch();">
                    <span class="icon-remove" aria-hidden="true"></span><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
                </button>

                <?php echo $this->lists['nation2'] . '&nbsp;&nbsp;'; ?>
                <?php for ($i = $startRange; $i <= $endRange; $i++) : ?>
                    <?php $character = '&#' . $i . ';'; ?>
                    <a href="javascript:searchPerson('<?php echo $character; ?>')"><?php echo $character; ?></a>&nbsp;&nbsp;&nbsp;&nbsp;
                <?php endfor; ?>
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
