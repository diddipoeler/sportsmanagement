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

/** Native Joomla 5/6 administrator controller for countries. */
final class JlextcountriesController extends SportsManagementAdminController
{
    /**
     * Resolve the administrator country model through Joomla MVC.
     *
     * Forwards the requested name, administrator namespace and factory
     * options without discarding additional caller configuration.
     *
     * @param string $name Requested country model name.
     * @param string $prefix Administrator MVC model namespace.
     * @param array $config Joomla model factory options.
     * @return mixed Resolved model or the parent's failure value.
     */
    public function getModel($name = 'Jlextcountry', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Import postal code information for selected countries.
     *
     * Requires a valid Joomla form token and component edit permission,
     * reads the selected country IDs from POST, removes invalid or duplicate
     * IDs and calls the native import model only when it is available.
     * Returns the administrator to the country overview after completion.
     *
     * @return void
     * @throws \RuntimeException If the current user cannot edit countries.
     */
    public function importplz(): void
    {
        $this->checkToken();
        $this->assertWritePermission('core.edit');

        $cid = $this->app->getInput()->post->get('cid', [], 'array');
        $cid = array_values(array_unique(array_filter(array_map('intval', $cid))));
        $model = $this->getModel();

        if (is_object($model)) {
            $model->importplz($cid);
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=jlextcountries');
    }
}
