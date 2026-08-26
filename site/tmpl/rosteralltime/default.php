<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage rosteralltime
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.keepalive');

$templatesToLoad = ['globalviews'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

echo $this->loadTemplate('jsm_warnings');
echo $this->loadTemplate('jsm_tips');
echo $this->loadTemplate('jsm_notes');

$startRange = (int) $this->params->get('character_filter_start_hex', 0);
$endRange = (int) $this->params->get('character_filter_end_hex', 0);
?>
<script>
    function tableOrdering(order, dir, task) {
        const form = document.adminForm;
        form.filter_order.value = order;
        form.filter_order_Dir.value = dir;
        form.submit();
    }

    function searchPerson(value) {
        const search = document.getElementById('filter_search');
        search.value = value;
        document.getElementById('adminForm').submit();
    }
</script>
<div class="<?php echo $this->divclasscontainer; ?>" id="rosteralltime">
    <form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars($this->uri->toString()); ?>" method="post">
        <fieldset class="filters">
            <legend class="hidelabeltxt"><?php echo Text::_('JGLOBAL_FILTER_LABEL'); ?></legend>
            <div class="filter-search">
                <input type="text" name="filter_search" id="filter_search"
                       value="<?php echo $this->escape($this->filter); ?>" class="inputbox"
                       onchange="document.getElementById('adminForm').submit();"/>
                <button type="submit" class="btn" title=""><i class="icon-search"></i><?php echo Text::_('JGLOBAL_FILTER_BUTTON'); ?></button>
                <button type="button" class="btn" title=""
                        onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i><?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>

                <?php if ($startRange > 0 && $endRange >= $startRange) : ?>
                    <?php for ($i = $startRange; $i <= $endRange; $i++) : ?>
                        <a href="javascript:searchPerson('<?php echo '&#' . $i . ';'; ?>')"><?php echo '&#' . $i . ';'; ?></a>&nbsp;&nbsp;&nbsp;&nbsp;
                    <?php endfor; ?>
                <?php endif; ?>
            </div>

            <input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>"/>
            <input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>"/>
            <input type="hidden" name="limitstart" value=""/>

            <div class="display-limit">
                <?php echo Text::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
                <?php echo $this->pagination->getLimitBox(); ?>
            </div>
        </fieldset>

        <?php
        echo $this->loadTemplate($this->config['show_rosteralllayout']);
        echo $this->loadTemplate('jsminfo');
        ?>
    </form>
</div>
