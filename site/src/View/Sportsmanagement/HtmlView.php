<?php
/**
 * Native Joomla 5/6 frontend SportsManagement root view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Sportsmanagement;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/** Native Joomla 5/6 frontend root view. */
final class HtmlView extends BaseHtmlView
{
    public $item;

    public function display($tpl = null)
    {
        $this->item = $this->get('Item');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        parent::display($tpl);
    }
}
