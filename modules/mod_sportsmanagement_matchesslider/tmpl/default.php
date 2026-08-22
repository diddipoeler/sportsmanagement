<?php
/** Joomla 5/6 template for the SportsManagement match slider module. */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

$scrollerId = 'jsm-matchesslider-' . (int) ($module->id ?? 0);
$direction = (string) $params->get('slide_direction', 'backwards');
$direction = in_array($direction, ['backwards', 'forwards'], true) ? $direction : 'backwards';
$showPictures = (int) $params->get('show_picture', 1) === 1;
$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>

<script>
(() => {
    const initialise = () => {
        const $ = window.jQuery;
        const element = document.getElementById(<?php echo json_encode($scrollerId); ?>);

        if (!$ || !element || typeof $.fn.simplyScroll !== 'function') {
            return;
        }

        $(element).simplyScroll({
            customClass: 'custom',
            direction: <?php echo json_encode($direction); ?>,
            pauseOnHover: false,
            frameRate: 20,
            speed: 2
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialise, {once: true});
    } else {
        initialise();
    }
})();
</script>

<div id="<?php echo $escape($scrollerId); ?>" class="row">
    <?php foreach ($slidermatches as $match) : ?>
        <div class="section">
            <div class="hp-highlight">
                <div class="feature-headline">
                    <h1>
                        <a href="<?php echo $escape($match->link ?? '#'); ?>" title="">
                            <?php
                            echo HTMLHelper::_('date', $match->match_date, (string) $params->get('dateformat', 'D, d. M. Y'), null);
                            echo ' ';
                            echo HTMLHelper::_('date', $match->match_date, (string) $params->get('timeformat', 'H.i'), null);
                            ?>
                        </a>
                    </h1>

                    <p style="text-align: center;">
                        <?php if ($showPictures && !empty($match->home_logo_url)) : ?>
                            <img
                                style="float: left; width: <?php echo (int) $params->get('xsize', 50); ?>px"
                                src="<?php echo $escape($match->home_logo_url); ?>"
                                alt="<?php echo $escape($match->teamhome ?? ''); ?>"
                                title="<?php echo $escape($match->teamhome ?? ''); ?>"
                            >
                        <?php endif; ?>

                        <?php echo $escape($match->team1_result ?? ''); ?>
                        -
                        <?php echo $escape($match->team2_result ?? ''); ?>

                        <?php if ($showPictures && !empty($match->away_logo_url)) : ?>
                            <img
                                style="float: right; width: <?php echo (int) $params->get('xsize', 50); ?>px"
                                src="<?php echo $escape($match->away_logo_url); ?>"
                                alt="<?php echo $escape($match->teamaway ?? ''); ?>"
                                title="<?php echo $escape($match->teamaway ?? ''); ?>"
                            >
                        <?php endif; ?>
                    </p>

                    <p style="text-align: center;">
                        <?php echo $escape($match->teamhome ?? ''); ?>
                        -
                        <?php echo $escape($match->teamaway ?? ''); ?>
                    </p>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
