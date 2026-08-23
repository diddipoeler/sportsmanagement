<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ColorField as JoomlaColorField;

/** Joomla 5/6 native replacement for the historical SportsManagement color picker. */
final class ColorpickerField extends JoomlaColorField
{
    protected $type = 'colorpicker';
}
