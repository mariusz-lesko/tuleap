/*
 * Copyright (c) Enalean, 2020-Present. All Rights Reserved.
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

const downloadExportDocument = jest.fn();
jest.mock("../../helpers/Export/download-export-document", () => {
    return {
        downloadExportDocument,
    };
});

import { shallowMount, Wrapper } from "@vue/test-utils";
import { createStoreMock } from "../../../../../../../src/scripts/vue-components/store-wrapper-jest";
import { RootState } from "../../store/type";
import ExportButton from "./ExportButton.vue";
import { BacklogItemState } from "../../store/backlog-item/type";
import { CampaignState } from "../../store/campaign/type";
import { createTestPlanLocalVue } from "../../helpers/local-vue-for-test";

describe("ExportButton", () => {
    beforeEach(() => {
        downloadExportDocument.mockReset();
    });

    async function createWrapper(
        backlog_item: BacklogItemState,
        campaign: CampaignState
    ): Promise<Wrapper<ExportButton>> {
        return shallowMount(ExportButton, {
            localVue: await createTestPlanLocalVue(),
            mocks: {
                $store: createStoreMock({
                    state: {
                        project_name: "Project",
                        milestone_title: "My milestone",
                        backlog_item,
                        campaign,
                    } as RootState,
                }),
            },
        });
    }

    it("Allows to download a report", async () => {
        const wrapper = await createWrapper(
            {
                is_loading: false,
                has_loading_error: false,
            } as BacklogItemState,
            {
                is_loading: false,
                has_loading_error: false,
            } as CampaignState
        );

        expect(wrapper.element.hasAttribute("disabled")).toBe(false);
        expect(
            wrapper
                .get("[data-test=download-export-button-icon]")
                .element.classList.contains("fa-spin")
        ).toBe(false);

        downloadExportDocument.mockImplementation((): void => {
            expect(
                wrapper
                    .get("[data-test=download-export-button-icon]")
                    .element.classList.contains("fa-spin")
            ).toBe(true);

            // This is here to make sure the user cannot spam-click the export button
            // Only one report should be generated at a time, subsequent clicks should do nothing
            wrapper.trigger("click");
        });

        wrapper.trigger("click");
    });

    it("Does not allow to download the report when the backlog items are not loaded", async () => {
        const wrapper = await createWrapper(
            {
                is_loading: true,
                has_loading_error: false,
            } as BacklogItemState,
            {
                is_loading: false,
                has_loading_error: false,
            } as CampaignState
        );

        expect(wrapper.element.hasAttribute("disabled")).toBe(true);
    });

    it("Does not allow to download the report when the campaigns are not loaded", async () => {
        const wrapper = await createWrapper(
            {
                is_loading: false,
                has_loading_error: false,
            } as BacklogItemState,
            {
                is_loading: true,
                has_loading_error: false,
            } as CampaignState
        );

        expect(wrapper.element.hasAttribute("disabled")).toBe(true);
    });

    it("Does not allow to download the report when the backlog items have a loading error", async () => {
        const wrapper = await createWrapper(
            {
                is_loading: false,
                has_loading_error: true,
            } as BacklogItemState,
            {
                is_loading: false,
                has_loading_error: false,
            } as CampaignState
        );

        expect(wrapper.element.hasAttribute("disabled")).toBe(true);
    });

    it("Does not allow to download the report when the campaigns have a loading error", async () => {
        const wrapper = await createWrapper(
            {
                is_loading: false,
                has_loading_error: false,
            } as BacklogItemState,
            {
                is_loading: false,
                has_loading_error: true,
            } as CampaignState
        );

        expect(wrapper.element.hasAttribute("disabled")).toBe(true);
    });
});
