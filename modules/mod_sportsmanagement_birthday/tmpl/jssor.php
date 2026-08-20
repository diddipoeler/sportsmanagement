<?php
/** Joomla 5/6 slider layout. The historical Jssor dependency is replaced by Joomla's Bootstrap carousel. */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$carouselId = 'jsm-birthday-slider-' . (int) $module->id;
?>
<div id="<?php echo $carouselId; ?>" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        <?php foreach ($persons as $index => $person) : ?>
            <div class="carousel-item<?php echo $index === 0 ? ' active' : ''; ?>">
                <?php if (!empty($person['picture_url'])) : ?>
                    <a href="<?php echo htmlspecialchars($person['person_link'], ENT_QUOTES, 'UTF-8'); ?>">
                        <img src="<?php echo htmlspecialchars($person['picture_url'], ENT_QUOTES, 'UTF-8'); ?>" class="d-block w-100"
                             alt="<?php echo htmlspecialchars($person['display_name'], ENT_QUOTES, 'UTF-8'); ?>" style="max-height:300px;object-fit:contain">
                    </a>
                <?php endif; ?>
                <div class="carousel-caption position-static text-body">
                    <h5><?php echo $person['flag_html']; ?> <?php echo htmlspecialchars($person['display_name'], ENT_QUOTES, 'UTF-8'); ?></h5>
                    <div><?php echo $person['birthday_text']; ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $carouselId; ?>" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden"><?php echo Text::_('JPREV'); ?></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $carouselId; ?>" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden"><?php echo Text::_('JNEXT'); ?></span>
    </button>
</div>
