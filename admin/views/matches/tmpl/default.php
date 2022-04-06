<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage matches
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;

$massadd         = Factory::getApplication()->input->getInt('massadd', 0);
$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

if (COM_SPORTSMANAGEMENT_SHOW_DEBUG_INFO)
{
	echo $this->loadTemplate('debug');
}
?>

<div id="alt_decision_enter" style="display:<?php echo ($massadd == 0) ? 'none' : 'block'; ?>">
<?php
echo $this->loadTemplate('massadd');
?>
</div>
<?php

switch ($this->projectws->sports_type_name)
{
case 'COM_SPORTSMANAGEMENT_ST_SMALL_BORE_RIFLE_ASSOCIATION': 
echo $this->loadTemplate('matches_small_bore_rifle');
break;
default:
echo $this->loadTemplate('matches');
?>
<?php
if (ComponentHelper::getParams($this->option)->get('show_edit_matches_matrix'))
{
echo $this->loadTemplate('matrix');
}
break;
}


$this->document->addScript(Uri::root() . 'administrator/components/com_sportsmanagement/assets/js/jquery.datetimepicker.js');
?>
<div>
<?PHP
echo $this->loadTemplate('footer');
?>
</div>
