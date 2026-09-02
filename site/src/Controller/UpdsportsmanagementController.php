<?php
/**
 * Joomla 5/6 controller for the small SportsManagement sample record updater.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\UpdsportsmanagementModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use RuntimeException;

/** Joomla 5/6 controller for the small SportsManagement sample record updater. */
final class UpdsportsmanagementController extends BaseController
{
    public function submit(): bool
    {
        if (!Session::checkToken('post')) {
            throw new RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $data = $this->getApplication()
            ->getInput()
            ->post
            ->get('jform', [], 'array');

        if (!class_exists(UpdsportsmanagementModel::class)) {
            require_once JPATH_SITE
                . '/components/com_sportsmanagement/src/Model/UpdsportsmanagementModel.php';
        }

        $updated = (new UpdsportsmanagementModel())->updItem((array) $data);

        echo $updated
            ? '<h2>Updated Greeting has been saved</h2>'
            : '<h2>Updated Greeting failed to be saved</h2>';

        return $updated;
    }
}
