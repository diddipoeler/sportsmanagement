<?php
/**
 * Joomla 5/6 compatibility view for the SportsManagement frontend root view.
 *
 * @version    5.6.0
 * @author     diddipoeler, stony, svdoldie und donclumsy (diddipoeler@gmx.de)
 * @copyright  Copyright (C) 2013-2026 Fussball in Europa
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;

/**
 * Legacy class name retained for compatibility with historic frontend dispatchers.
 */
class sportsmanagementViewsportsmanagement extends HtmlView
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
