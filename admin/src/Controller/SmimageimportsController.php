<?php
/**
 * Native Joomla 5/6 list controller for image-package imports.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmimageimportModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

/** Native Joomla 5/6 list controller for image-package imports. */
final class SmimageimportsController extends SportsManagementAdminController
{
    /**
     * Import an administrator-selected image package.
     *
     * Requires a valid Joomla POST token and component core.create access,
     * resolves the native image import model and delegates the import.
     * Reports model errors and returns to the image-import overview.
     *
     * @return void
     * @throws \RuntimeException On invalid token, insufficient permission or missing model.
     */
    /**
     * Import an image package through the native administrator model.
     *
     * Validates Joomla's POST CSRF token and component create permission
     * before resolving SmimageimportModel and starting the import.
     * Reports errors returned by the model and redirects to the list view.
     *
     * @return void
     * @throws \RuntimeException For invalid token, insufficient access or missing model.
     */
    public function import(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        if (!$this->app->getIdentity()->authorise('core.create', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $model = $this->getModel('Smimageimport', 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof SmimageimportModel) {
            throw new \RuntimeException('SmimageimportModel is unavailable.', 500);
        }

        $ok = (bool) $model->import();

        if (!$ok && $model->getError()) {
            $this->app->enqueueMessage($model->getError(), 'warning');
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=smimageimports');
    }

    /**
     * Retrieve the native Joomla administrator image-import model.
     *
     * Defaults to request-independent loading while retaining additional
     * MVC configuration passed by Joomla or a caller.
     *
     * @param string $name Model name, defaulting to Smimageimport.
     * @param string $prefix Administrator model namespace.
     * @param array $config Additional model factory configuration.
     * @return mixed MVC factory model instance or the parent's failure result.
     */
    /**
     * Obtain the administrator image import model through Joomla's MVC factory.
     *
     * Uses request-independent loading by default and retains model factory
     * options explicitly supplied by Joomla or another caller.
     *
     * @param string $name Model name, defaulting to Smimageimport.
     * @param string $prefix Administrator MVC namespace.
     * @param array $config Additional MVC factory configuration.
     * @return mixed Joomla model instance or the parent's failure result.
     */
    public function getModel($name = 'Smimageimport', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, array_replace(['ignore_request' => true], $config));
    }
}
