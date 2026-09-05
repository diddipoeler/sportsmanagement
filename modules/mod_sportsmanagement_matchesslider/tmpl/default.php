<?php
/**
 * Joomla 5/6 template for the SportsManagement match slider module.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

$scrollerId = 'jsm-matchesslider-' . (int) ($module->id ?? 0);
$direction = (string) $params->get('slide_direction', 'backwards');
$direction = in_array($direction, ['backwards', 'forwards'], true) ? $direction : 'backwards';
$showPictures = (int) $params->get('show_picture', 1) === 1;
$pictureWidth = max(1, (int) $params->get('xsize', 50));
$moduleClass = trim((string) $params->get('moduleclass_sfx', ''));
$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<div
    id="<?php echo $escape($scrollerId); ?>"
    class="jsm-matchesslider<?php echo $moduleClass !== '' ? ' ' . $escape($moduleClass) : ''; ?>"
    data-jsm-matchesslider
    data-scroll-direction="<?php echo $escape($direction); ?>"
    data-scroll-speed="40"
>
    <div class="jsm-matchesslider__track" data-jsm-matchesslider-track>
        <?php foreach ($slidermatches as $match) : ?>
            <article class="jsm-matchesslider__item section">
                <div class="hp-highlight">
                    <div class="feature-headline">
                        <h3 class="jsm-matchesslider__date">
                            <a href="<?php echo $escape($match->link ?? '#'); ?>">
                                <?php
                                echo HTMLHelper::_('date', $match->match_date, (string) $params->get('dateformat', 'D, d. M. Y'), null);
                                echo ' ';
                                echo HTMLHelper::_('date', $match->match_date, (string) $params->get('timeformat', 'H.i'), null);
                                ?>
                            </a>
                        </h3>

                        <div class="jsm-matchesslider__score">
                            <?php if ($showPictures && !empty($match->home_logo_url)) : ?>
                                <img
                                    class="jsm-matchesslider__logo jsm-matchesslider__logo--home"
                                    src="<?php echo $escape($match->home_logo_url); ?>"
                                    alt="<?php echo $escape($match->teamhome ?? ''); ?>"
                                    title="<?php echo $escape($match->teamhome ?? ''); ?>"
                                    width="<?php echo $pictureWidth; ?>"
                                    loading="lazy"
                                >
                            <?php endif; ?>

                            <span class="jsm-matchesslider__result">
                                <?php echo $escape($match->team1_result ?? ''); ?>
                                -
                                <?php echo $escape($match->team2_result ?? ''); ?>
                            </span>

                            <?php if ($showPictures && !empty($match->away_logo_url)) : ?>
                                <img
                                    class="jsm-matchesslider__logo jsm-matchesslider__logo--away"
                                    src="<?php echo $escape($match->away_logo_url); ?>"
                                    alt="<?php echo $escape($match->teamaway ?? ''); ?>"
                                    title="<?php echo $escape($match->teamaway ?? ''); ?>"
                                    width="<?php echo $pictureWidth; ?>"
                                    loading="lazy"
                                >
                            <?php endif; ?>
                        </div>

                        <p class="jsm-matchesslider__teams">
                            <?php echo $escape($match->teamhome ?? ''); ?>
                            -
                            <?php echo $escape($match->teamaway ?? ''); ?>
                        </p>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</div>
