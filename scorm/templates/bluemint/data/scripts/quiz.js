var correctanswer;
var summary = "";

/* Questions. */
var quiz =
[
    { question: "Question", answers: ["A", "B", "C", "D"], correctanswer: 0 }
]

var i = 0;
var score = 0;

$(document).ready(function () {
    $('#start').on('click', function () {
        load_data(i)
        $('#start').hide();
        $('#submitanswer').show('slow');
    });
});

var users_answers = [];
$(document).ready(function () {
    $(document).on('click', '#submitanswer', function () {
        var answer = $('input[name="answers"]:checked').val();
        var answerString = quiz[i].answers[answer];
        $('p[class="useranswer"][value=' + i + ']').text(answerString);
        correctanswer = quiz[i].correctanswer;
        var correctanswer = quiz[i].correctanswer;
        $('p[class="correctanswer"][value=' + i + ']').text(quiz[i].answers[correctanswer]);
        users_answers.push(answer);

        if (answer == quiz[i].correctanswer) {
            score++;
        } else {

        }

        if (!$('input[name="answers"]').is(':checked')) {
            alert("please make a choice");
            return undefined;
        }
        i++;

        if (i < 1) {
            console.log("in")
            load_data(i); 
            $('input[name="answers"]').prop('checked', false);
        } else {
            // Assessment Summary.
            $('#quiz').css('display', 'none');
            $('#start').css('display', 'block');

            var j = 0;
            $.each(quiz, function(index, val) {

                var correctanswer = val.correctanswer;
                summary += " <div class='qtitle'>" + val.question + "</div>";

                $.each(val.answers, function(index, val1) {

                    if (users_answers[j] == correctanswer && correctanswer == index) {
                        summary += "<div><img src='img/t.png' width='20px' height='20px' style='margin:0 0 -4px 4px;'/>" + val1 + "</div>"
                    } else if (users_answers[j] == index) {
                        summary += "<div><img src='img/f.png' width='20px' height='20px' style='margin:0 0 -4px 4px;'/>" + val1 + "</div>"
                    } else if(correctanswer== index) {
                        summary += "<div><img src='img/t.png' width='20px' height='20px' style='margin:0 0 -4px 4px;'/>" + val1 + "</div>"
                    } else {
                        summary += "<div><input type='radio'/> " + val1 + "</div>"
                    }
                });
                j++;
            })
            $("#result").html(summary);
        }

        if (i > 0) {
            $('#quiz').remove();
            var userscore = Math.round(score*(100/i));
            if (userscore > 50) {
                $('#score').html("You got <font color='#00CC0A'>" + score + " </font>right out of <font color='#00CC0A'>" + i + "</font> questions. Your score is:<font color='#00CC0A' size='12pt'> " + Math.round(score*(100/i)) + "%</font>.");
            } else {
                $('#score').html("You got <font color='#FF0000'>" + score + " </font>right out of <font color='#FF0000'>" + i + "</font> questions. Your score is:<font color='#FF0000' size='12pt'> " + Math.round(score*(100/i)) + "%</font>.");
            }
            $('#summary').text('Assessment Summary:');
            $('#result').fadeIn('fast');
            if (parent.RecordTest) {
                parent.RecordTest(Math.round(score*(100/i)));
            }
        }
    });
});

function load_data(count){
    $('#questions').text(quiz[count].question);
    $(".choicediv > p").hide();
    $(".choices").hide();
    $.each(quiz[count].answers, function(index, val) {
        console.log(val)
        $(".choicediv p#"+index).text(val).show();
        $("input[value="+index+"]").show();
    })
}
