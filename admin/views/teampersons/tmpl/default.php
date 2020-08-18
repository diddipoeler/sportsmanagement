<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teampersons
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

if ($this->restartpage)
{
	echo '<meta http-equiv="refresh" content="1; URL=' . $this->request_url . '">';
}

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

$ordering = ($this->sortColumn == 'ppl.ordering');

/** Welche joomla version */
if (version_compare(substr(JVERSION, 0, 5), '4.0.0', 'ge'))
{
}	
elseif (version_compare(substr(JVERSION, 0, 5), '3.0.0', 'ge'))
{
	HTMLHelper::_('behavior.framework', true);
}
else
{
	HTMLHelper::_('behavior.mootools');
}


?>
    <style>
        .search-item {
            font: normal 11px tahoma, arial, helvetica, sans-serif;
            padding: 3px 10px 3px 10px;
            border: 1px solid #fff;
            border-bottom: 1px solid #eeeeee;
            white-space: normal;
            color: #555;
        }

        .search-item h3 {
            display: block;
            font: inherit;
            font-weight: bold;
            color: #222;
        }

        .search-item h3 span {
            float: right;
            font-weight: normal;
            margin: 0 0 5px 5px;
            width: 100px;
            display: block;
            clear: none;
        }
    </style>

    <script>
        var quickaddsearchurl = '<?php echo Uri::root();?>administrator/index.php?option=com_sportsmanagement&task=quickadd.searchplayer&projectteam_id=<?php echo $this->teamws->id; ?>';

        function searchPlayer(val) {
            var s = document.getElementById("filter_search");
            s.value = val;
            Joomla.submitform('', this.form)
        }
    </script>


    <form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
        <!--	<fieldset class="adminform"> -->

		<?PHP
		echo $this->loadTemplate('joomla_version');

		?>

        <!--	</fieldset> -->
        <input type="hidden" name="project_team_id" value="<?php echo $this->project_team_id; ?>"/>
        <input type="hidden" name="team_id" value="<?php echo $this->team_id; ?>"/>
	<input type="hidden" name="season_team_id" value="<?php echo $this->season_team_id; ?>"/>
        <input type="hidden" name="season_id" value="<?php echo $this->season_id; ?>"/>
        <input type="hidden" name="pid" value="<?php echo $this->project_id; ?>"/>
        <input type="hidden" name="persontype" value="<?php echo $this->_persontype; ?>"/>
        <input type="hidden" name="search_mode" value="<?php echo $this->lists['search_mode']; ?>" id="search_mode"/>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <input type="hidden" name="filter_order" value="<?php echo $this->sortColumn; ?>"/>
        <input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>"/>
		<?php echo HTMLHelper::_('form.token'); ?>
    </form>
<?PHP
echo "<div>";
echo $this->loadTemplate('footer');
echo "</div>";
