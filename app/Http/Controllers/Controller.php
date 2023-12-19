<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function response($data,$status,$message='',$other=array())
    {
    	$response = array();
        
        if($status==200)
    	{
    		$response = array('status'=>'success','status_code'=>$status,'message'=>$message,'response'=>($data));
		}
    	elseif($status=='')
    	{
    		$response = array('status'=>'failed','status_code'=>203,'message'=>($message!=''?$message:'Invalid request!'),'response'=>array('errors'=>'something went wrong or validation issue.'));
    	}
		else
    	{
    		$response = array('status'=>'failed','status_code'=>$status,'message'=>$message,'response'=>array('errors'=>$data));
    	}

    	return response()->json($response,200);

    }
}
