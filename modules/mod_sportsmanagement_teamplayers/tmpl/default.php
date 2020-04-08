<?php
/**
*
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage mod_sportsmanagement_teamplayers
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

// check if any players returned
$items = count($list['roster']);

if (!$items) {
    echo '<p class="modjlgteamplayers">' . Text::_('NO ITEMS') . '</p>';
    return;
}?>

<div class="modjlgteamplayers"><?php if ($params->get('show_project_name', 0)) :?>
<p class="projectname"><?php echo $list['project']->name; ?></p>
<?php endif; ?>


<ul>
<h1>
<?php if ($params->get('show_team_name', 0)) :?>
    <?php echo $list['project']->team_name; ?>
<?php endif; ?></div>
</h1>
<?php foreach (array_slice($list['roster'], 0, $params->get('limit', 24)) as $items) :  ?>
    <li>
        <ul>
    <?php foreach (array_slice($items, 0, $params->get('limit', 24)) as $item) : ?>
            <li><?php
            echo modSportsmanagementTeamPlayersHelper::getPlayerLink($item, $params, $list['project'], $module);
    ?></li>
    <?php	endforeach; ?>
        </ul>
    </li>
<?php endforeach; ?>
</ul>
