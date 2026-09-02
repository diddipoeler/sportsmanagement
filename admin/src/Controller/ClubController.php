<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\LocationHelper;

/**
 * Controller for editing a club.
 */
class ClubController extends SportsManagementFormController
{
    public function save($key = null, $urlVar = null)
    {
        $input = $this->input;
        $data = $input->post->get('jform', [], 'array');
        $extended = $input->post->get('extended', [], 'array');

        if (($data['country'] ?? '') === 'DDR') {
            $data['country'] = 'DEU';
        }

        $addressParts = array_values(array_filter([
            trim((string) ($data['address'] ?? '')),
            trim(trim((string) ($data['zipcode'] ?? '')) . ' ' . trim((string) ($data['location'] ?? ''))),
            trim((string) ($data['country'] ?? '')),
        ], static fn (string $value): bool => $value !== ''));

        if ($addressParts) {
            $resolved = (new LocationHelper())->resolveDetailed(implode(', ', $addressParts));

            if (isset($resolved['latitude'], $resolved['longitude'])) {
                $data['latitude'] = (string) $resolved['latitude'];
                $data['longitude'] = (string) $resolved['longitude'];
            }

            $address = isset($resolved['address']) && is_array($resolved['address'])
                ? $resolved['address']
                : [];

            if ($address) {
                $state = strtolower((string) ($address['country_code'] ?? '')) === 'gb'
                    ? (string) ($address['county'] ?? '')
                    : (string) ($address['state'] ?? '');

                if ($state === '') {
                    $state = (string) ($address['state_district'] ?? '');
                }

                if ($state !== '') {
                    $data['state'] = $state;
                }

                $extended = $this->mergeAddressDetails($extended, $address);
            }
        }

        $input->post->set('jform', $data);
        $input->post->set('extended', $extended);

        return parent::save($key, $urlVar);
    }

    /** @param array<string, mixed> $extended @param array<string, string> $address */
    private function mergeAddressDetails(array $extended, array $address): array
    {
        $first = static function (array $keys) use ($address): string {
            foreach ($keys as $key) {
                $value = trim((string) ($address[$key] ?? ''));

                if ($value !== '') {
                    return $value;
                }
            }

            return '';
        };

        $mapping = [
            'COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_LONG_NAME' => ['county'],
            'COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_1_SHORT_NAME' => ['state_district'],
            'COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_LONG_NAME' => ['suburb'],
            'COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_2_SHORT_NAME' => ['quarter'],
            'COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_LONG_NAME' => ['region'],
            'COM_SPORTSMANAGEMENT_ADMINISTRATIVE_AREA_LEVEL_3_SHORT_NAME' => ['city_district'],
            'COM_SPORTSMANAGEMENT_LOCALITY_LONG_NAME' => ['city', 'town', 'village'],
            'COM_SPORTSMANAGEMENT_SUBLOCALITY_LONG_NAME' => ['suburb', 'neighbourhood'],
            'COM_SPORTSMANAGEMENT_OSM_LEISURE' => ['leisure'],
            'COM_SPORTSMANAGEMENT_OSM_HOUSE_NUMBER' => ['house_number'],
            'COM_SPORTSMANAGEMENT_OSM_ROAD' => ['road'],
            'COM_SPORTSMANAGEMENT_OSM_VILLAGE' => ['village'],
            'COM_SPORTSMANAGEMENT_OSM_MUNICIPALITY' => ['municipality'],
            'COM_SPORTSMANAGEMENT_OSM_COUNTY' => ['county'],
            'COM_SPORTSMANAGEMENT_OSM_STATE' => ['state'],
            'COM_SPORTSMANAGEMENT_OSM_INDUSTRIAL' => ['industrial'],
            'COM_SPORTSMANAGEMENT_OSM_BUILDING' => ['building'],
            'COM_SPORTSMANAGEMENT_OSM_QUARTER' => ['quarter'],
            'COM_SPORTSMANAGEMENT_OSM_SUBURB' => ['suburb'],
            'COM_SPORTSMANAGEMENT_OSM_CITY_DISTRICT' => ['city_district'],
            'COM_SPORTSMANAGEMENT_OSM_CITY' => ['city'],
            'COM_SPORTSMANAGEMENT_OSM_TOWN' => ['town'],
            'COM_SPORTSMANAGEMENT_OSM_HAMLET' => ['hamlet'],
            'COM_SPORTSMANAGEMENT_OSM_NEIGHBOURHOOD' => ['neighbourhood'],
        ];

        foreach ($mapping as $field => $keys) {
            $value = $first($keys);

            if ($value !== '') {
                $extended[$field] = $value;
            }
        }

        return $extended;
    }
}
