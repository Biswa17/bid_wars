<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use App\Models\ProfileAddress;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_id  = $request->token_id;
        $user = User::where('id',$user_id)->first();
        if($user){
            $data = $user;
            $msg = "User Fetched Sucessfully";
            $status = 200;
        } 
        else{
            $data = [];
            $msg = "User could not be Fetched";
            $status = 401;
        }
        return $this->response($data,$status,$msg);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_profile(Request $request)
    {   
        try{
            $id  = $request->token_id;
            $rules = [
                    'profile_pic' => 'image'];
            
            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $data['validation_errors'] = $errors;
                $status = 422;
                $msg = "Validation error";
            }
            else{
                $data = ['user_id' => $id,];

                if ($request->hasFile('profile_pic')) {
                    $image = $request->file('profile_pic');
                    $imageName = 'profile_pic_' . $id . '.' . 'jpeg';
                    $image->move(public_path('images/profile_picture'), $imageName);
                    $path = 'images/profile_picture/' . $imageName;
                    $data['profile_pic'] = $path;
                }
                $profile = Profile::where('user_id', $id)->first();
                if($profile){
                    $profile->update($data);
                }
                else{
                    $profile = Profile::create($data);
                }

                $data = $profile;
                $msg = "Profile updated successfully";
                $status = 200;
            }
        }
        catch (\Exception $e) {
            $data = [];
            $status = 500;
            $msg = 'Error occurred while updating profile';

            // Log the error for debugging purposes
            \Log::error('Error occurred while updating profile.', [
                'message' => $e->getMessage(),
                'function called' => 'UserController::update',
            ]);
        }
        
        return $this->response($data,$status,$msg);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function update_address(Request $request){

    }
}
