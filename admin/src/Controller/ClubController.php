<?php
/**
 * @package     SportsManagement
 * @subpackage  com_sportsmanagement
 *
 * Joomla 5/6 migration.
 */

namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

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
            $this->input->post->set('jform', $data);
        }

        return parent::save($key, $urlVar);
    }
}
