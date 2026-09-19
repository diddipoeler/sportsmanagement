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
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/** Native Joomla 5/6 frontend JSON view. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $state;

    public function display($tpl = null)
    {
        $this->state = $this->get('State');
        $this->form = $this->get('Form');

        if ($errors = $this->get('Errors')) {
            Log::add(implode('<br />', $errors));

            return false;
        }

        $this->addDocStyle();

        return parent::display($tpl);
    }

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
