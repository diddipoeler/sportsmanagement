<?php
/** SportsManagement club plan template for Joomla 5/6. */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
?>
<div class="<?php echo $this->escape($this->divclasscontainer); ?>" id="clubplan">
    <?php echo $this->loadTemplate('projectheading'); ?>

    <?php if (!empty($this->config['show_sectionheader']) && $this->club) : ?>
        <div class="<?php echo $this->escape($this->divclassrow); ?>" id="sectionheader">
            <p><strong><?php echo $this->escape($this->headertitle); ?></strong></p>
        </div>
    <?php endif; ?>

    <?php echo $this->loadTemplate('datenav'); ?>

    <?php
    $type = (int) ($this->config['type_matches'] ?? 4);

    $renderMatches = function (array $matches, string $emptyText, string $titleText = ''): void {
        if ($matches) {
            $count = count($matches);
            $label = $titleText !== '' ? $titleText : 'COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES';
            echo '<h4>' . $count . ' ' . Text::_($label) . '</h4>';
            $this->matches = $matches;
            echo $this->loadTemplate('matches');
            return;
        }

        echo '<h4>' . Text::_($emptyText) . '</h4><br>';
    };

    switch ($type) {
        case 0:
            $renderMatches($this->allmatches, 'COM_SPORTSMANAGEMENT_CLUBPLAN_NO_MATCHES');
            break;

        case 1:
            $renderMatches($this->homematches, 'COM_SPORTSMANAGEMENT_CLUBPLAN_NO_HOME_MATCHES');
            break;

        case 2:
            $renderMatches($this->awaymatches, 'COM_SPORTSMANAGEMENT_CLUBPLAN_NO_AWAY_MATCHES');
            break;

        case 4:
            if ($this->allmatches) {
                echo '<h4>' . count($this->allmatches) . ' ' . Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES') . '</h4>';
                $this->matches = $this->allmatches;
                echo $this->loadTemplate('matches_sorted_by_date');
            } else {
                echo '<h4>' . Text::_('COM_SPORTSMANAGEMENT_CLUBPLAN_NO_MATCHES') . '</h4><br>';
            }
            break;

        default:
            $homeLabel = $this->awaymatches
                ? 'COM_SPORTSMANAGEMENT_CLUBPLAN_HOME_MATCHES'
                : 'COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES';
            $awayLabel = $this->homematches
                ? 'COM_SPORTSMANAGEMENT_CLUBPLAN_AWAY_MATCHES'
                : 'COM_SPORTSMANAGEMENT_CLUBPLAN_MATCHES';
            $renderMatches($this->homematches, 'COM_SPORTSMANAGEMENT_CLUBPLAN_NO_HOME_MATCHES', $homeLabel);
            $renderMatches($this->awaymatches, 'COM_SPORTSMANAGEMENT_CLUBPLAN_NO_AWAY_MATCHES', $awayLabel);
            break;
    }
    ?>

    <?php echo $this->loadTemplate('jsminfo'); ?>
</div>
