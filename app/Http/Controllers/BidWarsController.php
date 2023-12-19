<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BidWarsController extends Controller
{
    public function index()
    {   
        $data = [];
        $status = 200;
        $message = "This is index page";
        return $this->response($data,$status,$message);
    }
}
