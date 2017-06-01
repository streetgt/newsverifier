<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>

    <title>Notícias - LPJI</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Raleway', sans-serif;
            font-weight: 100;
            height: 100vh;
            margin: 0;
        }

        .title {
            text-align: center;
            padding: 10px;
        }

        .title h1 {
            font-size: 64px;
        }

        #search {
            padding-top: 25px;
        }

        #search .form-control {
            border: 1px solid #cc6d27;
        }

        #results {
            display: none;
            padding-top: 20px;
        }

        #spinner {
            margin-top: 50px;
            display: none;
        }

        #classifications {
            text-align: center;
            padding: 10px;
        }
        #classifications .buttons {
            padding-top:20px;
        }

    </style>
</head>
<body>
<div class="container">
    <div class="row">
        <div id="search">
            <div class="col-md-8 col-md-offset-2">
                <div class="title">
                    <span class="label label-danger">Alpha</span>
                    <h1>Notícias</h1>
                </div>

                <form onsubmit="processRequest()">
                    <input id="url" type="text" class="form-control"
                           placeholder="Insira o link de uma notícia...">
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-offset-6">
            <div id="spinner"></div>
        </div>
        <div class="col-md-8 col-md-offset-2">
            <div id="results" class="row">
                <div id="classifications">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <div id="results-classification"></div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div id="results-content" class="panel-body"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="http://spin.js.org/spin.min.js"></script>
</body>

<script>
    var current_new_id = null;
    var news = null;
    var alreadyVoted = false;

    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');

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

    function processRequest() {
        $('#spinner').fadeIn();
        $('#results').hide();
        $('#results-content').empty();

        event.preventDefault();

        var field = $('#results-content');
        var request = $.ajax({
            type: 'POST',
            url: "{{ route('api.news') }}",
            data: {
                url: $('#url').val(),
                _token: CSRF_TOKEN,
            },
            dataType: 'json',
            success: function (data) {
                if (data.status == 500) {
                    console.log("found 500")
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
                        '<button id="btnYes" type="button" class="btn btn-success btn-sm" onclick="upvote('+ data.data.current_new.id+')">Sim</button> ' +
                        '<button id="btnNo" type="button" class="btn btn-danger btn-sm"  onclick="downvote('+ data.data.current_new.id+')">Não</button>' +
                        '<br><br><span id="upvotes">Upvotes: '+ data.data.current_new.upvotes + '</span><br>' +
                        '<span id="downvotes">Downvotes: '+ data.data.current_new.downvotes + '</span>' +
                    '</div>';

                    $('#results-classification').html(admin_html + classification_html);


                }
                else if(data.status == 505)
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

</script>
</html>
