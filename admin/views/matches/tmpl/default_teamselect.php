<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matches
 * @file       default_teamselect.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;

?>
<form name="projectteamForm" id="projectteamForm" method="post">
<?php
if (array_key_exists('projectteams', $this->lists)) {
echo "" . HTMLHelper::_('select.genericlist', $this->lists['projectteams'], 'projectteam', 'class="inputbox" size="1" onchange="this.form.submit();" ', 'value', 'text', $this->projectteamsel) . "";
}
?>
</form>
