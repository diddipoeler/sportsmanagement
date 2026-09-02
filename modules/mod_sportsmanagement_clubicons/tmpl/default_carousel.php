<?php
/**
 * Joomla 5/6 Bootstrap carousel layout for SportsManagement club icons.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$items = [];
foreach ($ranking as $row) {
    $projectTeamId = (int) ($row->projectteamid ?? 0);
    if ($projectTeamId > 0 && isset($teams[$projectTeamId])) {
        $items[] = $teams[$projectTeamId];
    }
}

if (!$items) {
    return;
}

$carouselId = 'jsm-clubicons-carousel-' . (int) $module->id;
$newWindow = (int) $params->get('teamlink', 0) === 5 && (int) $params->get('newwindow', 0) === 1;
$height = max(1, (int) $params->get('picture_height', 50));
$moduleClass = htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8');
?>
<div class="mod-sportsmanagement-clubicons <?php echo $moduleClass; ?>">
    <div id="<?php echo $carouselId; ?>" class="carousel slide" data-bs-ride="carousel">
        <?php if (count($items) > 1) : ?>
            <div class="carousel-indicators">
                <?php foreach ($items as $index => $team) : ?>
                    <button type="button"
                            data-bs-target="#<?php echo $carouselId; ?>"
                            data-bs-slide-to="<?php echo (int) $index; ?>"
                            class="<?php echo $index === 0 ? 'active' : ''; ?>"
                            <?php echo $index === 0 ? 'aria-current="true"' : ''; ?>
                            aria-label="<?php echo htmlspecialchars(Text::sprintf('JLIB_HTML_PAGE_CURRENT_OF_TOTAL', $index + 1, count($items)), ENT_QUOTES, 'UTF-8'); ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="carousel-inner">
            <?php foreach ($items as $index => $team) : ?>
                <?php
                $name = (string) ($team['name'] ?? '');
                $link = (string) ($team['link'] ?? '');
                $logoUrl = (string) ($team['logo_url'] ?? '');
                ?>
                <div class="carousel-item<?php echo $index === 0 ? ' active' : ''; ?> text-center py-3">
                    <?php if ($link !== '') : ?>
                        <a href="<?php echo htmlspecialchars($link, ENT_QUOTES, 'UTF-8'); ?>"<?php echo $newWindow ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                    <?php endif; ?>

                    <?php if ($logoUrl !== '') : ?>
                        <img src="<?php echo htmlspecialchars($logoUrl, ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                             title="<?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                             class="d-block mx-auto img-fluid"
                             loading="lazy"
                             style="max-height:<?php echo $height; ?>px;width:auto;">
                    <?php endif; ?>

                    <?php if ($link !== '') : ?></a><?php endif; ?>
                    <div class="mt-2"><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($items) > 1) : ?>
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
