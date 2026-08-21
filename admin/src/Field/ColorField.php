<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ColorField as JoomlaColorField;

final class ColorField extends JoomlaColorField
{
    protected $type = 'Color';
}
