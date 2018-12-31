<?php
/**
 * Copyright (c) Enalean, 2018. All Rights Reserved.
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

namespace Tuleap\Docman\Upload;

use Tuleap\DB\DataAccessObject;

class DocumentOngoingUploadDAO extends DataAccessObject
{
    public function searchDocumentOngoingUploadByParentIDTitleAndExpirationDate(
        $parent_id,
        $title,
        $current_time
    ) {
        $sql = 'SELECT *
                FROM plugin_docman_new_document_upload
                WHERE parent_id = ? AND title = ? AND expiration_date > ?';

        return $this->getDB()->run($sql, $parent_id, $title, $current_time);
    }

    public function searchDocumentOngoingUploadByItemID($item_id)
    {
        return $this->getDB()->row(
            'SELECT plugin_docman_new_document_upload.*, `groups`.group_id
             FROM plugin_docman_new_document_upload
             JOIN plugin_docman_item ON (plugin_docman_item.item_id = plugin_docman_new_document_upload.parent_id)
             JOIN `groups` ON (`groups`.group_id = plugin_docman_item.group_id)
             WHERE plugin_docman_new_document_upload.item_id = ?',
            $item_id
        );
    }

    public function searchDocumentOngoingUploadByItemIDUserIDAndExpirationDate($item_id, $user_id, $current_time)
    {
        return $this->getDB()->row(
            'SELECT * FROM plugin_docman_new_document_upload WHERE item_id = ? AND user_id = ? AND expiration_date > ?',
            $item_id,
            $user_id,
            $current_time
        );
    }

    /**
     * @return array
     */
    public function searchDocumentOngoingUploadItemIDs()
    {
        return $this->getDB()->column('SELECT item_id FROM plugin_docman_new_document_upload');
    }

    /**
     * @return int
     */
    public function saveDocumentOngoingUpload(
        $expiration_date,
        $parent_id,
        $title,
        $description,
        $user_id,
        $filename,
        $filesize
    ) {
        $item_id = (int) $this->getDB()->insertReturnId('plugin_docman_item_id', []);
        $this->getDB()->insert(
            'plugin_docman_new_document_upload',
            [
                'item_id'         => $item_id,
                'expiration_date' => $expiration_date,
                'parent_id'       => $parent_id,
                'title'           => $title,
                'description'     => $description,
                'user_id'         => $user_id,
                'filename'        => $filename,
                'filesize'        => $filesize
            ]
        );
        return $item_id;
    }

    public function deleteUnusableDocuments($current_time)
    {
        $this->getDB()->run(
            'DELETE plugin_docman_new_document_upload, plugin_docman_item_id
             FROM plugin_docman_new_document_upload
             JOIN plugin_docman_item_id ON (plugin_docman_item_id.id = plugin_docman_new_document_upload.item_id)
             LEFT JOIN plugin_docman_item ON (plugin_docman_item.item_id = plugin_docman_new_document_upload.parent_id)
             LEFT JOIN `groups` ON (`groups`.group_id = plugin_docman_item.group_id)
             WHERE ? >= plugin_docman_new_document_upload.expiration_date OR `groups`.status = "D" OR `groups`.group_id IS NULL',
            $current_time
        );
    }

    public function deleteByItemID($item_id)
    {
        $this->getDB()->delete('plugin_docman_new_document_upload', ['item_id' => $item_id]);
    }
}
