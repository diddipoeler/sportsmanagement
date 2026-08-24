<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\League;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Model\LeagueModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

/** Native Joomla 5/6 administrator edit view for a league. */
final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;
    public $state;
    public array $logohistory = [];
    public array $extraFields = [];
    public ?Form $extended = null;
    public ?Form $extendeduser = null;
    public ?Form $logoHistoryForm = null;

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
            throw new \RuntimeException('League form could not be loaded.', 500);
        }

        $model = $this->getModel();
        $leagueId = (int) ($this->item->id ?? 0);

        foreach (['founded', 'dissolved'] as $field) {
            $value = trim((string) ($this->item->{$field} ?? ''));

            if ($leagueId <= 0 || $value === '0000-00-00') {
                $this->form->setValue($field, null, '');
                $this->item->{$field} = '';
            }
        }

        if ($leagueId <= 0) {
            $country = (string) $app->getUserState('com_sportsmanagement.leaguenation', '');

            if ($country !== '') {
                $this->form->setValue('country', null, $country);
            }
        }

        $this->form->setValue(
            'sports_type_id',
            'request',
            (int) ($this->item->sports_type_id ?? 0)
        );
        $this->form->setValue(
            'agegroup_id',
            'request',
            (int) ($this->item->agegroup_id ?? 0)
        );

        if ($model instanceof LeagueModel && $leagueId > 0) {
            $this->logohistory = $model->getlogohistoryLeague($leagueId);
            $this->extraFields = $model->getExtraFields($leagueId);
        }

        $this->extended = $this->loadExtendedForm(
            'extended',
            JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/assets/extended/league.xml',
            (string) ($this->item->extended ?? '')
        );
        $this->extendeduser = $this->loadExtendedForm(
            'extendeduser',
            JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/assets/extendeduser/league.xml',
            (string) ($this->item->extendeduser ?? '')
        );
        $this->logoHistoryForm = $this->loadLogoHistoryForm();

        if ($this->logoHistoryForm && !empty($this->item->picture)) {
            $this->logoHistoryForm->setValue('league_logo_history', null, (string) $this->item->picture);
        }

        $isNew = $leagueId <= 0;
        ToolbarHelper::title(
            Text::_($isNew ? 'COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_ADD_NEW' : 'COM_SPORTSMANAGEMENT_ADMIN_LEAGUE_EDIT'),
            'league'
        );
        ToolbarHelper::apply('league.apply');
        ToolbarHelper::save('league.save');
        ToolbarHelper::save2new('league.save2new');
        ToolbarHelper::save2copy('league.save2copy');
        ToolbarHelper::cancel('league.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }

    private function loadExtendedForm(string $group, string $path, string $stored): ?Form
    {
        if (!is_file($path)) {
            return null;
        }

        try {
            $registry = new Registry();

            if ($stored !== '') {
                $registry->loadString($stored);
            }

            $form = $this->createForm(
                'com_sportsmanagement.league.' . $group,
                ['control' => $group]
            );

            if (!$form->loadFile($path, false, '/config')) {
                return null;
            }

            $form->bind($registry);

            return $form;
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');

            return null;
        }
    }

    private function loadLogoHistoryForm(): ?Form
    {
        $path = JPATH_ADMINISTRATOR . '/components/com_sportsmanagement/forms/leaguelogohistory.xml';

        if (!is_file($path)) {
            return null;
        }

        try {
            $form = $this->createForm(
                'com_sportsmanagement.leaguelogohistory',
                ['control' => '']
            );

            return $form->loadFile($path, false) ? $form : null;
        } catch (\Throwable $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');

            return null;
        }
    }

    private function createForm(string $name, array $options): Form
    {
        return Factory::getContainer()
            ->get(FormFactoryInterface::class)
            ->createForm($name, $options);
    }
}
