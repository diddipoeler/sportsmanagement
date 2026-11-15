<?php
/**
 * Joomla 5/6 SportsManagement administrator controller.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\PlaygroundGeocoder;

/** Native Joomla 5/6 form controller for a playground. */
final class PlaygroundController extends SportsManagementFormController
{
    /**
     * Save a playground with optional location enrichment.
     *
     * Reads the submitted Joomla form and asks the location geocoder for
     * state and coordinates when possible. Merges only populated values
     * into the form before delegating persistence to the shared controller.
     * Joomla's CSRF and edit/create checks run before any geocoding request,
     * and a geocoder failure never blocks a permitted save.
     *
     * @param string|null $key Optional legacy record key.
     * @param string|null $urlVar Optional URL record identifier.
     * @return mixed Result of the parent Joomla form save operation.
     */
    public function save($key = null, $urlVar = null)
    {
        $data = $this->input->post->get('jform', [], 'array');

        // A remote geocoder must not run for an invalid CSRF token or a
        // user who cannot create/edit the requested playground record.
        $this->assertFormSaveAccess($data, $key);

        if ($data) {
            try {
                $result = (new PlaygroundGeocoder($this->jsmdb))->geocode((object) $data);

                if ($result !== null) {
                    if ($result['state'] !== '') {
                        $data['state'] = $result['state'];
                    }

                    if ($result['latitude'] !== null) {
                        $data['latitude'] = $result['latitude'];
                    }

                    if ($result['longitude'] !== null) {
                        $data['longitude'] = $result['longitude'];
                    }
                }
            } catch (\Throwable) {
                // External geocoding must never prevent saving the playground.
            }

            $this->input->post->set('jform', $data);
        }

        return parent::save($key, $urlVar);
    }
}
