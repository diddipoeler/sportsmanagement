<?php
namespace Diddipoeler\Component\SportsManagement\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

final class PredictiongameTable extends SportsManagementTable
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__sportsmanagement_prediction_game', 'id', $db);
    }

    public function check()
    {
        $name = trim((string) ($this->name ?? ''));

        if ($name === '') {
            $this->setError(Text::_('CHECK FAILED - Empty name of prediction game'));

            return false;
        }

        $alias = OutputFilter::stringURLSafe($name);

        if (empty($this->alias) || $this->alias === $alias) {
            $this->alias = $alias;
        }

        return true;
    }
}
