<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Tuleap\AgileDashboard\ExplicitBacklog;

use AgileDashboard_BacklogItemDao;
use Codendi_Request;
use MilestoneReportCriterionDao;
use PFUser;
use Planning_MilestoneFactory;
use Project;
use Tuleap\DB\DBTransactionExecutor;

class ConfigurationUpdater
{
    /**
     * @var ExplicitBacklogDao
     */
    private $explicit_backlog_dao;

    /**
     * @var MilestoneReportCriterionDao
     */
    private $milestone_report_criterion_dao;

    /**
     * @var DBTransactionExecutor
     */
    private $db_transaction_executor;

    /**
     * @var AgileDashboard_BacklogItemDao
     */
    private $backlog_item_dao;

    /**
     * @var Planning_MilestoneFactory
     */
    private $milestone_factory;

    /**
     * @var ArtifactsInExplicitBacklogDao
     */
    private $artifacts_in_explicit_backlog_dao;

    public function __construct(
        ExplicitBacklogDao $explicit_backlog_dao,
        MilestoneReportCriterionDao $milestone_report_criterion_dao,
        AgileDashboard_BacklogItemDao $backlog_item_dao,
        Planning_MilestoneFactory $milestone_factory,
        ArtifactsInExplicitBacklogDao $artifacts_in_explicit_backlog_dao,
        DBTransactionExecutor $db_transaction_executor
    ) {
        $this->explicit_backlog_dao              = $explicit_backlog_dao;
        $this->milestone_report_criterion_dao    = $milestone_report_criterion_dao;
        $this->db_transaction_executor           = $db_transaction_executor;
        $this->backlog_item_dao                  = $backlog_item_dao;
        $this->milestone_factory                 = $milestone_factory;
        $this->artifacts_in_explicit_backlog_dao = $artifacts_in_explicit_backlog_dao;
    }

    public function updateScrumConfiguration(Codendi_Request $request): void
    {
        if (! $request->exist('use-explicit-top-backlog')) {
            return;
        }

        $user                     = $request->getCurrentUser();
        $project                  = $request->getProject();
        $project_id               = (int) $project->getID();
        $use_explicit_top_backlog = (bool) $request->get('use-explicit-top-backlog');
        if ($this->mustBeDeactivated($use_explicit_top_backlog, $project_id)) {
            $this->deactivateExplicitBacklogManagement($project_id);
        } elseif ($this->mustBeActivated($use_explicit_top_backlog, $project_id)) {
            $this->activateExplicitBacklogManagement($project, $user);
        }
    }

    private function activateExplicitBacklogManagement(Project $project, PFUser $user): void
    {
        $this->db_transaction_executor->execute(function () use ($project, $user) {
            $project_id = (int) $project->getID();
            $this->explicit_backlog_dao->setProjectIsUsingExplicitBacklog($project_id);
            //duplicate data
            $top_milestone = $this->milestone_factory->getVirtualTopMilestone($user, $project);
            $backlog_items_rows = $this->backlog_item_dao->getOpenUnplannedTopBacklogArtifacts(
                $top_milestone->getPlanning()->getBacklogTrackersIds(),
                []
            );

            foreach ($backlog_items_rows as $backlog_items_row) {
                $this->artifacts_in_explicit_backlog_dao->addArtifactToProjectBacklog(
                    $project_id,
                    (int) $backlog_items_row['id']
                );
            }
        });
    }

    private function deactivateExplicitBacklogManagement(int $project_id): void
    {
        $this->db_transaction_executor->execute(function () use ($project_id) {
            $this->artifacts_in_explicit_backlog_dao->removeExplicitBacklogOfProject($project_id);
            $this->milestone_report_criterion_dao->updateAllUnplannedValueToAnyInProject($project_id);
        });

        $GLOBALS['Response']->addFeedback(
            \Feedback::INFO,
            dgettext(
                'tuleap-agiledashboard',
                'All tracker reports using "Unplanned" option for "In milestone" report criterion will now use "Any".'
            )
        );
    }

    private function mustBeDeactivated(bool $use_explicit_top_backlog, int $project_id): bool
    {
        return ! $use_explicit_top_backlog &&
            $this->explicit_backlog_dao->isProjectUsingExplicitBacklog($project_id);
    }

    private function mustBeActivated(bool $use_explicit_top_backlog, int $project_id): bool
    {
        return $use_explicit_top_backlog &&
            ! $this->explicit_backlog_dao->isProjectUsingExplicitBacklog($project_id);
    }
}
