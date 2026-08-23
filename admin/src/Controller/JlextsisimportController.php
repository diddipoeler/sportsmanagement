<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/** Native Joomla 5/6 controller for the SIS import workflow. */
final class JlextsisimportController extends BaseController
{
    public function save()
    {
        $this->checkToken();

        $app = Factory::getApplication();
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement') ?: 'com_sportsmanagement';
        $model = $this->getLegacyImportModel();
        $model->getData();

        $this->setRedirect(
            'index.php?option=' . $option . '&view=jlxmlimports&task=jlxmlimport.edit'
        );

        return true;
    }

    private function getLegacyImportModel(): object
    {
        if (!class_exists('sportsmanagementModeljlextsisimport', false)) {
            require_once JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/models/jlextsisimport.php';
        }

        return new \sportsmanagementModeljlextsisimport();
    }
}
