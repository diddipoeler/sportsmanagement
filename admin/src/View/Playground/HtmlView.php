<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Playground;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtendedFormHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtraFieldsReadHelper;
use Diddipoeler\Component\SportsManagement\Administrator\Helper\SportsManagementDatabaseResolver;
use Diddipoeler\Component\SportsManagement\Administrator\Model\PlaygroundModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Database\DatabaseInterface;

/** Native Joomla 5/6 administrator edit view for a playground. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;
    public ?Form $extended = null;
    public ?Form $extendeduser = null;
    public ?Form $logoHistoryForm = null;
    public array $extraFields = [];
    public array $playgroundnotic = [];
    public array $logohistory = [];
    public bool $map = false;

    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $app->getInput()->set('hidemainmenu', true);

        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->state = $this->get('State');

        if ($errors = $this->get('Errors')) {
            throw new \RuntimeException(implode("\n", $errors), 500);
        }

        if (!$this->form) {
            throw new \RuntimeException('Playground form could not be loaded.', 500);
        }

        $model = $this->getModel();
        if (!$model instanceof PlaygroundModel) {
            throw new \RuntimeException('Playground view requires PlaygroundModel.', 500);
        }

        $playgroundId = (int) ($this->item->id ?? 0);
        $extendedLoader = new ExtendedFormHelper();
        $this->extended = $extendedLoader->load(
            'extended',
            'playground',
            (string) ($this->item->extended ?? '')
        );
        $this->extendeduser = $extendedLoader->load(
            'extendeduser',
            'playground',
            (string) ($this->item->extendeduser ?? '')
        );

        /** @var DatabaseInterface $joomlaDatabase */
        $joomlaDatabase = Factory::getContainer()->get(DatabaseInterface::class);
        $databaseSelector = $app->getInput()->getInt(
            'cfg_which_database',
            (int) $app->getUserState('com_sportsmanagement.cfg_which_database', 0)
        );
        $database = (new SportsManagementDatabaseResolver())->resolve($databaseSelector, $joomlaDatabase);

        $this->extraFields = (new ExtraFieldsReadHelper())->getFields(
            $playgroundId,
            'playground',
            'backend',
            $database
        );

        if ($playgroundId > 0) {
            $this->playgroundnotic = $model->getPlaygroundNotic($playgroundId);
            $this->logohistory = $model->getlogohistoryPlayground($playgroundId, 0);
            $this->configureMapState();
        }

        $this->logoHistoryForm = $this->loadLogoHistoryForm();
        $this->registerDetailRowScript();
        $this->configureToolbar($playgroundId <= 0);

        parent::display($tpl);
    }

    private function configureMapState(): void
    {
        $latitude = is_numeric($this->item->latitude ?? null) ? (float) $this->item->latitude : 255.0;
        $longitude = is_numeric($this->item->longitude ?? null) ? (float) $this->item->longitude : 255.0;
        $this->map = $latitude >= -90.0 && $latitude <= 90.0
            && $longitude >= -180.0 && $longitude <= 180.0;

        if (!$this->map) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_SPORTSMANAGEMENT_NO_GEOCODE'), 'warning');
        }
    }

    private function loadLogoHistoryForm(): ?Form
    {
        $path = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms/playgroundlogohistory.xml';

        if (!is_file($path)) {
            return null;
        }

        try {
            $factory = Factory::getContainer()->get(FormFactoryInterface::class);
            $form = $factory->createForm(
                'com_sportsmanagement.playgroundlogohistory',
                ['control' => '']
            );

            return $form->loadFile($path) ? $form : null;
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');

            return null;
        }
    }

    private function configureToolbar(bool $isNew): void
    {
        $title = $isNew ? Text::_('JTOOLBAR_NEW') : Text::_('JTOOLBAR_EDIT');
        $name = trim((string) ($this->item->name ?? ''));

        if ($name !== '') {
            $title .= ': ' . $name;
        }

        ToolbarHelper::title($title, 'playground');
        ToolbarHelper::apply('playground.apply');
        ToolbarHelper::save('playground.save');
        ToolbarHelper::save2new('playground.save2new');
        ToolbarHelper::save2copy('playground.save2copy');
        ToolbarHelper::cancel('playground.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }

    private function registerDetailRowScript(): void
    {
        $this->getDocument()->getWebAssetManager()->addInlineScript(<<<'JS'
document.addEventListener('DOMContentLoaded', () => {
    const button = document.querySelector('[data-jsm-add-playground-detail]');
    const target = document.querySelector('#playground-detail-new tbody');
    const template = document.getElementById('playground-detail-row-template');

    if (!button || !target || !template) {
        return;
    }

    button.addEventListener('click', () => {
        target.append(template.content.cloneNode(true));
    });
});
JS);
    }
}
