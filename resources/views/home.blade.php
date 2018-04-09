<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html">
<head>
    <title>Home</title>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <!--<link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}"> -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.20.1/moment.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/eonasdan-bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/eonasdan-bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/vader/jquery-ui.min.css" />
    <script
            src="https://code.jquery.com/ui/1.12.0-rc.2/jquery-ui.min.js"
            integrity="sha256-55Jz3pBCF8z9jBO1qQ7cIf0L+neuPTD1u7Ytzrp2dqo="
            crossorigin="anonymous"></script>


    <script type="text/javascript">

        $(function () {


            var hostName = 'https://9efe19f3.ngrok.io';

            $(function () {
                $('#datetimepicker1').datetimepicker();
            });

            $('#to').datetimepicker({
                format: 'YYYY-MM-DD',
                maxDate: new Date()
            });

            $("#from").datetimepicker({
                format: 'YYYY-MM-DD',
                maxDate: new Date()

            }).on('dp.change', function (e) {
                $('#to').data('DateTimePicker').minDate(e.date._d);
            });

            $.ajax({

                url : hostName + '/candidates',
                type : 'GET',
                success : function (response) {

                    var candidateData = [];
//                    console.log(response);
                    var len = response.length;
                    $("#candidate").empty();

                    for(var i=0;i < len ;i++) {
                        var id = response[i]['c_id'];
                        var value =response[i]['name'];

                        var pushData = {
                            "id" : id,
                            "value" : value
                        };

                        candidateData.push(pushData);

                    }

                    $("#candidate").autocomplete({
                        source: candidateData,
                        delay: 0,
                        minLength : 3,
                        select : function (event, ui) {
                            $("#candidateId").val(ui.item.id);
                        }
                    }).data("ui-autocomplete")._renderItem = function (ul, item) {
                        return $("<li>")
                            .data("item.autocomplete", item)
                            .append(item.value + "</li>")
                            .appendTo(ul);
                    };

                }
            });


            $.ajax({

                url : hostName + '/contacts',
                type : 'GET',
                success : function (response) {

                    var contactPersonData = [];
                    var len = response.length;
                    $("#manOfAction").empty();

                    for(var i=0;i < len ;i++) {
                        var id = response[i]['user_id'];
                        var value =response[i]['name'];

                        var pushData = {
                            "id" : id,
                            "value" : value
                        };

                        contactPersonData.push(pushData);

                    }


                    $("#manOfAction").autocomplete({
                        source: contactPersonData,
                        delay: 0,
                        minLength : 3,
                        select : function (event, ui) {
                            $("#contactPersonId").val(ui.item.id);
                        }
                    }).data("ui-autocomplete")._renderItem = function (ul, item) {
                        return $("<li></li>")
                            .data("item.autocomplete", item)
                            .append(item.value)
                            .appendTo(ul);
                    };

                    $("#manOfAction").siblings().addBack().addClass("ui-screen-hidden");

                }
            });


            $("#homeForm").submit(function (event) {
                event.preventDefault();

                $.ajax({
                    url : hostName + "/send",
                    type : "POST",
                    data : $("#homeForm").serialize(),
                    success : function (response) {
                        if($("#action").val() == 5  || $("#action").val() == 6) {
                            $("#responsePage").append(response);
                            $("#responsePage").show();
                            $("#landingPage").hide();
                        } else {
                            alert("The request has been submitted successfully");
                        }
                    }
                });
            });

            $("#action").change(function () {

                if(this.value != 6) { //Global Summary
                    $("#candidateRow").show();
                }
                if(this.value == 2 || this.value == 1) { //Assign & Remind
                    $("#stakeholder").show();
                    $("#interviewType").show();
                    $("#fromDatePicker").hide();
                    $("#toDatePicker").hide();
                    $("#datepicker").hide();
                    $("#verdict").hide();
                }else if(this.value == 6) {
                    $("#fromDatePicker").hide();
                    $("#toDatePicker").hide();
                    $("#candidateRow").hide();
                    $("#datepicker").hide();
                    $("#verdict").hide();
                    $("#stakeholder").hide();
                    $("#interviewType").hide();
                }else if(this.value == 3) {
                    $("#interviewType").show();
                    $("#stakeholder").hide();
                    $("#fromDatePicker").hide();
                    $("#toDatePicker").hide();
                    $("#datepicker").show();
                    $("#verdict").hide();
                } else if(this.value == 4) {
                    $("#interviewType").show();
                    $("#stakeholder").hide();
                    $("#fromDatePicker").hide();
                    $("#toDatePicker").hide();
                    $("#datepicker").show();
                    $("#verdict").show();
                }else if(this.value == 5) {
                    $("#fromDatePicker").hide();
                    $("#toDatePicker").hide();
                    $("#datepicker").hide();
                    $("#verdict").hide();
                    $("#interviewType").hide();
                    $("#stakeholder").hide();
                }
            });

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
        .fromDatePicker {
            display: none;
        }

        .toDatePicker {
            display: none;
        }

        .stakeholder {
            display: none;
        }
        
        .candidateRow {
            display: none;
        }
        #datepicker {
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

        .ui-widget-content {
            background: white;
            color: black;
        }

        .ui-menu .ui-menu-item {
            padding: 10px 20px;
        }
        
        #interviewType {
            display: none;
        }

        #verdict {
            display: none;
        }
    </style>
</head>
<div id="landingPage">
    <form id="homeForm">
        <div class="container publisherRow" id="publisherRow">
            <div class="row">
                <div class='col-sm-6'>
                    <div class="form-group" >
                        <label>Action</label>
                        <select class="form-control custom-select" name="action" id="action">
                            <option value="0">- Select -</option>
                            <option value="1">Remind</option>
                            <option value="2">Assign</option>
                            <option value="3">Start</option>
                            <option value="4">End</option>
                            <option value="5">Candidate Summary</option>
                            <option value="6">Global Summary</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="container candidateRow" id="candidateRow">
            <div class="row">
                <div class='col-sm-6'>
                    <div class="form-group">
                        <label>Candidate</label>
                        <input type="text" name="candidate" id="candidate" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="container" id="datepicker">
            <div class="row">
                <div class='col-md-6'>
                    <div class="form-group">
                        <div class='input-group date' id='datetimepicker1'>
                            <input type='text' class="form-control" name="date"/>
                            <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container fromDatePicker" id="fromDatePicker">
            <div class="row">
                <div class='col-md-6'>
                    <label for="from">Start Date</label>
                    <div class="form-group">
                        <div class='input-group date' id='from'>

                            <input type='text' class="form-control" name="from" id="fromdate"
                                   value="<?php echo date('Y-m-d'); ?>"/>
                            <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container toDatePicker" id="toDatePicker">
            <div class="row">
                <div class='col-md-6'>
                    <div class="form-group">
                        <label for="to">End Date</label>
                        <div class='input-group date' id='to'>
                            <input type='text' class="form-control" name="to" id="todate"
                                   value="<?php echo date('Y-m-d'); ?>"/>
                            <span class="input-group-addon">
                            <span class="glyphicon glyphicon-calendar"></span>
                        </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container stakeholder" id="stakeholder">
            <div class="row">
                <div class='col-sm-6'>
                    <div class="form-group" >
                        <label>Stakeholder</label>
                        <input type="text" class="form-control" name="manOfAction" id="manOfAction">
                    </div>
                </div>
            </div>
        </div>


        <div class="container" id="interviewType">
            <div class="row">
                <div class='col-sm-6'>
                    <div class="form-group" >
                        <label>Interview Type</label>
                        <select class="form-control custom-select" name="interview">
                            <option value="0">- Select -</option>
                            <option value="1">Basic Round</option>
                            <option value="2">Coding Round</option>
                            <option value="3">Algorithm Round</option>
                            <option value="4">Hiring Manager Round</option>
                            <option value="5">Final Round</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>


        <div class="container" id="verdict">
            <div class="row">
                <div class='col-sm-6'>
                    <div class="form-group" >
                        <label>Verdict</label>
                        <select class="form-control custom-select" name="verdictType">
                            <option value="0">- Select -</option>
                            <option value="1">Pass</option>
                            <option value="2">Fail</option>
                            <option value="3">Marginal Pass</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>


        <input type="hidden" name="user" id="user"
               value= <?php $flockEvent = json_decode($_GET['flockEvent'], true); echo json_encode($flockEvent['userName']);?> >
        <input type="hidden" name="contactPersonId" id="contactPersonId">
        <input type="hidden" name="candidateId" id="candidateId">
        <div class="container">
            <div class="row">
                <div class='col-sm-6'>
                    <div class="form-group">
                        <div class='input-group date' id='submit'>
                            <!--<input type='submit' class="form-control" value="Submit" onclick="addTimeStamp()"/> -->
                            <button type="submit" class="btn" id="formSubmit">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>


<!--<div id="loadingPage" class="loading">
    {{--<img src="<?php echo env('ngRokUrl')?>/flock-app/build/images/loader.gif" height="80px" width="80px">--}}
</div> -->

<div id="responsePage">

</div>
</body>
</html>