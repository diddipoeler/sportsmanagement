<?php
/**
 * Legacy JSON view kept for compatibility with older SportsManagement links.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright: © 2013-2023 Fussball in Europa http://fussballineuropa.de/ All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Diddipoeler\Component\SportsManagement\Site\Service\SportsManagementSiteApplicationResolver;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView;

if (!class_exists(SportsManagementSiteApplicationResolver::class)) {
    $resolverFile = JPATH_SITE . '/components/com_sportsmanagement/src/Service/SportsManagementSiteApplicationResolver.php';

    if (is_file($resolverFile)) {
        require_once $resolverFile;
    }
}

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
        $app = SportsManagementSiteApplicationResolver::resolve();

        $app->getDocument()
            ->getWebAssetManager()
            ->registerAndUseStyle(
                'com_sportsmanagement.site',
                'media/com_sportsmanagement/css/site.stylesheet.css',
                ['version' => 'auto']
            );
    }
}
