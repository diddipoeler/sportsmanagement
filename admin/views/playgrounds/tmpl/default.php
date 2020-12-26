<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage playgrounds
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;

// Ordering allowed ?
$ordering = ($this->sortColumn == 'v.ordering');


$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
    <script>

        function searchPlayground(val, key) {
            var f = $('adminForm');
            if (f) {
                f.elements['filter_search'].value = val;

                f.submit();
            }
        }

    </script>
    <form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">

		<?PHP
		echo $this->loadTemplate('joomla_version');
		?>
        <input type="hidden" name="search_mode" value="<?php echo $this->lists['search_mode']; ?>"/>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>"/>
		<?php echo HTMLHelper::_('form.token') . "\n"; ?>
    </form>
<div>
<?PHP
echo $this->loadTemplate('footer');
?>
</div>
