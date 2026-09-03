<?php
/**
 * Native Joomla 5/6 controller for the SIS import workflow.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/** Native Joomla 5/6 controller for the SIS import workflow. */
final class JlextsisimportController extends BaseController
{
    public function save()
    {
        $this->checkToken();

        $app = $this->app;
        $option = $app->getInput()->getCmd('option', 'com_sportsmanagement') ?: 'com_sportsmanagement';
        $model = $this->getImportModel();
        $model->getData();

        $this->setRedirect(
            'index.php?option=' . $option . '&view=jlxmlimports&task=jlxmlimport.edit'
        );

        return true;
    }

    private function getImportModel(): object
    {
        $model = $this->app
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory()
            ->createModel('Jlextsisimport', 'Administrator', ['ignore_request' => true]);

        if ($model === null) {
            throw new \RuntimeException('SportsManagement SIS import model not found.', 500);
        }

        return $model;
    }
}
