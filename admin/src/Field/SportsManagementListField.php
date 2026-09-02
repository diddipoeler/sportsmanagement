<?php
/**
 * Shared Joomla 5/6 list-field base for SportsManagement administrator selectors.
 *
 * @version   5.6.0
 * @author    diddipoeler
 * @copyright Copyright (C) diddipoeler
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;

abstract class SportsManagementListField extends ListField
{
    use SportsManagementDatabaseTrait;
}
