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

/** Native Joomla 5/6 administrator controller for associations. */
final class JlextassociationsController extends SportsManagementAdminController
{
    /**
     * Resolve an association administrator model through Joomla MVC.
     *
     * @param string $name Requested association model name.
     * @param string $prefix Joomla administrator MVC factory namespace.
     * @param array $config Optional factory configuration.
     * @return mixed Instantiated association model or parent failure.
     */
    public function getModel($name = 'Jlextassociation', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Import only missing configured associations after authorisation.
     *
     * Requires a valid POST token and core.create permission on the
     * SportsManagement component before invoking the non-destructive
     * model importer, and then returns to the association overview.
     *
     * @return void
     * @throws \RuntimeException If the request token or permission is invalid.
     */
    public function import(): void
    {
        $this->checkToken();
        $this->assertWritePermission('core.create');

        $model = parent::getModel('Jlextassociations', 'Administrator', ['ignore_request' => false]);

        if ($model !== false) {
            $model->checkAssociations();
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=jlextassociations');
    }
}
