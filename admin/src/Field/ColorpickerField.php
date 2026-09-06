<?php
/**
 * Native Joomla 5/6 SportsManagement color picker field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ColorField as JoomlaColorField;

/** Joomla 5/6 native replacement for the historical SportsManagement color picker. */
final class ColorpickerField extends JoomlaColorField
{
    protected $type = 'colorpicker';
}
