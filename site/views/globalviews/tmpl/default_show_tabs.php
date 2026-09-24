<?php
/**
 * Joomla 5/6 tab renderer for shared SportsManagement frontend views.
 *
 * @version    5.6.0
 * @package    Sportsmanagement
 * @subpackage globalviews
 * @file       default_show_tabs.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$output = is_array($this->output ?? null) ? $this->output : [];

if (($this->view ?? '') === 'player') {
    $playerTabs = [];

    foreach ($output as $tab) {
        if (!is_array($tab)) {
            continue;
        }

        $text = (string) ($tab['text'] ?? '');
        $template = (string) ($tab['template'] ?? '');

        if ($text !== '' && $template !== '') {
            $playerTabs[$text] = $template;
        }
    }

    $output = $playerTabs;
}

$config = is_array($this->config ?? null) ? $this->config : [];
?>
<div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?>" id="show_tabs">
    <?php if ($output !== []) : ?>
        <?php
        $activeTab = (string) array_key_first($output);
        echo HTMLHelper::_('bootstrap.startTabSet', 'myTab4', ['active' => $activeTab]);
        ?>
        <?php foreach ($output as $text => $template) : ?>
            <?php
            $text = (string) $text;
            $template = (string) $template;
            echo HTMLHelper::_('bootstrap.addTab', 'myTab4', $text, Text::_($text));
            ?>
            <div class="<?php echo htmlspecialchars((string) $this->divclasscontainer, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="<?php echo htmlspecialchars((string) $this->divclassrow, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php
                    $showRankingTabs = (bool) $this->params->get('show_allranking', 0)
                        && ($this->view ?? '') === 'resultsranking'
                        && $template === 'ranking';

                    if ($showRankingTabs) {
                        $rankingTabs = [
                            'show_table_1' => 'ranking',
                            'show_table_2' => 'ranking_home',
                            'show_table_3' => 'ranking_away',
                            'show_table_4' => 'ranking_first',
                            'show_table_5' => 'ranking_second',
                        ];
                        $activeRankingTab = null;

                        foreach ($rankingTabs as $flag => $rankingTemplate) {
                            if (!empty($config[$flag])) {
                                $activeRankingTab = $flag;
                                break;
                            }
                        }

                        if ($activeRankingTab !== null) {
                            echo HTMLHelper::_('bootstrap.startTabSet', 'defaulttabsranking', ['active' => $activeRankingTab]);

                            foreach ($rankingTabs as $flag => $rankingTemplate) {
                                if (empty($config[$flag])) {
                                    continue;
                                }

                                $label = (string) ($config['table_text_' . substr($flag, -1)] ?? $flag);
                                echo HTMLHelper::_('bootstrap.addTab', 'defaulttabsranking', $flag, Text::_($label));
                                echo $this->loadTemplate($rankingTemplate);
                                echo HTMLHelper::_('bootstrap.endTab');
                            }

                            echo HTMLHelper::_('bootstrap.endTabSet');
                        }
                    } else {
                        echo $this->loadTemplate($template);
                    }
                    ?>
                </div>
            </div>
            <?php echo HTMLHelper::_('bootstrap.endTab'); ?>
        <?php endforeach; ?>
        <?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
    <?php endif; ?>
</div>
