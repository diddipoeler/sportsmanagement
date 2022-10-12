<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teams
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
    <script>

        function searchTeam(val, key) {
            var f = $('adminForm');
            if (f) {
                f.elements['search'].value = val;

                f.submit();
            }
        }

    </script>
    <form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">

		<?PHP
		if ($this->assign)
		{
			?>
            <button type="button" onclick="Joomla.submitform('seasons.applyteams', this.form);">
				<?php echo Text::_('JAPPLY'); ?></button>
            <input type="text" name="filter_search" id="filter_search"
                   value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
                   class="text_area" onchange="$('adminForm').submit(); "/>

            <button onclick="this.form.submit(); "><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
            <button onclick="document.getElementById('filter_search').value='';this.form.submit(); ">
				<?php
				echo Text::_('JSEARCH_FILTER_CLEAR');
				?>
            </button>
            <?php echo $this->pagination->getLimitBox(); ?>
			<?php
			echo $this->loadTemplate('data');
		}
		else
		{
			echo $this->loadTemplate('joomla_version');
		}
		?>
        <input type="hidden" name="search_mode" value="<?php echo $this->lists['search_mode']; ?>"/>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="season_id" value="<?php echo $this->season_id; ?>"/>
        <input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>"/>
        <input type="hidden" name="club_id" value="<?php echo $this->club_id; ?>"/>
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>

<?PHP
echo $this->loadTemplate('footer');
?>

