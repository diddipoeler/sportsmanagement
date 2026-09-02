<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Club;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtendedFormHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraFieldsReadHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Model\ClubModel;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 administrator edit view for clubs. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;
    public ?Form $extended = null;
    public ?Form $logoHistoryForm = null;
    public array $logohistory = [];
    public array $teamsofclub = [];
    public array $extraFields = [];

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $input = $app->getInput();
        $input->set('hidemainmenu', true);

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->form) {
            throw new \RuntimeException('Club form could not be loaded.', 500);
        }

        $model = $this->getModel();

        if (!$model instanceof ClubModel) {
            throw new \RuntimeException('Club view requires ClubModel.', 500);
        }

        $clubId = (int) ($this->item->id ?? 0);
        $this->normaliseFormValues($clubId);

        if ($clubId > 0) {
            $this->logohistory = (array) $model->getlogohistory($clubId);
            $this->teamsofclub = (array) $model->teamsofclub($clubId);

            /** @var DatabaseInterface $joomlaDatabase */
            $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
            $databaseSelector = $input->getInt(
                'cfg_which_database',
                (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
            );
            $database = (new SportsManagementDatabaseResolver())->resolve(
                $databaseSelector,
                $joomlaDatabase
            );

            $this->extraFields = (new ExtraFieldsReadHelper())->getFields(
                $clubId,
                'club',
                'backend',
                $database
            );
        }

        $this->extended = (new ExtendedFormHelper())->load(
            'extended',
            'club',
            (string) ($this->item->extended ?? '')
        );
        $this->logoHistoryForm = $this->loadLogoHistoryForm();
        $this->registerAddressSummaryScript();

        $isNew = $clubId <= 0;
        ToolbarHelper::title(
            Text::_($isNew ? 'JTOOLBAR_NEW' : 'JTOOLBAR_EDIT') . ': ' . Text::_('COM_SPORTSMANAGEMENT_ADMIN_CLUB'),
            'users'
        );
        ToolbarHelper::apply('club.apply');
        ToolbarHelper::save('club.save');
        ToolbarHelper::save2new('club.save2new');
        ToolbarHelper::save2copy('club.save2copy');
        ToolbarHelper::cancel('club.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }

    private function normaliseFormValues(int $clubId): void
    {
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_sportsmanagement');

        if ((string) ($this->item->country ?? '') === 'DDR') {
            $this->item->country = 'DEU';
            $this->form->setValue('country', null, 'DEU');
        }

        foreach (['founded', 'dissolved'] as $field) {
            $value = (string) ($this->item->{$field} ?? '');

            if ($clubId <= 0 || $value === '0000-00-00' || str_starts_with($value, '0000-00-00')) {
                $this->form->setValue($field, null, '');
            }
        }

        if ($clubId <= 0) {
            $this->form->setValue(
                'country',
                null,
                (string) $app->getUserState('com_sportsmanagement.clubnation', '')
            );
        }

        $legacyPlaceholders = [
            'logo_big' => [
                'images/com_sportsmanagement/database/clubs/large/placeholder_150.png',
                (string) $params->get('ph_logo_big', ''),
            ],
            'logo_middle' => [
                'images/com_sportsmanagement/database/clubs/medium/placeholder_50.png',
                (string) $params->get('ph_logo_medium', ''),
            ],
            'logo_small' => [
                'images/com_sportsmanagement/database/clubs/small/placeholder_small.gif',
                (string) $params->get('ph_logo_small', ''),
            ],
        ];

        foreach ($legacyPlaceholders as $field => [$legacy, $replacement]) {
            if ((string) ($this->item->{$field} ?? '') !== $legacy || $replacement === '') {
                continue;
            }

            $this->item->{$field} = $replacement;

            if ($this->form->getField($field)) {
                $this->form->setValue($field, null, $replacement);
            }
        }

        if ((float) ($this->item->latitude ?? 0) === 255.0) {
            $app->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_NO_GEOCODE'), 'warning');
        }
    }

    private function loadLogoHistoryForm(): ?Form
    {
        $path = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms/clublogohistory.xml';

        if (!is_file($path)) {
            return null;
        }

        try {
            $factory = Factory::getContainer()->get(FormFactoryInterface::class);
            $form = $factory->createForm(
                'com_sportsmanagement.clublogohistory',
                ['control' => '']
            );

            return $form->loadFile($path) ? $form : null;
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');

            return null;
        }
    }

    private function registerAddressSummaryScript(): void
    {
        $assets = $this->getDocument()->getWebAssetManager();
        $assets->useScript('showon');
        $assets->registerAndUseScript(
            'com_sportsmanagement.admin.club-address-summary',
            'administrator/components/com_sportsmanagement/assets/js/club-address-summary.js',
            ['version' => 'auto'],
            ['defer' => true]
        );
    }
}
