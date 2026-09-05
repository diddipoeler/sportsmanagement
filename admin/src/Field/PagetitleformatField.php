<?php
/**
 * Joomla 5/6 native page-title format field.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;

final class PagetitleformatField extends ListField
{
    protected $type = 'pagetitleformat';

    protected function getOptions(): array
    {
        $language = Factory::getApplication()->getLanguage();
        $language->load('com_sportsmanagement', JPATH_ADMINISTRATOR, $language->getTag(), true);

        $keys = [
            'COM_SPORTSMANAGEMENT_FES_PARAM_PAGE_TITLE_PROJECT',
            'COM_SPORTSMANAGEMENT_FES_PARAM_PAGE_TITLE_PROJECT_LEAGUE',
            'COM_SPORTSMANAGEMENT_FES_PARAM_PAGE_TITLE_PROJECT_LEAGUE_SEASON',
            'COM_SPORTSMANAGEMENT_FES_PARAM_PAGE_TITLE_PROJECT_SEASON',
            'COM_SPORTSMANAGEMENT_FES_PARAM_PAGE_TITLE_LEAGUE',
            'COM_SPORTSMANAGEMENT_FES_PARAM_PAGE_TITLE_LEAGUE_SEASON',
            'COM_SPORTSMANAGEMENT_FES_PARAM_PAGE_TITLE_SEASON',
            'COM_SPORTSMANAGEMENT_FES_PARAM_PAGE_TITLE_NONE',
        ];
        $options = [];

        foreach ($keys as $value => $key) {
            $options[] = (object) [
                'value' => (string) $value,
                'text' => Text::_($key),
            ];
        }

        return array_merge(parent::getOptions(), $options);
    }
}
