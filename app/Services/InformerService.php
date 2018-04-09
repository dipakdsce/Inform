<?php
/**
 * Created by PhpStorm.
 * User: dipak
 * Date: 24/1/18
 * Time: 5:00 PM
 */

namespace App\Services;

use App\InformUser;
use DeepCopy\f006\A;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use App\Candidate;
use App\Action;
use Carbon\Carbon;
use App\Utils\GuzzleProxy;
use Mockery\Exception;

class InformerService
{

    const FLOCK_URL = 'https://api.flock.co/v1/';
    const BOT_ID = 'u:Baasxhxu5cishaxj';
    const  BUTLER_ID = 'u:io0dygzkalyliyyw';
    const BOT_TOKEN = 'c886e596-6b93-46c0-99d0-4fd1682cb3b9';

    const INTERVIEW_TYPE = [
        1 => "Basic Round",
        2 => "Coding Round",
        3 => "Algorithm Round",
        4 => "Hiring Manager Round",
        5 => "Final Round"
    ];

    const  VERDICT = [
        1 => "Pass",
        2 => "Fail",
        3 => "Marginal Pass"
    ];

    public function insertUserDetails($payload)
    {

        \Log::info(json_encode($payload));

        $model = new InformUser();

        $model->user_id = $payload['userId'];
        $model->token = $payload['token'];

        $rosterList = Redis::get('flockRosterList');

        if(!$rosterList) {
            $rosterList = $this->callContactRosterListApi();
            Redis::set("flockRosterList", $rosterList);
        }
        $rosterList = json_decode($rosterList, true);
        foreach($rosterList as $row) {
            if($row['id'] == $payload['userId']) {
                $model->name = $row['firstName'] . " " . $row['lastName'];
            }
        }
        $model->save();

        \Log::info("Saved");
    }


    private function callContactRosterListApi()
    {

        $client = new \GuzzleHttp\Client();

        $response = $client->get('https://api.flock.co/v1/roster.listContacts?token=9cc8ed08-d962-4492-ad97-e48b2d9a58ac');

        return $response->getBody()->getContents();
    }


    public function getContactList()
    {
        return InformUser::all(['name', 'user_id'])->toArray();
    }


    public function getCandidateList()
    {
        return Candidate::all(['c_id', 'name', 'mobile', 'role_applied']);
    }


    public function sendRequest($payload)
    {
        $msg = "";

        if($payload['action'] == '5') {
           /* $result = Action::where('candidate_id', $payload['candidateId'])->orderBy('created_at', SORT_DESC)->get();
            return $result->toArray();*/
           $response = $this->getWaitTime($payload['candidateId'], $payload);

           return $response;

        } /*elseif ($payload['action'] == '4') {

            $action = Action::where('candidate_id', $payload['candidateId'])->where('assignee', $payload['user'])->where('action_type', 3)->first();

            if(isset($payload['date']) && $payload['date']) {
                $action->end_time = Carbon::parse(date_format(date_create($payload['date']), 'Y-m-d H:i:s'));

            } else{
                $action->end_time = Carbon::now('Asia/Kolkata');
            }
            $action->save();


            $response = "<flockml><b>Interview Update</b><br/>";

            $notifyUser = Candidate::select('contact_person')->where('c_id', $payload['candidateId'])->get()->toArray()[0]['contact_person'];

            $token = InformUser::select('token')->where('user_id', $notifyUser)->get()->toArray()[0]['token'];

            $response = $response . $payload['candidate'] . "'s interview has been completed by " . $payload['user'] . "</flockml>";

            $msg = $this->getMessagePayload($response, $token);

            $url = self::FLOCK_URL . 'chat.sendMessage';

            return $this->sendMessage($url, $msg);

        } */else if($payload['action'] == '6') {
            return Candidate::where('purpose', 1)->get()->toArray();
        } else {
            $action = new Action();

            $action->candidate_id = $payload['candidateId'];
            $action->action_type = $payload['action'];
            $action->action_value = array_get($payload, 'manOfAction', " ");
            $action->assignee = array_get($payload, 'user', " ");
            $action->verdict = $payload['verdictType'];
            $action->interview_type = $payload['interview'];
            if(isset($payload['date']) && $payload['date']) {
                $action->start_time = Carbon::parse(date_format(date_create($payload['date']), 'Y-m-d H:i:s'));

            } else{
                $action->start_time = Carbon::now('Asia/Kolkata');
            }

            $action->save();

            $waitTime = $this->getWaitTime($payload['candidateId'], $payload)['totalWaitTime'];

            $candidate = Candidate::where('c_id', $payload['candidateId'])->first();

            $candidate->total_wait_time = $waitTime;

            $candidate->save();

            if ($payload['action'] == 2) {
                $response = "<flockml><b>Candidate Arrived</b><br/>";
                $response = $response . $payload['candidate'] . " is ready for interview<br/>";
                $response = $response . "Interview Type : " . self::INTERVIEW_TYPE[$payload['interview']] . "</flockml>";
                /*$response = $response . "ID : ". $id . "<br/>";
                $response = $response . "Role : " . $payload['role'] . "<br/>";
                $response = $response . "Time : " . Carbon::now('Asia/Kolkata') . "</flockml>";*/

//                $token = InformUser::select('token')->where('user_id', $payload['contactPersonId'])->get()->toArray()[0]['token'];

                $msg = $this->getMessagePayload($response, self::BOT_TOKEN, $payload['contactPersonId']);


            } elseif ($payload['action'] == '3') {

                $response = "<flockml><b>Interview Update</b><br/>";

                $notifyUser = Candidate::select('contact_person')->where('c_id', $payload['candidateId'])->get()->toArray()[0]['contact_person'];

//                $token =  InformUser::select('token')->where('user_id', $notifyUser)->get()->toArray()[0]['token'];

                $response = $response . $payload['candidate'] . "'s interview has been started by " . $payload['user'] . "<br/>";

                $response = $response . "Interview Type : " . self::INTERVIEW_TYPE[$payload['interview']] . "</flockml>";

                $msg = $this->getMessagePayload($response, self::BOT_TOKEN, $notifyUser);

            } elseif($payload['action'] == '4') {

                $response = "<flockml><b>Interview Update</b><br/>";

                $notifyUser = Candidate::select('contact_person')->where('c_id', $payload['candidateId'])->get()->toArray()[0]['contact_person'];

//                $token =  InformUser::select('token')->where('user_id', $notifyUser)->get()->toArray()[0]['token'];

                $response = $response . $payload['candidate'] . "'s interview has been completed by " . $payload['user'] . "<br/>";

                $response = $response . "Interview Type : " . self::INTERVIEW_TYPE[$payload['interview']] . "<br/>";

                $response = $response . "Verdict : " . self::VERDICT[$payload['verdictType']] . "</flockml>";

                $msg = $this->getMessagePayload($response, self::BOT_TOKEN, $notifyUser);

            } elseif ($payload['action'] == '1') {
                $response = "<flockml><b>Interview Reminder</b><br/>";

                $response = $response . $payload['candidate'] . " is waiting for you </br>";

                $response = $response . "Interview Type : " . self::INTERVIEW_TYPE[$payload['interview']] . "</flockml>";

//                $token = InformUser::select('token')->where('user_id', $payload['contactPersonId'])->get()->toArray()[0]['token'];

                $msg = $this->getMessagePayload($response, self::BOT_TOKEN, $payload['contactPersonId']);

            }

            $url = self::FLOCK_URL . 'chat.sendMessage';

            return $this->sendMessage($url, $msg);
        }

    }




    private function getMessagePayload($response, $token, $to)
    {
        $msg = array(
            "token" => $token,
            "to" => $to,
            "text" => '',
            "attachments" => [array(
                "title" => "",
                "description" => "",
                "views" => array(
                    "flockml" => $response
                )
            )]

        );

        return $msg;
    }



    public function inform($payload)
    {
        if($payload['type'] == 'exit') {

            /*$action = new Action();

            $action->candidate_id = $payload['exitUserId'];
            $action->action_type = 7;
            $action->assignee = "";

            $action->save();*/

            $waitTime = $this->getWaitTime($payload['exitUserId'], $payload);

            $candidate = Candidate::where('c_id', $payload['exitUserId'])->first();

            $candidate->total_wait_time = $waitTime;

            $candidate ->save();

            return ["status" => "OK"];
        } else {
            $candidate = new Candidate();

            $candidate->name = $payload['userName'];
            $candidate->contact_person = $payload['contactPersonId'];
            $candidate->role_applied = $payload['role'];
            $candidate->mobile = $payload['mobile'];
            $id = count(Candidate::all()->toArray()) != 0 ? DB::table('candidate')->max('c_id') + 1 : 1;
            Log::info(DB::table('candidate')->max('c_id'));
            $candidate->c_id = $id;
            $candidate->purpose = $payload['purpose'];

            $candidate->save();


            $action = new Action();

            $action->candidate_id = $id;
            $action->action_type = 0;
            $action->action_value = $payload['contactPerson'];
            $action->assignee  = "";
            $action->start_time = Carbon::now('Asia/Kolkata');

            $action->save();

//            $token = InformUser::select('token')->where('user_id', $payload['contactPersonId'])->get()->toArray()[0]['token'];
            $token = self::BOT_TOKEN;

            if($payload['purpose'] == 1) {
                $response = "<flockml><b>Candidate Arrived</b><br/>";
                $response = $response . "Name : " . $payload['userName'] . "<br/>";
                $response = $response . "ID : ". $id . "<br/>";
                $response = $response . "Role : " . $payload['role'] . "<br/>";
                $response = $response . "Time : " . Carbon::now('Asia/Kolkata') . "</flockml>";

                $msg = $this->getMessagePayload($response, $token, $payload['contactPersonId']);
            } else {
                $response = "<flockml><b>" . $payload['userName'] . "</b> has arrived and waiting for you at Front Desk </flockml>";

                $msg = $this->getMessagePayload($response, $token, $payload['contactPersonId']);
            }

            $response = "<flockml><b> Guest Arrived </b><br/>";
            $response = $response . "Name : " . $payload['userName'] . "<br/>";
            $response = $response . "Please provide some refreshment for the Guest" . "</flockml>";

//            $token = self::BUTLER_TOKEN;

            $url = self::FLOCK_URL . 'chat.sendMessage';

            $this->sendMessage($url, $msg); // Sending to Contact Person

            $msg = $this->getMessagePayload($response, $token , self::BUTLER_ID);

            return $this->sendMessage($url, $msg); //Sending to Butler

        }
        /*$candidate = new Candidate();

        $candidate->name = $payload['userName'];
        $candidate->contact_person = $payload['contactPersonId'];
        $candidate->role_applied = $payload['role'];
        $candidate->mobile = $payload['mobile'];
        $id = sizeof(Candidate::all()->toArray()) != 0 ? ((DB::table('candidate')->max('c_id')) + 1) : 1;
        $candidate->c_id = $id;

        $candidate->save();


        $action = new Action();

        $action->candidate_id = $id;
        $action->action_type = 0;
        $action->action_value = $payload['contactPerson'];
        $action->assignee  = "";
        $action->start_time = Carbon::now('Asia/Kolkata');

        $action->save();

//        $token = InformUser::select('token')->where('user_id', $payload['contactPersonId'])->get()->toArray()[0]['token'];

        $token = self::BOT_TOKEN;
        $response = "<flockml><b>Candidate Arrived</b><br/>";
        $response = $response . "Name : " . $payload['userName'] . "<br/>";
        $response = $response . "ID : ". $id . "<br/>";
        $response = $response . "Role : " . $payload['role'] . "<br/>";
        $response = $response . "Time : " . Carbon::now('Asia/Kolkata') . "</flockml>";

        $msg = $this->getMessagePayload($response, $token, $payload['contactPersonId']);


        $url = self::FLOCK_URL . 'chat.sendMessage';

        return $this->sendMessage($url, $msg);*/

    }

    private function getWaitTime($candidateId, $payload)
    {
        /*$result = Action::where('candidate_id', $payload['candidateId'])->orderget()->toArray();

        $startTime = "";
        $endTime = "";
        $user = "";
        $waitTime = 0;
        foreach ($result as $row) {
            if($row['action_type'] == 0) {

                $user = $row['action_value'];
                $startTime = new Carbon($row['start_time']);

            } elseif(!is_null($user) && !is_null($startTime) && ($user == trim($row['assignee'])) && ($row['action_type'] == 2)){

                $endTime = new Carbon($row['start_time']);
                $waitTime = $waitTime + $endTime->diffInMinutes($startTime);
                $startTime = $endTime;
                $endTime = "";
                $user = $row['action_value'];

            } elseif (!is_null($user) && !is_null($startTime) && ($user == $row['assignee']) && $row['action_type'] == 3) {

                $endTime = new Carbon($row['start_time']);
                $waitTime = $waitTime + $endTime->diffInMinutes($startTime);
                $startTime = $endTime;
                $endTime = "";

            } //else if($row['action_type'] == 2 && )
        }
        dd($waitTime);*/


        $result = Action::where('candidate_id', $candidateId)->orderBy('created_at')->get()->toArray();

        $waitTime = 0;
        $startTime = null;
        $response = [];
        $responseRow = array();
        $previousWait = 0;
        foreach ($result as $row) {

            if($row['action_type'] != 4 ) {

                if(is_null($startTime)) {

                    $startTime = new Carbon($row['created_at']);


                } else {
                    $endTime = new Carbon($row['created_at']);
                    $waitTime = $waitTime + $endTime->diffInMinutes($startTime);

                    $responseRow['interviewType'] = $row['interview_type'] ? self::INTERVIEW_TYPE[$row['interview_type']] : '';
                    if(sizeof($response) != 0) {
                        $size = sizeof($response);

                        $responseRow['waitTime'] = $waitTime -$response[$size -1]['waitTime'];
                    } else {
                        $responseRow['waitTime'] = $waitTime;
                    }


                    $startTime = $endTime;
                }
            } else {
                $startTime = new Carbon($row['created_at']);
                $responseRow['verdict'] = self::VERDICT[$row['verdict']];
                $responseRow['interviewer'] = $row['assignee'];
                array_push($response, $responseRow);
                $responseRow = array();
            }
        }
        $response['totalWaitTime'] = $waitTime;
        return $response;
    }

    public function sendMessage($url, $payload)
    {


        $client = new \GuzzleHttp\Client();

        try{
            $client->post($url, [
                \GuzzleHttp\RequestOptions::JSON => $payload
            ]);
            return [
                'status' => 'OK'
            ];
        } catch (\Exception $e) {
            return [
                "status" => 'FAIL',
                "data" => $e->getMessage()
            ];
        }
    }


    public function testMessage()
    {
//        $to = 'u:psowt2wpsta2jdsp';
//        $token = 'u:psowt2wpsta2jdsp';
/*
        $response = "<flockml>Hey Sweetie!</flockml>";

        $msg = $this->getMessagePayload($response, $token, $to);

        $url = self::FLOCK_URL . 'chat.sendMessage';*/

//        return $this->sendMessage($url, $msg); //Sending to Butler

//        $client = new \GuzzleHttp\Client();
//        $response = $client->get('https://api.flock.co/v1/chat.sendMessage?to=u:mjkmehq5j5fffcef&text=Are kaha ho?&token=6766066f-1d2f-4b08-abba-dade233c35af');

//        return $response;

    }
}