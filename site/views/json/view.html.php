<?php
/**
 * Legacy JSON view kept for compatibility with older SportsManagement links.
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView;

class sportsmanagementViewjson extends HtmlView
{
    public function display($tpl = null)
    {
        // Keep the historical model access for this compatibility-only view,
        // but do not bootstrap removed Joomla 3 view/HTML libraries.
        $state = $this->get('State');
        $this->form = $this->get('Form');
        $this->state = $state;

        if (count($errors = $this->get('Errors'))) {
            Log::add(implode('<br />', $errors));

            return false;
        }

        $this->addDocStyle();

        return parent::display($tpl);
    }

    protected function addDocStyle(): void
    {
        Factory::getApplication()
            ->getDocument()
            ->getWebAssetManager()
            ->registerAndUseStyle(
                'com_sportsmanagement.site',
                'media/com_sportsmanagement/css/site.stylesheet.css',
                ['version' => 'auto']
            );
    }
}
