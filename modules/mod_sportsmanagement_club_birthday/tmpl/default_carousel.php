<?php
/**
 * Joomla 5/6 Bootstrap carousel for club anniversaries.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$carouselId = 'jsm-club-birthday-' . (int) $module->id;
?>
<div id="<?php echo $carouselId; ?>" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-indicators">
        <?php foreach ($clubs as $index => $club) : ?>
            <button type="button" data-bs-target="#<?php echo $carouselId; ?>" data-bs-slide-to="<?php echo (int) $index; ?>"
                    class="<?php echo $index === 0 ? 'active' : ''; ?>" <?php echo $index === 0 ? 'aria-current="true"' : ''; ?>
                    aria-label="<?php echo Text::sprintf('JLIB_HTML_PAGE_CURRENT_OF_TOTAL', $index + 1, count($clubs)); ?>"></button>
        <?php endforeach; ?>
    </div>
    <div class="carousel-inner">
        <?php foreach ($clubs as $index => $club) : ?>
            <div class="carousel-item<?php echo $index === 0 ? ' active' : ''; ?> text-center">
                <?php if ((bool) $params->get('show_picture', 1) && $club->picture_url !== '') : ?>
                    <a href="<?php echo htmlspecialchars($club->club_link, ENT_QUOTES, 'UTF-8'); ?>">
                        <img src="<?php echo htmlspecialchars($club->picture_url, ENT_QUOTES, 'UTF-8'); ?>" class="d-block mx-auto"
                             alt="<?php echo htmlspecialchars((string) $club->name, ENT_QUOTES, 'UTF-8'); ?>"
                             style="max-height:260px;max-width:100%;object-fit:contain">
                    </a>
                <?php endif; ?>
                <div class="carousel-caption position-static text-body">
                    <h5>
                        <?php if ((bool) $params->get('show_club_flag', 0)) : ?><?php echo $club->flag_html; ?><?php endif; ?>
                        <a href="<?php echo htmlspecialchars($club->club_link, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string) $club->name, ENT_QUOTES, 'UTF-8'); ?></a>
                    </h5>
                    <div><?php echo $club->birthday_text; ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $carouselId; ?>" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('JPREV'); ?></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $carouselId; ?>" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('JNEXT'); ?></span>
    </button>
</div>