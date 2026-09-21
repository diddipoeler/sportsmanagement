<?php
/**
 * Native Joomla 5/6 administrator edit view for prediction projects.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Predictionproject;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/** Native Joomla 5/6 administrator edit view for prediction projects. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;

    public function display($tpl = null)
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->form || !$this->item) {
            throw new \RuntimeException('Prediction project form data is unavailable.', 500);
        }

        $this->item->name = '';
        self::administratorApplication()->setUserState(
            'com_sportsmanagement.pid',
            (int) ($this->item->project_id ?? 0)
        );

        $layout = strtolower((string) $this->getLayout());

        if (in_array($layout, ['edit_3', 'edit_4'], true)) {
            $this->setLayout('edit');
        }

        parent::display($tpl);
    }

    private static function administratorApplication(): AdministratorApplication
    {
        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        if (!$app->isClient('administrator')) {
            throw new \RuntimeException('SportsManagement prediction project view requires the Joomla administrator application.', 500);
        }

        return $app;
    }
}
