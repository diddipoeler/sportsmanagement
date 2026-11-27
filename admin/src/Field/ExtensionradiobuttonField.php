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

    /**
     * Render the historical extension radio control using Joomla's switcher layout.
     * Temporarily selects the native radio type and layout, restoring the
     * original field metadata even if the parent renderer fails.
     * @return string Native Joomla radio-switcher markup.
     */
    protected function getInput(): string
    {
        $originalLayout = $this->layout;
        $originalType = $this->type;
        $this->layout = 'joomla.form.field.radio.switcher';
        $this->type = 'radio';

        try {
            return parent::getInput();
        } finally {
            $this->layout = $originalLayout;
            $this->type = $originalType;
        }
    }
}
