<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request;
use Facades\App\Services\InformerService;
use Illuminate\View\View;
use App\Candidate;

class InformerController extends Controller
{
    public function getWelcomePage()
    {

        return view('welcome');
    }

    public function request()
    {
        return InformerService::inform(Request::all());
//        print_r(Request::all());
    }

    public function install()
    {
        return InformerService::insertUserDetails(Request::all());
    }

    public function getInstallationMessage()
    {
        return "The App has been installed successfully";
    }


    public function getHomePage()
    {
        return view('home');
    }


    public function getContactList()
    {
        return InformerService::getContactList();
    }


    public function getCandidateList()
    {
        return InformerService::getCandidateList();
    }


    public function sendRequest()
    {
        $params = Request::all();
        $response = InformerService::sendRequest($params);

        if ($params['action'] == 5 || $params['action'] == 6)
            return view('summary', ['response' => $response, 'actionType' => $params['action']]);
        else
            return $response;
    }


    public function send()
    {
        return InformerService::testMessage();
    }
}
