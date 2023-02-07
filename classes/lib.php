<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * SCORM-CREATOR
 *
 * @package    local_scormcreator
 * @copyright  2023 Lavanya Manne
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 *
 * The local_scormcreator_pluginfile() function defines the placement of an uploaded file.
 *
 * @param My_Type $course
 * @param My_Type $cm
 * @param My_Type $cm
 * @param My_Type $context
 * @param My_Type $filearea
 * @param My_Type $forcedownload
 * @param My_Type $options
 */
function local_scormcreator_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $DB;

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    require_login();

    if ($filearea != 'attachment') {
        return false;
    }

    $itemid = (int)array_shift($args);

    if ($itemid != 0) {
        return false;
    }

    $fs = get_file_storage();

    $filename = array_pop($args);
    if (empty($args)) {
        $filepath = '/';
    } else {
        $filepath = '/'.implode('/', $args).'/';
    }

    $file = $fs->get_file($context->id, 'local_scormcreator', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false;
    }

    // Finally send the file.
    send_stored_file($file, 0, 0, true, $options); // Force to download - security!.
}
