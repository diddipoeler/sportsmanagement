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

use Diddipoeler\Component\SportsManagement\Administrator\Model\JlextsisimportModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

/** Native Joomla 5/6 controller for the SIS import workflow. */
final class JlextsisimportController extends BaseController
{
    /**
     * Execute the administrator SIS import workflow.
     *
     * Requires Joomla's form token and component core.admin permission
     * before calling the native SIS importer model, then redirects to
     * the XML import edit view on completion.
     *
     * @return bool True after the model import completes.
     * @throws \RuntimeException If access or model resolution fails.
     */
    public function save()
    {
        $this->checkToken();

        // CSRF validation is not an authorization decision. Importers can
        // persist data and replace import files, so require component admin.
        $identity = $this->app->getIdentity();

        if ($identity === null || !$identity->authorise('core.admin', 'com_sportsmanagement')) {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        $app = $this->app;
        $option = 'com_sportsmanagement';
        $model = $this->getImportModel();
        $model->getData();

        $this->setRedirect(
            'index.php?option=' . $option . '&view=jlxmlimports&task=jlxmlimport.edit'
        );

        return true;
    }

    /**
     * Resolve the SIS importer through the Joomla 5/6 MVC component factory.
     *
     * Boots com_sportsmanagement and requests its administrator import
     * model with request-independent state; rejects an unexpected model.
     *
     * @return JlextsisimportModel Valid native importer model.
     * @throws \RuntimeException If the importer cannot be resolved.
     */
    private function getImportModel(): JlextsisimportModel
    {
        $model = $this->app
            ->bootComponent('com_sportsmanagement')
            ->getMVCFactory()
            ->createModel('Jlextsisimport', 'Administrator', ['ignore_request' => true]);

        if (!$model instanceof JlextsisimportModel) {
            throw new \RuntimeException('SportsManagement SIS import model not found.', 500);
        }

        return $model;
    }
}
