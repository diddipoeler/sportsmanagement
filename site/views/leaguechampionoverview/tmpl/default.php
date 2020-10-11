<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage leaguechampionoverview
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

$templatesToLoad = array('globalviews');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
?>
<div class="<?php echo $this->divclasscontainer; ?> table-responsive" id="defaultleaguechampionoverview">
<?php
echo $this->loadTemplate('projectheading');
?>
<h1 class="componentheading">
<?php echo Text::_('Übersicht nach Mannschaft'); ?>
</h1>
<table class="table">
<thead>
<th>
<?php echo Text::_('Mannschaft'); ?>
</th>
<th>
<?php echo Text::_('Titel'); ?>
</th>
<th>
<?php echo Text::_('Saisons'); ?>
</th>
</thead>

<?php
foreach ($this->teamstotal as $this->count_i => $this->team)
{
?>
<tr>
<td>
<?php echo $this->leagueteamchampions[$this->team['team_id']]->teamname; ?>
</td>
<td>
<?php echo $this->teamseason[$this->team['team_id']]['title']; ?>
</td>
<td>
<?php echo implode(",",$this->teamseason[$this->team['team_id']]['season']); ?>
</td>
</tr>
<?php
}
?>
</table>

<?php












echo $this->loadTemplate('jsminfo');
?>
</div>
