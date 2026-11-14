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
     * Import an image archive only for an authorised Joomla administrator.
     *
     * Verifies the POST CSRF token and an existing identity with component
     * create permission before resolving the native model. Delegates the
     * import, replaces potentially sensitive model error text with a
     * translated generic warning and redirects to the import overview.
     *
     * @return void
     * @throws \RuntimeException For CSRF failure, absent permissions or missing model.
     */
    public function import(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }

        $identity = $this->app->getIdentity();

        if ($identity === null || !is_callable([$identity, 'authorise'])
            || !$identity->authorise('core.create', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $model = $this->getModel('Smimageimport', 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof SmimageimportModel) {
            throw new \RuntimeException('SmimageimportModel is unavailable.', 500);
        }

        $ok = (bool) $model->import();

        if (!$ok && $model->getError()) {
            // Custom model/database drivers may supply SQL or credentials in
            // getError(); never render their diagnostic text in Joomla HTML.
            $this->app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'warning');
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=smimageimports');
    }

    /**
     * Obtain the native administrator image-import model through Joomla MVC.
     *
     * Applies request-independent loading by default while retaining
     * explicitly provided Joomla model factory options and the caller's
     * requested model name and namespace.
     *
     * @param string $name Requested image import model.
     * @param string $prefix Administrator MVC model namespace.
     * @param array<string,mixed> $config Additional factory configuration.
     * @return mixed Joomla MVC model or the parent's unavailable result.
     */
    public function getModel($name = 'Smimageimport', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, array_replace(['ignore_request' => true], $config));
    }
}
