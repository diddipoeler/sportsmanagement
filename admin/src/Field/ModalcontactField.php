<?php
/**
 * Native Joomla 5/6 contact modal field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\Component\Contact\Administrator\Field\Modal\ContactField;

/** Native wrapper for Joomla's current contact modal selector. */
final class ModalcontactField extends ContactField
{
    protected $type = 'Modalcontact';
}
