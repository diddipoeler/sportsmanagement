<?php
/**
 * Native Joomla 5/6 frontend SportsManagement Hitlist view.
 *
 * @version    5.6.0
 * @author     diddipoeler
 * @copyright  Copyright (C) diddipoeler
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Diddipoeler\Component\SportsManagement\Site\View\Hitlist;

\defined('_JEXEC') or die;

use Diddipoeler\Component\SportsManagement\Site\Model\HitlistModel;
use Diddipoeler\Component\SportsManagement\Site\View\SportsManagementHtmlView;

final class HtmlView extends SportsManagementHtmlView
{
    public string $tableclass = 'table';
    public array $model_hits = [];

    /**
     * Prepare and render the configured frontend entity hit lists.
     *
     * Resolves the native hit-list model, reads Joomla-filtered display
     * options and queries enabled entity categories using an allowlisted
     * table name. Resets legacy static hit results before collecting
     * fresh counts so repeated renders do not reuse stale categories.
     *
     * @param string|null $tpl Optional Joomla layout name.
     * @return mixed Result of the parent Joomla view renderer.
     * @throws \RuntimeException If HitlistModel cannot be resolved.
     */
    public function display($tpl = null)
    {
        /** @var HitlistModel $model */
        $model = $this->getModel();

        if (!$model instanceof HitlistModel) {
            throw new \RuntimeException('Hitlist view requires HitlistModel.', 500);
        }

        // The legacy model stores data in a static property shared by views.
        // Clear it so disabled categories do not leak from earlier renders.
        HitlistModel::$_success_text = [];

        $this->tableclass = $this->input->getString('table_class', 'table');
        $maxHits = max(0, $this->input->getInt('max_hits', 20));

        foreach ([
            'project' => $this->input->getBool('show_project', true),
            'club' => $this->input->getBool('show_club', true),
            'team' => $this->input->getBool('show_team', true),
            'person' => $this->input->getBool('show_person', true),
            'playground' => $this->input->getBool('show_playground', true),
        ] as $table => $enabled) {
            if ($enabled) {
                $model->getSportsmanagementHits(null, $maxHits, $table);
            }
        }

        $this->model_hits = HitlistModel::$_success_text;

        return parent::display($tpl);
    }
}
