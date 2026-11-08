<?php
/**
 * Safe plain-text renderer for database-backed SportsManagement quotations.
 *
 * @version    5.6.0
 * @author     SportsManagement contributors
 * @copyright  Copyright (C) 2026 SportsManagement contributors
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Module\SportsManagementRquotes\Site\Helper;

\defined('_JEXEC') or die;

/**
 * Prepare untrusted quote text for insertion into a Joomla HTML template.
 *
 * A historical quote may contain markup, which is deliberately treated as
 * plain text except for safe line breaks. Imported HTML attributes and tags
 * cannot therefore execute scripts in public module output.
 */
final class QuoteText
{
    /**
     * Convert database quote values into escaped HTML with safe line breaks.
     *
     * Accepts strings and numbers, translates br tags to newlines, removes
     * other markup and attributes, decodes entities and escapes final text.
     * Arrays and objects are never cast into presentation markup.
     *
     * @param mixed $value Stored quote value from the database.
     * @return string Escaped quote with optional safe br elements.
     */
    public static function render(mixed $value): string
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            return '';
        }

        $text = (string) $value;
        $text = preg_replace('~<br\s*/?>~i', "\n", $text) ?? $text;
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return nl2br(htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'), false);
    }
}
