<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage updates
 * @file       default_data.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

$templatesToLoad = array('footer', 'listheader');
sportsmanagementHelper::addTemplatePaths($templatesToLoad, $this);
if (version_compare(substr(JVERSION, 0, 5), '3.0.0', 'ge'))
{
$this->startPane = 'startTabSet';
$this->endPane = 'endTabSet';
$this->addPanel = 'addTab';
$this->endPanel = 'endTab';
}
else
{
$this->startPane = 'startPane';
$this->endPane = 'endPane';
$this->addPanel = 'addPanel';
$this->endPanel = 'endPanel';
}
?>

<?php
// Define tabs options for version of Joomla! 3.0
			$tabsOptions = array(
			"active" => "tab1_id1" // It is the ID of the active tab.
			);
	// tabs anzeigen
	$idxTab = 1;
    echo HTMLHelper::_('bootstrap.' . $this->startPane, 'ID-Tabs-Group', $tabsOptions);
//	echo HTMLHelper::_('tabs.start', 'tabs_updates', array('useCookie' => 1));    
	echo HTMLHelper::_('bootstrap.' . $this->addPanel, 'ID-Tabs-Group', 'tab1_id'. ($idxTab++), Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_LIST'));
//	echo HTMLHelper::_('tabs.panel', Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_LIST'), 'panel' . ($idxTab++));
	?>
    <table class="table">
        <thead>
        <tr>
            <th width="5" style="vertical-align: top; "><?php echo Text::_('COM_SPORTSMANAGEMENT_GLOBAL_NUM'); ?></th>
            <th class="title"
                class="nowrap"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_UPDATES_FILE', 'name', $this->sortDirection, $this->sortColumn); ?></th>
            <th class="title" class="nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_DESCR'); ?></th>
            <th class="title"
                class="nowrap"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_UPDATES_VERSION', 'version', $this->sortDirection, $this->sortColumn); ?></th>
            <th class="title"
                class="nowrap"><?php echo HTMLHelper::_('grid.sort', 'COM_SPORTSMANAGEMENT_ADMIN_UPDATES_DATE', 'date', $this->sortDirection, $this->sortColumn); ?></th>
            <th class="title" class="nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_EXECUTED'); ?></th>
            <th class="title" class="nowrap"><?php echo Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_COUNT'); ?></th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan='7'><?php echo '&nbsp;'; ?></td>
        </tr>
        </tfoot>
        <tbody><?php
		$k = 0;
		for ($i = 0, $n = count($this->updateFiles); $i < $n; $i++)
		{
			$row  =& $this->updateFiles[$i];
			$link = Route::_('index.php?option=com_sportsmanagement&view=updates&task=update.save&file_name=' . $row['file_name']);
			?>
            <tr class="<?php echo "row$k"; ?>">
                <td class="center"><?php echo $i + 1; ?></td>
				<?php
				$linkTitle  = $row['file_name'];
				$linkParams = "title='" . Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_MAKE_UPDATE') . "'";
				$link       = 'index.php?option=com_sportsmanagement&tmpl=component&view=update&task=update.save&file_name=' . $row['file_name'];
				?>
                <td class="center" nowrap="nowrap">
                   
					<?PHP
$link = 'index.php?option=com_sportsmanagement&tmpl=component&view=update&task=update.save&file_name=' . $row['file_name'];
echo sportsmanagementHelper::getBootstrapModalImage('ModalSelect' . $i, Uri::root() . 'administrator/components/com_sportsmanagement/assets/images/link.png', Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_MAKE_UPDATE'), '20', Uri::base() . $link, $this->modalwidth, $this->modalheight);
/*
$html = '<a href="#' . '" title="' . Text::_('') . '" data-bs-toggle="modal"' .'data-bs-target="#ModalSelect' . $i . '">'.      'starten '.'</a>';

$html .= HTMLHelper::_(
				'bootstrap.renderModal',
				'ModalSelect' . $i,
				array(
					'title'       => Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_MAKE_UPDATE'),
					'url'         => 'index.php?option=com_sportsmanagement&tmpl=component&view=update&task=update.save&file_name=' . $row['file_name'],
					'height'      => '400px',
					'width'       => '800px',
					'bodyHeight'  => 70,
					'modalWidth'  => 80,
					'footer'      => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
										. Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
				)
			);
echo $html;		
*/
echo $row['file_name'];





					?>
                </td>
                <td><?php
					if ($row['updateDescription'] != "")
					{
						echo $row['updateDescription'];
					}
					else
					{
						echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_UPDATE', $row['last_version'], $row['version']);
					}
					?></td>
                <td class="center"><?php echo $row['version']; ?></td>
                <td class="center"><?php echo Text::_($row['updateFileDate']) . ' ' . Text::_($row['updateFileTime']); ?></td>
                <td class="center"><?php echo $row['date']; ?></td>
                <td class="center"><?php echo $row['count']; ?></td>
            </tr>
			<?php
			$k = 1 - $k;
		}
		?></tbody>
    </table>

	<?PHP
    echo HTMLHelper::_('bootstrap.' . $this->endPanel);
    echo HTMLHelper::_('bootstrap.' . $this->addPanel, 'ID-Tabs-Group', 'tab1_id'. ($idxTab++), Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_HISTORY'));
//	echo HTMLHelper::_('tabs.panel', Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_HISTORY'), 'panel' . ($idxTab++));
	foreach ($this->versionhistory as $history)
	{
		?>
        <fieldset>
            <legend>
                <strong>
					<?php
					//echo $history->date;
					echo Text::sprintf('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_VERSIONEN', $history->version, HTMLHelper::date($history->date, Text::_('COM_SPORTSMANAGEMENT_ADMIN_UPDATES_DAYDATE')));
					?>
                </strong>
            </legend>
			<?php
			//echo $history->text;
			echo Text::_($history->text);
			?>
        </fieldset>
		<?PHP
	}
    echo HTMLHelper::_('bootstrap.' . $this->endPanel);
    echo HTMLHelper::_('bootstrap.' . $this->endPane, 'ID-Tabs-Group');
//	echo HTMLHelper::_('tabs.end');
	?>