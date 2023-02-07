(function ($) {

    // Override to make it case insensitive
    AblePlayer.prototype.searchFor = function(searchString) {

        // return chronological array of caption cues that match searchTerms
        var captionLang, captions, results, caption, c, i, j;
        results = [];

        // Split searchTerms into an array.
        var searchTerms = searchString.split(' ');
        if (this.captions.length > 0) {
            // Get caption track that matches this.searchLang.
            for (i=0; i < this.captions.length; i++) {
                if (this.captions[i].language === this.searchLang) {
                    captionLang = this.searchLang;
                    captions = this.captions[i].cues;
                }
            }
            if (captions.length > 0) {
                c = 0;
                for (i = 0; i < captions.length; i++) {
                    if ($.inArray(captions[i].components.children[0]['type'], ['string','i','b','u','v','c']) !== -1) {
                        caption = this.flattenCueForCaption(captions[i]);
                        for (j = 0; j < searchTerms.length; j++) {
                            if (caption.toLowerCase().indexOf(searchTerms[j].toLowerCase()) !== -1) {
                                results[c] = [];
                                results[c]['start'] = captions[i].start;
                                results[c]['lang'] = captionLang;
                                results[c]['caption'] = this.highlightSearchTerm(searchTerms,j,caption);
                                c++;
                                break;
                            }
                        }
                    }
                }
            }
        }
        return results;
    };

    // Override to make it case insensitive.
    AblePlayer.prototype.highlightSearchTerm = function(searchTerms, index, resultString) {

        /* Highlight ALL found searchTerms in the current resultString
           index is the first index in the searchTerm array where a match has already been found
           Need to step through the remaining terms to see if they're present as well.
        */
        var i, searchTerm, termIndex, termLength, str1, str2, str3;

        for (i=index; i<searchTerms.length; i++) {
            searchTerm = searchTerms[i];
            termIndex = resultString.toLowerCase().indexOf(searchTerm.toLowerCase());
            if (termIndex !== -1) {
                termLength = searchTerm.length;
                if (termLength > 0) {
                    str1 = resultString.substring(0, termIndex);
                    str2 = '<span class="search-term">' + resultString.substr(termIndex, termLength) + '</span>';
                    str3 = resultString.substring(termIndex+termLength);
                    resultString = str1 + str2 + str3;
                } else {
                    str1 = '<span class="search-term">' + searchTerm + '</span>';
                    str2 = resultString.substring(termIndex+termLength);
                    resultString = str1 + str2;
                }
            }
        }
        return resultString;
    };

    var clearSearchBox = function () {
        $captionSearchForm.find('.caption-search-input').val('').trigger('keyup').focus();
    };

    var ablePlayer = new AblePlayer($('#video1'));
    var $captionSearchForm = $('.caption-search');

    $captionSearchForm.on('keyup', function(e) {
        e.preventDefault();
        var searchTerm = $captionSearchForm.find('.caption-search-input')[0].value;

        if(searchTerm === '') {
            $('#transcript').find('.able-transcript').removeClass('hide');
            $('#results-wrapper').addClass('hide');
        }
        else {
            $('#results').html('');

            ablePlayer.searchDiv = 'results';
            ablePlayer.searchString = searchTerm;
            ablePlayer.showSearchResults();

            $('#search-term').html(searchTerm);
            $('#transcript').find('.able-transcript').addClass('hide');
            $('#results-wrapper').removeClass('hide');
        }
    });

    $captionSearchForm.on('click', '.caption-search-clear', function (e) {
        e.preventDefault();
        clearSearchBox();
    });

    $(document).on('keyup', function(e) {
        if (e.key === "Escape") { 
            clearSearchBox();    
        }
    });
}(jQuery));
