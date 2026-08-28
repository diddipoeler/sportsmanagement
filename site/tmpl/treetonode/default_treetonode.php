<?php
/** Native Joomla 5/6 tournament tree rendering. */
\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Helper\CountryPresentationHelper;
use Diddipoeler\Component\SportsManagement\Site\Helper\SiteRouteHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$background = $escape($this->config['tree_bg_colour'] ?? '#c0c0c0');
$border = $escape($this->config['tree_border_colour'] ?? '#c0c0c0');
$fontSize = max(1, (int) ($this->config['tree_field_fontsize'] ?? 8));
$fieldWidth = max(1, (int) ($this->config['tree_field_width'] ?? 75));
$nodeStyle = 'background-color:' . $background
    . ';border:1px solid ' . $border
    . ';font-weight:bold;white-space:nowrap'
    . ';font-size:' . $fontSize . 'pt'
    . ';width:' . $fieldWidth . 'px'
    . ';font-family:verdana;text-align:left;padding-left:10px';

$bracketBase = Uri::root(true) . '/media/com_sportsmanagement/treebracket/'
    . ((int) ($this->config['tree_bracket_type'] ?? 0) === 1 ? 'onblack/' : 'onwhite/');
$connector = static fn (string $name): string => '<img src="'
    . htmlspecialchars($bracketBase . $name, ENT_QUOTES, 'UTF-8')
    . '" alt="" width="16" height="30">';
$dl = $connector('treedl.gif');
$ul = $connector('treeul.gif');
$cl = $connector('treecl.gif');
$p = $connector('treep.gif');

$renderIcon = static function (object $node, int $type): string {
    if (!isset($node->country)) {
        return '';
    }

    if ($type === 1) {
        $logo = trim((string) ($node->logo_small ?? ''));
        return $logo !== ''
            ? HTMLHelper::image($logo, '', ['width' => 20])
            : '&nbsp;';
    }

    if ($type === 2) {
        return CountryPresentationHelper::flag((string) $node->country);
    }

    // Legacy showClubIcon() emitted nothing for the historical values 3/4.
    return '';
};

$teamName = static function (object $node, int $type): string {
    $property = match ($type) {
        0 => 'short_name',
        1 => 'middle_name',
        default => 'team_name',
    };

    return trim((string) ($node->{$property} ?? ''));
};

if (!$this->node) {
    echo Text::_('COM_SPORTSMANAGEMENT_TREETONODE_GENERATE_THE_TREE');
    return;
}

$depth = max(0, (int) ($this->node[0]->tree_i ?? 0));
$hide = max(0, (int) ($this->node[0]->hide ?? 0));
$rows = 2 * (2 ** $depth);
$columns = 2 * $depth + 1;
$visibleColumns = max(0, $columns - 2 * $hide);
?>
<table class="table">
    <?php if (!empty($this->config['show_treeheader'])) : ?>
        <tr style="text-align:center">
            <td></td>
            <?php for ($headerColumn = 0; $headerColumn <= $columns; $headerColumn++) : ?>
                <?php if (($headerColumn % 2) !== 0 && $headerColumn > 2) : ?>
                    <?php $roundIndex = (int) ((($headerColumn - 1) / 2) - 1); ?>
                    <td align="middle" colspan="2">
                        <?php
                        if ((int) ($this->config['show_name_from'] ?? 0) === 0) {
                            echo $escape($this->roundname[$roundIndex]->name ?? '');
                        } else {
                            echo $escape($this->config['tree_name_' . $roundIndex] ?? '');
                        }

                        if (!empty($this->config['show_round_date']) && isset($this->roundname[$roundIndex])) {
                            $round = $this->roundname[$roundIndex];
                            $first = new Date((string) ($round->round_date_first ?? 'now'));
                            $last = new Date((string) ($round->round_date_last ?? 'now'));
                            $date1 = $first->format('d-m-Y');
                            $date2 = $last->format('d-m-Y');
                            echo '<br>';
                            echo $date1 === $date2
                                ? $escape($date1)
                                : $escape($first->format('d')) . '&divide;' . $escape($date2);
                        }
                        ?>
                    </td>
                <?php endif; ?>
            <?php endfor; ?>
        </tr>
    <?php endif; ?>

    <?php for ($rowIndex = 1; $rowIndex < $rows; $rowIndex++) : ?>
        <?php $node = $this->node[$rowIndex - 1] ?? null; ?>
        <?php if (!$node || (int) ($node->published ?? 0) === 0) { continue; } ?>
        <tr>
            <td height="30"></td>
            <?php for ($column = 1; $column <= $visibleColumns; $column++) : ?>
                <?php
                $isNodeCell = false;
                for ($level = 0; $level <= $depth; $level++) {
                    if ($column === 1 + ($level * 2)
                        && $rowIndex % (2 * (2 ** $level)) === (2 ** $level)) {
                        $isNodeCell = true;
                        break;
                    }
                }
                ?>
                <td<?php echo $isNodeCell ? ' style="' . $nodeStyle . '"' : ''; ?>>
                    <?php for ($level = 0; $level <= $depth; $level++) : ?>
                        <?php $power = 2 ** $level; ?>
                        <?php if ($column === 1 + ($level * 2) && $rowIndex % (2 * $power) === $power) : ?>
                            <?php
                            $isLeaf = !empty($node->is_leaf);
                            $iconType = (int) ($this->config[$isLeaf ? 'show_logo_small_flag_leaf' : 'show_logo_small_flag'] ?? 0);
                            echo $renderIcon($node, $iconType);
                            echo ' ';

                            $nameType = (int) ($this->config[$isLeaf ? 'name_team_type_leaf' : 'name_team_type'] ?? 2);
                            echo $escape($teamName($node, $nameType));

                            if (!empty($node->match_id)) {
                                $reportLink = SiteRouteHelper::view('matchreport', [
                                    'cfg_which_database' => $this->input->getInt('cfg_which_database', 0),
                                    's' => $this->input->getInt('s', 0),
                                    'p' => (int) ($this->project->id ?? $this->input->getInt('p', 0)),
                                    'mid' => (int) $node->match_id,
                                ]);
                                $iconUrl = Uri::root(true)
                                    . '/components/com_sportsmanagement/assets/images/history-icon-png--21.png';
                                ?>
                                <a href="<?php echo $escape($reportLink); ?>">
                                    <img src="<?php echo $escape($iconUrl); ?>"
                                         width="20"
                                         alt="<?php echo $escape(Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOW_MATCHREPORT')); ?>"
                                         title="<?php echo $escape(Text::_('COM_SPORTSMANAGEMENT_RESULTS_SHOW_MATCHREPORT')); ?>">
                                </a>
                                <?php
                            }
                            ?>
                        <?php elseif ($column === 2 + ($level * 2) && $rowIndex % (4 * $power) === $power) : ?>
                            <?php echo $dl; ?>
                        <?php elseif ($column === 2 + ($level * 2) && $rowIndex % (4 * $power) === 2 * $power) : ?>
                            <?php if (empty($node->is_leaf)) { echo $cl; } ?>
                        <?php elseif ($column === 2 + ($level * 2) && $rowIndex % (4 * $power) === 3 * $power) : ?>
                            <?php echo $ul; ?>
                        <?php elseif ($column === 2 + ($level * 2)
                            && $rowIndex % (4 * $power) > $power
                            && $rowIndex % (4 * $power) < 3 * $power) : ?>
                            <?php echo $p; ?>
                        <?php endif; ?>
                    <?php endfor; ?>
                </td>
            <?php endfor; ?>
        </tr>
    <?php endfor; ?>
</table>
