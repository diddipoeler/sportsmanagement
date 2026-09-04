<?php
/**
 * Joomla 5/6 native color field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ColorField as JoomlaColorField;

final class ColorField extends JoomlaColorField
{
    protected $type = 'Color';
}
