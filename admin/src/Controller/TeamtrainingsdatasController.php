<?php
/**
 * Native Joomla 5/6 compatibility controller for legacy team training-data list tasks.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

/**
 * Compatibility controller for the historic teamtrainingsdatas.* task namespace.
 */
final class TeamtrainingsdatasController extends SportsManagementAdminController
{
    public function saveorder()
    {
        if (!Session::checkToken()) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $order = (array) $this->input->post->get('order', [], 'array');
        $originalValues = $this->input->post->getString('original_order_values', '');
        $originalOrder = $originalValues === '' ? [] : explode(',', $originalValues);

        $this->view_list = 'teams';

        if ($order !== $originalOrder) {
            return parent::saveorder();
        }

        $this->setRedirect(
            Route::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false)
        );

        return true;
    }

    public function getModel($name = 'Trainingdata', $prefix = 'Administrator', $config = [])
    {
        $normalisedName = strtolower((string) $name);

        if ($normalisedName === '' || in_array($normalisedName, ['teamtrainingsdata', 'teamtrainingsdatas'], true)) {
            $name = 'Trainingdata';
        }

        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
