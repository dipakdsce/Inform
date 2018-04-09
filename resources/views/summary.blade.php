<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <style>
        .container {
            font-size: 1em;
            border-radius: 2px;
            background: #f2f2f2;
            padding: 75px 15px 10px;
        }

        .card {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            box-shadow: 0px 2px 10px 1px #d0d0d0;
        }

        .card h2 {
            font-size: 1em;
            font-weight: bold;
        }

        .heading {
            background: white;
            height: 60px;
            padding: 10px 0;
            box-shadow: 0px 2px 5px 1px #dcdcdc;
        }

        .rower {
            margin: 0;
        }

        .back-button button {
            margin: 0;
            padding: 10px 20px;
            border-radius: 4px;
            color: #686868;
            background: white;
            border: 1px solid #d7d7d7;
            outline: none;
        }

        .back-button button:hover, .back-button button:focus, .back-button button:active {
            outline: none;
        }

        .back-button {
            text-align: right;
        }

        .back-button form {
            margin-bottom: 0;
        }
    </style>

    <script type="text/javascript">
        $(function () {
            $("#backButton").click(function () {
                $("#responsePage").empty();
                $("#landingPage").show();
            });
        });
    </script>
</head>

<body>
<div class="rower">
    <div class="col-xs-12 heading">
        <div class="col-xs-8  event-head">
            @if($actionType == 5)
                <b>Candidate Summary</b>
            @elseif($actionType == 6)
                <b>Global Summary</b>
            @endif
        </div>
        <div class="col-xs-4 back-button" id="backButton">
            <button type="submit" class="btn" id="back">Back</button>
        </div>
    </div>

</div>
    @if($actionType == 5)
        <div class="container">
            @foreach($response as $key => $row )
                <div class="panel panel-default">
                    <div class="panel-body">
                        @if($key === 'totalWaitTime')
                            <div class="card">
                                <div>
                                    Total Wait Time : <b><?php  echo $row . " Minutes" ?></b>
                                </div>
                            </div>
                        @else
                            <div class="card">
                                @include('CandidateSummary')
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="container">
            @foreach($response as $row)
                <div class="panel panel-default">
                    <div class="panel-body">
                        <div class="card">
                            @include('GlobalSummary')
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</body>
</html>