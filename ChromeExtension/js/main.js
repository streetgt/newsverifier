var current_new_id = null;
var news = null;
var alreadyVoted = false;

var opts = {
    lines: 13, // The number of lines to draw
    length: 7, // The length of each line
    width: 4, // The line thickness
    radius: 10, // The radius of the inner circle
    corners: 1, // Corner roundness (0..1)
    rotate: 0, // The rotation offset
    color: '#000', // #rgb or #rrggbb
    speed: 1, // Rounds per second
    trail: 60, // Afterglow percentage
    shadow: false, // Whether to render a shadow
    hwaccel: false, // Whether to use hardware acceleration
    className: 'spinner', // The CSS class to assign to the spinner
    zIndex: 2e9, // The z-index (defaults to 2000000000)
    top: 'auto', // Top position relative to parent in px
    left: 'auto' // Left position relative to parent in px
};

var trget = document.getElementById('spinner');
var spnr = new Spinner(opts).spin(trget);

$(document).keypress(function(e) {
    if(e.which == 13) {
        if($('#url').val().length > 0) {
            processRequest();
        }
    }
});

$('#submit').click(function() {

});

function processRequest() {
    $('#spinner').fadeIn();
    $('#results').hide();
    $('#results-content').empty();

    event.preventDefault();

    var field = $('#results-content');
    var request = $.ajax({
        type: 'POST',
        url: "http://lpji.dev/api/news",
        data: {
            url: $('#url').val(),
        },
        dataType: 'json',
        success: function (data) {
            if (data.status == 500) {
                news = data.item;
                console.log(data.data.current_new.verified)
                data.data.data.forEach(function (item, idx) {
                    field.append('<b>' + item.name + '</b>');
                    field.append('<br><a href="'+item.url+'">' + item.displayUrl+ '</a>');
                    if(idx != data.data.length - 1) {
                        field.append('<hr>');
                    }

                    if(current_new_id == null) {
                        current_new_id = item.source_db_id;
                    }
                })

                var admin_html = '<div class="alert alert-info" role="alert"><b>Esta notícia foi ainda não verificada pela Administração como senda verdadeira.<b></div>';
                if(data.data.current_new.verified == true) {
                    admin_html = '<div class="alert alert-success" role="alert"><b>Esta notícia foi verificada pela Administração como senda verdadeira.<b></div>';
                }
                var classification_html =
                '<span><b>Com base nos resultados, acha a notícia credível?</b></span>' +
                '<div class="buttons">' +
                    '<button id="btnYes" type="button" class="btn btn-success btn-sm" click="upvote('+ data.data.current_new.id+')">Sim</button> ' +
                    '<button id="btnNo" type="button" class="btn btn-danger btn-sm"  click="downvote('+ data.data.current_new.id+')">Não</button>' +
                    '<br><br><span id="upvotes">Upvotes: '+ data.data.current_new.upvotes + '</span><br>' +
                    '<span id="downvotes">Downvotes: '+ data.data.current_new.downvotes + '</span>' +
                    '</div>';

                $('#results-classification').html(admin_html + classification_html);

                document.getElementById("btnYes").addEventListener("click", upvote);
                document.getElementById("btnNo").addEventListener("click", downvote);

            } else if(data.status == 505)
            {
                $('#results').html(data.data);
            }
            console.log(data);
        },
        error: function () {
            $('#results-content').html('<p>An error has occurred</p>');
        },

    });

    request.done(function () {
        $('#results').show();
        $('#url').val("");
        $('#spinner').hide();
    });
}

function upvote(id) {

    event.preventDefault();

    if(alreadyVoted) {
        return
    }

    var request = $.ajax({
        type: 'GET',
        url: "http://lpji.dev/api/news/upvote",
        data: {
            id: current_new_id,
        },
        dataType: 'json',
        success: function (data) {
            if (data.status == 500) {
                $('#upvotes').html('Upvotes: ' + data.data.upvotes);
                $('#downvotes').html('Downvotes: ' + data.data.downvotes);
            }
            alreadyVoted = true;
        },

    });
    request.done(function () {
        $('#btnYes').attr("disabled", true);
        $('#btnNo').attr("disabled", true);
    });
}

function downvote(id) {

    event.preventDefault();

    if(alreadyVoted) {
        return
    }

    var request = $.ajax({
        type: 'GET',
        url: "http://lpji.dev/api/news/downvote",
        data: {
            id: current_new_id,
        },
        dataType: 'json',
        success: function (data) {
            if (data.status == 500) {
                $('#upvotes').html('Upvotes: ' + data.data.upvotes);
                $('#downvotes').html('Downvotes: ' + data.data.downvotes);
            }
            alreadyVoted = true;
        },

    });
    request.done(function () {
        $('#btnYes').attr("disabled", true);
        $('#btnNo').attr("disabled", true);
    });
}