var lessonStatus;
var lastLocation;
var _Debug = false; // Set this to false to turn debugging off.
                    // And get rid of those annoying alert boxes.
                    // Sco load action - function called from default.html as soon as sco loads.
function sco_load() {
    try {
        var result = doLMSInitialize();
    }
    catch(e){
        if (_Debug == true) {
            alert("Unable to locate the LMS's API Implementation.\nLMSCommit was not successful.");
        }
        return;
    }
    if (result == "false") {
        if (_Debug == true) {
            alert("Unable to locate the LMS's API Implementation.\nLMSCommit was not successful.");
        }
        return;
    }
    // Set start time in the global variable.
    // Check for last location of page during last seeion on server.
    lessonStatus = doLMSGetValue("cmi.core.lesson_status");
    lastLocation = doLMSGetValue("cmi.core.lesson_location");
    // Alert("CourseValues: "+CourseValues).
    // Set first time lesson status.
    if (lessonStatus == "not attempted" || lessonStatus == "") {
        lessonStatus = "incomplete";
        doLMSSetValue("cmi.core.lesson_status", lessonStatus);
        doLMSCommit();
    }
    /* Return the last location - it will be either the empty string or undefined or the name of the swf which was
       last loaded.*/
    return true;
}
/* Content page load action - function called on each content page swf load - it sets the current page and tells whether
   currentpage is the last page or not.
   NOTE: this function is no longer in use.*/
function sco_pageload() {
    if (!LMSIsInitialized()) {
        sco_restart();
    }
    doLMSCommit("");
    if(completeStatus == "true"){
        doLMSSetValue("cmi.core.lesson_status", "completed");
        doLMSCommit("");
    }
}
// This function is being used to pass the values to the LMS.
var LMS_valuesCkeck_interval;
var percentComplete;
var curPageName;
var curCompleteStatus;
function scoexit(args){
    if (!LMSIsInitialized()){
        sco_restart();
    }
    doLMSSetValue("cmi.core.lesson_status", args.lessonStatus);
    doLMSSetValue("cmi.core.lesson_location", args.lastLocation);
    doLMSCommit("");
}
// Forcefully exit LMS.
function quitLMS(){
    doLMSCommit("");
    doLMSFinish();
}
// Restart the SCO.
function sco_restart() {
    return doLMSInitialize();
}
