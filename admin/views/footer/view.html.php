<?php
/**
 * Joomla 5/6 compatibility view for the SportsManagement administrator footer.
 */
\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;

/**
 * Legacy class name retained for compatibility with the existing administrator loader.
 */
class sportsmanagementViewFooter extends HtmlView
{
    /**
     * Compatibility hook used by the legacy SportsManagement view lifecycle.
     */
    public function init(): void
    {
    }
}
