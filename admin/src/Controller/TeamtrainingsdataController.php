<?php
/**
 * Native Joomla 5/6 compatibility controller for legacy team training-data form tasks.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

/**
 * Compatibility proxy for the historic teamtrainingsdata.* task namespace.
 *
 * Training rows are now persisted by TeamModel during the normal team form
 * save cycle, so legacy form tasks are routed through the native Team flow.
 */
final class TeamtrainingsdataController extends SportsManagementFormController
{
    public function __construct(
        $config = [],
        ?MVCFactoryInterface $factory = null,
        ?CMSWebApplicationInterface $app = null,
        ?Input $input = null,
        ?FormFactoryInterface $formFactory = null
    ) {
        $config['view_item'] = $config['view_item'] ?? 'team';
        $config['view_list'] = $config['view_list'] ?? 'teams';

        parent::__construct($config, $factory, $app, $input, $formFactory);
    }

    public function getModel($name = 'Team', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        if ($name === '' || strcasecmp((string) $name, 'Teamtrainingsdata') === 0) {
            $name = 'Team';
        }

        return parent::getModel($name, $prefix, $config);
    }
}
