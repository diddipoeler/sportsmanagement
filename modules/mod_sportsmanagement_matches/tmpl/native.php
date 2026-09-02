<?php
/**
 * Native Joomla 5/6 rendering layout for the matches module.
 *
 * @version   5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$moduleClass = trim('mod-sportsmanagement-matches ' . (string) $params->get('moduleclass_sfx', ''));
?>
<div class="<?php echo $escape($moduleClass); ?>" id="mod-sportsmanagement-matches-<?php echo (int) $module->id; ?>">
    <?php if (!$matches) : ?>
        <?php if ((int) $params->get('show_no_matches_notice', 0) === 1) : ?>
            <p class="jsm-matches-empty"><?php echo $escape($params->get('no_matches_notice', 'No upcoming matches!')); ?></p>
        <?php endif; ?>
    <?php else : ?>
        <?php foreach ($matches as $index => $match) : ?>
            <article class="jsm-match-card <?php echo $escape((string) $params->get($index % 2 ? 'sectiontableentry1' : 'sectiontableentry2', '')); ?>">
                <?php if ($match['status_heading'] !== '') : ?>
                    <div class="jsm-match-status jsm-match-status-<?php echo $escape($match['type']); ?>">
                        <?php echo $escape($match['status_heading']); ?>
                    </div>
                <?php endif; ?>

                <?php if ($match['heading'] !== '') : ?>
                    <div class="jsm-match-heading <?php echo $escape((string) $params->get('heading_style', 'sectiontableheader')); ?>">
                        <?php echo $match['heading']; ?>
                    </div>
                <?php endif; ?>

                <div class="jsm-match-datetime">
                    <?php if ($match['venue']) : ?>
                        <span class="jsm-match-venue">
                            <?php echo $escape($params->get('venue_text', 'Venue:')); ?>
                            <?php if ($match['venue']['url'] !== '') : ?>
                                <a href="<?php echo $escape($match['venue']['url']); ?>"><?php echo $escape($match['venue']['name']); ?></a>
                            <?php else : ?>
                                <?php echo $escape($match['venue']['name']); ?>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                    <time><?php echo $escape($match['date']); ?> · <?php echo $escape($match['time']); ?><?php echo $escape(Text::_('MOD_SPORTSMANAGEMENT_MATCHES_CLOCK')); ?></time>
                </div>

                <div class="jsm-match-teams">
                    <?php foreach (['home', 'away'] as $side) : ?>
                        <?php $team = $match[$side]; ?>
                        <div class="jsm-match-team jsm-match-team-<?php echo $side; ?>">
                            <?php if ($team['logo']) : ?>
                                <img
                                    src="<?php echo $escape($team['logo']['src']); ?>"
                                    alt="<?php echo $escape($team['logo']['alt']); ?>"
                                    <?php if ((int) $team['logo']['width'] > 0) : ?>width="<?php echo (int) $team['logo']['width']; ?>"<?php endif; ?>
                                    <?php if ((int) $team['logo']['height'] > 0) : ?>height="<?php echo (int) $team['logo']['height']; ?>"<?php endif; ?>
                                    loading="lazy"
                                >
                                <?php if ((int) $params->get('new_line_after_logo', 1) === 1) : ?><br><?php endif; ?>
                            <?php endif; ?>

                            <?php if ((int) $params->get('show_names', 1) === 1) : ?>
                                <span class="jsm-match-team-name"><?php echo $escape($team['name']); ?></span>
                            <?php endif; ?>

                            <?php if ($team['links']) : ?>
                                <nav class="jsm-match-team-links" aria-label="<?php echo $escape($team['name']); ?>">
                                    <?php foreach ($team['links'] as $link) : ?>
                                        <a
                                            href="<?php echo $escape($link['url']); ?>"
                                            <?php if ($link['external']) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
                                        ><?php echo $escape($link['label']); ?></a>
                                    <?php endforeach; ?>
                                </nav>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="jsm-match-result<?php echo $match['cancel'] ? ' is-cancelled' : ''; ?>">
                        <?php echo $escape($match['result']); ?>
                    </div>
                </div>

                <?php if ($match['partresults'] !== '') : ?>
                    <div class="jsm-match-parts"><?php echo $escape($match['partresults']); ?></div>
                <?php endif; ?>

                <?php if ($match['referees'] || $match['spectators'] > 0) : ?>
                    <div class="jsm-match-meta">
                        <?php if ($match['referees']) : ?>
                            <ul class="jsm-match-referees">
                                <?php foreach ($match['referees'] as $referee) : ?>
                                    <li>
                                        <?php if ($referee['position'] !== '') : ?>
                                            <span class="jsm-match-referee-position"><?php echo $escape(Text::_($referee['position'])); ?>:</span>
                                        <?php endif; ?>
                                        <?php echo $escape($referee['name']); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if ($match['spectators'] > 0) : ?>
                            <span class="jsm-match-spectators">
                                <?php echo $escape(Text::_('MOD_SPORTSMANAGEMENT_MATCHES_SPECTATORS')); ?>:
                                <?php echo $escape(number_format((int) $match['spectators'], 0, ',', '.')); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($match['notice'] !== '') : ?>
                    <div class="jsm-match-notice"><?php echo $escape($match['notice']); ?></div>
                <?php endif; ?>

                <?php if ($match['links']) : ?>
                    <nav class="jsm-match-links">
                        <?php foreach ($match['links'] as $link) : ?>
                            <a href="<?php echo $escape($link['url']); ?>"><?php echo $escape($link['label']); ?></a>
                        <?php endforeach; ?>
                    </nav>
                <?php endif; ?>

                <?php if (!empty($match['navigation']['previous']) || !empty($match['navigation']['next'])) : ?>
                    <nav class="jsm-match-navigation" aria-label="<?php echo $escape(Text::_('MOD_SPORTSMANAGEMENT_MATCHES_ENABLE_ARROWS_TITLE')); ?>">
                        <?php if (!empty($match['navigation']['previous'])) : ?>
                            <a rel="prev" href="<?php echo $escape($match['navigation']['previous']['url']); ?>">
                                <?php echo $escape($match['navigation']['previous']['label']); ?>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($match['navigation']['next'])) : ?>
                            <a rel="next" href="<?php echo $escape($match['navigation']['next']['url']); ?>">
                                <?php echo $escape($match['navigation']['next']['label']); ?>
                            </a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($legacyUpdateRequested) : ?>
        <!-- ISHD compatibility update is intentionally not executed during native Joomla 5/6 module rendering. -->
    <?php endif; ?>
</div>
