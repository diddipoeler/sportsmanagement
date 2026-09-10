<?php
/**
 * SportsManagement all playgrounds template for Joomla 5/6.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

$this->getDocument()->getWebAssetManager()->useScript('keepalive');

$startRange = (int) $this->params->get('character_filter_start_hex', 0);
$endRange = (int) $this->params->get('character_filter_end_hex', 0);
?>
<script>
window.tableOrdering = function (order, dir) {
    const form = document.getElementById('adminForm');

    if (!form) {
        return;
    }

    form.filter_order.value = order;
    form.filter_order_Dir.value = dir;
    form.submit();
};

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('adminForm');
    const search = document.getElementById('filter_search');
    const clearButton = document.getElementById('clear-playground-search');

    if (!form || !search) {
        return;
    }

    search.addEventListener('change', function () {
        form.submit();
    });

    if (clearButton) {
        clearButton.addEventListener('click', function () {
            search.value = '';
            form.submit();
        });
    }

    form.querySelectorAll('[data-character-filter]').forEach(function (button) {
        button.addEventListener('click', function () {
            search.value = button.dataset.characterFilter || '';
            form.submit();
        });
    });
});
</script>
<div class="<?php echo $this->escape($this->divclasscontainer); ?>" id="allplaygrounds">
    <form name="adminForm" id="adminForm"
          action="<?php echo htmlspecialchars($this->uri->toString(), ENT_QUOTES, 'UTF-8'); ?>"
          method="post">
        <fieldset class="filters">
            <legend class="hidelabeltxt"><?php echo Text::_('JGLOBAL_FILTER_LABEL'); ?></legend>
            <div class="filter-search">
                <input type="text" name="filter_search" id="filter_search"
                       value="<?php echo $this->escape($this->filter); ?>" class="inputbox">
                <button type="submit" class="btn">
                    <span class="icon-search" aria-hidden="true"></span><?php echo Text::_('JGLOBAL_FILTER_BUTTON'); ?>
                </button>
                <button type="button" class="btn" id="clear-playground-search">
                    <span class="icon-remove" aria-hidden="true"></span><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
                </button>

                <?php echo $this->lists['nation2'] . '&nbsp;&nbsp;'; ?>
                <?php for ($i = $startRange; $i <= $endRange; $i++) : ?>
                    <?php $character = html_entity_decode('&#' . $i . ';', ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>
                    <button type="button"
                            class="btn btn-sm btn-link p-0 me-2"
                            data-character-filter="<?php echo $this->escape($character); ?>">
                        <?php echo $this->escape($character); ?>
                    </button>
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
