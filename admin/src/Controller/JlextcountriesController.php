<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

/** Native Joomla 5/6 administrator controller for countries. */
final class JlextcountriesController extends SportsManagementAdminController
{
    public function getModel($name = 'Jlextcountry', $prefix = 'Administrator', $config = [])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function importplz(): void
    {
        $this->checkToken();

        $cid = $this->app->getInput()->post->get('cid', [], 'array');
        $cid = array_values(array_unique(array_filter(array_map('intval', $cid))));
        $model = $this->getModel();

        if ($model !== false) {
            $model->importplz($cid);
        }

        $this->setRedirect('index.php?option=com_sportsmanagement&view=jlextcountries');
    }
}
