<?php
/**
 * SportsManagement all clubs template for Joomla 5/6.
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
    'com_sportsmanagement.site.allclubs',
    'components/com_sportsmanagement/assets/js/allclubs.js',
    ['version' => 'auto'],
    ['defer' => true],
    ['core']
);
?>
<div class="container-fluid">
    <form
        name="adminForm"
        id="adminForm"
        data-jsm-allclubs-form
        action="<?php echo htmlspecialchars($this->uri->toString(), ENT_QUOTES, 'UTF-8'); ?>"
        method="post"
    >
        <fieldset class="filters">
            <legend class="visually-hidden"><?php echo Text::_('JGLOBAL_FILTER_LABEL'); ?></legend>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <input
                    type="text"
                    name="filter_search"
                    id="filter_search"
                    value="<?php echo $this->escape($this->filter); ?>"
                    class="form-control w-auto"
                    data-jsm-auto-submit
                >
                <button type="submit" class="btn btn-primary" title="<?php echo $this->escape(Text::_('JGLOBAL_FILTER_BUTTON')); ?>">
                    <span class="icon-search" aria-hidden="true"></span>
                    <?php echo Text::_('JGLOBAL_FILTER_BUTTON'); ?>
                </button>
                <button type="button" class="btn btn-outline-secondary" data-jsm-clear-filter>
                    <span class="icon-remove" aria-hidden="true"></span>
                    <?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?>
                </button>

                <?php echo $this->lists['nation2'] . '&nbsp;&nbsp;'; ?>
                <?php
                $startRange = (int) $this->params->get('character_filter_start_hex', 0);
                $endRange = (int) $this->params->get('character_filter_end_hex', 0);

                for ($i = $startRange; $i <= $endRange; $i++) {
                    $character = html_entity_decode('&#' . $i . ';', ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    printf(
                        '<button type="button" class="btn btn-sm btn-link p-0 me-2" data-character-filter="%s">%s</button>',
                        $this->escape($character),
                        $this->escape($character)
                    );
                }
                ?>
            </div>
            <input type="hidden" name="filter_order" value="<?php echo $this->escape($this->sortColumn); ?>">
            <input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->sortDirection); ?>">
            <input type="hidden" name="limitstart" value="">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span><?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?></span>
                <?php echo $this->pagination->getLimitBox(); ?>
            </div>
        </fieldset>
        <?php
        echo $this->loadTemplate('items');
        echo $this->loadTemplate('jsminfo');
        ?>
    </form>
</div>
