<?php
/**
 * Native Joomla 5/6 JSM link field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

final class JsmlinkField extends FormField
{
    protected $type = 'JSMLink';

    /**
     * Render an optional administrator resource link with escaped title text.
     *
     * Rejects unsupported URL schemes and relative paths with traversal
     * components before constructing the clickable link or image markup.
     *
     * @return string Safe link label HTML, or plain translated title text.
     */
    protected function getLabel(): string
    {
        $title = trim((string) ($this->element['title'] ?? ''));
        $image = trim((string) ($this->element['imagesrc'] ?? ''));
        $link = trim((string) ($this->element['link'] ?? ''));
        $translatedTitle = Text::_($title);
        $link = self::safeLink($link);
        $image = self::safeLink($image);
        $content = $image !== ''
            ? '<img src="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . '" alt="'
                . htmlspecialchars($translatedTitle, ENT_QUOTES, 'UTF-8') . '">'
            : htmlspecialchars($translatedTitle, ENT_QUOTES, 'UTF-8');

        if ($link === '') {
            return '<div style="overflow: hidden; margin: 5px 0">'
                . htmlspecialchars($translatedTitle, ENT_QUOTES, 'UTF-8') . '</div>';
        }

        return '<div style="overflow: hidden; margin: 5px 0">'
            . '<a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer" title="'
            . htmlspecialchars($translatedTitle, ENT_QUOTES, 'UTF-8') . '">'
            . $content . '</a></div>';
    }

    /**
     * Render the translated explanation for an administrator resource link.
     *
     * Uses a validated link value for optional placeholders and escapes
     * translated text so language overrides cannot inject HTML markup.
     *
     * @return string Display-only link description markup.
     */
    protected function getInput(): string
    {
        $title = trim((string) ($this->element['title'] ?? ''));
        $image = trim((string) ($this->element['imagesrc'] ?? ''));
        $text = trim((string) ($this->element['text'] ?? ''));
        $link = self::safeLink(trim((string) ($this->element['link'] ?? '')));
        $titleInText = (string) ($this->element['titleintext'] ?? '') === 'true';
        $html = '<div style="padding: ' . ($image !== '' ? '5px 0 0 0' : '0') . '; overflow: inherit">';

        if ($titleInText && $title !== '') {
            $html .= '<strong>' . htmlspecialchars(Text::_($title), ENT_QUOTES, 'UTF-8') . '</strong>: ';
        }

        if ($text !== '') {
            $html .= htmlspecialchars(Text::sprintf($text, $link), ENT_QUOTES, 'UTF-8');
        }

        return $html . '</div>';
    }

    /**
     * Validate a resource link without permitting script or data URL schemes.
     *
     * Allows normal HTTP(S) URLs and site-relative paths without traversal,
     * control characters or protocol-relative network destinations.
     *
     * @param string $url Configured administrator link or image path.
     * @return string Allowed URL, or empty text if the address is unsafe.
     */
    private static function safeLink(string $url): string
    {
        if ($url === '' || strlen($url) > 2048 || str_contains($url, chr(92))
            || str_starts_with($url, '//')
            || preg_match('/[\\x00-\\x1F\\x7F]/', $url) === 1
            || preg_match('~(?:^|/)\\.{1,2}(?:/|$)~', $url) === 1) {
            return '';
        }

        if (preg_match('~^[a-z][a-z0-9+.-]*:~i', $url) === 1) {
            return preg_match('~^https?://~i', $url) === 1
                && filter_var($url, FILTER_VALIDATE_URL) !== false
                ? $url
                : '';
        }

        return $url;
    }
}
