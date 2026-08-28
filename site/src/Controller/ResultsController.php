<?php
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\ResultsAccessModel;
use Diddipoeler\Component\SportsManagement\Site\Model\ResultsEditModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

/** Joomla 5/6 frontend controller for results actions. */
final class ResultsController extends BaseController
{
    public function saveReferees(): void
    {
        $this->assertPostToken();

        $post = $this->getApplication()->getInput()->post->getArray();
        $layout = (string) ($post['layout'] ?? '');

        $this->setRedirect($this->buildResultsRedirect($post, $layout));
    }

    public function display($cachable = false, $urlparams = [])
    {
        return parent::display($cachable, $urlparams);
    }

    public function saveshort(): void
    {
        $this->assertPostToken();

        $app = $this->getApplication();
        $input = $app->getInput();
        $post = $input->post->getArray();
        $layout = (string) ($post['layout'] ?? $input->getCmd('layout', 'form'));
        $matchIds = (array) $input->post->get('cid', [], 'array');
        $databaseSelector = (int) ($post['cfg_which_database'] ?? $input->getInt('cfg_which_database', 0));
        $projectId = (int) ($post['p'] ?? $input->getInt('p', 0));

        if (!class_exists(ResultsAccessModel::class)) {
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementModel.php';
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/SportsManagementProjectModel.php';
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsAccessModel.php';
        }

        $accessModel = new ResultsAccessModel();
        $accessModel->setDatabaseSelector($databaseSelector);
        $accessModel->setProjectId($projectId);

        if (!$accessModel->canEditMatches($matchIds)) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            $this->setRedirect($this->buildResultsRedirect($post, $layout));
            return;
        }

        if (!class_exists(ResultsEditModel::class)) {
            require_once JPATH_SITE . '/components/com_sportsmanagement/src/Model/ResultsEditModel.php';
        }

        $editModel = new ResultsEditModel();
        $editModel->setDatabaseSelector($databaseSelector);
        $editModel->saveShort($post, $matchIds);

        $this->setRedirect($this->buildResultsRedirect($post, $layout));
    }

    private function assertPostToken(): void
    {
        if (!Session::checkToken('post')) {
            throw new \RuntimeException(Text::_('JINVALID_TOKEN'), 403);
        }
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
