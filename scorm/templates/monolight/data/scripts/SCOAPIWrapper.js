var _NoError = 0;
var _GeneralException = 101;
var _ServerBusy = 102;
var _InvalidArgumentError = 201;
var _ElementCannotHaveChildren = 202;
var _ElementIsNotAnArray = 203;
var _NotInitialized = 301;
var _NotImplementedError = 401;
var _InvalidSetValue = 402;
var _ElementIsReadOnly = 403;
var _ElementIsWriteOnly = 404;
var _IncorrectDataType = 405;

// Local variable definitions.
var apiHandle = null;
var API = null;
var findAPITries = 0;

function doLMSInitialize() {
    var api = getAPIHandle();
    if (api == null) {
        if (_Debug == true) {
            alert("Unable to locate the LMS's API Implementation.\nLMSInitialize was not successful.");
        }
        return "false";
    }
    try {
        var result = api.LMSInitialize("");
    }
    catch (e) {
        alert("Unable to locate the LMS's API Implementation.\nLMSInitialize was not successful.");
        return "false";
    }

    if (result.toString() != "true") {
      var err = ErrorHandler();
    }

    return result.toString();
}

function doLMSFinish() {
    var api = getAPIHandle();
    if (api == null) {
        if (_Debug == true) {
            alert("Unable to locate the LMS's API Implementation.\nLMSFinish was not successful.");
        }
        return "false";
    } else {
        // Call the LMSFinish function that should be implemented by the API.
        var result = api.LMSFinish("");
        if (result.toString() != "true") {
            var err = ErrorHandler();
        }
    }
    return true;
}

function doLMSGetValue(name) {
    var api = getAPIHandle();
    if (api == null) {
        if (_Debug == true) {
            alert("Unable to locate the LMS's API Implementation.\nLMSGetValue was not successful.");
        }
        return "";
    } else {
        var value = api.LMSGetValue(name);
        var errCode = api.LMSGetLastError().toString();
        if (errCode != _NoError) {
            // An error was encountered so display the error description.
            if (_Debug == true) {
                var errDescription = api.LMSGetErrorString(errCode);
                alert("LMSGetValue("+name+") failed. \n"+ errDescription);
            }
            return "";
        } else {
            return value.toString();
        }
    }
}

function doLMSSetValue(name, value) {
    var api = getAPIHandle();
    if (api == null) {
        if (_Debug == true) {
            alert("Unable to locate the LMS's API Implementation.\nLMSSetValue was not successful.");
        }
        return;
    } else {
        var result = api.LMSSetValue(name, value);
        if (result.toString() != "true") {
            var err = ErrorHandler();
        }
    }
    return;
}

function doLMSCommit() {
    var api = getAPIHandle();
    if (api == null) {
        if (_Debug == true) {
            alert("Unable to locate the LMS's API Implementation.\nLMSCommit was not successful.");
        }
        return "false";
    } else {
        var result = api.LMSCommit("");
        if (result != "true") {
            var err = ErrorHandler();
        }
    }
    return result.toString();
}

function doLMSGetLastError() {
    var api = getAPIHandle();
    if (api == null) {
        if (_Debug == true) {
            alert("Unable to locate the LMS's API Implementation.\nLMSGetLastError was not successful.");
        }
        // Since we can't get the error code from the LMS, return a general error.
        return _GeneralError;
    }
    return api.LMSGetLastError().toString();
}

function doLMSGetErrorString(errorCode) {
    var api = getAPIHandle();
    if (api == null) {
        if (_Debug == true) {
            alert("Unable to locate the LMS's API Implementation.\nLMSGetErrorString was not successful.");
        }
    }
    return api.LMSGetErrorString(errorCode).toString();
}

function doLMSGetDiagnostic(errorCode) {
    var api = getAPIHandle();
    if (api == null) {
        if (_Debug == true) {
            alert("Unable to locate the LMS's API Implementation.\nLMSGetDiagnostic was not successful.");
        }
    }
    return api.LMSGetDiagnostic(errorCode).toString();
}

function LMSIsInitialized() {
    /* There is no direct method for determining if the LMS API is initialized
       for example an LMSIsInitialized function defined on the API so we'll try
       a simple LMSGetValue and trap for the LMS Not Initialized Error.
    */
    var api = getAPIHandle();
    if (api == null) {
        if (_Debug == true) {
            alert("Unable to locate the LMS's API Implementation.\nLMSIsInitialized() failed.");
        }
        return false;
    } else {
        var value = api.LMSGetValue("cmi.core.student_name");
        var errCode = api.LMSGetLastError().toString();
        if (errCode == _NotInitialized) {
            return false;
        } else {
            return true;
        }
    }
}

function ErrorHandler() {
    var api = getAPIHandle();
    if (api == null) {
        if (_Debug == true) {
            alert("Unable to locate the LMS's API Implementation.\nCannot determine LMS error code.");
        }
        return;
    }

    // Check for errors caused by or from the LMS.
    var errCode = api.LMSGetLastError().toString();
    if (errCode != _NoError) {
        // An error was encountered so display the error description.
        var errDescription = api.LMSGetErrorString(errCode);
        if (_Debug == true) {
            errDescription += "\n";
            errDescription += api.LMSGetDiagnostic(null);
            // By passing null to LMSGetDiagnostic, we get any available.
            // Diagnostics on the previous error.
           alert(errDescription);
        }
    }
    return errCode;
}

function getAPIHandle() {
   if (apiHandle == null) {
        apiHandle = getAPI();
   }
   return apiHandle;
}

function findAPI(win) {
    while ((win.API == null) && (win.parent != null) && (win.parent != win)) {
        findAPITries++;
        // Note: 7 is an arbitrary number, but should be more than sufficient.
        if (findAPITries > 7) {
            if (_Debug == true) {
                alert("Error finding API -- too deeply nested.");
            }
            return null;
        }
        win = win.parent;
    }
    return win.API;
}

function getAPI() {
    var theAPI = findAPI(window);
    if ((theAPI == null) && (window.opener != null) && (typeof(window.opener) != "undefined")) {
        theAPI = findAPI(window.opener);
    }
    if (theAPI == null) {
        if (_Debug == true) {
            alert("Unable to find an API adapter");
        }
    }
    return theAPI;
}

