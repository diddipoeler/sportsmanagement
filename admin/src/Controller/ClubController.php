<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration.
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\LocationHelper;
use Joomla\Registry\Registry;

/**
 * Controller for editing a club.
 */
class ClubController extends SportsManagementFormController
{
    public function save($key = null, $urlVar = null)
    {
        $data = $this->input->post->get('jform', [], 'array');
        $extended = $this->input->post->get('extended', null, 'array');

        if (is_array($extended)) {
            $registry = new Registry();
            $registry->loadArray($extended);
            $data['extended'] = $registry->toString();
        }

        if (($data['country'] ?? '') === 'DDR') {
            $data['country'] = 'DEU';
        }

        $addressParts = array_values(array_filter([
            trim((string) ($data['address'] ?? '')),
            trim(trim((string) ($data['zipcode'] ?? '')) . ' ' . trim((string) ($data['location'] ?? ''))),
            trim((string) ($data['country'] ?? '')),
        ], static fn (string $value): bool => $value !== ''));

        if ($addressParts) {
            $coordinates = (new LocationHelper())->resolve(implode(', ', $addressParts));

            if (isset($coordinates['latitude'], $coordinates['longitude'])) {
                $data['latitude'] = $coordinates['latitude'];
                $data['longitude'] = $coordinates['longitude'];
            }
        }

        $this->input->post->set('jform', $data);

        return parent::save($key, $urlVar);
    }
}
