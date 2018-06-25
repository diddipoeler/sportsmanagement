<?php
/** SportsManagement ein Programm zur Verwaltung fűr alle Sportarten
 * @version   1.0.05
 * @file      deafult_rankingheading.php
 * @author    diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright Copyright: © 2013 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license   This file is part of SportsManagement.
 * @package   sportsmanagement
 * @subpackage ranking
 */

defined('_JEXEC') or die('Restricted access');

$columns = explode(',', $this->config['ordered_columns']);
$column_names = explode(',', $this->config['ordered_columns_names']);
?>
<thead>
    <tr class="sectiontableheader">
        <th class="rankheader" colspan="3">
            <?php sportsmanagementHelperHtml::printColumnHeadingSort(JText::_('COM_SPORTSMANAGEMENT_RANKING_POSITION'), "rank", $this->config, "ASC", $this->paramconfig); ?>
        </th>

        <?php
        if ($this->config['show_logo_small_table'] != "no_logo") {
            echo '<th style="text-align: center">&nbsp;</th>';
        }
        ?>

        <th class="teamheader">	
<?php sportsmanagementHelperHtml::printColumnHeadingSort(JText::_('COM_SPORTSMANAGEMENT_RANKING_TEAM'), "name", $this->config, "ASC", $this->paramconfig); ?>
        </th>

<?php
foreach ($columns as $k => $column) {
    if (empty($column_names[$k])) {
        $column_names[$k] = '???';
    }

    $c = strtoupper(trim($column));
    $c = "COM_SPORTSMANAGEMENT_" . $c;

    $toolTipTitle = $column_names[$k];
    $toolTipText = JText::_($c);

    switch (trim(strtoupper($column))) {
        case 'PLAYED':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "played", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'WINS':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "won", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'TIES':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "draw", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'LOSSES':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "loss", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'WOT':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "wot", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'WSO':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "wso", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'LOT':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "lot", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'LSO':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "lso", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'WINPCT':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "winpct", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'GB':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            echo $column_names[$k];
            echo '</span></th>';
            break;

        case 'LEGS':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            echo $column_names[$k];
            echo '</span></th>';
            break;

        case 'LEGS_DIFF':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "legsdiff", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'LEGS_RATIO':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "legsratio", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'SCOREFOR':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "goalsfor", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'SCOREAGAINST':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "goalsagainst", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'SCOREPCT':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            echo $column_names[$k];
            echo '</span></th>';
            break;

        case 'RESULTS':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "goalsp", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'DIFF':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "diff", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'POINTS':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "points", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'PENALTYPOINTS':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "penaltypoints", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'NEGPOINTS':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "negpoints", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'OLDNEGPOINTS':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "negpoints", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'POINTS_RATIO':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "pointsratio", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'BONUS':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "bonus", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'START':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "start", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'QUOT':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            sportsmanagementHelperHtml::printColumnHeadingSort($column_names[$k], "quot", $this->config, JFactory::getApplication()->input->getVar('dir', ''), $this->paramconfig);
            echo '</span></th>';
            break;

        case 'TADMIN':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            echo $column_names[$k];
            echo '</span></th>';
            break;

        case 'GFA':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            echo $column_names[$k];
            echo '</span></th>';
            break;

        case 'GAA':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            echo $column_names[$k];
            echo '</span></th>';
            break;

        case 'PPG':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            echo $column_names[$k];
            echo '</span></th>';
            break;

        case 'PPP':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            echo $column_names[$k];
            echo '</span></th>';
            break;

        case 'LASTGAMES':
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            echo $column_names[$k];
            echo '</span></th>';
            break;

        default:
            echo '<th class="headers">';
            echo '<span class="hasTip" title="' . $toolTipTitle . '::' . $toolTipText . '">';
            echo JText::_($column);
            echo '</span></th>';
            break;
    }
}
?>
    </tr>
</thead>
