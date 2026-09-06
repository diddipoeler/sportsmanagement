<?php
/**
 * Native Joomla 5/6 JLG color field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ColorField;

final class JlgcolorField extends ColorField
{
    protected $type = 'JLGColor';
}
