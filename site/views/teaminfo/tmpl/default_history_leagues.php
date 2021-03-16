<?php
/**
 * SportsManagement ein Programm zur Verwaltung für Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage teaminfo
 * @file       deafult_history_leagues.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;

$this->columns  = 4;
$this->divclass = '';

$this->notes = array();
$this->notes[] = Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_HISTORY_OVERVIEW_SUMMARY');
echo $this->loadTemplate('jsm_notes');

/** tabelle oder bootstrap ansicht */
if ($this->overallconfig['use_table_or_bootstrap'])
{
	?>
    <table class="<?PHP echo $this->config['table_class']; ?>">
        <thead>
        <tr class="sectiontableheader">
            <th class="" nowrap="" style="background:#BDBDBD;">
				<?PHP echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_LEAGUE'); ?>
            </th>
            <th class="" nowrap="" style="background:#BDBDBD;">
				<?PHP echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GAMES'); ?>
            </th>
            <th class="" nowrap="" style="background:#BDBDBD;">
				<?PHP echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_WDL'); ?>
            </th>

            <th class="" nowrap="" style="background:#BDBDBD;">
				<?PHP echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GOALS'); ?>
            </th>
        </tr>
        </thead>
		<?php
		foreach ($this->leaguerankoverviewdetail as $league => $summary)
		{
			?>
            <tr class="">
                <td><?php echo $league; ?></td>
                <td><?php echo $summary->match; ?></td>

                <td><?php echo $summary->won;
					echo ' / ';
					?>
					<?php echo $summary->draw;
					echo ' / ';
					?>
					<?php echo $summary->loss; ?></td>

                <td><?php echo $summary->goalsfor;
					echo ' : ';
					?>
					<?php echo $summary->goalsagain; ?></td>
            </tr>
			<?php
		}
		?>

    </table>
	<?php
}
else
{
	/** welche bootstrap version */
	$params = ComponentHelper::getParams('com_sportsmanagement');

	if (version_compare(JSM_JVERSION, '4', 'eq') || $params->get('use_jsmgrid'))
	{
		$this->divclass = 'col p-2';
	}
    elseif ($this->overallconfig['use_bootstrap_version'] && !$params->get('use_jsmgrid'))
	{
		//$this->divclass = 'col p-2';
		$this->divclass .= "col-xs-" . round((12 / $this->columns));
		$this->divclass .= " col-sm-" . round((12 / $this->columns));
		$this->divclass .= " col-md-" . round((12 / $this->columns));
		$this->divclass .= " col-lg-" . round((12 / $this->columns));
	}
	else
	{
		$this->divclass = "span" . round((12 / $this->columns));
	}
	?>
    <!--    <div class="container-fluid no-gutters">-->
    <div class="row no-gutters">
        <div class=" <?php echo $this->divclass; ?>"
             style="background:#BDBDBD;"><?PHP echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_LEAGUE'); ?></div>
        <div class=" <?php echo $this->divclass; ?>"
             style="background:#BDBDBD;"><?PHP echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GAMES'); ?></div>
        <div class=" <?php echo $this->divclass; ?>"
             style="background:#BDBDBD;"><?PHP echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_WDL'); ?></div>
        <div class=" <?php echo $this->divclass; ?>"
             style="background:#BDBDBD;"><?PHP echo Text::_('COM_SPORTSMANAGEMENT_TEAMINFO_TOTAL_GOALS'); ?></div>
    </div>
	<?php
	foreach ($this->leaguerankoverviewdetail as $league => $summary)
	{
		?>
        <div class="row no-gutters">
            <div class="<?php echo $this->divclass; ?>"><?php echo $league; ?></div>
            <div class="<?php echo $this->divclass; ?>"><?php echo $summary->match; ?></div>
            <div class="<?php echo $this->divclass; ?>"><?php echo $summary->won;
				echo ' / ';
				?>
				<?php echo $summary->draw;
				echo ' / ';
				?>
				<?php echo $summary->loss; ?></div>
            <div class="<?php echo $this->divclass; ?>"><?php echo $summary->goalsfor;
				echo ' : ';
				?>
				<?php echo $summary->goalsagain; ?></div>
        </div>
		<?php
	}
	?>

    <!--</div>-->
	<?php
}
?>
