window.addEventListener('unload', {
    fetch('/siteAnalytics', {
        method: 'POST',
        body: getStatistics(),
        keepalive: true
    });
}

window.onload = function() {

    // Video.
    var video = document.getElementById("video");

    // Buttons.
    var playButton = document.getElementById("play-pause");
    var rewindButton = document.getElementById("rewind");
    var muteButton = document.getElementById("mute");

    // Sliders.
    var seekBar = document.getElementById("seek-bar");
    var volumeBar = document.getElementById("volume-bar");

    // Event listener for the play/pause button.
    playButton.addEventListener("click", function() {
        if (video.paused == true) {
            // Play the video.
            video.play();

            // Update the button text to 'Pause'.
            playButton.innerHTML = '<img width="20" height="20" src="img/play.svg" />';
        } else {
            // Pause the video.
            video.pause();

            // Update the button text to 'Play'.
            playButton.innerHTML = '<img width="20" height="20" src="img/pause.svg" />';
        }
    });

    // Event listener for the play/pause via spacebar.
    var video = document.getElementById('video');
        document.onkeypress = function(e){
        if((e || window.event).keyCode === 32){
            video.play ? video.pause() : video.play();
        }
    }

    // Event listener for the rewind button.
    rewindButton.addEventListener("click", function() {
        if (video.paused == true) {
            // rewind the video.
            video.currentTime = 0;
        } else {
            // Unmute the video.
            video.currentTime = 0;
        }
    });

    // Event listener for the mute button.
    muteButton.addEventListener("click", function() {
        if (video.muted == false) {
            // Mute the video.
            video.muted = true;
            volumeBar.value = 0;
            // Update the button text.
            muteButton.innerHTML = '<img width="94" height="24" src="img/muted.png" />';
        } else {
            // Unmute the video.
            video.muted = false;
            volumeBar.value = 100;
            // Update the button text.
            muteButton.innerHTML = '<img width="94" height="24" src="img/mute.png" />';
        }
    });

    video.addEventListener("timeupdate", function() {

        function formatTime(seconds) {
            minutes = Math.floor(seconds / 60);
            minutes = (minutes >= 10) ? minutes : "0" + minutes;
            seconds = Math.floor(seconds % 60);
            seconds = (seconds >= 10) ? seconds : "0" + seconds;
            return minutes + ":" + seconds;
        }

        var seconds = video.currentTime;
        currentTime.innerHTML = formatTime(seconds);
    });

    video.addEventListener("timeupdate", function() {

        function formatTime(seconds) {
            minutes = Math.floor(seconds / 60);
            minutes = (minutes >= 10) ? minutes : "0" + minutes;
            seconds = Math.floor(seconds % 60);
            seconds = (seconds >= 10) ? seconds : "0" + seconds;
            return minutes + ":" + seconds;
        }

        var seconds = video.duration;
        durationTime.innerHTML = formatTime(seconds);
    });

    // Event listener for the seek bar.
    seekBar.addEventListener("change", function() {
        // Calculate the new time
        var time = video.duration * (seekBar.value / 100);

        // Update the video time.
        video.currentTime = time;
    });

    // Update the seek bar as the video plays.
    video.addEventListener("timeupdate", function() {
        // Calculate the slider value.
        var value = (100 / video.duration) * video.currentTime;

        // Update the slider value.
        seekBar.value = value;
    });

    // Pause the video when the seek handle is being dragged.
    seekBar.addEventListener("mousedown", function() {
        video.pause();
    });

    // Play the video when the seek handle is dropped.
    seekBar.addEventListener("mouseup", function() {
        video.play();
    });

    // Event listener for the volume bar.
    volumeBar.addEventListener("change", function() {
        // Update the video volume.
        video.volume = volumeBar.value;
    });
}
