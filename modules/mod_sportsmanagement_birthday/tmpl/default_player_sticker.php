<?php
/**
 * Joomla 5/6 sticker layout for the SportsManagement birthday module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$border = (bool) $params->get('border', 1);
$styleParts = [
    'margin:0 0 30px 0',
    'background-color:' . htmlspecialchars((string) $params->get('background_color', '#ffffff'), ENT_QUOTES, 'UTF-8'),
];
if ($border) {
    $styleParts[] = 'border:1px solid ' . htmlspecialchars((string) $params->get('border_color', '#cccccc'), ENT_QUOTES, 'UTF-8');
}
if ((bool) $params->get('border_rounded', 1)) {
    $styleParts[] = 'border-radius:20px';
}
if ((bool) $params->get('border_shadow', 1)) {
    $styleParts[] = 'box-shadow:0 .5rem 1rem rgba(0,0,0,.25)';
}

$cake = $params->get('cake_image', '');
if (is_object($cake)) {
    $cake = (string) ($cake->imagefile ?? '');
}
$cake = preg_replace('/#.*/', '', (string) $cake) ?: '';
?>
<style>
.jsm-birthday-vertical{writing-mode:vertical-rl;transform:rotate(180deg);text-align:center}
</style>
<div class="row g-4">
<?php foreach ($persons as $person) : ?>
    <?php
    $projectSlug = (string) ($person['project_slug'] ?? '');
    $projectName = str_contains($projectSlug, ':') ? substr($projectSlug, strpos($projectSlug, ':') + 1) : $projectSlug;
    ?>
    <div class="col-12">
        <article class="container-fluid p-3" style="<?php echo implode(';', $styleParts); ?>">
            <div class="row align-items-center g-2">
                <?php if ((bool) $params->get('show_team', 1)) : ?>
                    <div class="col-auto jsm-birthday-vertical"><?php echo htmlspecialchars((string) ($person['team_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <div class="col text-center">
                    <h5 style="color:<?php echo htmlspecialchars((string) $params->get('title_color', '#000000'), ENT_QUOTES, 'UTF-8'); ?>">
                        <a href="<?php echo htmlspecialchars($person['person_link'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($person['display_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </h5>
                    <?php if (!empty($person['picture_url'])) : ?>
                        <img src="<?php echo htmlspecialchars($person['picture_url'], ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid mb-2"
                             alt="<?php echo htmlspecialchars($person['display_name'], ENT_QUOTES, 'UTF-8'); ?>" style="max-height:260px">
                    <?php endif; ?>
                    <?php if (!empty($person['position_name'])) : ?>
                        <div><?php echo htmlspecialchars(Text::_((string) $person['position_name']), ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <div style="color:<?php echo htmlspecialchars((string) $params->get('text_color', '#000000'), ENT_QUOTES, 'UTF-8'); ?>;font-size:<?php echo max(1, (int) $params->get('text_size', 16)); ?>px">
                        <?php echo $person['birthday_text']; ?>
                    </div>
                    <?php if ((bool) $params->get('birthday_cake', 0) && $cake !== '') : ?>
                        <img src="<?php echo htmlspecialchars(Uri::root() . ltrim($cake, '/'), ENT_QUOTES, 'UTF-8'); ?>" alt="" class="img-fluid mt-2" style="max-height:64px">
                    <?php endif; ?>
                </div>
                <div class="col-auto jsm-birthday-vertical">
                    <?php echo $person['flag_html']; ?>
                    <?php if ((bool) $params->get('show_project', 1)) : ?><?php echo htmlspecialchars($projectName, ENT_QUOTES, 'UTF-8'); ?><?php endif; ?>
                </div>
            </div>
        </article>
    </div>
<?php endforeach; ?>
</div>
