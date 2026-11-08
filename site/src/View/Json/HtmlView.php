<?php
/**
 * Native Joomla 5/6 frontend SportsManagement JSON view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Json;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementViewErrorReporter;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/** Native Joomla 5/6 frontend JSON view. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $state;

    /**
     * Render the native JSON-related HTML form with safe model error handling.
     *
     * Retrieves Joomla model state and form data, records detailed errors
     * privately and raises the same generic site error used by the root view.
     * Adds the registered component stylesheet only for successful views.
     *
     * @param string|null $tpl Optional Joomla layout template suffix.
     * @return mixed Parent view display result.
     * @throws \RuntimeException If the attached Joomla model reports errors.
     */
    public function display($tpl = null)
    {
        $this->state = $this->get('State');
        $this->form = $this->get('Form');

        SportsManagementViewErrorReporter::assertNoErrors($this->get('Errors'), 'json');

        $this->addDocStyle();

        return parent::display($tpl);
    }

    /**
     * Register the frontend component stylesheet via Joomla's asset manager.
     *
     * Resolves the active site application through the native service and
     * uses the versioned asset API instead of manual legacy HTML tags.
     *
     * @return void
     */
    protected function addDocStyle(): void
    {
        SportsManagementSiteApplicationResolver::resolve()
            ->getDocument()
            ->getWebAssetManager()
            ->registerAndUseStyle(
                'com_sportsmanagement.site',
                'media/com_sportsmanagement/css/site.stylesheet.css',
                ['version' => 'auto']
            );
    }
}
