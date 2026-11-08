<?php
/**
 * Native Joomla 5/6 administrator list view for extended XML/PHP files.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Smextxmleditors;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\SmextxmleditorsModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator list view for extended XML/PHP files. */
final class HtmlView extends BaseHtmlView
{
    public array $files = [];

    /**
     * Display validated XML/PHP editor files in the Joomla administrator.
     *
     * Requires the matching namespaced list model, prevents missing-model
     * PHP errors, and logs any private diagnostics internally before raising
     * a generic public exception. The normal administrator toolbar and
     * HTML list are prepared only when the model query succeeds.
     *
     * @param string|null $tpl Optional Joomla view template suffix.
     * @return void
     * @throws \RuntimeException When model resolution or loading fails.
     */
    public function display($tpl = null): void
    {
        $model = $this->getModel();

        if (!$model instanceof SmextxmleditorsModel) {
            throw new \RuntimeException('Extended XML editor requires SmextxmleditorsModel.', 500);
        }

        $this->files = $model->getXMLFiles();

        if ($errors = $model->getErrors()) {
            foreach ((array) $errors as $error) {
                Log::add(
                    'SportsManagement extended XML editor failed: '
                        . (is_scalar($error) ? (string) $error : get_debug_type($error)),
                    Log::ERROR,
                    'com_sportsmanagement'
                );
            }

            throw new \RuntimeException(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 500);
        }

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_XML_EDITORS'), 'xml-edits');
        ToolbarHelper::back(
            Text::_('JPREV'),
            Route::_('index.php?option=com_sportsmanagement&view=cpanel')
        );

        parent::display($tpl);
    }
}
