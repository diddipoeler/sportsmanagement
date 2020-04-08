<?php
/**
 *
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 *
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage databasetool
 * @file       default.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
$templatesToLoad = array('footer','listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);

?>

	<form action="<?php echo $this->request_url; ?>" method="post" id="adminForm" name="adminForm">
<p class="nowarning"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPDATE_INPROGRESS') ?></p>
<div class="joomlaupdate_spinner" ></div>

<?PHP
if (version_compare(JVERSION, '3.0.0', 'ge'))
{
	if ($this->bar_value < 100)
	{
		$div_class = 'progress progress-info progress-striped';
	}
	else
	{
		$div_class = 'progress progress-success progress-striped';
	}
?>
<div class="<?php echo $div_class; ?>">
<div class="bar" style="width: <?php echo $this->bar_value; ?>%;"></div>

</div>
<?PHP
echo 'step -> ' . $this->work_table . '<br>';
}
else
{
?>
<div id="progressbar">
<div class="progress-label">
<?php echo $this->task . ' - ' . $this->work_table; ?>
</div>
</div>
<?PHP
// Echo 'step -> '.$this->work_table.'<br>';
}
?>

<input type="hidden" name="step" value="<?php echo $this->step; ?>" />
<input type="hidden" name="totals" value="<?php echo $this->totals; ?>" />


<?PHP


if ($this->bar_value < 100)
{
	echo '<meta http-equiv="refresh" content="1; URL=' . $this->request_url . '">';
}
?>

</form>
