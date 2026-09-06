<?php
/**
 * Native Joomla 5/6 geocomplete field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\TextField;

/**
 * Read-only location summary field used by club and playground forms.
 *
 * Geocoding itself is handled by component services; this field preserves the
 * historic form type as a native Joomla field and displays provider attribution.
 */
final class GeocompleteField extends TextField
{
    protected $type = 'Geocomplete';

    protected function getInput(): string
    {
        return parent::getInput()
            . '<div class="form-text">Geocoding &copy; '
            . '<a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">'
            . 'OpenStreetMap contributors</a></div>';
    }
}
