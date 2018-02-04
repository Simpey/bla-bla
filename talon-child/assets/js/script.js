jQuery(document).ready(function () {
    

    jQuery('#dictionaryForm').on('submit', function (e) {
        // dictionary_result
        e.preventDefault();
        var form = jQuery(this).serialize();
        jQuery.ajax({
            type: "POST",
            url: main.ajaxurl,
            data: {
                action: 'searchDefinition',
                word: jQuery('[name=word]').val(),
            },
            success: function (data) {
                var $feedback = jQuery('#words-meaning-list');
                console.time('render ejs');
                console.log(data);
                data.word = jQuery('[name=word]').val();
                history.pushState(null, document.title, data.push_url);
                // var $parsed = JSON.parse(data);
                // console.log($parsed);
                var html_products = new EJS({url: main.ejs_dir + 'dictionary_result.ejs'}).render(data);
                $feedback.html(html_products);
                console.timeEnd('render ejs');
            },
            error: function (errorThrown) {
                alert(errorThrown);
            }
        });
        return false;
    });

    jQuery('#words-meaning-list').on('click','#singlebutton',function (e) {
        e.preventDefault();
        // console.log('efwef');
        var reqWord = jQuery('#request-word').val();
        var reqDefinition = jQuery('#request-definition').val();
        jQuery.ajax({
            type: "POST",
            url: main.ajaxurl,
            data: {
                action: 'suggestWord',
                word: reqWord,
                definition: reqDefinition
            },
            success: function (data) {
                var $feedback = jQuery('#word-status');
                var successStr="Your request will be checked by admin"
                console.time('render ejs');
                console.log(data);
                $feedback.html(successStr);
                console.timeEnd('render ejs');
            },
            error: function (errorThrown) {
                alert(errorThrown);
            }
        });
        return false
    });
    

    jQuery('#scrabbleForm').on('submit', function (e) {
        // main-form_result
        e.preventDefault();
        var form = jQuery(this).serialize();
        jQuery.ajax({
            type: "POST",
            url: main.ajaxurl,
            data: {
                action: 'searchWord',
                form: form
            },
            success: function (data) {
                var $feedback = jQuery('#scrabbleTable');
                console.time('render ejs');
                console.log(data);
                var elementOffset = jQuery("#scrabbleTable").offset().top - (jQuery("#header").height() + 40);
                // data.reversedLength = Object.values(data.length).reverse();
                jQuery('html, body').animate({scrollTop: elementOffset}, 500);
                var html_products = new EJS({url: main.ejs_dir + 'main_result.ejs',cache: false}).render(data);
                $feedback.html(html_products);
                 // alert(html_products);
                $feedback.find("table").each(function(){
                    var curTable = jQuery(this);
                    curTable.tablesorter({sortList: [[0,0], [1,0]]});
                });
                console.timeEnd('render ejs');
            },
            error: function (errorThrown) {
                alert(errorThrown);
            }
        });
        return false;
    });

            jQuery('#wordTipsForm').on('submit', function (e) {
        // word-tips_result
        e.preventDefault();
        var form = jQuery(this).serialize();
        jQuery.ajax({
            type: "POST",
            url: main.ajaxurl,
            data: {
                action: 'wordTips',
                form: form,
            },
            success: function (data) {
                // alert(data);
                var $feedback = jQuery('#words-tips-table');
                console.time('render ejs');
                console.log(data);
                data.wordLength = jQuery('[name=wt-dlen]').val();
                data.wordBegins = jQuery('[name=wt-bw]').val();
                data.wordEnds = jQuery('[name=wt-bw]').val();
                data.wordContains = jQuery('[name=wt-cw]').val();
                data.wordWithout = jQuery('[name=wt-nw]').val();
                // var test = jQuery.unique(Object.values(data.length));
                //  data.tableTitles = test;
                // var url = jQuery('[name=url]').val();
                // console.log(url);
                history.pushState(null, document.title, data.push_url);
                // alert(data.metadesc);
                 console.log("meta "+data.metadesc);
                jQuery('meta[name*=description]').attr('content', data.metadesc);
                // var $parsed = JSON.parse(data);
                // console.log($parsed);
                // EJS.config({})
                var html_products = new EJS({url: main.ejs_dir + 'word-tips_result.ejs',cache: false}).render(data);
                $feedback.html(html_products);
                // alert(html_products);
                $feedback.find("table").each(function(){
                    var curTable = jQuery(this);
                    curTable.tablesorter({sortList: [[0,0], [1,0]]});
                });
                console.timeEnd('render ejs');
            },
            error: function (errorThrown) {
                alert(errorThrown);
            }
        });
        return false;
    });


});


