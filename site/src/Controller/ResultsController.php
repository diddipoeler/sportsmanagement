<?php
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ResultsEditModel;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

/** Joomla 5/6 frontend controller for results actions. */
final class ResultsController extends BaseController
{
    public function saveReferees(): void
    {
        $post = Factory::getApplication()->getInput()->post->getArray();
        $layout = (string) ($post['layout'] ?? '');

        $this->setRedirect($this->buildResultsRedirect($post, $layout));
    }

    public function display($cachable = false, $urlparams = [])
    {
    }

    public function saveshort(): void
    {
        $input = Factory::getApplication()->getInput();
        $post = $input->post->getArray();
        $layout = $input->getCmd('layout', 'form');

        if (!class_exists(ResultsEditModel::class)) {
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsEditModel.php';
        }

        $model = new ResultsEditModel();
        $model->setDatabaseSelector((int) ($post['cfg_which_database'] ?? $input->getInt('cfg_which_database', 0)));
        $model->saveShort($post, (array) $input->post->get('cid', [], 'array'));

        $this->setRedirect($this->buildResultsRedirect($post, $layout));
    }

    private function buildResultsRedirect(array $post, string $layout): string
    {
        $parameters = [
            'option' => 'com_sportsmanagement',
            'view' => 'results',
            'cfg_which_database' => (string) ($post['cfg_which_database'] ?? ''),
            's' => (string) ($post['s'] ?? ''),
            'p' => (string) ($post['p'] ?? ''),
            'r' => (string) ($post['r'] ?? ''),
            'division' => (string) ($post['division'] ?? ''),
            'mode' => (string) ($post['mode'] ?? ''),
            'order' => (string) ($post['order'] ?? ''),
            'layout' => $layout,
        ];

        return 'index.php?' . http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }
}
