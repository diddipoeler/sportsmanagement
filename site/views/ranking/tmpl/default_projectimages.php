<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage ranking
 * @file       default_projectimages.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;

$actualItems = count($this->matchimages);
$setItems = count($this->matchimages);
$rssitems_colums = $this->config['show_pictures_columns'];
$pictures_width = $this->config['show_pictures_width'];

if ($setItems > $actualItems)
{
	$totalItems = $actualItems;
}
else
{
	$totalItems = $setItems;
}
?>
<div class="<?php echo $this->divclassrow;?>" id="projectimages">
<h4>
	<?php echo Text::_('COM_SPORTSMANAGEMENT_PROJECTIMAGES'); ?>
</h4>
<table class="table ">
<?php
$j = 0;

foreach ($this->matchimages as $images)
{
	if (($j % $rssitems_colums) == 0)
	:
		$row = 'row' . (floor($j / $rssitems_colums) % $rssitems_colums);
		?>
		<tr class="<?php echo $row; ?>">
	<?php endif; ?>
<td class="item" style="width:<?php echo floor(99 / $rssitems_colums) . "%";?>">
<a href="<?php echo $images->sitepath . DIRECTORY_SEPARATOR . $images->name;?>" alt="<?php echo $images->name;?>" title="<?php echo $images->name;?>" class="highslide" onclick="return hs.expand(this)">
<?php
echo sportsmanagementHelperHtml::getBootstrapModalImage(
	$images->name,
	$images->sitepath . DIRECTORY_SEPARATOR . $images->name,
	$images->name,
	$pictures_width,
	'',
	$this->modalwidth,
	$this->modalheight,
	$this->overallconfig['use_jquery_modal']
);
?>
</a>
</td>
<?php if (($j % $rssitems_colums) == ($rssitems_colums - 1))
	:
	?>
</tr>
<?php endif; ?>
<?php
$j++;
}
?>
</table>  
</div>
