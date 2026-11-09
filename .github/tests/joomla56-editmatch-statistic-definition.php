<?php
/**
 * Isolated Joomla 5/6 editmatch statistic icon regression coverage.
 *
 * Usage: php .github/tests/joomla56-editmatch-statistic-definition.php
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\HTML {
    /** Minimal image helper capturing the escaped statistic icon dimensions. */
    final class HTMLHelper
    {
        /**
         * Capture height and image path used to render a statistic icon.
         *
         * @param string $method Joomla HTML helper name.
         * @param string $path Resolved statistic image location.
         * @param string $name Statistic display label.
         * @param array<string,mixed> $attributes Image HTML attributes.
         * @return string Serialized values for deterministic test assertions.
         */
        public static function _(string $method, string $path, string $name, array $attributes): string
        {
            return $path . '|' . $attributes['style'];
        }
    }
}

namespace Joomla\CMS\Language {
    /** Minimal identity translation for isolated editmatch rendering tests. */
    final class Text
    {
        /**
         * Return a statistic label without a running Joomla language service.
         *
         * @param string $key Source label.
         * @return string Untranslated label.
         */
        public static function _(string $key): string { return $key; }
    }
}

namespace {
    \defined('_JEXEC') || \define('_JEXEC', 1);
    \defined('JPATH_ADMINISTRATOR') || \define('JPATH_ADMINISTRATOR', '/nonexistent/administrator');

    require_once dirname(__DIR__, 2) . '/site/src/Service/EditmatchStatisticDefinition.php';

    use Diddipoeler\Component\SportsManagement\Site\Service\EditmatchStatisticDefinition;

    $icon = new EditmatchStatisticDefinition((object) [
        'id' => 1, 'name' => 'Goals', 'short' => 'G', 'icon' => 'goals.png',
    ]);

    if (!str_contains($icon->getImage(-100), 'height: 1px')
        || !str_contains($icon->getImage(999999), 'height: 512px')
        || !str_contains($icon->getImage(30), 'height: 30px')) {
        throw new \RuntimeException('Editmatch statistic image height must remain within 1–512 px.');
    }

    $fallback = new EditmatchStatisticDefinition((object) [
        'id' => 2, 'name' => '<name>', 'short' => '<G>', 'icon' => '',
    ]);

    if (!str_contains($fallback->getImage(), '&lt;G&gt;')
        || str_contains($fallback->getImage(), '<G>')) {
        throw new \RuntimeException('Editmatch statistic fallback text must remain escaped.');
    }

    // Invalid class names must not access files outside bundled statistics.
    foreach (['../base', 'base.php', 'base/extra', '..', 'bad\\\\class'] as $invalidClass) {
        $definition = new EditmatchStatisticDefinition((object) [
            'id' => 3,
            'name' => 'Synthetic',
            'short' => 'S',
            'icon' => '',
            'class' => $invalidClass,
            'calculated' => 1,
        ]);

        if ($definition->getCalculated() !== 1) {
            throw new \RuntimeException('Invalid statistic class did not use its fallback.');
        }
    }

    // Two rows using an unavailable class may have different stored flags.
    // Caching the first row's fallback would change the second row's result.
    $missingClass = 'nonexistentstatisticclass987';
    $first = new EditmatchStatisticDefinition((object) [
        'id' => 4, 'class' => $missingClass, 'calculated' => 0,
    ]);
    $second = new EditmatchStatisticDefinition((object) [
        'id' => 5, 'class' => $missingClass, 'calculated' => 1,
    ]);

    if ($first->getCalculated() !== 0 || $second->getCalculated() !== 1) {
        throw new \RuntimeException('A row-specific statistic fallback was cached across rows.');
    }

    echo "Joomla 5/6 editmatch statistic icon validation passed.\n";
}
