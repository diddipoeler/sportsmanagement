<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Optional Kunena category selector used by SportsManagement configuration forms.
 *
 * The Kunena renderer is intentionally kept behind its public compatibility
 * globals so SportsManagement does not acquire a hard package dependency.
 */
final class KunenaCategoryListField extends ListField
{
    protected $type = 'KunenaCategoryList';

    protected function getInput(): string
    {
        if (
            !class_exists('KunenaForum')
            || !method_exists('KunenaForum', 'installed')
            || !\KunenaForum::installed()
        ) {
            return '<a href="index.php?option=com_kunena">PLEASE COMPLETE KUNENA INSTALLATION</a>';
        }

        if (class_exists('KunenaFactory') && method_exists('KunenaFactory', 'loadLanguage')) {
            \KunenaFactory::loadLanguage('com_kunena');
        }

        $attributes = [];
        $size = trim((string) ($this->element['size'] ?? ''));
        $class = trim((string) ($this->element['class'] ?? '')) ?: 'inputbox';

        if ($size !== '') {
            $attributes[] = 'size="' . htmlspecialchars($size, ENT_QUOTES, 'UTF-8') . '"';
        }

        $attributes[] = 'class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '"';

        if (!empty($this->element['multiple'])) {
            $attributes[] = 'multiple="multiple"';
        }

        return (string) HTMLHelper::_(
            'kunenaforum.categorylist',
            $this->name,
            0,
            $this->getOptions(),
            $this->element,
            ' ' . implode(' ', $attributes),
            'value',
            'text',
            $this->value
        );
    }
}
