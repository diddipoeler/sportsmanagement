<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/** Native Joomla 5/6 controller for the JoomLeague import workflow. */
final class JoomleagueimportsController extends BaseController
{
    public function joomleaguesetagegroup()
    {
        $this->checkToken();

        $model = $this->getModel();
        $model->joomleaguesetagegroup();

        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=joomleagueimports&jl_table_import_step=0&layout=infofield',
                false
            ),
            Text::_('COM_SPORTSMANAGEMENT_ADMIN_JOOMLEAGUE_IMPORT_SETAGEGROUP')
        );

        return true;
    }

    public function importjoomleaguenew()
    {
        $this->checkToken();

        $app = $this->app;
        $input = $app->getInput();
        $step = $input->getString('jl_table_import_step', '0');
        $sportsTypeId = $input->getInt('filter_sports_type', 0);

        if ($step === 'ENDE') {
            $this->setRedirect(
                Route::_(
                    'index.php?option=com_sportsmanagement&view=joomleagueimports&jl_table_import_step=0&layout=infofield',
                    false
                )
            );

            return true;
        }

        $model = $this->getModel();
        $result = $model->importjoomleaguenew($step, $sportsTypeId);
        $app->getDocument()->addScriptOptions('success', $result);

        $nextStep = $input->getString('jl_table_import_step', '0');
        $app->setUserState('com_sportsmanagement.jl_table_import_success', $result);

        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=joomleagueimports&layout=default'
                . '&jl_table_import_step=' . rawurlencode($nextStep)
                . '&filter_sports_type=' . $sportsTypeId,
                false
            )
        );

        return true;
    }

    public function importjoomleagueagegroup()
    {
        $this->checkToken();

        $this->setRedirect(
            Route::_(
                'index.php?option=com_sportsmanagement&view=joomleagueimports&layout=infofield',
                false
            )
        );

        return true;
    }

    public function getModel($name = 'Joomleagueimports', $prefix = 'Administrator', $config = [])
    {
        $config['ignore_request'] = true;

        return parent::getModel($name, $prefix, $config);
    }
}
