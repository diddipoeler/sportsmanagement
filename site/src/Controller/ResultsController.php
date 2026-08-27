<?php
namespace Diddipoeler\Component\SportsManagement\Site\Controller;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Legacy\LegacyBootstrap;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use RuntimeException;

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

        $this->legacyResultsModel()->saveshort();
        $this->setRedirect($this->buildResultsRedirect($post, $layout));
    }

    private function legacyResultsModel(): \sportsmanagementModelResults
    {
        LegacyBootstrap::bootForView('results');

        if (!class_exists('sportsmanagementModelResults', false)) {
            require_once JPATH_SITE . '/components/com_sportsmanagement/models/results.php';
        }

        if (!class_exists('sportsmanagementModelResults', false)) {
            throw new RuntimeException('Legacy Results model is unavailable.', 500);
        }

        return new \sportsmanagementModelResults();
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
