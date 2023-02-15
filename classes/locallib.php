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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/config.php');
global $CFG, $DB, $PAGE, $COURSE, $USER;
require_login();

/**
 * Initialization of local_scormcreator_scormlib class.
 */
class local_scormcreator_scormlib {

    /**
     *
     * @var $filename
     * @var $stringtoreplace
     * @var $replacewith
     * @var $path
     * @var $defaultvalues
     * @var $imsid
     */
    public $filename, $stringtoreplace, $replacewith, $path, $defaultvalues, $imsid;

    /**
     *
     * The local_scormcreator_deleteDir() delete the existing scorm directory.
     *
     * @param My_Type $dirpath
     */
    public function local_scormcreator_deletedir($dirpath) {

        if (is_dir($dirpath) === true) {

            $files = array_diff(scandir($dirpath), array('.', '..'));

            foreach ($files as $file) {
                self::local_scormcreator_deletedir(realpath($dirpath) . '/' . $file);
            }
            return rmdir($dirpath);
        } else if (is_file($dirpath) === true) {
            return unlink($dirpath);
        }
        return false;
    }

    /**
     *
     * The local_scormcreator_replace_string_infile() replaces the required string.
     *
     * @param My_Type $filename
     * @param My_Type $stringtoreplace
     * @param My_Type $replacewith
     */
    public function local_scormcreator_replace_string_infile($filename, $stringtoreplace, $replacewith) {

        $content = file_get_contents($filename);
        $contentchunks = explode($stringtoreplace, $content);
        $content = implode($replacewith, $contentchunks);
        file_put_contents($filename, $content);
    }

    /**
     *
     * The data_preprocessing() function defines the html editor data.
     *
     * @param My_Type $defaultvalues
     */
    public function data_preprocessing($defaultvalues) {
        if ($this->current->instance) {
            $draftitemid = file_get_submitted_draft_itemid('page');
            $defaultvalues['page']['format'] = $defaultvalues['contentformat'];
            $defaultvalues['page']['text']   = file_prepare_draft_area($draftitemid, $context->id,
                                                'local_scormcreator', 'content', 0,
                                                page_get_editor_options($context), $defaultvalues['content']);
            $defaultvalues['page']['itemid'] = $draftitemid;
        }
        if (!empty($defaultvalues['displayoptions'])) {
            $displayoptions = unserialize($defaultvalues['displayoptions']);
            if (isset($displayoptions['printintro'])) {
                $defaultvalues['printintro'] = $displayoptions['printintro'];
            }
            if (isset($displayoptions['printheading'])) {
                $defaultvalues['printheading'] = $displayoptions['printheading'];
            }
            if (!empty($displayoptions['popupwidth'])) {
                $defaultvalues['popupwidth'] = $displayoptions['popupwidth'];
            }
            if (!empty($displayoptions['popupheight'])) {
                $defaultvalues['popupheight'] = $displayoptions['popupheight'];
            }
        }
    }

    /**
     *
     * The local_scormcreator_manifest() creates a scorm manifest page.
     *
     * @param My_Type $imsid
     */
    public function local_scormcreator_manifest($imsid) {

        global $DB, $CFG;
        $fest = $DB->get_records('local_scormcreator_manifest', ['id' => $imsid]);
        $feststem = [];
        foreach ($fest as $f) {
            $object = new stdClass();
            $object->id = $f->id;
            $object->seriestitle = $f->seriestitle;
            $object->sestitle = $f->sestitle;
            $object->rid1 = $f->rid1;
            $object->rid2 = $f->rid2;
            $object->landingpage = $f->landingpage;
            $object->template = $f->template;
            $object->pagecount = $f->pagecount;
            $object->scorm_name = $f->scorm_name;
            $object->logo = $f->logo;
            $feststem[] = $object;
        }
        return $feststem;
    }

    /**
     *
     * The local_scormcreator_page() intialize the page context.
     *
     * @param My_Type $imsid
     */
    public function local_scormcreator_page($imsid) {

        global $DB, $CFG;
        $fest = $DB->get_records('local_scormcreator_page', ['imsid' => $imsid]);
        $feststem = [];
        foreach ($fest as $f) {
            $object = new stdClass();
            $object->id = $f->id;
            $object->imsid = $f->imsid;
            $feststem[] = $object;
        }
        return $feststem;
    }

    /**
     *
     * The local_scormcreator_pageoptions() creates the page contents.
     *
     * @param My_Type $imsid
     */
    public function local_scormcreator_pageoptions($imsid) {

        global $DB, $CFG;
        $pageoption = $DB->get_records_sql('SELECT ROW_NUMBER() OVER (ORDER BY t.id) AS num, t.* FROM
                                           {local_scormcreator_poptions} t WHERE imsid = ?', [$imsid]);
        $pagestem = [];
        foreach ($pageoption as $po) {
            $object = new stdClass();
            $object->num = $po->num;
            $object->id = $po->id;
            $object->imsid = $po->imsid;
            $object->pageid = $po->pageid;
            $object->transcript = $po->transcript;
            $object->pagetitle = $po->pagetitle;
            $object->pagesubtitle = $po->pagesubtitle;
            $object->pagevideo = $po->pagevideo;
            $object->webvttfile = $po->webvttfile;
            $pagestem[] = $object;
        }
        return $pagestem;
    }

    /**
     *
     * The local_scormcreator_quiz() intialize the quiz context.
     *
     * @param My_Type $imsid
     */
    public function local_scormcreator_quiz($imsid) {

        global $DB, $CFG;
        $fest = $DB->get_records('local_scormcreator_quiz', ['imsid' => $imsid]);
        $feststem = [];
        foreach ($fest as $f) {
            $object = new stdClass();
            $object->id = $f->id;
            $object->imsid = $f->imsid;
            $feststem[] = $object;
        }
        return $feststem;
    }

    /**
     *
     * The local_scormcreator_quizoptions() creates a quiz contents.
     *
     * @param My_Type $imsid
     */
    public function local_scormcreator_quizoptions($imsid) {

        global $DB, $CFG;
        $quizoption = $DB->get_records('local_scormcreator_qoptions', ['imsid' => $imsid]);
        $quizstem = [];
        foreach ($quizoption as $qo) {
            $object = new stdClass();
            $object->id = $qo->id;
            $object->imsid = $qo->imsid;
            $object->quizid = $qo->quizid;
            $object->qtitle = $qo->qtitle;
            $object->description = $qo->description;
            $object->descriptionformat = $qo->descriptionformat;
            $object->qtype = $qo->qtype;
            $object->question = $qo->question;
            $object->qcorrect = $qo->qcorrect;
            $object->qincorrect1 = $qo->qincorrect1;
            $object->qincorrect2 = $qo->qincorrect2;
            $object->qincorrect3 = $qo->qincorrect3;
            $quizstem[] = $object;
        }
        return $quizstem;
    }

    /**
     *
     * The local_scormcreator_questions() creates the questions for the quiz.
     *
     * @param My_Type $imsid
     */
    public function local_scormcreator_questions($imsid) {

        global $DB, $CFG;
        $question = $DB->get_records_sql('SELECT count(*) AS quecount FROM {local_scormcreator_qoptions} WHERE
                                                imsid = ?', [$imsid]);
        $qtype = [];
        foreach ($question as $que) {
            $object = new stdClass();
            $object->quecount = $que->quecount;
            $qtype[] = $object;
        }
        return $qtype;
    }
}
