<?php
/**
 * SportsManagement image-select form field.
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Uri\Uri;

class JFormFieldImageSelect extends FormField
{
    protected $type = 'imageselect';

    protected function getInput()
    {
        $helperFile = JPATH_SITE . '/components/com_sportsmanagement/helpers/imageselect.php';

        if (!class_exists('ImageSelectSM', false) && is_file($helperFile)) {
            require_once $helperFile;
        }

        if (!class_exists('ImageSelectSM', false)) {
            return '';
        }

        $app = Factory::getApplication();
        $app->getInput()->getCmd('option', 'com_sportsmanagement');
        $default = (string) $this->value;
        $targetFolder = (string) ($this->element['targetfolder'] ?? '');
        $output = \ImageSelectSM::getSelector(
            $this->name,
            $this->name . '_preview',
            $targetFolder,
            $this->value,
            $default,
            $this->name,
            $this->id
        );

        $output .= '<img class="imagepreview" src="'
            . htmlspecialchars(Uri::root(true) . '/media/com_sportsmanagement/jl_images/spinner.gif', ENT_QUOTES, 'UTF-8')
            . '" name="' . htmlspecialchars($this->name . '_preview', ENT_QUOTES, 'UTF-8')
            . '" id="' . htmlspecialchars($this->id . '_preview', ENT_QUOTES, 'UTF-8')
            . '" alt="Preview" title="Preview" />';
        $output .= '<input type="hidden" id="original_' . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8')
            . '" name="original_' . htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8')
            . '" value="' . htmlspecialchars((string) $this->value, ENT_QUOTES, 'UTF-8') . '" />';
        $output .= '<input type="hidden" id="copy_' . htmlspecialchars($this->id, ENT_QUOTES, 'UTF-8')
            . '" name="copy_' . htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8')
            . '" value="' . htmlspecialchars((string) $this->value, ENT_QUOTES, 'UTF-8') . '" />';

        return $output;
    }
}
