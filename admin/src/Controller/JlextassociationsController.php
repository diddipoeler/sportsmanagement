<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/** Native Joomla 5/6 administrator controller for associations. */
final class JlextassociationsController extends SportsManagementAdminController
{
    public function getModel($name = 'Jlextassociation', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function import(): void
    {
        $this->checkToken();

        $model = parent::getModel('Jlextassociations', 'Administrator', ['ignore_request' => false]);

        if ($model !== false) {
            $model->checkAssociations();
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=jlextassociations');
    }
}
