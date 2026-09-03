<?php
/**
 * Joomla 5/6 default layout for the SportsManagement birthday module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$moduleClass = htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8');
$tableClass = htmlspecialchars((string) $params->get('table_class', 'table'), ENT_QUOTES, 'UTF-8');
$maxDays = trim((string) $params->get('maxdays', ''));

if (!$persons) {
    $notFound = Text::_((string) $params->get('not_found_text', ''));
    if ($maxDays !== '') {
        $notFound = str_replace('%DAYS%', $maxDays, $notFound);
    }
    echo '<p>' . htmlspecialchars($notFound, ENT_QUOTES, 'UTF-8') . '</p>';
    return;
}

if ($mode === 'S') {
    require __DIR__ . '/default_player_sticker.php';
    return;
}

if ($mode === 'J') {
    require __DIR__ . '/jssor.php';
    return;
}

if ($mode === 'L' && (bool) $params->get('show_player_card', 1)) {
    require __DIR__ . '/default_player_card.php';
    return;
}

$moduleId = 'jsm-birthday-' . (int) $module->id;
?>
<div class="<?php echo $moduleClass; ?>" id="<?php echo $moduleId; ?>">
<?php if ($mode === 'B') : ?>
    <div id="<?php echo $moduleId; ?>-carousel" class="carousel slide" data-bs-ride="carousel">
        <?php if (count($persons) > 1) : ?>
            <div class="carousel-indicators">
                <?php foreach ($persons as $index => $person) : ?>
                    <button type="button" data-bs-target="#<?php echo $moduleId; ?>-carousel" data-bs-slide-to="<?php echo (int) $index; ?>"
                            class="<?php echo $index === 0 ? 'active' : ''; ?>"
                            <?php echo $index === 0 ? 'aria-current="true"' : ''; ?>
                            aria-label="<?php echo htmlspecialchars(Text::sprintf('JLIB_HTML_PAGE_CURRENT_OF_TOTAL', $index + 1, count($persons)), ENT_QUOTES, 'UTF-8'); ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="carousel-inner">
            <?php foreach ($persons as $index => $person) : ?>
                <div class="carousel-item<?php echo $index === 0 ? ' active' : ''; ?>">
                    <?php if (!empty($person['picture_url'])) : ?>
                        <img class="d-block mx-auto" src="<?php echo htmlspecialchars($person['picture_url'], ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo htmlspecialchars($person['display_name'], ENT_QUOTES, 'UTF-8'); ?>"
                             loading="lazy"
                             style="max-width:<?php echo max(1, (int) $params->get('picture_width', 250)); ?>px;height:auto">
                    <?php endif; ?>
                    <div class="carousel-caption position-static text-body">
                        <h5><a href="<?php echo htmlspecialchars($person['person_link'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo $person['flag_html']; ?> <?php echo htmlspecialchars($person['display_name'], ENT_QUOTES, 'UTF-8'); ?></a></h5>
                        <p><?php echo $person['birthday_text']; ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($persons) > 1) : ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $moduleId; ?>-carousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('JPREV'); ?></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $moduleId; ?>-carousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('JNEXT'); ?></span>
            </button>
        <?php endif; ?>
    </div>
<?php else : ?>
    <div class="table-responsive">
        <table class="<?php echo $tableClass; ?> align-middle">
            <tbody>
            <?php foreach ($persons as $person) : ?>
                <tr>
                    <td>
                        <a href="<?php echo htmlspecialchars($person['person_link'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo $person['flag_html']; ?> <?php echo htmlspecialchars($person['display_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <?php if (!empty($person['picture_url'])) : ?>
                            <div class="mt-2"><img src="<?php echo htmlspecialchars($person['picture_url'], ENT_QUOTES, 'UTF-8'); ?>"
                                 alt="<?php echo htmlspecialchars($person['display_name'], ENT_QUOTES, 'UTF-8'); ?>"
                                 loading="lazy"
                                 style="max-height:<?php echo max(1, (int) $params->get('picture_height', 50)); ?>px;width:auto"></div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $person['birthday_text']; ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
</div>
