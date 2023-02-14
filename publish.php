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
$scormmaker = new local_scormcreator_scormlib();
$manifest = $scormmaker->local_scormcreator_manifest($imsid);
$page = $scormmaker->local_scormcreator_page($imsid);
$pageoptions = $scormmaker->local_scormcreator_pageoptions($imsid);
$quiz = $scormmaker->local_scormcreator_quiz($imsid);
$quizoptions = $scormmaker->local_scormcreator_quizoptions($imsid);
$qcount = $scormmaker->local_scormcreator_questions($imsid);

/*
    **** SCORM-CREATOR: 1 ****
    Creating the SCORM begins here.
    Create a copy of directory of your chosen template to a scorm directory.
    Rename the template directory to seriestitle.
    On editmode, it will delete the existing directory and create a new directory.
*/

// Create a temporary directory:local_scormcreator in moodledata if not exists.

if (!file_exists($CFG->tempdir .'/local_scormcreator/')) {
    mkdir($CFG->tempdir .'/local_scormcreator/', 0755, true);
}

foreach ($manifest as $m) {
    $template = $m->template;
    $seriestitle = $m->seriestitle;
    $scormname = $m->scorm_name;
    $mid = $m->id;
    $source = 'scorm/templates/'.$template.'';
    $destination = $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid; 

    mkdir($destination, 0755);
    foreach ($iterator = new \RecursiveIteratorIterator(
    new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
    \RecursiveIteratorIterator::SELF_FIRST) as $item) {
        if ($item->isDir()) {
            mkdir($destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        } else {
            copy($item, $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
        }
    }

    // Rename the template directory to seriestile.
    $oldname = $destination;
    $newname = $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid;
    rename($oldname, $newname);
}

/*
    **** SCORM-CREATOR: 2 ****
    Update and save the imsmanifest.xml.
*/

$xml = simplexml_load_file($CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid."/imsmanifest.xml");
foreach ($manifest as $xl) {
    // Update XML tags with php variable.
    $xml->organizations->organization->title = $xl->seriestitle;
    $xml->organizations->organization->item->title = $xl->seriestitle;
    $xml->organizations->organization->item->attributes()->identifierref = "resource_".$xl->rid1."_".$xl->rid2;
    $xml->resources->resource->attributes()->identifier = "resource_".$xl->rid1."_".$xl->rid2;
    $xml->resources->resource->attributes()->href = $xl->landingpage;
}

// Save the updated XML document.
$xml->asXML($CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid."/imsmanifest.xml");

/*
    **** SCORM-CREATOR: 3 ****
    Rename the file launch.htm/launch.html/index.htm/index.html, submitted by the user.
    Update content definition in scripts/launchpage.html.
    Replace the string "var pageArray = new Array(2);" to page dynamic value.
    Replace the string "pageArray[0] = "data/page1.html";" to page dynamic value.
*/

// Rename the file launch.htm/launch.html/index.htm/index.html, submitted by the user.
foreach ($manifest as $xl) {
    rename($CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid.'/launch.htm',
           $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid.'/'.$xl->landingpage);
}

// Update content definition in scripts/launchpage.html.
$scormlaunch = $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid.'/scripts/launchpage.html';
$pcount = count($pageoptions);

// Replace the string "var pageArray = new Array(2);" to page dynamic value.
$plusone = $pcount + 1;
$stringtoreplace1 = "var pageArray = new Array(2);";
$replacewith1 = "var pageArray = new Array(".$plusone.");";
$scormmaker->local_scormcreator_replace_string_infile($scormlaunch, $stringtoreplace1, $replacewith1);

// Replace the string "pageArray[0] = "data/page1.html";" to page dynamic value.
$replacewith2 = [];
for ($x = 0; $x <= $pcount; $x++) {
    $i = $x + 1;
    $stringtoreplace2 = 'pageArray[0] = "data/page1.html";';
    $replacewith2[] = 'pageArray['.$x.'] = "data/page'.$i.'.html";';
}
$pagearray2 = implode("\n    ", $replacewith2);
$scormmaker->local_scormcreator_replace_string_infile($scormlaunch, $stringtoreplace2, $pagearray2);
$endarray = end($replacewith2); // Get end of loop value.
$beforeslash = strtok($endarray, '/'); // Get the string before '/' (slash).
$afterslash  = explode("/", $endarray); // Get the string before '/' (slash).

/*
    **** SCORM-CREATOR: 4 ****
    PHP end takes a reference to a variable as argument. With strict standards enabled.
    It is mandatory to input the result of explode into a variable first.
*/

$afterslashend = end($afterslash);
$afterslashupdate = str_replace($afterslashend, ' ', 'quiz.html";');

$combine = $beforeslash . '/' . $afterslashupdate; // Combine $beforeslash and $afterslashupdate.
$scormmaker->local_scormcreator_replace_string_infile($scormlaunch, $endarray, $combine);

/*
    **** SCORM-CREATOR: 5 ****
    Create pages (page1.html, page2.html....).
    Update stuff the pagetitle, pagesubtitle, page sequence in each page.
*/

// Create pages (page1.html, page2.html, etc.,).
$pcount = count($pageoptions);
for ($x = 2; $x <= $pcount; $x++) {
    copy($CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid.'/data/page2.html',
         $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid.'/data/page'.$x.'.html');
}

// Update the pagetitle, pagesubtitle, page sequence in each page.
foreach ($pageoptions as $po) {
    $num = $po->num;
    $pagetitle = $po->pagetitle;
    $pagesubtitle = $po->pagesubtitle;

    $pcount = count($pageoptions);
    for ($x = 1; $x <= $pcount; $x++) {
        $page = $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid.'/data/page'.$x.'.html';
        $numpage = $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid.'/data/page'.$num.'.html';
        if ($page == $numpage) {

            // Replace pagetitle.
            $stringtoreplace1 = "Series | Session Title";
            $replacewith1 = $pagetitle;
            $scormmaker->local_scormcreator_replace_string_infile($page, $stringtoreplace1, $replacewith1);

            // Replace pagesubtitle.
            $stringtoreplace2 = "Subtitle";
            $replacewith2 = $pagesubtitle;
            $scormmaker->local_scormcreator_replace_string_infile($page, $stringtoreplace2, $replacewith2);

            // Replace page sequence.
            $pagetoreplace3 = "Page ? of ?";
            $fp = $pcount + 1;
            $replacepagewith3 = 'Page '.$x.' of '.$fp.'';
            $scormmaker->local_scormcreator_replace_string_infile($page, $pagetoreplace3, $replacepagewith3);
        }
    }
}

/*
    **** SCORM-CREATOR: 6 ****
    Move transcript file to media directory and replace them in pages.
*/

$file1options = array('subdirs' => 0, 'maxbytes' => '', 'context' => $context,
                      'accepted_types' => array('.webm', '.ogg', '.pdf', '.docx', '.txt'),
                      'return_types' => FILE_INTERNAL | FILE_EXTERNAL );
foreach ($pageoptions as $po) {
    file_save_draft_area_files($po->transcript, $context->id, 'local_scormcreator', 'transcript', '0',
                               $file1options);
    $fs = get_file_storage();
    if ($files = $fs->get_area_files($context->id, 'local_scormcreator', 'transcript', '0', 'sortorder', false)) {
        foreach ($files as $file) {
            $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                       $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);

            $downloadurl = $fileurl->get_port() ? $fileurl->get_scheme() . '://' . $fileurl->get_host() .
                            $fileurl->get_path() . ':' . $fileurl->get_port() : $fileurl->get_scheme() . '://' .
                            $fileurl->get_host() . $fileurl->get_path();
            $path = $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid.'/data/media/';
            $file->copy_content_to($path.'/'.$file->get_filename());
        }
    }
}

/*
    **** SCORM-CREATOR: 7 ****
    Move mp4 videos to media directory and replace them in pages.

*/

$file2options = array('subdirs' => 0, 'maxbytes' => '', 'maxfiles' => 1, 'context' => $context,
                      'accepted_types' => array('video/mp4'),
                      'return_types' => FILE_INTERNAL | FILE_EXTERNAL );
foreach ($pageoptions as $po) {
    file_save_draft_area_files($po->pagevideo, $context->id, 'local_scormcreator', 'attachment', '0',
                               $file2options);
    $fs = get_file_storage();
    if ($files = $fs->get_area_files($context->id, 'local_scormcreator', 'attachment', '0', 'sortorder', false)) {
        foreach ($files as $file) {
            $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                       $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);

            $downloadurl = $fileurl->get_port() ? $fileurl->get_scheme() . '://' . $fileurl->get_host() .
                           $fileurl->get_path() . ':' . $fileurl->get_port() : $fileurl->get_scheme() . '://' .
                           $fileurl->get_host() . $fileurl->get_path();
            $path = $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid.'/data/media/';
            $file->copy_content_to($path.'/'.$file->get_filename());

            // Replace mp4, webm and vtt pages.
            $numpage = $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid."/data/page".$po->num.".html";
            if ($numpage) {
                // Mp4).
                $stringtoreplace47 = "filename.mp4";
                $replacewith47 = $file->get_filename();
                $scormmaker->local_scormcreator_replace_string_infile($numpage, $stringtoreplace47, $replacewith47);

                // Webm.
                $stringtoreplace48 = "filename.webm";
                $beforedot48 = strtok($file->get_filename(), '.'); // Get the string before '.' (dot).
                $replacewith48 = "".$beforedot48.".webm";
                $scormmaker->local_scormcreator_replace_string_infile($numpage, $stringtoreplace48, $replacewith48);
            }
        }
    }
}

/*
    **** SCORM-CREATOR: 8 ****
    Move webvvtfile to lang directory and replace them in pages.

*/

$file3options = array('subdirs' => 0, 'maxbytes' => '', 'maxfiles' => 1, 'context' => $context,
                      'accepted_types' => array('webvtt/vtt'),
                      'return_types' => FILE_INTERNAL | FILE_EXTERNAL );
foreach ($pageoptions as $po) {
    file_save_draft_area_files($po->webvttfile, $context->id, 'local_scormcreator', 'webvttfile', '0', $file3options);
    $fs = get_file_storage();
    if ($files = $fs->get_area_files($context->id, 'local_scormcreator', 'webvttfile', '0', 'sortorder', false)) {
        foreach ($files as $file) {
                $fileurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(),
                                       $file->get_filearea(), $file->get_itemid(), $file->get_filepath(),
                                       $file->get_filename(), false);

                $downloadurl = $fileurl->get_port() ? $fileurl->get_scheme() . '://' . $fileurl->get_host() .
                               $fileurl->get_path() . ':' . $fileurl->get_port() : $fileurl->get_scheme() . '://' .
                               $fileurl->get_host() . $fileurl->get_path();
                $url = '<a href="' . $downloadurl . '">' . $file->get_filename() . '</a><br/>';
                $path = $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid.'/data/lang/en/';

                $file->copy_content_to($path.'/'.$file->get_filename());

                // Replace (webvttfile) to pages.
                $numpage = $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid."/data/page".$po->num.".html";
            if ($numpage) {
                    $stringtoreplace41 = "filename.vtt";
                    $replacewith41 = $file->get_filename();
                    $scormmaker->local_scormcreator_replace_string_infile($numpage, $stringtoreplace41, $replacewith41);
            }
        }
    }
}

/*
    **** SCORM-CREATOR: 9 ****
    Move logo to img directory.

*/

$logooptions = array('subdirs' => 0, 'maxbytes' => '', 'context' => $context,
                     'accepted_types' => array('.png', '.jpg'),
                     'return_types' => FILE_INTERNAL | FILE_EXTERNAL);
foreach ($manifest as $manif) {
    file_save_draft_area_files($manif->logo, $context->id, 'local_scormcreator', 'logo', '0', $logooptions);
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
            $path = $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid.'/data/img/';
            $file->copy_content_to($path.'/'.$file->get_filename());

            $numpage = $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid.'/data/img/'.$file->get_filename().'';
            $numpage2 = $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid.'/data/img/logo_sel.png';
            rename($numpage, $numpage2);

            // Auto resize the logo.
            $image = imagecreatefrompng($numpage2);
            $imgresized = imagescale ($image, 200, 40);
            imagepng($imgresized, $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid.'/data/img/logo_sel.png');
        }
    }
}

/*
    **** SCORM-CREATOR: 10 ****
    We're done with the pages, move to quiz.
    Update all the required strings in quiz.html.

*/

foreach ($quizoptions as $qt) {
    $quiz = $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid."/data/quiz.html";

    // Replace quiz.html (quizheading).
    $stringtoreplace1 = "Quiz Heading";
    $replacewith1 = $qt->qtitle;
    $scormmaker->local_scormcreator_replace_string_infile($quiz, $stringtoreplace1, $replacewith1);

    // Replace quiz.html (querytext).
    $qc = $scormmaker->local_scormcreator_quizoptions($imsid);
    foreach ($qc as $qccd) {
        $stringtoreplace2 = "Quiz Sub-heading";
        $replacewith2 = $qccd->description;
        $scormmaker->local_scormcreator_replace_string_infile($quiz, $stringtoreplace2, $replacewith2);
    }
}

// Update quiz.js.
$qcount = count($quizoptions);
$quiz = $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid."/data/scripts/quiz.js";

// Replace count of questions.
$stringtoreplace1 = "i < 1";
$replacewith1 = "i < ".$qcount."";
$scormmaker->local_scormcreator_replace_string_infile($quiz, $stringtoreplace1, $replacewith1);

// Replace one question per page logic.
$oneque = $qcount - 1;
$stringtoreplace2 = "i > 0";
$replacewith2 = "i > ".$oneque."";
$scormmaker->local_scormcreator_replace_string_infile($quiz, $stringtoreplace2, $replacewith2);

// Replace questions.
$quizc = $scormmaker->local_scormcreator_quizoptions($imsid);
$quecorner = [];

foreach ($quizc as $qa) {

    // Correct answer.
    $qac = array($qa->qcorrect => "0", $qa->qincorrect1 => "1", $qa->qincorrect2 => "2", $qa->qincorrect3 => "3");

    $quiztoreplace3 = '{ question: "Question", answers: ["A", "B", "C", "D"], correctanswer: 0 }';

    if (empty($qa->qincorrect2) && empty($qa->qincorrect3) ) {

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

$quiz = $CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid."/data/scripts/quiz.js";
$questr = array_pop($quecorner);

// Update the question and answers.
$qcount = $scormmaker->local_scormcreator_questions($imsid);
foreach ($qcount as $qc) {
    if ($qc->quecount > 1) { // If there is more than one question.
        $str = implode(", ", $quecorner).", ".$questr." \n "; // The last question willn't have comma at the end.
        $scormmaker->local_scormcreator_replace_string_infile($quiz, $quiztoreplace3, $str);
    } else { // If there is one question.
        $str = implode(", ", $quecorner)."".$questr." \n "; // The last question willn't have comma at the end.
        $scormmaker->local_scormcreator_replace_string_infile($quiz, $quiztoreplace3, $str);
    }
}

/*
    **** SCORM-CREATOR: 11 ****
    We udated all the strings and files to the scorm directory.
    Now zip the directory inside the scorm directory

*/

$rootpath = realpath($CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid."/");
$zip = new ZipArchive();
$zip->open($CFG->tempdir.'/local_scormcreator/'.$seriestitle.$imsid."/".$seriestitle.$imsid.".zip",
           ZipArchive::CREATE | ZipArchive::OVERWRITE);
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootpath), RecursiveIteratorIterator::LEAVES_ONLY);

foreach ($files as $name => $file) {
    if (!$file->isDir()) {
        $filepath = $file->getRealPath();
        $relativepath = substr($filepath, strlen($rootpath) + 1);
        $zip->addFile($filepath, $relativepath);
    }
}
$zip->close();

/*
    **** SCORM-CREATOR: 12 ****
    Update database scormname and redirect to page where zip files can be downloadable.

*/

$getsessiontitle = $scormmaker->local_scormcreator_manifest($imsid);
foreach ($getsessiontitle as $st) {
    $sessiontitle = $st->seriestitle;
    $DB->execute('UPDATE {local_scormcreator_manifest} SET scorm_name="'.$sessiontitle.$imsid.'" WHERE id='.$imsid.'');
    redirect(new moodle_url('/local/scormcreator/dscorm.php'));
}

echo $OUTPUT->header();
echo $OUTPUT->footer();
