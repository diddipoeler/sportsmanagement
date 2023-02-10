<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage persons
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Filesystem\File;

$user            = Factory::getUser();
$userId          = $user->get('id');
$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
    <script>
        function searchPerson(val) {
            var s = document.getElementById("filter_search");
            s.value = val;
            Joomla.submitform('', this.form)
        }
    </script>
    <form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
		<?PHP
		if ($this->assign)
		{
			?>
            <button type="button" onclick="Joomla.submitform('seasons.applypersons', this.form);">
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
            <button type="button" onclick="Joomla.submitform('seasons.cancel', this.form)">
				<?php echo Text::_('JCANCEL'); ?></button>
                <?php echo $this->pagination->getLimitBox(); ?>

			<?php
			echo $this->loadTemplate('data');
		}
		else
		{
			echo $this->loadTemplate('joomla_version');
		}
		?>
        <input type="hidden" name="search_mode" value="<?php echo $this->lists['search_mode']; ?>" id="search_mode"/>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="season_id" value="<?php echo $this->season_id; ?>"/>
        <input type="hidden" name="project_id" value="<?php echo $this->project_id; ?>"/>
        <input type="hidden" name="team_id" value="<?php echo $this->team_id; ?>"/>
        <input type="hidden" name="persontype" value="<?php echo $this->persontype; ?>"/>
	    <input type="hidden" name="whichview" value="<?php echo $this->whichview; ?>"/>
        <input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>"/>
		<?php echo HTMLHelper::_('form.token') . "\n"; ?>
    </form>

<?PHP
echo $this->loadTemplate('footer');
?>

