<?php
/**
 * Joomla 5/6 layout for the SportsManagement playground ticker module.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$playgrounds = is_array($playgrounds ?? null) ? $playgrounds : [];

if (!$playgrounds) {
    echo Text::_('MOD_SPORTSMANAGEMENT_PLAYGROUND_TICKER_NO_PLAYGROUND');
    return;
}

$escape = static fn (mixed $value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$mode = strtoupper((string) $params->get('mode', 'L'));
$pictureWidth = max(30, min(1200, (int) $params->get('picture_width', 150)));
$pictureServer = rtrim((string) ($module->picture_server ?? ''), '/\\');

$normaliseColour = static function (mixed $value, string $fallback): string {
    $value = trim((string) $value);

    return preg_match('/^#[0-9a-fA-F]{3,8}$/', $value) ? $value : $fallback;
};

$imageUrl = static function (object $playground) use ($pictureServer): string {
    $path = trim((string) ($playground->picture ?? ''));

    if ($path === '') {
        return rtrim((string) Uri::root(), '/')
            . '/images/com_sportsmanagement/database/placeholders/placeholder_150.png';
    }

    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }

    $base = $pictureServer !== '' ? $pictureServer : rtrim((string) Uri::root(), '/');

    return $base . '/' . ltrim(str_replace('\\', '/', $path), '/');
};

$surface = static function (object $playground): string {
    $extended = trim((string) ($playground->extended ?? ''));
    if ($extended === '') {
        return '';
    }

    $decoded = json_decode($extended, true);
    if (!is_array($decoded)) {
        return '';
    }

    $value = trim((string) ($decoded['COM_SPORTSMANAGEMENT_EXT_PLAYGROUND_GROUND'] ?? ''));

    return match ($value) {
        'Naturrasen' => Text::_('COM_SPORTSMANAGEMENT_ST_PLAYGROUND_NATURAL_GRASS'),
        'Kunstrasen' => Text::_('COM_SPORTSMANAGEMENT_ST_PLAYGROUND_SYNTHETIC_GRASS'),
        'Hyprid-Rasen' => Text::_('COM_SPORTSMANAGEMENT_ST_PLAYGROUND_HYBRID_GRASS'),
        'TennenHartplatz', 'Grand' => Text::_('COM_SPORTSMANAGEMENT_ST_PLAYGROUND_GRAND'),
        'Gummiplatz' => Text::_('COM_SPORTSMANAGEMENT_ST_PLAYGROUND_RUBBERIZED_COURT'),
        default => $value,
    };
};

$details = static function (object $playground) use ($params, $surface): array {
    $rows = [];

    if ((int) $params->get('club', 0) === 1 && trim((string) ($playground->club_name ?? '')) !== '') {
        $rows[] = [Text::_('MOD_SPORTSMANAGEMENT_PLAYGROUND_TICKER_FORE_CLUB'), (string) $playground->club_name];
    }

    if ((int) $params->get('capacity', 0) === 1 && trim((string) ($playground->max_visitors ?? '')) !== '') {
        $rows[] = [Text::_('MOD_SPORTSMANAGEMENT_PLAYGROUND_TICKER_FORE_CAPACITY'), (string) $playground->max_visitors];
    }

    if ((int) $params->get('address', 0) === 1) {
        $address = trim(implode(' ', array_filter([
            trim((string) ($playground->address ?? '')),
            trim((string) ($playground->zipcode ?? '')),
            trim((string) ($playground->city ?? '')),
        ], static fn (string $part): bool => $part !== '')));

        if ($address !== '') {
            $rows[] = [Text::_('MOD_SPORTSMANAGEMENT_PLAYGROUND_TICKER_FORE_ADDRESS'), $address];
        }
    }

    if ((int) $params->get('gps_coor', 0) === 1) {
        $latitude = trim((string) ($playground->latitude ?? ''));
        $longitude = trim((string) ($playground->longitude ?? ''));

        if ($latitude !== '' || $longitude !== '') {
            $rows[] = [Text::_('MOD_SPORTSMANAGEMENT_PLAYGROUND_TICKER_FORE_GPS'), trim($latitude . ', ' . $longitude, ', ')];
        }
    }

    if ((int) $params->get('web', 0) === 1 && trim((string) ($playground->website ?? '')) !== '') {
        $rows[] = [Text::_('MOD_SPORTSMANAGEMENT_PLAYGROUND_TICKER_FORE_WEB'), (string) $playground->website];
    }

    if ((int) $params->get('field_type', 0) === 1) {
        $surfaceName = $surface($playground);
        if ($surfaceName !== '') {
            $rows[] = [Text::_('MOD_SPORTSMANAGEMENT_PLAYGROUND_TICKER_FORE_SURFACE'), $surfaceName];
        }
    }

    return $rows;
};

$renderContent = static function (
    object $playground,
    bool $compact = false,
    bool $showTitle = true
) use (
    $params,
    $escape,
    $imageUrl,
    $pictureWidth,
    $details
): void {
    $name = trim((string) ($playground->playground_name ?? $playground->name ?? ''));
    $showName = $showTitle && (int) $params->get('name', 0) === 1 && $name !== '';
    ?>
    <div class="jsm-playground-ticker-item">
        <?php if ($showName) : ?>
            <h5 class="mb-2"><?php echo $escape($name); ?></h5>
        <?php endif; ?>

        <img
            src="<?php echo $escape($imageUrl($playground)); ?>"
            class="img-fluid mb-2"
            alt="<?php echo $escape($name); ?>"
            width="<?php echo $pictureWidth; ?>"
            loading="lazy"
        >

        <?php foreach ($details($playground) as [$label, $value]) : ?>
            <div class="<?php echo $compact ? 'small' : ''; ?>">
                <strong><?php echo $escape($label); ?>:</strong>
                <?php echo $escape($value); ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
};

if ($mode === 'B') {
    $carouselId = 'jsm-playground-ticker-' . (int) ($module->id ?? 0);
    ?>
    <div id="<?php echo $escape($carouselId); ?>" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach ($playgrounds as $index => $playground) : ?>
                <div class="carousel-item<?php echo $index === 0 ? ' active' : ''; ?>">
                    <div class="card">
                        <div class="card-body">
                            <?php $renderContent($playground, true); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (count($playgrounds) > 1) : ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $escape($carouselId); ?>" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden"><?php echo $escape(Text::_('JPREV')); ?></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $escape($carouselId); ?>" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden"><?php echo $escape(Text::_('JNEXT')); ?></span>
            </button>
        <?php endif; ?>
    </div>
    <?php
    return;
}

$border = (int) $params->get('border', 1) === 1;
$rounded = (int) $params->get('border_rounded', 1) === 1;
$shadow = (int) $params->get('border_shadow', 1) === 1;
$backgroundColour = $normaliseColour($params->get('background_color', '#eeeeee'), '#eeeeee');
$borderColour = $normaliseColour($params->get('border_color', '#41008a'), '#41008a');
$textColour = $normaliseColour($params->get('text_color', '#000000'), '#000000');
$titleColour = $normaliseColour($params->get('title_color', '#000000'), '#000000');
$textSize = max(8, min(72, (int) $params->get('text_size', 14)));
$titleSize = max(10, min(96, (int) $params->get('title_size', 18)));

foreach ($playgrounds as $playground) {
    $style = [
        'background-color:' . $backgroundColour,
        'color:' . $textColour,
        'font-size:' . $textSize . 'px',
    ];

    if ($border) {
        $style[] = 'border:1px solid ' . $borderColour;
    }
    if ($rounded) {
        $style[] = 'border-radius:1rem';
    }
    if ($shadow) {
        $style[] = 'box-shadow:0 .5rem 1rem rgba(0,0,0,.15)';
    }
    ?>
    <div class="container-fluid mb-4 p-3" style="<?php echo $escape(implode(';', $style)); ?>">
        <?php if ((int) $params->get('name', 0) === 1) : ?>
            <div class="fw-semibold mb-2" style="<?php echo $escape('color:' . $titleColour . ';font-size:' . $titleSize . 'px'); ?>">
                <?php echo $escape((string) ($playground->playground_name ?? $playground->name ?? '')); ?>
            </div>
        <?php endif; ?>
        <?php $renderContent($playground, false, false); ?>
    </div>
    <?php
}
