<?php
/**
 * SportsManagement ein Programm zur Verwaltung für alle Sportarten
 * @version    1.0.05
 * @package    Sportsmanagement
 * @subpackage playground
 * @file       default_matches.php
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Helper\TeamLogoHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

if (!$this->games) {
    return;
}

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$showLogo = !empty($this->config['show_logo']);
$preferSmall = !empty($this->config['show_logo_small']);
$logoHeight = $preferSmall ? 20 : 50;
$modalMode = (int) ($this->overallconfig['use_jquery_modal'] ?? 0);
$gamesByDate = [];

foreach ($this->games as $game) {
    $gamesByDate[substr((string) $game->match_date, 0, 10)][] = $game;
}

$this->notes = [Text::_('COM_SPORTSMANAGEMENT_PLAYGROUND_NEXT_GAMES')];
echo $this->loadTemplate('jsm_notes');
?>
<div class="<?php echo $this->divclassrow; ?> table-responsive" id="playground_matches">
    <table class="<?php echo $escape($this->config['matches_table_class'] ?? 'table'); ?>">
        <?php foreach ($gamesByDate as $date => $games) : ?>
            <tr>
                <td colspan="<?php echo $showLogo ? 7 : 5; ?>">
                    <?php echo HTMLHelper::date($date, Text::_('COM_SPORTSMANAGEMENT_GLOBAL_MATCHDAYDATE')); ?>
                </td>
            </tr>

            <?php foreach ($games as $game) : ?>
                <?php
                $home = $this->gamesteams[(int) ($game->team1 ?? 0)] ?? null;
                $away = $this->gamesteams[(int) ($game->team2 ?? 0)] ?? null;

                if (!$home || !$away) {
                    continue;
                }
                ?>
                <tr class="sectiontableentry1">
                    <td><?php echo $escape(substr((string) $game->match_date, 11, 5)); ?></td>
                    <td class="nowrap"><?php echo $escape($game->project_name ?? ''); ?></td>

                    <?php if ($showLogo) : ?>
                        <td class="nowrap text-end">
                            <?php
                            echo TeamLogoHelper::render(
                                $home,
                                'playground-next-' . (int) $game->id . '-home',
                                $preferSmall,
                                $logoHeight,
                                $this->modalwidth,
                                $this->modalheight,
                                $modalMode
                            );
                            ?>
                        </td>
                    <?php endif; ?>

                    <td class="nowrap"><?php echo $escape($home->name ?? ''); ?></td>
                    <td class="nowrap">-</td>

                    <?php if ($showLogo) : ?>
                        <td class="nowrap text-end">
                            <?php
                            echo TeamLogoHelper::render(
                                $away,
                                'playground-next-' . (int) $game->id . '-away',
                                $preferSmall,
                                $logoHeight,
                                $this->modalwidth,
                                $this->modalheight,
                                $modalMode
                            );
                            ?>
                        </td>
                    <?php endif; ?>

                    <td class="nowrap"><?php echo $escape($away->name ?? ''); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </table>
</div>
