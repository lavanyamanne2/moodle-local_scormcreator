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

require('../../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/local/scormcreator/classes/lib.php');
require_once($CFG->dirroot . '/local/scormcreator/classes/locallib.php');

$instance = optional_param('id', 0, PARAM_INT);
$path = optional_param('path', '', PARAM_PATH);
$pageparams = array();

if ($path) {
    $pageparams['path'] = $path;
}

$imsid = required_param('imsid', PARAM_INT);

global $CFG, $USER, $DB, $OUTPUT, $PAGE, $instance, $imsid, $scormmaker;

$PAGE->set_url('/local/scormcreator/edit/editpage.php', array('imsid' => $imsid));

require_login();

$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context($context);

admin_externalpage_setup('cscorm', '', $pageparams);

$header = $SITE->fullname;
$PAGE->set_title(get_string('pluginname', 'local_scormcreator'));
$PAGE->set_heading($header);

$scormmaker = new local_scormcreator_scormlib();

/**
 * Initialization of editpagecontext_form class.
 */
class local_scormcreator_editpage_form extends moodleform {

    /**
     *
     * The definition() function defines the form elements.
     *
     */
    public function definition() {

        global $DB, $CFG, $PAGE, $imsid, $instance, $contextid, $scormmaker, $context;

        $scormmaker = new local_scormcreator_scormlib();

        $mform = $this->_form;

        // Get pageoptions.
        $getpageoptions = $scormmaker->local_scormcreator_pageoptions($imsid);
        $pagecount = count($getpageoptions);

        // Form header one.
        $mform->addElement('header', 'formheaderone', get_string('formheaderone', 'local_scormcreator'));

        // IMSID (hidden).
        $mform->addElement('hidden', 'imsid');
        $mform->setType('imsid', PARAM_INT);
        $mform->setDefault('imsid', $imsid);

        // Form header two.
        $mform->addElement('header', 'formheadertwo', get_string('formheadertwo', 'local_scormcreator'));
        $mform->setExpanded('formheadertwo', true);

        // Transcript.
        $file1options = array('subdirs' => 0, 'maxbytes' => '', 'context' => $context,
                        'accepted_types' => array('.webm', '.ogg', '.pdf', '.docx', '.txt'),
                        'return_types' => FILE_INTERNAL | FILE_EXTERNAL );
        $mform->addElement('filemanager', 'transcript', get_string('transcript', 'local_scormcreator'),
                            null, $file1options);
        $mform->setType('transcript', PARAM_RAW);
        $mform->addHelpButton('transcript', 'transcript', 'local_scormcreator');
        // Set default value on edit mode.
        foreach ($getpageoptions as $go) {
            $mform->setDefault('transcript', $go->transcript);
        }

        // Page loop headers.
        $repeatarray = array();
        $repeateloptions = array();
        $repeatarray[] = $mform->createElement('header', 'pageloopheader',
                         get_string('pagetitleno', 'local_scormcreator'));
        $repeateloptions['pageloopheader']['advanced'] = true;

        // Page title.
        $getsessiontitle = $scormmaker->local_scormcreator_manifest($imsid);
        foreach ($getsessiontitle as $st) {
            $sessiontitle = $st->seriestitle;
        }
        $repeatarray[] = $mform->createElement('text', 'pagetitle', get_string('pagetitleno', 'local_scormcreator'),
                         array('maxlength' => '1333', 'disabled' => 'disabled'));
        $repeatarray[] = $mform->createElement('hidden', 'pagetitleid', 0);
        $repeateloptions['pagetitle']['default'] = $sessiontitle;
        $repeateloptions['pagetitle']['type'] = PARAM_RAW;
        $repeateloptions['pagetitle']['helpbutton'] = array('pagetitle', 'local_scormcreator');
        $mform->setType('pagetitle', PARAM_CLEANHTML);
        $mform->setType('pagetitleid', PARAM_INT);

        // Page subtitle.
        $repeatarray[] = $mform->createElement('text', 'pagesubtitle',
                         get_string('pagesubtitleno', 'local_scormcreator'),
                         array('maxlength' => '1333', 'pattern' => '[A-Za-z0-9-|:~"!@#$%^+&,)\-=}({:;">.|<@?/<!&$_ ]+' ));
        $repeateloptions['pagesubtitle']['disabledif'] = array('limitanswers', 'eq', 0);
        $repeateloptions['pagesubtitle']['rule'] = 'required';
        $repeateloptions['pagesubtitle']['type'] = PARAM_RAW;
        $repeateloptions['pagesubtitle']['helpbutton'] = array('pagesubtitle', 'local_scormcreator');
        $mform->setType('pagesubtitle', PARAM_CLEANHTML);
        $mform->setType('pagesubtitleid', PARAM_INT);
        // Set default value on edit mode.
        $i = 0;
        foreach ($getpageoptions as $go) {
            $mform->setDefault('pagesubtitle['.$i.']', $go->pagesubtitle);
            $i++;
        }

        // Page video.
        $file2options = array('subdirs' => 0, 'maxbytes' => '', 'maxfiles' => 1, 'context' => $context,
                        'accepted_types' => array('video/mp4'), 'return_types' => FILE_INTERNAL | FILE_EXTERNAL );
        $repeatarray[] = $mform->createElement('filemanager', 'attachment',
                         get_string('attachmentno', 'local_scormcreator'), null, $file2options);
        $repeateloptions['attachment']['disabledif'] = array('limitanswers', 'eq', 0);
        $repeateloptions['attachment']['rule'] = 'required';
        $repeateloptions['attachment']['type'] = PARAM_INT;
        $repeateloptions['attachment']['helpbutton'] = array('attachment', 'local_scormcreator');
        $mform->setType('attachment', PARAM_CLEANHTML);
        $mform->setType('attachmentid', PARAM_INT);
        // Set default value on edit mode.
        $i = 0;
        foreach ($getpageoptions as $go) {
            $mform->setDefault('attachment['.$i.']', $go->pagevideo);
            $i++;
        }

        // Page webvtt.
        $file3options = array('subdirs' => 0, 'maxbytes' => '', 'maxfiles' => 1,
                        'context' => $context, 'accepted_types' => array('webvtt/vtt'),
                        'return_types' => FILE_INTERNAL | FILE_EXTERNAL );
        $repeatarray[] = $mform->createElement('filemanager', 'webvttfile',
                         get_string('webvttfileno', 'local_scormcreator'), null, $file3options);
        $repeatarray[] = $mform->createElement('hidden', 'pagesubtitleid', 0);
        $repeateloptions['webvttfile']['disabledif'] = array('limitanswers', 'eq', 0);
        $repeateloptions['webvttfile']['rule'] = 'required';
        $repeateloptions['webvttfile']['type'] = PARAM_INT;
        $repeateloptions['webvttfile']['helpbutton'] = array('webvttfile', 'local_scormcreator');
        $mform->setType('webvttfile', PARAM_CLEANHTML);
        $mform->setType('webvttfileid', PARAM_INT);
        // Set default value on edit mode.
        $i = 0;
        foreach ($getpageoptions as $go) {
            $mform->setDefault('webvttfile['.$i.']', $go->webvttfile);
            $i++;
        }

        $repeatno = $pagecount;
        $this->repeat_elements($repeatarray, $repeatno, $repeateloptions, 'option_repeats', 'option_add_fields', 1,
               get_string('addpage', 'local_scormcreator'), null, true);

        // Action buttons.
        $buttonarray = array();
        $buttonarray[] = $mform->createElement('submit', 'submitbutton', get_string('quiz_button', 'local_scormcreator'));
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', '', false);
    }

    /**
     *
     * The validation() function defines the form validation.
     *
     * @param My_Type $data
     * @param My_Type $files
     */
    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        list($instance, $plugin, $context) = $this->_customdata;
        return $errors;
    }
}

// Save the files.
$file1options = array('subdirs' => 0, 'maxbytes' => '', 'context' => $context,
                'accepted_types' => array('.webm', '.ogg', '.pdf', '.docx', '.txt'),
                'return_types' => FILE_INTERNAL | FILE_EXTERNAL );
$file2options = array('subdirs' => 0, 'maxbytes' => '', 'maxfiles' => 1, 'context' => $context,
                'accepted_types' => array('video/mp4'), 'return_types' => FILE_INTERNAL | FILE_EXTERNAL );
$file3options = array('subdirs' => 0, 'maxbytes' => '', 'maxfiles' => 1, 'context' => $context,
                'accepted_types' => array('text/vtt'), 'return_types' => FILE_INTERNAL | FILE_EXTERNAL );

$mform = new local_scormcreator_editpage_form();

// Form submission.
if ($mform->is_cancelled()) {

    redirect(new moodle_url('/index.php'));

} else if ($mform->is_submitted()) {

    if ($data = $mform->get_data()) {

            // Save page to sc_page if imsid doesn't exists.
        if (!$DB->record_exists('local_scormcreator_page', array('imsid' => $imsid))) {

                $page = new stdClass();
                $timemodified = time();
                $page->imsid = $imsid;
                $page->timemodified = $timemodified;
                $DB->insert_record('local_scormcreator_page', $page);
        }

        // Get pageid.
        $getpage = $scormmaker->local_scormcreator_page($imsid);
        foreach ($getpage as $gp) {
            $pageid = $gp->id;
        }

        // Creating a dummy $data->id to differentiate between INSERT and UPDATE.
        $getpageoptions = $scormmaker->local_scormcreator_pageoptions($imsid);
        $id = array();
        foreach ($getpageoptions as $go) {
            $id[] = $go->id;
        }
        $data->id = $id;

        // INSERT pageoptions to sc_pageoptions if id doesn't exists.
        foreach ($data->pagetitle as $index => $value) {
            $value = trim($value);
            if (isset($value) && $value <> '') {

                $pageoption = new stdClass();
                $timemodified = time();
                $pageoption->pagetitle = $data->pagetitle[$index];
                $pageoption->id = $data->id[$index];
                $pageoption->imsid = $imsid;
                $pageoption->pageid = $pageid;
                $pageoption->transcript = $data->transcript;
                $pageoption->pagesubtitle = $data->pagesubtitle[$index];
                $pageoption->pagevideo = $data->attachment[$index];
                $pageoption->webvttfile = $data->webvttfile[$index];
                $pageoption->timemodified = $timemodified;
                if (!$DB->record_exists('local_scormcreator_poptions', array('id' => $pageoption->id))) {
                    $DB->insert_record('local_scormcreator_poptions', $pageoption);
                }
            }
        }

        // UPDATE pageoptions to sc_pageoptions if id exists.
        foreach ($data->pagetitle as $index => $value) {
            $value = trim($value);
            if (isset($value) && $value <> '') {

                $pageoption = new stdClass();
                $timemodified = time();
                $pageoption->pagetitle = $data->pagetitle[$index];
                $pageoption->id = $data->id[$index];
                $pageoption->imsid = $imsid;
                $pageoption->pageid = $pageid;
                $pageoption->transcript = $data->transcript;
                $pageoption->pagesubtitle = $data->pagesubtitle[$index];
                $pageoption->pagevideo = $data->attachment[$index];
                $pageoption->webvttfile = $data->webvttfile[$index];
                $pageoption->timemodified = $timemodified;
                if ($DB->record_exists('local_scormcreator_poptions', array('id' => $pageoption->id))) {
                    $DB->update_record('local_scormcreator_poptions', $pageoption);
                }
            }
        }

        $getfiles = $scormmaker->local_scormcreator_pageoptions($imsid);
        foreach ($getfiles as $gf) {

            file_save_draft_area_files($gf->transcript, $context->id, 'local_scormcreator', 'transcript',
                                       '0', $file1options);
            file_save_draft_area_files($gf->pagevideo, $context->id, 'local_scormcreator', 'attachment',
                                       '0', $file2options);
            file_save_draft_area_files($gf->webvttfile, $context->id, 'local_scormcreator', 'webvttfile',
                                       '0', $file3options);
            redirect(new moodle_url('/local/scormcreator/edit/editquiz.php', array('imsid' => $imsid)));
        }
    }
}

echo $OUTPUT->header();

$title = get_string('cpageheader', 'local_scormcreator');
echo $OUTPUT->heading_with_help($title, 'cpageheader', 'local_scormcreator');

$mform->display();

echo $OUTPUT->footer();
