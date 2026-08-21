<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

final class JsmlinkField extends FormField
{
    protected $type = 'JSMLink';

    protected function getLabel(): string
    {
        $title = trim((string) ($this->element['title'] ?? ''));
        $image = trim((string) ($this->element['imagesrc'] ?? ''));
        $link = trim((string) ($this->element['link'] ?? ''));
        $translatedTitle = Text::_($title);
        $content = $image !== ''
            ? '<img src="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . '" alt="'
                . htmlspecialchars($translatedTitle, ENT_QUOTES, 'UTF-8') . '">'
            : htmlspecialchars($translatedTitle, ENT_QUOTES, 'UTF-8');

        return '<div style="overflow: hidden; margin: 5px 0">'
            . '<a href="' . htmlspecialchars($link, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer" title="'
            . htmlspecialchars($translatedTitle, ENT_QUOTES, 'UTF-8') . '">'
            . $content . '</a></div>';
    }

    protected function getInput(): string
    {
        $title = trim((string) ($this->element['title'] ?? ''));
        $image = trim((string) ($this->element['imagesrc'] ?? ''));
        $text = trim((string) ($this->element['text'] ?? ''));
        $link = trim((string) ($this->element['link'] ?? ''));
        $titleInText = (string) ($this->element['titleintext'] ?? '') === 'true';
        $html = '<div style="padding: ' . ($image !== '' ? '5px 0 0 0' : '0') . '; overflow: inherit">';

        if ($titleInText && $title !== '') {
            $html .= '<strong>' . htmlspecialchars(Text::_($title), ENT_QUOTES, 'UTF-8') . '</strong>: ';
        }

        if ($text !== '') {
            $html .= Text::sprintf($text, $link);
        }

        return $html . '</div>';
    }
}
