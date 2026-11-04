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
    /**
     * Save modified team-training ordering through the historic task name.
     *
     * Rejects an invalid Joomla session token, compares the posted order
     * against its original values and delegates changed lists to the native
     * parent ordering operation. Otherwise returns to the teams view.
     *
     * @return mixed Parent save result, or true for unchanged order.
     * @throws \RuntimeException When the session token is invalid.
     */
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

    /**
     * Resolve training data models for legacy task and model aliases.
     *
     * Maps the old teamtrainingsdata singular and plural model names to
     * Trainingdata and uses request-independent model loading while
     * retaining the remaining Joomla MVC factory settings.
     *
     * @param string $name Native or historical training model name.
     * @param string $prefix Administrator model namespace.
     * @param array $config Additional factory configuration.
     * @return mixed Native training model or parent failure result.
     */
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
