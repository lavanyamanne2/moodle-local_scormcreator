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

// Version.php.
$string['pluginname'] = 'SCORM-CREATOR';

// Access.php.
$string['scormcreator:viewpages'] = 'View Pages';
$string['scormcreator:managepages'] = 'Manage Pages';

// Settings.php.
$string['cscorm'] = 'Create HTML5 SCORM';
$string['dscorm'] = 'HTML5 SCORM packages';

// Manifest.php & editmanifest.php.
$string['mpageheader'] = 'SCORM-CREATOR IMSMANIFEST DATA';
$string['mpageheader_help'] = 'SCORM Creator IMSManifest Data';
$string['mformheader'] = "Configure scorm imsmanifest xml:";
$string['seriestitle'] = 'Series title';
$string['seriestitle_help'] = 'Series title is the SCORM main title, can be alphanumeric characters.<br>
                               ex: Internal Training Service Console. <br>
                               ex: Aeronautics Series.';
$string['sessiontitle'] = 'Session title';
$string['sessiontitle_help'] = 'Session title is the SCORM session title, can be alphanumeric characters.<br>
                                ex: What is a Support Case? <br>
                                ex: Ecommerce | Creating Shipment Data.';
$string['resourceidone'] = 'Resource ID';
$string['resourceidone_help'] = 'Resource is a container element for an individual file. It is a two digit code format [<b>resource_00_00</b>]<br>
                                 ex: resource_03_06 <br>
                                 ex: resource_09_05';
$string['resourceidtwo'] = 'Resource ID';
$string['resourceidtwo_help'] = 'Resource is a container element for an individual file. It is a two digit code format
                                [<b>resource_00_00</b>]<br>
                                 ex: resource_03_06 <br>
                                 ex: resource_09_05';
$string['resourcetext'] = 'Numeric value for the series and session.';
$string['series'] = 'Series ##';
$string['session'] = 'Session ##';
$string['landingpage'] = 'Landing page';
$string['landingpage_help'] = 'Landing page is a quick launch of the SCORM package,
                               followed by <b>launch.htm/launch.html/index.htm/index.html</b>';
$string['landingpagetext'] = 'The launching html file that launches the scorm.';
$string['template'] = 'Template';
$string['temp_select'] = 'Select template';
$string['temp_bluemint'] = 'Bluemint';
$string['temp_classic'] = 'Classic';
$string['temp_mono'] = 'Monolight';
$string['temp_vat'] = 'Varsity';
$string['template_help'] = 'Choose the theme for your template.';
$string['pagecount'] = 'Page count';
$string['pagecount_help'] = 'Count of content pages.';
$string['logo'] = 'Logo';
$string['logo_help'] = 'Upload logo for your SCORM package.<br> Logo supports PNG format<br>
                        with pixels: 200W x 40H.';
$string['error_landingpage'] = 'You must supply one of the values: launch.htm / launch.html / index.htm / index.html';
$string['error_template'] = 'Choose the template';
$string['content_button'] = 'Continue to content pages?';

// Pagecontext.php & editpage.php.
$string['cpageheader'] = 'SCORM-CREATOR Content';
$string['cpageheader_help'] = 'SCORM Creator content';
$string['formheaderone'] = 'Define the page contents:';
$string['formheadertwo'] = 'Transcript';
$string['transcript'] = 'Transcript (optional)';
$string['transcript_help'] = 'Transcript is a help file.';
$string['imsid'] = 'imsid';
$string['imsidno'] = 'imsid {no}';
$string['pagetitle'] = 'Page title';
$string['pagetitleno'] = 'Page{no} title';
$string['pagetitle_help'] = 'Page title can be alphanumeric characters: <br>
                             A-Z <br>
                             a-z <br>
                             0-9 <br>
                             -|:@!&$_ (space) <br>
                             ex: Introduction. <br>
                             ex: Mechanics | Creating Isometric Drawings. <br> ';
$string['pagetitlechars'] = 'Page{no} title must not exceed 100 characters.';
$string['error_pagetitle'] = 'Maximum number of characters is 255!';
$string['pagesubtitle'] = 'Page subtitle';
$string['pagesubtitleno'] = 'Page{no} subtitle';
$string['pagesubtitle_help'] = 'Page subtitle can be alphanumeric characters: <br>
                                A-Z <br>
                                a-z <br>
                                0-9 <br>
                                -|:@!&$_ (space) <br>
                                ex: Creating Isometric Drawings from the objects. <br>
                                ex: Resolving an Isometric extraction issue.';
$string['pagesubtitlechars'] = 'Page{no} subtitle must not exceed 100 characters.';
$string['attachment'] = 'Page{no} video';
$string['attachmentno'] = 'Page{no} video';
$string['attachment_help'] = 'Upload mp4 video by naming in the format:<br>
                               ex: Page1.mp4<br>
                               ex: Page2.mp4<br>
                               Make sure with a filename.<br>
                               <b>When filename is incorrect, the video might not play during SCORM access.</b>';
$string['webvttfile'] = 'Page{no} webvtt';
$string['webvttfileno'] = 'Page{no} webvtt';
$string['webvttfile_help'] = 'Upload WEBVTT language file.';
$string['error_webvttname'] = 'Maximum captions should be same as page count';
$string['addpage'] = 'Add page';
$string['deletepage'] = 'Delete page {no}';
$string['deletepageno'] = 'Delete page {no}';

// Quiz.php & editquiz.php.
$string['qpageheader'] = 'SCORM-CREATOR Quiz';
$string['qpageheader_help'] = 'SCORM Creator Quiz';
$string['qformheaderone'] = 'Add questions to the quiz page:';
$string['qtitle'] = 'Quiz title';
$string['qtitle_help'] = 'Quiz title can be alphanumeric characters: <br>
                          A-Z <br>
                          a-z <br>
                          0-9 <br>
                          -|:@!&$_ (space) <br>
                          ex: Welcome to the quiz session! ';
$string['qtitlechars'] = 'Quiz title must not exceed 255 characters.';
$string['description'] = 'Description';
$string['description_help'] = 'Description can be alphanumeric characters, display on the quiz page.';
$string['qformheadertwo'] = 'Questions';
$string['addquestion'] = 'Add Question';
$string['question'] = 'Question';
$string['questionno'] = 'Question{no}';
$string['question_help'] = 'Question can be alphanumeric characters: <br>
                            A-Z <br>
                            a-z <br>
                            0-9 <br>
                            -|:@!&$_ (space)';
$string['questionchars'] = 'Question{no} must not exceed 100 characters.';
$string['questiontype'] = 'Question type';
$string['questiontypeno'] = 'Question type';
$string['questiontype_help'] = 'Question type';
$string['TF'] = 'True/False';
$string['mul'] = 'Multiple choice';
$string['qcorrect'] = 'Q Correct answer';
$string['qcorrectno'] = 'Q{no} Correct answer';
$string['qcorrect_help'] = 'Q Correct answer can be alphanumeric characters: <br>
                            A-Z <br>
                            a-z <br>
                            0-9 <br>
                            -|:@!&$_ (space)';
$string['qcorrectchars'] = 'Q{no} Correct answer must not exceed 100 characters.';
$string['qincorrect1'] = 'Q{no} Incorrect answer1';
$string['qincorrect1no'] = 'Q{no} Incorrect answer1';
$string['qincorrect1_help'] = 'Q Incorrect answer can be alphanumeric characters: <br>
                               A-Z <br>
                               a-z <br>
                               0-9 <br>
                               -|:@!&$_ (space)';
$string['qincorrect1chars'] = 'Q{no} Incorrect answer must not exceed 100 characters.';
$string['qincorrect2'] = 'Q{no} Incorrect answer2';
$string['qincorrect2no'] = 'Q{no} Incorrect answer2';
$string['qincorrect2_help'] = 'Q Incorrect answer can be alphanumeric characters: <br>
                               A-Z <br>
                               a-z <br>
                               0-9 <br>
                               -|:@!&$_ (space)';
$string['qincorrect2chars'] = 'Q{no} Incorrect answer must not exceed 100 characters.';
$string['qincorrect3'] = 'Q{no} Incorrect answer3';
$string['qincorrect3no'] = 'Q{no} Incorrect answer3';
$string['qincorrect3_help'] = 'Q Incorrect answer can be alphanumeric characters: <br>
                               A-Z <br>
                               a-z <br>
                               0-9 <br>
                               -|:@!&$_ (space)';
$string['qincorrect3chars'] = 'Q{no} Incorrect answer must not exceed 100 characters.';
$string['question'] = 'Questions';
$string['crscorm'] = 'Create SCORM';
$string['erscorm'] = 'Update SCORM';
$string['quiz_button'] = 'Continue to quiz creation?';

// Unique.
$string['required'] = 'You must supply a value here!';
$string['maximumchars'] = 'Maximum of {$a} characters!';
$string['alphanumeric'] = 'You must enter only letters or numbers here!';

// Dscorm.php.
$string['sload'] = 'For security purposes, Browser applications block the links to local files and directories. This includes linking to files on your hard drive, on mapped network drives and accessible via Uniform Naming Convention (UNC) paths. This prevents a number of unpleasant possibilities. You can download the scorm files from Moodledata/temp/local_scormcreator/(yourscorm)';
