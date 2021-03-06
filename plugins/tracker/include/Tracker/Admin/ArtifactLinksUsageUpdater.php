<?php
/**
 * Copyright (c) Enalean, 2017. All Rights Reserved.
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

namespace Tuleap\Tracker\Admin;

use Feedback;
use Project;

class ArtifactLinksUsageUpdater
{
    /**
     * @var ArtifactLinksUsageDao
     */
    private $dao;

    public function __construct(ArtifactLinksUsageDao $dao)
    {
        $this->dao = $dao;
    }

    public function update(Project $project)
    {
        if ($this->dao->isProjectUsingArtifactLinkTypes($project->getID())) {
            $GLOBALS['Response']->addFeedback(
                Feedback::INFO,
                dgettext('tuleap-tracker', 'Artifact link types has been disabled.')
            );

            return $this->dao->deactivateForProject($project->getID());
        }

        $GLOBALS['Response']->addFeedback(
            Feedback::INFO,
            dgettext('tuleap-tracker', 'Artifact link types has been enabled.')
        );

        return $this->dao->activateForProject($project->getID());
    }

    public function forceUsageOfArtifactLinkTypes(Project $project)
    {
        return $this->dao->activateForProject($project->getID());
    }

    public function forceDeactivationOfArtifactLinkTypes(Project $project)
    {
        return $this->dao->deactivateForProject($project->getID());
    }

    public function isProjectAllowedToUseArtifactLinkTypes(Project $project)
    {
        return $this->dao->isProjectUsingArtifactLinkTypes($project->getId());
    }
}
