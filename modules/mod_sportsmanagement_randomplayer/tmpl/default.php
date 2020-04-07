<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @package    sportsmanagement
 * @subpackage mod_sportsmanagement_randomplayer
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// Check if any player returned
$items = count($list['player']);

if (!$items)
{
	echo '<p class="modjlgrandomplayer">' . Text::_('NO ITEMS') . '</p>';

	return;
}?>

<div class="row">
<div class="col-md-10 blogShort">
<?php if ($params->get('show_project_name'))
:
?>

<h4><?php echo $list['project']->name; ?></h4>

<?php endif; ?> <?php
$person = $list['player'];
$routeparameter = array();
$routeparameter['cfg_which_database'] = $params->get('cfg_which_database');
$routeparameter['s'] = $params->get('s');
$routeparameter['p'] = $list['project']->slug;
$routeparameter['tid'] = $list['infoteam']->team_slug;
$routeparameter['pid'] = $person->slug;
$link = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);

// $link = sportsmanagementHelperRoute::getPlayerRoute( $list['project']->slug,
//												$list['infoteam']->team_id,
//												$person->slug );
?>

<?php
$picturetext = Text::_('MOD_SPORTSMANAGEMENT_RANDOMPLAYER_PERSON_PICTURE');
$text = sportsmanagementHelper::formatName(
	null, $person->firstname,
	$person->nickname,
	$person->lastname,
	$params->get("name_format")
);

$imgTitle = Text::sprintf($picturetext . ' %1$s', $text);

if (isset($list['inprojectinfo']->picture))
{
	$picture = $list['inprojectinfo']->picture;
}
else
{
	$picture = '';
}

$pic = sportsmanagementHelper::getPictureThumb($picture, $imgTitle, $params->get('picture_width'), 'auto');
echo '<a href="' . $link . '">' . $pic . '</a>';
?>

<article>
<p>
<?php
if ($params->get('show_player_flag'))
{
	echo JSMCountries::getCountryFlag($person->country) . " ";
}


if ($params->get('show_player_link'))
{
	   $routeparameter = array();
	$routeparameter['cfg_which_database'] = $params->get('cfg_which_database');
	$routeparameter['s'] = $params->get('s');
	$routeparameter['p'] = $list['project']->slug;
	$routeparameter['tid'] = $list['infoteam']->team_slug;
	$routeparameter['pid'] = $person->slug;
	$link = sportsmanagementHelperRoute::getSportsmanagementRoute('player', $routeparameter);

	//		$link = sportsmanagementHelperRoute::getPlayerRoute($list['project']->slug,
	//														$list['infoteam']->team_id,
	//														$person->slug );
	echo HTMLHelper::link($link, $text);
}
else
{
	echo Text::sprintf('%1$s', $text);
}
?>
</p>
<?php if ($params->get('show_team_name'))
:
?>
<p>
<?php
	echo sportsmanagementHelper::getPictureThumb(
		$list['infoteam']->team_picture,
		$list['infoteam']->name,
		$params->get('team_picture_width', 21),
		'auto',
		1
	) . " ";
	$text = $list['infoteam']->name;

if ($params->get('show_team_link'))
	{
	   $routeparameter = array();
	$routeparameter['cfg_which_database'] = $params->get('cfg_which_database');
	$routeparameter['s'] = $params->get('s');
	$routeparameter['p'] = $list['project']->slug;
	$routeparameter['tid'] = $list['infoteam']->team_slug;
	$routeparameter['ptid'] = 0;
	$link = sportsmanagementHelperRoute::getSportsmanagementRoute('teaminfo', $routeparameter);

	//		$link = sportsmanagementHelperRoute::getTeamInfoRoute($list['project']->slug,
	//														$list['infoteam']->team_id);
	echo HTMLHelper::link($link, $text);
}
else
	{
	echo Text::sprintf('%1$s', $text);
}
?>
</p>
<?php endif; ?>
<?php
if ($params->get('show_position_name') && isset($list['inprojectinfo']->position_name))
:
?>
<p>
<?php
	$positionName = $list['inprojectinfo']->position_name;
	echo Text::_($positionName);?>
</p>
<?php endif; ?>
</article>
</div>

</div>
