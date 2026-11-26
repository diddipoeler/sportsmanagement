<?php
/**
 * Joomla 5/6 modal and popup URL safety regression coverage.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Joomla\CMS\HTML {
    final class HTMLHelper
    {
        public static array $options = [];
        public static int $calls = 0;

        /**
         * Capture the native modal call and its URL configuration.
         * @param string $name Joomla HTML helper operation.
         * @param string $target Modal identifier.
         * @param array $options Modal settings.
         * @return string Stub modal markup.
         */
        public static function _(string $name, string $target, array $options): string
        {
            if ($name !== 'bootstrap.renderModal') {
                throw new \RuntimeException('Unexpected Joomla HTML helper.');
            }

            ++self::$calls;
            self::$options = $options;

            return '<div data-test-modal></div>';
        }
    }
}

namespace Joomla\CMS\Language {
    final class Text
    {
        /**
         * Return untranslated language keys for stable assertions.
         * @param string $key Language key.
         * @return string Unchanged key.
         */
        public static function _(string $key): string
        {
            return $key;
        }
    }
}

namespace {
    define('_JEXEC', 1);
    require_once dirname(__DIR__, 2) . '/site/src/Helper/ModalImageHelper.php';

    $modal = \Diddipoeler\Component\SportsManagement\Site\Helper\ModalImageHelper::class;
    $html = \Joomla\CMS\HTML\HTMLHelper::class;

    foreach ([
        'javascript:alert(1)',
        'data:text/html,evil',
        'vbscript:msgbox(1)',
        '//external.example/escape',
        'http://user:pass@example.org/image',
        'https://user@example.org/path',
        '%2f%2fexternal.example/escape',
        '%256aavascript%253aalert(1)',
        'images/%252e%252e/private.png',
        'images/%2500.png',
        "https://example.org/\npath",
        'https:\evil.example\path',
    ] as $unsafe) {
        foreach ([0, 1, 2] as $mode) {
            $html::$calls = 0;
            $output = $modal::render(
                target: 'test-modal',
                picture: 'images/club.png',
                text: 'Sample',
                url: $unsafe,
                mode: $mode
            );

            if (str_contains($output, '<a ')
                || $html::$calls !== 0
                || !str_contains($output, 'src="images/club.png"')) {
                throw new \RuntimeException('Unsafe modal URL was rendered as a link: ' . $unsafe);
            }
        }

        $output = $modal::render(picture: $unsafe, mode: 2);

        if ($output !== '') {
            throw new \RuntimeException('Unsafe image source bypassed validation.');
        }
    }

    $html::$calls = 0;
    $output = $modal::render(
        target: 'club-dialog',
        picture: 'images/club.png',
        text: 'A & B',
        url: 'index.php?option=com_sportsmanagement&view=clubinfo',
        mode: 0
    );

    if ($html::$calls !== 1
        || ($html::$options['url'] ?? null) !== 'index.php?option=com_sportsmanagement&view=clubinfo'
        || !str_contains($output, 'A &amp; B')
        || !str_contains($output, 'data-test-modal')) {
        throw new \RuntimeException('Normal Joomla Bootstrap image modal was broken.');
    }

    $output = $modal::render(
        picture: 'images/club.png',
        url: 'https://sports.example/club',
        mode: 1
    );

    if (!str_contains($output, 'href="https://sports.example/club"')
        || !str_contains($output, 'data-jsm-popup')) {
        throw new \RuntimeException('Valid HTTPS popup was rejected.');
    }

    $output = $modal::render(picture: 'images/club.png', mode: 2);

    if (!str_contains($output, 'href="images/club.png"')) {
        throw new \RuntimeException('Legacy image-only lightbox was rejected.');
    }

    $html::$calls = 0;
    $route = 'index.php?option=com_sportsmanagement&view=clubinfo&name=A%20B#details';
    $output = $modal::render(target: 'route-test', picture: 'images/club%20logo.png', url: $route);
    if ($html::$calls !== 1 || ($html::$options['url'] ?? null) !== $route
        || !str_contains($output, 'src="images/club%20logo.png"')) {
        throw new \RuntimeException('Joomla query parameters or encoded image names were rejected.');
    }

    echo "Joomla 5/6 modal URL validation passed.\n";
}
