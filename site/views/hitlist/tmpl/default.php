<?php
/**
 * SportsManagement hit list layout for Joomla 5/6.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.keepalive');

$templatesToLoad = ['globalviews'];
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
$uri = Uri::getInstance();
?>
<script>
    function tableOrdering(order, dir, task) {
        const form = document.adminForm;
        form.filter_order.value = order;
        form.filter_order_Dir.value = dir;
        Joomla.submitform(task || '', form);
    }

    function searchPerson(val) {
        const search = document.getElementById('filter_search');
        if (search) {
            search.value = val;
        }
        Joomla.submitform('', document.adminForm);
    }
</script>
<div class="row">
    <form name="adminForm" id="adminForm" action="<?php echo htmlspecialchars($uri->toString(), ENT_QUOTES, 'UTF-8'); ?>" method="post">
        <?php
        echo $this->loadTemplate('items');
        echo $this->loadTemplate('jsminfo');
        ?>
    </form>
</div>
