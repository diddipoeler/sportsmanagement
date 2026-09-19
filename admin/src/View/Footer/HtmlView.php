<?php
/**
 * Native Joomla 5/6 administrator SportsManagement Footer view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Footer;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/** Native Joomla 5/6 administrator footer compatibility view. */
final class HtmlView extends BaseHtmlView
{
    /**
     * Compatibility hook used by the legacy SportsManagement view lifecycle.
     */
    public function init(): void
    {
    }
}
