<?php
/**
 * Render linked preview images for native Joomla 5/6 site views.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class ModalImageHelper
{
    /**
     * Render an image with optional Joomla, JCE or popup linking.
     * Validates image and target URLs, escapes HTML attributes and limits
     * modal dimensions to positive values before building markup.
     * @param string $target Optional modal element identifier.
     * @param string $picture Image URL or site-relative path.
     * @param string $text Image alt text and popup title.
     * @param int|string $pictureHeight Displayed image height.
     * @param string $url Optional link destination.
     * @param int|string $width Modal width.
     * @param int|string $height Modal height.
     * @param int $mode Image popup rendering mode.
     * @param string $schemaAttribute Image schema attribute name.
     * @param string $schemaValue Schema attribute value.
     * @return string Safe image or linked modal markup.
     */
    public static function render(
        string $target = '',
        string $picture = '',
        string $text = '',
        int|string $pictureHeight = 20,
        string $url = '',
        int|string $width = 100,
        int|string $height = 200,
        int $mode = 0,
        string $schemaAttribute = 'itemprop',
        string $schemaValue = 'logo'
    ): string {
        $targetEscaped = self::escape($target);
        $picture = self::safeUrl($picture);
        $resolvedUrl = self::safeUrl($url !== '' ? $url : $picture);
        $pictureEscaped = self::escape($picture);
        $textEscaped = self::escape($text);
        $urlEscaped = self::escape($resolvedUrl);
        $width = max(1, (int) $width);
        $height = max(1, (int) $height);
        $pictureHeight = max(1, (int) $pictureHeight);
        $schemaAttribute = preg_match('/^[A-Za-z_:][A-Za-z0-9:._-]*$/', $schemaAttribute)
            ? $schemaAttribute
            : 'itemprop';
        $schemaValueEscaped = self::escape($schemaValue);
        $image = '<img ' . $schemaAttribute . '="' . $schemaValueEscaped . '" src="'
            . $pictureEscaped . '" alt="' . $textEscaped
            . '" style="width: auto;height: ' . $pictureHeight . 'px" />';

        // Do not render a clickable popup or pass a dangerous URI into
        // Joomla's Bootstrap modal configuration.
        if ($resolvedUrl === '') {
            return $image;
        }

        if ($mode === 2) {
            if ($url !== '') {
                return '<a class="jcepopup" title="' . $textEscaped . '" href="' . $urlEscaped
                    . '" data-mediabox-width="" data-mediabox-height="" target="" data-mediabox-title="'
                    . $textEscaped . '">' . $image . '</a>';
            }

            return '<a class="jcepopup jcemediabox-image" title="' . $textEscaped . '" href="'
                . $pictureEscaped . '" data-mediabox="1" data-mediabox-title="' . $textEscaped . '">'
                . $image . '</a>';
        }

        if ($mode === 1) {
            return '<a id="' . $targetEscaped . '" href="' . $urlEscaped
                . '" target="SingleSecondaryWindowName" data-jsm-popup'
                . ' data-jsm-popup-width="' . $width . '" data-jsm-popup-height="' . $height
                . '" title="' . $textEscaped . '">' . $image . '</a>';
        }

        $output = '<a href="#' . $targetEscaped . '" title="' . $textEscaped
            . '" data-bs-toggle="modal">' . $image . '</a>';
        $output .= HTMLHelper::_(
            'bootstrap.renderModal',
            $target,
            [
                'title' => $text,
                'url' => $resolvedUrl,
                'height' => $height,
                'width' => $width,
                'footer' => '<button type="button" class="btn btn-default" data-bs-dismiss="modal">'
                    . Text::_('JCANCEL') . '</button>',
            ]
        );

        return $output;
    }

    /**
     * Allow normal site-relative URLs and absolute HTTP(S) URLs. Reject
     * executable URI schemes, protocol-relative targets and control bytes.
     */
    private static function safeUrl(string $value): string
    {
        if ($value === ''
            || preg_match('/[\x00-\x20\x7F]/', $value)
            || str_contains($value, chr(92))
            || str_starts_with($value, '//')) {
            return '';
        }

        if (preg_match('~^https?://~i', $value)) {
            $parts = parse_url($value);

            return filter_var($value, FILTER_VALIDATE_URL) !== false
                && is_array($parts)
                && !empty($parts['host'])
                && !isset($parts['user'])
                && !isset($parts['pass'])
                    ? $value
                    : '';
        }

        return preg_match('~^[a-z][a-z0-9+.-]*:~i', $value) ? '' : $value;
    }

    /**
     * Escape text for safe insertion into HTML attributes.
     * @param string $value Attribute or label text.
     * @return string HTML-escaped text.
     */
    private static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
