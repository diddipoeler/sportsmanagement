<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;

final class LanguagecharacterField extends ListField
{
    protected $type = 'languagecharacter';

    protected function getOptions(): array
    {
        $tag = Factory::getApplication()->getLanguage()->getTag();
        [$start, $end] = match ($tag) {
            'ru-RU' => [hexdec('0410'), hexdec('042F')],
            'el-GR' => [hexdec('0391'), hexdec('03A9')],
            default => [hexdec('0041'), hexdec('005A')],
        };
        $options = [];

        for ($codepoint = $start; $codepoint <= $end; $codepoint++) {
            $character = html_entity_decode('&#' . $codepoint . ';', ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $options[] = (object) [
                'value' => (string) $codepoint,
                'text' => $character,
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
