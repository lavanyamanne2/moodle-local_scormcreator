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

require('../../config.php');
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/adminlib.php');

$instance = optional_param('id', 0, PARAM_INT);
$path = optional_param('path', '', PARAM_PATH);
$pageparams = array();

if ($path) {
    $pageparams['path'] = $path;
}

$imsid = required_param('imsid', PARAM_INT);

global $CFG, $USER, $DB, $OUTPUT, $PAGE, $instance, $imsid, $context, $scormmaker;

$PAGE->set_url('/local/scormcreator/publish.php', array('imsid' => $imsid));

require_login();

$PAGE->set_pagelayout('admin');
$context = context_system::instance();
$PAGE->set_context( $context );

admin_externalpage_setup('cscorm', '', $pageparams);

$header = $SITE->fullname;
$PAGE->set_title(get_string('pluginname', 'local_scormcreator'));
$PAGE->set_heading($header);

// Define the local_scormcreator_scormlib class functions.
$scormmaker = new local_scormcreator_scorm_lib();

/**
 * Initialization of local_scormcreator_scormlib class.
 */
class local_scormcreator_publish {

    /**
     *
     * @var $imsid
     * @var $context
     * @var $scormmaker
     */
    public $imsid, $context, $scormmaker;

    /**
     *
     * SCORM-CREATOR: 1
       Create a temporary directory:local_scormcreator in moodledata if not exists.
     *
     * @param My_Type $imsid
     */
    public function scormtemp($imsid) {

        global $DB, $CFG;
        if (!file_exists($CFG->tempdir .'/local_scormcreator/')) {
            mkdir($CFG->tempdir .'/local_scormcreator/', 0755, true);
        }
        return true;
    }

    /**
     *
     * SCORM-CREATOR: 2
       Create a copy of directory, of your chosen template to moodledata/local_scormcreator
       Rename the template directory to seriestitle.
     *
     * @param My_Type $imsid
     */
    public function scormcopy($imsid) {

        global $CFG, $imsid, $scormmaker;
        $manifest = $scormmaker->local_scormcreator_manifest($imsid);
        foreach ($manifest as $m) {
            $template = $m->template;
            $seriestitle = $m->seriestitle;
            $scormname = $m->scorm_name;
            $mid = $m->id;
            $source = 'scorm/templates/'.$template.'';
            $destination = $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid;

            $mkdira = mkdir($destination, 0755);
            foreach ($iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
                          \RecursiveIteratorIterator::SELF_FIRST) as $item) {
                if ($item->isDir()) {
                    $mkdirb = mkdir($destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                } else {
                    $mkdirc = copy($item, $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
                }
            }
        }
        return array('mkdira' => $mkdira, 'mkdirb' => $mkdirb, 'mkdirc' => $mkdirc);
    }

    /**
     *
     * SCORM-CREATOR: 3
       Update and save the imsmanifest.xml.
     *
     * @param My_Type $imsid
     */
    public function scormxml($imsid) {

        global $CFG, $imsid, $scormmaker;
        $manifest = $scormmaker->local_scormcreator_manifest($imsid);
        foreach ($manifest as $m) {
            $xml = simplexml_load_file($CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid."/imsmanifest.xml");

            // Update XML tags with php variable.
            $xml->organizations->organization->title = $m->seriestitle;
            $xml->organizations->organization->item->title = $m->seriestitle;
            $xml->organizations->organization->item->attributes()->identifierref = "resource_".$m->rid1."_".$m->rid2;
            $xml->resources->resource->attributes()->identifier = "resource_".$m->rid1."_".$m->rid2;
            $xml->resources->resource->attributes()->href = $m->landingpage;
        }
        // Save the updated XML document.
        $savexml = $xml->asXML($CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid."/imsmanifest.xml");
        return $savexml;
    }

    /**
     *
     * SCORM-CREATOR: 4
       Rename the file launch.htm/launch.html/index.htm/index.html, submitted by the user.
       Update strings, in scripts/launchpage.html.
     *
     * @param My_Type $imsid
     */
    public function scormlaunchpage($imsid) {

        global $CFG, $imsid, $scormmaker;

        // Rename the file launch.htm/launch.html/index.htm/index.html, submitted by the user.
        $manifest = $scormmaker->local_scormcreator_manifest($imsid);
        $pageoptions = $scormmaker->local_scormcreator_pageoptions($imsid);
        foreach ($manifest as $m) {
            $r1 = rename($CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid.'/launch.htm',
                         $CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid.'/'.$m->landingpage);
        }

        // Update content definition in scripts/launchpage.html.
        $scormlaunch = $CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid.'/scripts/launchpage.html';
        $pcount = count($pageoptions);

        // Replace the string "var pageArray = new Array(2);" to page dynamic value.
        $plusone = $pcount + 1;
        $stringtoreplace1 = "var pageArray = new Array(2);";
        $replacewith1 = "var pageArray = new Array(".$plusone.");";
        $r2 = $scormmaker->local_scormcreator_replace_string_infile($scormlaunch, $stringtoreplace1, $replacewith1);

        // Replace the string "pageArray[0] = "data/page1.html";" to page dynamic value.
        $replacewith2 = [];
        for ($x = 0; $x <= $pcount; $x++) {
            $i = $x + 1;
            $stringtoreplace2 = 'pageArray[0] = "data/page1.html";';
            $replacewith2[] = 'pageArray['.$x.'] = "data/page'.$i.'.html";';
        }
        $pagearray2 = implode("\n    ", $replacewith2);
        $r3 = $scormmaker->local_scormcreator_replace_string_infile($scormlaunch, $stringtoreplace2, $pagearray2);
        $endarray = end($replacewith2); // End of loop value.
        $beforeslash = strtok($endarray, '/'); // Get the string before '/' (slash).
        $afterslash = explode("/", $endarray); // Get the string before '/' (slash).
        $afterslashend = end($afterslash);
        $afterslashupdate = str_replace($afterslashend, ' ', 'quiz.html";');
        $combine = $beforeslash . '/' . $afterslashupdate; // Combine $beforeslash and $afterslashupdate.
        $r4 = $scormmaker->local_scormcreator_replace_string_infile($scormlaunch, $endarray, $combine);
        return array ('r1' => $r1, 'r2' => $r2, 'r3' => $r3, 'r4' => $r4);
    }

    /**
     *
     * SCORM-CREATOR: 5
       Create pages (page1.html, page2.html....).
       Update stuff the pagetitle, pagesubtitle, page sequence in each page.
     *
     * @param My_Type $imsid
     */
    public function scormtitle($imsid) {

        global $CFG, $imsid, $scormmaker;
        $manifest = $scormmaker->local_scormcreator_manifest($imsid);
        $pageoptions = $scormmaker->local_scormcreator_pageoptions($imsid);
        foreach ($manifest as $m) {
            // Create pages (page1.html, page2.html, etc.,).
            $pcount = count($pageoptions);
            for ($x = 2; $x <= $pcount; $x++) {
                $r1 = copy($CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid.'/data/page2.html',
                           $CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid.'/data/page'.$x.'.html');
            }
        }

        // Update the pagetitle, pagesubtitle, page sequence in each page.
        foreach ($pageoptions as $po) {
            $num = $po->num;
            $pagetitle = $po->pagetitle;
            $pagesubtitle = $po->pagesubtitle;
            $pcount = count($pageoptions);
            for ($x = 1; $x <= $pcount; $x++) {
                $page = $CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid.'/data/page'.$x.'.html';
                $numpage = $CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid.'/data/page'.$num.'.html';
                if ($page == $numpage) {
                    // Replace pagetitle.
                    $stringtoreplace1 = "Series | Session Title";
                    $replacewith1 = $pagetitle;
                    $r2 = $scormmaker->local_scormcreator_replace_string_infile($page, $stringtoreplace1, $replacewith1);
                    // Replace pagesubtitle.
                    $stringtoreplace2 = "Subtitle";
                    $replacewith2 = $pagesubtitle;
                    $r3 = $scormmaker->local_scormcreator_replace_string_infile($page, $stringtoreplace2, $replacewith2);
                    // Replace page sequence.
                    $pagetoreplace3 = "Page ? of ?";
                    $fp = $pcount + 1;
                    $replacepagewith3 = 'Page '.$x.' of '.$fp.'';
                    $r4 = $scormmaker->local_scormcreator_replace_string_infile($page, $pagetoreplace3, $replacepagewith3);
                }
            }
        }
        return array ('r1' => $r1, 'r2' => $r2, 'r3' => $r3, 'r4' => $r4);
    }

    /**
     *
     SCORM-CREATOR: 6
     Move transcript file to media directory and replace them in pages.
     *
     * @param My_Type $imsid
     */
    public function scormtranscript($imsid) {

        global $CFG, $imsid, $context, $scormmaker;
        $manifest = $scormmaker->local_scormcreator_manifest($imsid);
        $pageoptions = $scormmaker->local_scormcreator_pageoptions($imsid);
        $file1options = array('subdirs' => 0, 'maxbytes' => '', 'context' => $context,
                              'accepted_types' => array('.webm', '.ogg', '.pdf', '.docx', '.txt'),
                              'return_types' => FILE_INTERNAL | FILE_EXTERNAL );
        foreach ($pageoptions as $po) {
            file_save_draft_area_files($po->transcript, $context->id, 'local_scormcreator', 'transcript', '0', $file1options);
            $fs = get_file_storage();
            if ($files = $fs->get_area_files($context->id, 'local_scormcreator', 'transcript', '0', 'sortorder', false)) {
                foreach ($files as $file) {

                    $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                               $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(),
                               false);

                    $downloadurl = $fileurl->get_port() ? $fileurl->get_scheme() . '://' . $fileurl->get_host() .
                                   $fileurl->get_path() . ':' . $fileurl->get_port() : $fileurl->get_scheme() . '://' .
                                   $fileurl->get_host() . $fileurl->get_path();
                    foreach ($manifest as $m) {
                        $path = $CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid.'/data/media/';
                        $transcript = $file->copy_content_to($path.'/'.$file->get_filename());
                        return $transcript;
                    }
                }
            }
        }
    }

    /**
     *
     SCORM-CREATOR: 7
     Move mp4 videos to media directory and replace them in pages.
     *
     * @param My_Type $imsid
     */
    public function scormmedia($imsid) {

        global $CFG, $imsid, $context, $scormmaker;
        $pageoptions = $scormmaker->local_scormcreator_pageoptions($imsid);
        $file2options = array('subdirs' => 0, 'maxbytes' => '', 'maxfiles' => 1, 'context' => $context,
                              'accepted_types' => array('video/mp4'),
                              'return_types' => FILE_INTERNAL | FILE_EXTERNAL );
        foreach ($pageoptions as $po) {
            file_save_draft_area_files($po->pagevideo, $context->id, 'local_scormcreator', 'attachment', '0', $file2options);
            $fs = get_file_storage();
            if ($files = $fs->get_area_files($context->id, 'local_scormcreator', 'attachment', '0', 'sortorder', false)) {
                foreach ($files as $file) {
                    $manifest = $scormmaker->local_scormcreator_manifest($imsid);
                    foreach ($manifest as $m) {
                        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                                   $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(),
                                   false);

                        $downloadurl = $fileurl->get_port() ? $fileurl->get_scheme() . '://' . $fileurl->get_host() .
                                       $fileurl->get_path() . ':' . $fileurl->get_port() : $fileurl->get_scheme() . '://' .
                                       $fileurl->get_host() . $fileurl->get_path();
                        $path = $CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid.'/data/media/';
                        $copyfile = $file->copy_content_to($path.'/'.$file->get_filename());

                        // Replace mp4, webm and vtt pages.
                        $numpage = $CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid."/data/page".$po->num.".html";
                        if ($numpage) {
                            // Mp4.
                            $stringtoreplace47 = "filename.mp4";
                            $replacewith47 = $file->get_filename();
                            $mp4 = $scormmaker->local_scormcreator_replace_string_infile($numpage, $stringtoreplace47,
                                   $replacewith47);

                            // Webm.
                            $stringtoreplace48 = "filename.webm";
                            $beforedot48 = strtok($file->get_filename(), '.');
                            $replacewith48 = "".$beforedot48.".webm";
                            $webm = $scormmaker->local_scormcreator_replace_string_infile($numpage, $stringtoreplace48,
                                    $replacewith48);
                        }
                    }
                }
            }
        }
        return array('copyfile' => $copyfile, 'mp4' => $mp4, 'webm' => $webm);
    }

    /**
     *
     SCORM-CREATOR: 8
     Move webvvtfile to lang directory and replace them in pages.
     *
     * @param My_Type $imsid
     */
    public function scormvtt($imsid) {

        global $CFG, $imsid, $context, $scormmaker;
        $file3options = array('subdirs' => 0, 'maxbytes' => '', 'maxfiles' => 1, 'context' => $context,
                             'accepted_types' => array('webvtt/vtt'),
                             'return_types' => FILE_INTERNAL | FILE_EXTERNAL );
        $pageoptions = $scormmaker->local_scormcreator_pageoptions($imsid);
        foreach ($pageoptions as $po) {
            file_save_draft_area_files($po->webvttfile, $context->id, 'local_scormcreator', 'webvttfile', '0', $file3options);
            $fs = get_file_storage();
            if ($files = $fs->get_area_files($context->id, 'local_scormcreator', 'webvttfile', '0', 'sortorder', false)) {
                foreach ($files as $file) {
                    $manifest = $scormmaker->local_scormcreator_manifest($imsid);
                    foreach ($manifest as $m) {
                        $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                                   $file->get_filearea(), $file->get_itemid(), $file->get_filepath(),
                                   $file->get_filename(), false);

                        $downloadurl = $fileurl->get_port() ? $fileurl->get_scheme() . '://' . $fileurl->get_host() .
                                       $fileurl->get_path() . ':' . $fileurl->get_port() : $fileurl->get_scheme() . '://' .
                                       $fileurl->get_host() . $fileurl->get_path();
                        $url = '<a href="' . $downloadurl . '">' . $file->get_filename() . '</a><br/>';
                        $path = $CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid.'/data/lang/en/';

                        $copyfile = $file->copy_content_to($path.'/'.$file->get_filename());

                        // Replace (webvttfile) to pages.
                        $numpage = $CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid."/data/page".$po->num.".html";
                        if ($numpage) {
                            $stringtoreplace41 = "filename.vtt";
                            $replacewith41 = $file->get_filename();
                            $vtt = $scormmaker->local_scormcreator_replace_string_infile($numpage, $stringtoreplace41,
                            $replacewith41);
                        }
                    }
                }
            }
        }
        return array('copyfile' => $copyfile, 'vtt' => $vtt);
    }

    /**
     *
     SCORM-CREATOR: 9
     Move logo to img directory.
     *
     * @param My_Type $imsid
     */
    public function scormlogo($imsid) {

        global $CFG, $imsid, $context, $scormmaker;
        $logooptions = array('subdirs' => 0, 'maxbytes' => '', 'context' => $context,
                             'accepted_types' => array('.png', '.jpg'),
                             'return_types' => FILE_INTERNAL | FILE_EXTERNAL);
        $manifest = $scormmaker->local_scormcreator_manifest($imsid);
        foreach ($manifest as $m) {
            file_save_draft_area_files($m->logo, $context->id, 'local_scormcreator', 'logo', '0', $logooptions);
            $fs = get_file_storage();
            if ($files = $fs->get_area_files($context->id, 'local_scormcreator', 'logo', '0', 'sortorder', false)) {
                foreach ($files as $file) {
                    $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                               $file->get_filearea(),
                               $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
                    $downloadurl = $fileurl->get_port() ? $fileurl->get_scheme() . '://' . $fileurl->get_host() .
                                   $fileurl->get_path() . ':' . $fileurl->get_port() : $fileurl->get_scheme() . '://' .
                                   $fileurl->get_host() . $fileurl->get_path();

                    $url = '<a href="' . $downloadurl . '">' . $file->get_filename() . '</a><br/>';
                    $path = $CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid.'/data/img/';
                    $copyfile = $file->copy_content_to($path.'/'.$file->get_filename());

                    $numpage = $CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid.'/data/img/'.$file->get_filename().'';
                    $numpage2 = $CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid.'/data/img/logo_sel.png';
                    $rename = rename($numpage, $numpage2);

                    // Auto resize the logo.
                    $image = imagecreatefrompng($numpage2);
                    $imgresized = imagescale ($image, 200, 40);
                    $imagepng = imagepng($imgresized,
                                $CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid.'/data/img/logo_sel.png');
                }
            }
        }
        return array('copyfile' => $copyfile, 'rename' => $rename, 'imagepng' => $imagepng);
    }

    /**
     *
     SCORM-CREATOR: 10
     We are done with the pages, move to quiz. Update all the required strings in quiz.html.
     *
     * @param My_Type $imsid
     */
    public function scormquiz_html($imsid) {

        global $CFG, $imsid, $context, $scormmaker;
        $manifest = $scormmaker->local_scormcreator_manifest($imsid);
        $quizoptions = $scormmaker->local_scormcreator_quizoptions($imsid);
        foreach ($manifest as $m) {
            foreach ($quizoptions as $qt) {
                $quiz = $CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid."/data/quiz.html";

                // Replace quiz.html (quizheading).
                $stringtoreplace1 = "Quiz Heading";
                $replacewith1 = $qt->qtitle;
                $q1 = $scormmaker->local_scormcreator_replace_string_infile($quiz, $stringtoreplace1, $replacewith1);

                // Replace quiz.html (querytext).
                $qc = $scormmaker->local_scormcreator_quizoptions($imsid);
                foreach ($qc as $qccd) {
                    $stringtoreplace2 = "Quiz Sub-heading";
                    $replacewith2 = $qccd->description;
                    $q2 = $scormmaker->local_scormcreator_replace_string_infile($quiz, $stringtoreplace2, $replacewith2);
                }
            }
        }
        return array('q1' => $q1, 'q2' => $q2);
    }

    /**
     *
     SCORM-CREATOR: 11
     Update question count value in quiz.js.
     *
     * @param My_Type $imsid
     */
    public function scormquestion_count($imsid) {

        global $CFG, $imsid, $context, $scormmaker;
        $manifest = $scormmaker->local_scormcreator_manifest($imsid);
        $quizoptions = $scormmaker->local_scormcreator_quizoptions($imsid);
        foreach ($manifest as $m) {
            // Update quiz.js.
            $qcount = count($quizoptions);
            $quiz = $CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid."/data/scripts/quiz.js";

            // Replace count of questions.
            $stringtoreplace1 = "i < 1";
            $replacewith1 = "i < ".$qcount."";
            $q1 = $scormmaker->local_scormcreator_replace_string_infile($quiz, $stringtoreplace1, $replacewith1);

            // Replace one question per page logic.
            $oneque = $qcount - 1;
            $stringtoreplace2 = "i > 0";
            $replacewith2 = "i > ".$oneque."";
            $q2 = $scormmaker->local_scormcreator_replace_string_infile($quiz, $stringtoreplace2, $replacewith2);

        }
        return array ('q1' => $q1, 'q2' => $q2);
    }

    /**
     *
     SCORM-CREATOR: 12
     Update the questions value in quiz.js.
     *
     * @param My_Type $imsid
     */
    public function scormquestions($imsid) {

        global $CFG, $imsid, $context, $scormmaker;
        // Replace questions.
        $quizc = $scormmaker->local_scormcreator_quizoptions($imsid);
        $quecorner = [];
        foreach ($quizc as $qa) {
            // Correct answer.
            $qac = array($qa->qcorrect => "0", $qa->qincorrect1 => "1", $qa->qincorrect2 => "2", $qa->qincorrect3 => "3");
            $quiztoreplace3 = '{ question: "Question", answers: ["A", "B", "C", "D"], correctanswer: 0 }';
            if (empty($qa->qincorrect2) && empty($qa->qincorrect3)) {

                // Shuffle the answers.
                $shufflecase1 = [$qa->qcorrect, $qa->qincorrect1];
                shuffle($shufflecase1);
                $shufflesstringcase1 = '["' . implode('", "', $shufflecase1). '"]';

                if ($qa->qcorrect == $shufflecase1[0]) {
                    $quecorner[] = '{ question: "'.$qa->question.'", answers: '.$shufflesstringcase1.', correctanswer: 0 }';
                } else if ($qa->qcorrect == $shufflecase1[1]) {
                    $quecorner[] = '{ question: "'.$qa->question.'", answers: '.$shufflesstringcase1.', correctanswer: 1 }';
                }
            } else if (empty($qa->qincorrect3) ) {

                // Shuffle the answers.
                $shufflecase2 = [$qa->qcorrect, $qa->qincorrect1, $qa->qincorrect2];
                shuffle($shufflecase2);
                $shufflesstringcase2 = '["' . implode('", "', $shufflecase2). '"]';

                if ($qa->qcorrect == $shufflecase2[0]) {
                    $quecorner[] = '{ question: "'.$qa->question.'", answers: '.$shufflesstringcase2.', correctanswer: 0 }';
                } else if ($qa->qcorrect == $shufflecase2[1]) {
                    $quecorner[] = '{ question: "'.$qa->question.'", answers: '.$shufflesstringcase2.', correctanswer: 1 }';
                } else if ($qa->qcorrect == $shufflecase2[2]) {
                    $quecorner[] = '{ question: "'.$qa->question.'", answers: '.$shufflesstringcase2.', correctanswer: 2 }';
                }
            } else {

                // Shuffle the answers.
                $shufflecase3 = [$qa->qcorrect, $qa->qincorrect1, $qa->qincorrect2, $qa->qincorrect3];
                shuffle($shufflecase3);
                $shufflesstringcase3 = '["' . implode('", "', $shufflecase3). '"]';

                if ($qa->qcorrect == $shufflecase3[0]) {
                    $quecorner[] = '{ question: "'.$qa->question.'", answers: '.$shufflesstringcase3.', correctanswer: 0 }';
                } else if ($qa->qcorrect == $shufflecase3[1]) {
                    $quecorner[] = '{ question: "'.$qa->question.'", answers: '.$shufflesstringcase3.', correctanswer: 1 }';
                } else if ($qa->qcorrect == $shufflecase3[2]) {
                    $quecorner[] = '{ question: "'.$qa->question.'", answers: '.$shufflesstringcase3.', correctanswer: 2 }';
                } else if ($qa->qcorrect == $shufflecase3[3]) {
                    $quecorner[] = '{ question: "'.$qa->question.'", answers: '.$shufflesstringcase3.', correctanswer: 3 }';
                }
            }
        }
        $manifest = $scormmaker->local_scormcreator_manifest($imsid);
        foreach ($manifest as $m) {
            $quiz = $CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid."/data/scripts/quiz.js";
            $questr = array_pop($quecorner);

            // Update the question and answers.
            $qcount = $scormmaker->local_scormcreator_questions($imsid);
            foreach ($qcount as $qc) {
                if ($qc->quecount > 1) { // If there is more than one question.
                    $str = implode(", ", $quecorner).", ".$questr." \n "; // The last question willn't have comma at the end.
                    $q1 = $scormmaker->local_scormcreator_replace_string_infile($quiz, $quiztoreplace3, $str);
                } else { // If there is one question.
                    $str = implode(", ", $quecorner)."".$questr." \n "; // The last question willn't have comma at the end.
                    $q2 = $scormmaker->local_scormcreator_replace_string_infile($quiz, $quiztoreplace3, $str);
                }
            }
        }
        return array('q1' => $q1, 'q2' => $q2);
    }

    /**
     *
     SCORM-CREATOR: 12
     We udated all the strings and files to the scorm directory. Zip the directory.
     *
     * @param My_Type $imsid
     */
    public function scormzip($imsid) {

        global $CFG, $imsid, $context, $scormmaker;
        $manifest = $scormmaker->local_scormcreator_manifest($imsid);
        foreach ($manifest as $m) {
            $rootpath = realpath($CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid."/");
            $zip = new ZipArchive();
            $zip->open($CFG->tempdir.'/local_scormcreator/'.$m->seriestitle.$imsid."/".$m->seriestitle.$imsid.".zip",
                       ZipArchive::CREATE | ZipArchive::OVERWRITE);
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootpath),
                     RecursiveIteratorIterator::LEAVES_ONLY);
            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filepath = $file->getRealPath();
                    $relativepath = substr($filepath, strlen($rootpath) + 1);
                    $zip->addFile($filepath, $relativepath);
                }
            }
        }
        $zipclose = $zip->close();
        return $zipclose;
    }

    /**
     *
     SCORM-CREATOR: 13
     Update database scormname and redirect to page where zip files can be downloadable.
     *
     * @param My_Type $imsid
     */
    public function savescorm($imsid) {

        global $DB, $CFG, $imsid, $context, $scormmaker;
        $getsessiontitle = $scormmaker->local_scormcreator_manifest($imsid);
        foreach ($getsessiontitle as $st) {
            $sessiontitle = $st->seriestitle;
            $savescorm = $DB->execute('UPDATE {local_scormcreator_manifest} SET scorm_name="'.$sessiontitle.$imsid.'"
                          WHERE id = ?', [$imsid]);
            redirect(new moodle_url('/local/scormcreator/dscorm.php'));
        }
        return $savescorm;
    }
}

$publish = new local_scormcreator_publish();
$publish->scormtemp($imsid);
$publish->scormcopy($imsid);
$publish->scormxml($imsid);
$publish->scormlaunchpage($imsid);
$publish->scormtitle($imsid);
$publish->scormtranscript($imsid);
$publish->scormmedia($imsid);
$publish->scormvtt($imsid);
$publish->scormlogo($imsid);
$publish->scormquiz_html($imsid);
$publish->scormquestion_count($imsid);
$publish->scormquestions($imsid);
$publish->scormzip($imsid);
$publish->savescorm($imsid);

echo $OUTPUT->header();
echo $OUTPUT->footer();

