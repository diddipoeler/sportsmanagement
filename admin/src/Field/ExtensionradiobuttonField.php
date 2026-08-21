<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\RadioField;

final class ExtensionradiobuttonField extends RadioField
{
    protected $type = 'ExtensionRadioButton';

    protected function getInput(): string
    {
        $this->layout = 'joomla.form.field.radio.switcher';
        $this->type = 'radio';

        return parent::getInput();
    }
}
