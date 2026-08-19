<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage allprojects
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.keepalive');
$this->getDocument()->getWebAssetManager()->useScript('jquery');

$templatesToLoad = ['globalviews'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<script>
    function tableOrdering(order, dir, task) {
        const form = document.adminForm;
        form.filter_order.value = order;
        form.filter_order_Dir.value = dir;
        form.submit();
    }
</script>
<?php if ($this->params->get('show_page_heading', 1)) : ?>
    <h1 class="componentheading">
        <?php echo $this->escape($this->params->get('page_title')); ?>
    </h1>
<?php endif; ?>
<div class="<?php echo $this->divclasscontainer; ?>" id="allprojects">
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

                <?php echo $this->lists['nation2'] . '&nbsp;&nbsp;'; ?>
                <?php echo $this->lists['leagues'] . '&nbsp;&nbsp;'; ?>
                <?php echo $this->lists['seasons'] . '&nbsp;&nbsp;'; ?>
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
        echo $this->loadTemplate('items');
        echo $this->loadTemplate('jsminfo');
        ?>
    </form>
</div>
