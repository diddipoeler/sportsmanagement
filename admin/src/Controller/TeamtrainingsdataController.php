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
    /**
     * Initialise the legacy training form controller as a team form proxy.
     *
     * Defaults the item and list views to team and teams, respectively,
     * while forwarding the MVC factory, application, request input and
     * form factory services to the native Joomla parent controller.
     *
     * @param array $config Controller view and task configuration.
     * @param MVCFactoryInterface|null $factory Joomla MVC factory.
     * @param CMSWebApplicationInterface|null $app Joomla web application.
     * @param Input|null $input Request input service.
     * @param FormFactoryInterface|null $formFactory Joomla form factory.
     */
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

    /**
     * Resolve a team model for historic team training form tasks.
     *
     * Rewrites the former Teamtrainingsdata model alias to Team and
     * forwards the chosen name and caller's model configuration to
     * Joomla's administrator MVC model factory.
     *
     * @param string $name Legacy or native model name.
     * @param string $prefix Administrator model namespace.
     * @param array $config Additional Joomla model factory options.
     * @return mixed Resolved team model or parent failure result.
     */
    public function getModel($name = 'Team', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        if ($name === '' || strcasecmp((string) $name, 'Teamtrainingsdata') === 0) {
            $name = 'Team';
        }

        return parent::getModel($name, $prefix, $config);
    }
}
