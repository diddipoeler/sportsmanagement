<?php
/**
 * Native Joomla 5/6 administrator view for the handball.net import screen.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Jlexthandballnet;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

/** Native Joomla 5/6 administrator view for the handball.net import screen. */
final class HtmlView extends BaseHtmlView
{
    public string $request_url = '';
    public string $sortColumn = '';
    public string $sortDirection = '';

    public function display($tpl = null)
    {
        $layout = preg_replace('/_(?:3|4|5)$/', '', (string) $this->getLayout()) ?: 'default';
        $this->setLayout($layout);
        $this->request_url = Uri::getInstance()->toString();

        $state = $this->get('State');
        if (is_object($state)) {
            $this->sortColumn = (string) $state->get('list.ordering', '');
            $this->sortDirection = (string) $state->get('list.direction', '');
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    protected function addToolbar(): void
    {
        $document = $this->getDocument();

        if ($document instanceof HtmlDocument) {
            $document->getWebAssetManager()->registerAndUseStyle(
                'com_sportsmanagement.admin.handballnet',
                Uri::root(true) . '/administrator/components/com_sportsmanagement/assets/css/jlextusericons.css',
                ['version' => 'auto']
            );
        }

        ToolbarHelper::title(Text::_('COM_SPORTSMANAGEMENT_ADMIN_DBB_IMPORT'), 'dbb-cpanel');
        ToolbarHelper::back('JPREV', 'index.php?option=com_sportsmanagement&view=extensions');

        if (self::administratorApplication()->getIdentity()->authorise('core.admin', 'com_sportsmanagement')) {
            ToolbarHelper::preferences('com_sportsmanagement');
        }
    }

    private static function administratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement handball.net import view requires the Joomla administrator application.', 500);
        }

        return $app;
    }
}
