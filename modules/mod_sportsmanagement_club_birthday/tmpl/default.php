<?php
/**
 * @version    4.24.00
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$escape = static fn(mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

if (!$clubs) {
    $message = (string) $params->get('not_found_text', '');
    $message = str_replace('%DAYS%', (string) max(0, (int) $params->get('maxdays', 0)), $message);
    echo '<p>' . $escape(Text::_($message)) . '</p>';
    return;
}

if ($mode === 'BC') {
    require __DIR__ . '/default_carousel.php';
    return;
}
?>
<div class="<?php echo $escape($params->get('moduleclass_sfx', '')); ?>">
    <div class="row g-3">
        <?php foreach ($clubs as $club) : ?>
            <div class="col-12">
                <article class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title">
                            <a href="<?php echo $escape($club->club_link); ?>">
                                <?php if ((bool) $params->get('show_club_flag', 0)) : ?><?php echo $club->flag_html; ?><?php endif; ?>
                                <?php echo $escape($club->name); ?>
                            </a>
                        </h5>
                        <?php if ((bool) $params->get('show_picture', 1) && $club->picture_url !== '') : ?>
                            <img src="<?php echo $escape($club->picture_url); ?>"
                                 alt="<?php echo $escape($club->name); ?>"
                                 class="img-fluid mb-2"
                                 loading="lazy"
                                 style="max-width:<?php echo max(1, (int) $params->get('picture_width', 120)); ?>px">
                        <?php endif; ?>
                        <div class="card-text"><?php echo $club->birthday_text; ?></div>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</div>
