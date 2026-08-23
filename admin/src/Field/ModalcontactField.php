<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\Component\Contact\Administrator\Field\Modal\ContactField;

/** Native wrapper for Joomla's current contact modal selector. */
final class ModalcontactField extends ContactField
{
    protected $type = 'Modalcontact';
}
