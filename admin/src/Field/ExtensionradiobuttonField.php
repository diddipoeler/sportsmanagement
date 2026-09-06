<?php
/**
 * Native Joomla 5/6 extension radio-button field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
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
