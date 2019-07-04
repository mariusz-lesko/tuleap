/*
 * Copyright (c) Enalean, 2019 - present. All Rights Reserved.
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

import Vuex from "vuex";
import { shallowMount } from "@vue/test-utils";
import QuickLookDocumentMetadata from "./QuickLookDocumentMetadata.vue";

import localVue from "../../../helpers/local-vue.js";
import { TYPE_FILE } from "../../../constants.js";

import {
    rewire$formatDateUsingPreferredUserFormat,
    restore
} from "../../../helpers/date-formatter.js";

describe("QuickLookDocumentMetadata", () => {
    let metadata_factory, store, formatDateUsingPreferredUserFormat;

    beforeEach(() => {
        store = new Vuex.Store();

        metadata_factory = (props = {}) => {
            return shallowMount(QuickLookDocumentMetadata, {
                localVue,
                propsData: { ...props },
                mocks: { $store: store }
            });
        };

        formatDateUsingPreferredUserFormat = jasmine.createSpy(
            "formatDateUsingPreferredUserFormat"
        );
        rewire$formatDateUsingPreferredUserFormat(formatDateUsingPreferredUserFormat);
    });

    afterEach(() => {
        restore();
    });

    it(`Given document has multiple metadata
         Then they are displayed in two different columns`, () => {
        const item = {
            id: 42,
            title: "file",
            type: TYPE_FILE,
            owner: {
                id: 102
            },
            metadata: [
                { title: "custom", short_name: "custom metadata" },
                { title: "other", short_name: "other metadata" }
            ],
            creation_date: "2019-06-25T16:56:22+04:00"
        };

        const wrapper = metadata_factory({ item });

        expect(wrapper.contains("[data-test='additional-metadata-left-list']")).toBeTruthy();
        expect(wrapper.contains("[data-test='additional-metadata-right-list']")).toBeTruthy();
    });

    it(`Given document has hardocded metadata
         Then they are displayed in additionnal metadata`, () => {
        const item = {
            id: 42,
            title: "file",
            type: TYPE_FILE,
            owner: {
                id: 102
            },
            metadata: [{ title: "title document", short_name: "title" }],
            creation_date: "2019-06-25T16:56:22+04:00"
        };

        const wrapper = metadata_factory({ item });

        expect(wrapper.contains("[data-test='additional-metadata-left-list']")).toBeFalsy();
        expect(wrapper.contains("[data-test='additional-metadata-right-list']")).toBeFalsy();
    });

    it(`Given item is a file
         Then its size is displayed`, () => {
        const item = {
            id: 42,
            title: "file",
            type: TYPE_FILE,
            owner: {
                id: 102
            },
            file_properties: {
                file_size: 123456
            },
            creation_date: "2019-06-25T16:56:22+04:00",
            metadata: []
        };

        const wrapper = metadata_factory({ item });
        expect(wrapper.contains("[data-test='docman-file-size']")).toBeTruthy();
    });

    it(`Given item has an approval table,
         Then its approval status is displayed`, () => {
        const item = {
            id: 42,
            title: "file",
            type: TYPE_FILE,
            owner: {
                id: 102
            },
            approval_data: {
                approval_state: "Approved"
            },
            creation_date: "2019-06-25T16:56:22+04:00",
            metadata: []
        };

        const wrapper = metadata_factory({ item });
        expect(wrapper.contains("[data-test='docman-file-size']")).toBeTruthy();
    });
});