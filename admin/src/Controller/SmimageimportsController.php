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

    public function getModel($name = 'Smimageimport', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, ['ignore_request' => true]);
    }
}
