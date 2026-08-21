<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class SywonlinehelpField extends FormField
{
    protected $type = 'SYWOnlineHelp';

    protected function getLabel(): string
    {
        HTMLHelper::_('stylesheet', 'syw/fonts-min.css', ['version' => 'auto', 'relative' => true]);

        $title = trim((string) ($this->element['label'] ?? $this->element['title'] ?? ''));
        $heading = strtolower(trim((string) ($this->element['heading'] ?? 'h4')));
        $heading = in_array($heading, ['h2', 'h3', 'h4', 'h5', 'h6'], true) ? $heading : 'h4';
        $description = trim((string) ($this->element['description'] ?? ''));
        $url = trim((string) ($this->element['url'] ?? ''));
        $class = trim((string) $this->class);
        $html = '<div' . ($class !== '' ? ' class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"' : '') . '>';

        if ($title !== '') {
            $html .= '<' . $heading . '>' . htmlspecialchars(Text::_($title), ENT_QUOTES, 'UTF-8') . '</' . $heading . '>';
        }

        $html .= '<div class="d-flex align-items-center justify-content-between gap-3">';

        if ($description !== '') {
            $html .= '<div>' . Text::_($description) . '</div>';
        }

        if ($url !== '') {
            $html .= '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8')
                . '" target="_blank" rel="noopener noreferrer" class="btn btn-info btn-sm" aria-label="'
                . htmlspecialchars(Text::_('JHELP'), ENT_QUOTES, 'UTF-8') . '"><i class="SYWicon-local-library" aria-hidden="true"></i></a>';
        }

        return $html . '</div></div>';
    }

    protected function getInput(): string
    {
        return '';
    }
}
