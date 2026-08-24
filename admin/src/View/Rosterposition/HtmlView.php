<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\View\Rosterposition;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Administrator\Helper\ExtendedFormHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/** Native Joomla 5/6 administrator edit view for a roster position. */
final class HtmlView extends BaseHtmlView
{
    private const PITCH_WIDTH = 578;
    private const PITCH_HEIGHT = 1050;
    private const MAX_PLAYERS = 11;

    public $form;
    public $item;
    public $state;
    public ?Form $extended = null;

    /** @var array<int, array{top:int,left:int}> */
    public array $coordinates = [];

    public int $playerCount = self::MAX_PLAYERS;
    public string $positionType = '';
    public string $pitchPicture = 'spielfeld_578x1050.png';

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
            throw new \RuntimeException('Roster position form could not be loaded.', 500);
        }

        $id = (int) ($this->item->id ?? 0);
        $requestedType = strtoupper($input->getCmd('addposition', ''));
        $storedType = strtoupper(trim((string) ($this->item->short_name ?? $this->item->alias ?? '')));
        $this->positionType = in_array($requestedType, ['HOME_POS', 'AWAY_POS'], true)
            ? $requestedType
            : (in_array($storedType, ['HOME_POS', 'AWAY_POS'], true) ? $storedType : 'HOME_POS');

        if ($id <= 0 && in_array($requestedType, ['HOME_POS', 'AWAY_POS'], true)) {
            $this->form->setValue('name', null, '4231');
            $this->form->setValue('short_name', null, $requestedType);
            $this->form->setValue('country', null, 'DEU');
            $this->form->setValue('players', null, self::MAX_PLAYERS);
            $this->form->setValue('picture', null, 'spielfeld_578x1050.png');

            $this->item->name = '4231';
            $this->item->short_name = $requestedType;
            $this->item->country = 'DEU';
            $this->item->players = self::MAX_PLAYERS;
            $this->item->picture = 'spielfeld_578x1050.png';
        }

        $this->pitchPicture = trim((string) ($this->item->picture ?? '')) ?: 'spielfeld_578x1050.png';
        $this->playerCount = min(
            self::MAX_PLAYERS,
            max(1, (int) ($this->item->players ?? $this->form->getValue('players') ?? self::MAX_PLAYERS))
        );

        $this->extended = (new ExtendedFormHelper())->load(
            'extended',
            'rosterposition',
            (string) ($this->item->extended ?? '')
        );

        if ($this->extended) {
            $storedExtended = trim((string) ($this->item->extended ?? ''));
            $defaults = $this->defaultCoordinates($this->positionType);

            for ($position = 1; $position <= self::MAX_PLAYERS; $position++) {
                $topName = 'COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_' . $position . '_TOP';
                $leftName = 'COM_SPORTSMANAGEMENT_EXT_ROSTERPOSITIONS_' . $position . '_LEFT';

                if ($storedExtended === '') {
                    $this->extended->setValue($topName, null, $defaults[$position]['top']);
                    $this->extended->setValue($leftName, null, $defaults[$position]['left']);
                }

                $this->coordinates[$position] = [
                    'top' => (int) $this->extended->getValue($topName, null, $defaults[$position]['top']),
                    'left' => (int) $this->extended->getValue($leftName, null, $defaults[$position]['left']),
                ];
            }
        }

        $this->configureToolbar($id <= 0);
        parent::display($tpl);
    }

    /** @return array<int, array{top:int,left:int}> */
    private function defaultCoordinates(string $type): array
    {
        $home = [
            1 => ['top' => 5, 'left' => 233],
            2 => ['top' => 113, 'left' => 69],
            3 => ['top' => 113, 'left' => 179],
            4 => ['top' => 113, 'left' => 288],
            5 => ['top' => 113, 'left' => 397],
            6 => ['top' => 236, 'left' => 179],
            7 => ['top' => 236, 'left' => 288],
            8 => ['top' => 318, 'left' => 69],
            9 => ['top' => 318, 'left' => 233],
            10 => ['top' => 318, 'left' => 397],
            11 => ['top' => 400, 'left' => 233],
        ];

        if ($type !== 'AWAY_POS') {
            return $home;
        }

        return [
            1 => ['top' => 970, 'left' => 233],
            2 => ['top' => 828, 'left' => 69],
            3 => ['top' => 828, 'left' => 179],
            4 => ['top' => 828, 'left' => 288],
            5 => ['top' => 828, 'left' => 397],
            6 => ['top' => 746, 'left' => 179],
            7 => ['top' => 746, 'left' => 288],
            8 => ['top' => 664, 'left' => 69],
            9 => ['top' => 664, 'left' => 397],
            10 => ['top' => 587, 'left' => 179],
            11 => ['top' => 587, 'left' => 288],
        ];
    }

    private function configureToolbar(bool $isNew): void
    {
        ToolbarHelper::title(
            Text::_($isNew ? 'COM_SPORTSMANAGEMENT_ADMIN_ROSTERPOSITION_NEW' : 'COM_SPORTSMANAGEMENT_ADMIN_ROSTERPOSITION_EDIT'),
            'rosterposition'
        );
        ToolbarHelper::apply('rosterposition.apply');
        ToolbarHelper::save('rosterposition.save');
        ToolbarHelper::save2new('rosterposition.save2new');
        ToolbarHelper::save2copy('rosterposition.save2copy');
        ToolbarHelper::cancel('rosterposition.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
    }
}
