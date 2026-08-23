<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Service\PlaygroundGeocoder;

/** Native Joomla 5/6 form controller for a playground. */
final class PlaygroundController extends SportsManagementFormController
{
    public function save($key = null, $urlVar = null)
    {
        $data = $this->input->post->get('jform', [], 'array');

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
