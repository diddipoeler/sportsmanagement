<?php
namespace Diddipoeler\Component\SportsManagement\Site\View\Predictionrules;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementPredictionHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

final class HtmlView extends SportsManagementPredictionHtmlView
{
    protected function prepareView(): void
    {
        $this->headertitle = Text::_('COM_SPORTSMANAGEMENT_PRED_RULES_SECTION_TITLE');

        if ($this->predictionGame) {
            $this->getDocument()->setTitle(Text::_('COM_SPORTSMANAGEMENT_PRED_USERS_TITLE'));
            return;
        }

        Log::add(Text::_('COM_SPORTSMANAGEMENT_PRED_PREDICTION_NOT_EXISTING'), Log::INFO, 'jsmerror');
    }
}
