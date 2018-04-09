<!DOCTYPE html>
<html lang="en">
<head>
    <title>Inform</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <!--<link href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" rel="Stylesheet"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/vader/jquery-ui.min.css" />
    <script
            src="https://code.jquery.com/ui/1.12.0-rc.2/jquery-ui.min.js"
            integrity="sha256-55Jz3pBCF8z9jBO1qQ7cIf0L+neuPTD1u7Ytzrp2dqo="
            crossorigin="anonymous"></script>
    <script type="text/javascript">
        $(function () {
            var hostName = 'https://9efe19f3.ngrok.io';
            var contactPersonData = [];
            $.ajax({

                url : hostName + "/contacts",
                type : 'GET',
                success : function (response) {
//                    console.log(response);
                    var len = response.length;
                    $("#contactPerson").empty();
                   /* $("#contactPerson").append("<option value='all'>All</option>");*/
                    for(var i=0;i < len ;i++) {
                        var id = response[i]['user_id'];
                        var value =response[i]['name'];
                        var pushData = {
                            "id" : id,
                            "value" : value
                        };

                        contactPersonData.push(pushData);

                    }

                }
            });

            $.ajax({

                url : hostName + '/candidates',
                type : 'GET',
                success : function (response) {

                    var candidateData = [];
//                    console.log(response);
                    var len = response.length;
                    $("#exitUserName").empty();

                    for(var i=0;i < len ;i++) {
                        var id = response[i]['c_id'];
                        var value =response[i]['name'];

                        var pushData = {
                            "id" : id,
                            "value" : value
                        };

                        candidateData.push(pushData);

                    }

                    $("#exitUserName").autocomplete({
                        source: candidateData,
                        delay: 0,
                        minLength : 3,
                        select : function (event, ui) {
                            $("#exitUserId").val(ui.item.id);
                        }
                    }).data("ui-autocomplete")._renderItem = function (ul, item) {
                        return $("<li>")
                            .data("item.autocomplete", item)
                            .append(item.value + "</li>")
                            .appendTo(ul);
                    };

                }
            });

            $("#contactPerson").autocomplete({
                source: contactPersonData,
                delay: 0,
                minLength : 3,
                select : function (event, ui) {
                    $("#user").val(ui.item.id);
                }
            }).data("ui-autocomplete")._renderItem = function (ul, item) {
                return $("<li></li>")
                    .data("item.autocomplete", item)
                    .append(item.value)
                    .appendTo(ul);
            };

            $("#welcome").submit(function (event) {
                event.preventDefault();

                $.ajax({
                    url : hostName + "/request",
                    type : "POST",
                    data : $("#welcome").serialize(),
                    success : function (response) {
                        alert("The request has been submitted successfully, The concerned person will contact you shortly");
                        location.reload();
                    }
                });
            });

            $("#exit").submit(function (event) {
                event.preventDefault();

                $.ajax({
                    url : hostName + "/request",
                    type : "POST",
                    data : $("#exit").serialize(),
                    success : function (response) {
                        alert("The request has been submitted successfully, The concerned person will contact you shortly");
                        location.reload();
                    }
                })
            })

            $("#purpose").change(function () {
                if(this.value == 1) {
                    $("#role").show();
                } else {
                    $("#role").hide();
                    $("#role").val(0);
                }
            })

        });
        $(document).ready(function() {
            $(window).keydown(function(event){
                if(event.keyCode == 13) {
                    event.preventDefault();
                    return false;
                }
            });
        });
    </script>
    <style>

        body {
            max-width: 1440px;
            background-image: linear-gradient(rgba(255,255,255,.9), rgba(255,255,255,.9)),url({{'image/welcome_bg.jpg'}});
            margin-left: 0px;
        }

        #role {
            display: none;
        }
        .btn {
            background: #0abe51;
            color: white;
            font-weight: bold;
        }

        .btn:hover, .btn:focus {
            color: #fff;
        }

        .nav.li.active.a {
            background-color: #337ab7;
        }

        .ui-widget-content {
            background: white;
            color: black;
        }

        .ui-menu .ui-menu-item {
            padding: 10px 20px;
        }

        div#wrapper {
            position: absolute;
            top:  50%;
            left: 50%;
            transform: translate(-50%,-50%);
        }

        .main {
            width: 50%;
            margin: 10px auto 0 500px;
        }

        h1{
            font-size: 32px;
            font-weight: bold;
            margin-left: 11px;
            color: brown;
            margin-top: 0px;
        }

        h4{
            font-size: 18px;
            color: #124289;
            padding-top: 20px;
        }
        .logo{
            width:150px;
        }
        .logo img{
            max-width: 150%;
            margin-top: 20px;
            margin-left: 55px;
        }
    </style>
</head>

<body>
    <div class=" main">
        <div class="logo">
            <img src="{{asset('https://9efe19f3.ngrok.io/image/Directi_logo.png')}}">

        </div>
        <h1>Welcome to Directi!</h1>
        <h4 class="text-header">Please fill the below form</h4>

        <!--<ul class="nav nav-pills">
            <li class="nav-item active">
                <a class="nav-link active" data-toggle="tab" href="#panel1" role="tab">Entry</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#panel2" role="tab">Exit</a>
            </li>
        </ul> -->

        <div class="form">

        <div class="tab-content clearfix">
            <div class="tab-pane fade in active" id="panel1" role="tabpanel">
                <br>
                <form id="welcome">
                    <div class="row">
                        <div class='col-sm-6'>
                            <div class="form-group">
                                <select class="form-control custom-select" name="location" id="location" required="true">
                                    <option value="">Office Location</option>
                                    <option value="0">Bangalore</option>
                                    <option value="1">Delhi</option>
                                    <option value="2">Mumbai</option>
                                    <option value="3">Pune</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class='col-sm-6'>
                            <div class="form-group">
                                <input type="text" class="form-control" id="userName" placeholder="Name of the Guest" name="userName" required="true">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class='col-sm-6'>
                            <div class="form-group">
                                <input type="text" class="form-control" id="mobile" placeholder="Mobile Number" name="mobile" required="true">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class='col-sm-6'>
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class='col-sm-6'>
                            <div class="form-group">
                                <input type="text" class="form-control" id="contactPerson" placeholder="Contact Person" name="contactPerson" required="true">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class='col-sm-6'>
                            <div class="form-group">
                                <select class="form-control custom-select" name="purpose" id="purpose" required="true">
                                    <option value="">Purpose</option>
                                    <option value="1">Interview</option>
                                    <option value="2">Visit</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class='col-sm-6'>
                            <div class="form-group ui-widget">
                                <input type="text" class="form-control" id="role" placeholder="Role Applied For" name="role" required="true">
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="contactPersonId" id="user">

                    <input type="hidden" name="type" value="entry">

                    <div class="form-group">
                        <div class='input-group date' id='submit'>
                            <!--<input type='submit' class="form-control" value="Submit" onclick="addTimeStamp()"/> -->
                            <button type="submit" class="btn" id="formSubmit">Submit Request</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="tab-pane fade" id="panel2" role="tabpanel">
                <br>
                <form id="exit">
                    <div class="row">
                        <div class='col-sm-6'>
                            <div class="form-group">
                                <input type="text" class="form-control" id="exitUserName" placeholder="Name of the Guest" name="exitUserName" required="true">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="exitUserId" id="exitUserId">
                    <input type="hidden" name="type" value="exit">

                    <div class="row">
                        <div class='col-sm-6'>
                            <div class="form-group">
                                <div class='input-group date' id='exitSubmit'>
                                    <!--<input type='submit' class="form-control" value="Submit" onclick="addTimeStamp()"/> -->
                                    <button type="submit" class="btn" id="exitFormSubmit">Submit Request</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>



