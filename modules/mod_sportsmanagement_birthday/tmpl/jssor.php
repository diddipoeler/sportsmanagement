<?php
/**
 * Joomla 5/6 Bootstrap carousel layout for the SportsManagement birthday module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$carouselId = 'jsm-birthday-slider-' . (int) $module->id;
$moduleClass = htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8');
$pictureWidth = max(1, (int) $params->get('picture_width', 250));
?>
<div class="<?php echo $moduleClass; ?>">
    <div id="<?php echo $carouselId; ?>" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach ($persons as $index => $person) : ?>
                <div class="carousel-item<?php echo $index === 0 ? ' active' : ''; ?> text-center">
                    <?php if (!empty($person['picture_url'])) : ?>
                        <a href="<?php echo htmlspecialchars($person['person_link'], ENT_QUOTES, 'UTF-8'); ?>">
                            <img src="<?php echo htmlspecialchars($person['picture_url'], ENT_QUOTES, 'UTF-8'); ?>"
                                 class="d-block mx-auto img-fluid"
                                 alt="<?php echo htmlspecialchars($person['display_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                 loading="lazy"
                                 style="max-width:<?php echo $pictureWidth; ?>px;height:auto;">
                        </a>
                    <?php endif; ?>
                    <div class="carousel-caption position-static text-body">
                        <h5><?php echo $person['flag_html']; ?> <?php echo htmlspecialchars($person['display_name'], ENT_QUOTES, 'UTF-8'); ?></h5>
                        <div><?php echo $person['birthday_text']; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($persons) > 1) : ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $carouselId; ?>" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden"><?php echo Text::_('JPREV'); ?></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $carouselId; ?>" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden"><?php echo Text::_('JNEXT'); ?></span>
            </button>
        <?php endif; ?>
    </div>
</div>
