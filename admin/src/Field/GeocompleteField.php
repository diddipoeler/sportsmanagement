<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\TextField;

/**
 * Read-only location summary field used by club and playground forms.
 *
 * Geocoding itself is handled by the component's JavaScript/model services;
 * this field only preserves the historic form type as a native Joomla field.
 */
final class GeocompleteField extends TextField
{
    protected $type = 'Geocomplete';
}
