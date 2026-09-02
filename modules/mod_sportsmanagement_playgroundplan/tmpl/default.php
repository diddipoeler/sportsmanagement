<?php
/**
 * Joomla 5/6 layout for the SportsManagement Playground Plan module.
 *
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$mode = (int) $params->get('mode', 0);
$dateFormat = (string) $params->get('dateformat', 'l, d. F Y');
$timeFormat = (string) $params->get('timeformat', 'H:i');
$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$outerClass = trim((string) $params->get('divclasscontainer', 'container-fluid'));
$rowClass = trim((string) $params->get('divclassrow', 'row'));
$tableClass = trim((string) $params->get('table_class', 'table'));
$moduleId = (int) ($module->id ?? 0);
?>
<div class="<?php echo $escape($outerClass); ?> table-responsive" id="mod_sportsmanagement_playgroundplan-<?php echo $moduleId; ?>">
    <div
        class="<?php echo $escape($rowClass); ?>"
        id="modjlplaygroundplan<?php echo $mode; ?>-<?php echo $moduleId; ?>"
        <?php echo $mode === 0 ? 'data-jsm-playgroundplan-ticker' : ''; ?>
    >
        <table class="<?php echo $escape($tableClass); ?>">
            <tbody>
            <?php foreach ($list as $index => $match) : ?>
                <tr
                    class="jsm-playgroundplan-item"
                    <?php echo $mode === 0 && $index > 0 ? 'hidden' : ''; ?>
                >
                    <td>
                        <div class="qslidejl">
                            <?php if ((int) $params->get('show_playground_name', 1) === 1 && !empty($match->display_playground_name)) : ?>
                                <div class="jlplplaneplname">
                                    <?php if (!empty($match->playground_link)) : ?>
                                        <a href="<?php echo $escape($match->playground_link); ?>">
                                            <?php echo $escape($match->display_playground_name); ?>
                                        </a>
                                    <?php else : ?>
                                        <?php echo $escape($match->display_playground_name); ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ((int) $params->get('show_playground_picture', 1) === 1 && !empty($match->display_playground_picture)) : ?>
                                <div class="jlplplaneplpicture">
                                    <img
                                        src="<?php echo $escape($match->display_playground_picture); ?>"
                                        alt="<?php echo $escape($match->display_playground_name ?? ''); ?>"
                                        style="max-width:<?php echo max(1, (int) $params->get('picture_playground_width', 100)); ?>px;height:auto;"
                                    >
                                </div>
                            <?php endif; ?>

                            <div class="jlplplanedate">
                                <?php echo HTMLHelper::_('date', $match->match_date, $dateFormat, null); ?>
                                <?php echo Text::_('MOD_SPORTSMANAGEMENT_PLAYGROUNDPLAN_JSM_START_TIME'); ?>
                                <?php echo HTMLHelper::_('date', $match->match_date, $timeFormat, null); ?>
                            </div>

                            <?php if ((int) $params->get('show_project_name', 0) === 1) : ?>
                                <div class="jlplplaneleaguename"><?php echo $escape($match->project_name ?? ''); ?></div>
                            <?php endif; ?>

                            <?php if ((int) $params->get('show_league_name', 1) === 1) : ?>
                                <div class="jlplplaneleaguename"><?php echo $escape($match->league_name ?? ''); ?></div>
                            <?php endif; ?>

                            <div>
                                <div class="jlplplanetname">
                                    <?php if (!empty($match->team1_logo)) : ?>
                                        <p>
                                            <img
                                                src="<?php echo $escape($match->team1_logo); ?>"
                                                alt="<?php echo $escape($match->team1_name ?? ''); ?>"
                                                style="max-width:<?php echo max(1, (int) $params->get('picture_width', 25)); ?>px;height:auto;"
                                            >
                                        </p>
                                    <?php endif; ?>
                                    <p><?php echo $escape($match->team1_name ?? ''); ?></p>
                                </div>

                                <div class="jlplplanetnamesep"> - </div>

                                <div class="jlplplanetname">
                                    <?php if (!empty($match->team2_logo)) : ?>
                                        <p>
                                            <img
                                                src="<?php echo $escape($match->team2_logo); ?>"
                                                alt="<?php echo $escape($match->team2_name ?? ''); ?>"
                                                style="max-width:<?php echo max(1, (int) $params->get('picture_width', 25)); ?>px;height:auto;"
                                            >
                                        </p>
                                    <?php endif; ?>
                                    <p><?php echo $escape($match->team2_name ?? ''); ?></p>
                                </div>
                            </div>

                            <div style="clear:both"></div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
