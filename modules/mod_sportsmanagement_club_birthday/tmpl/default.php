<?php
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (!$clubs) {
    echo '<p>' . htmlspecialchars(Text::_((string) $params->get('not_found_text', '')), ENT_QUOTES, 'UTF-8') . '</p>';
    return;
}

if ($mode === 'BC') {
    require __DIR__ . '/default_carousel.php';
    return;
}

$refreshSeconds = max(0, (int) $params->get('minute', 0));
if ((bool) $params->get('refresh', 0) && $refreshSeconds > 0) : ?>
<script>window.setTimeout(() => window.location.reload(), <?php echo $refreshSeconds * 1000; ?>);</script>
<?php endif; ?>

<div class="<?php echo htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8'); ?>">
    <div class="row g-3<?php echo $mode === 'H' ? ' flex-nowrap overflow-auto' : ''; ?>">
    <?php foreach ($clubs as $club) : ?>
        <div class="<?php echo $mode === 'H' ? 'col-auto' : 'col-12'; ?>">
            <article class="card h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">
                        <a href="<?php echo htmlspecialchars($club->club_link, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php if ((bool) $params->get('show_club_flag', 0)) : ?><?php echo $club->flag_html; ?><?php endif; ?>
                            <?php echo htmlspecialchars((string) $club->name, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </h5>
                    <?php if ((bool) $params->get('show_picture', 1) && $club->picture_url !== '') : ?>
                        <img src="<?php echo htmlspecialchars($club->picture_url, ENT_QUOTES, 'UTF-8'); ?>"
                             alt="<?php echo htmlspecialchars((string) $club->name, ENT_QUOTES, 'UTF-8'); ?>"
                             class="img-fluid mb-2" style="max-width:<?php echo max(1, (int) $params->get('picture_width', 120)); ?>px">
                    <?php endif; ?>
                    <div class="card-text"><?php echo $club->birthday_text; ?></div>
                </div>
            </article>
        </div>
    <?php endforeach; ?>
    </div>
</div>
