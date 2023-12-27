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
    public function get_profile(Request $request)
    {
        try{
            $user_id = $request->token_id;
            $user = User::with('profile', 'addresses')->find($user_id);
            $data = $user;
            $status = 200;
            $msg = 'fetched user successfully';
        }
        catch (\Exception $e) {
            $data = [];
            $status = 500;
            $msg = 'Error occurred while createing address';

            // Log the error for debugging purposes
            \Log::error('Error occurred while createing address.', [
                'message' => $e->getMessage(),
                'function called' => 'UserController::add_new_address',
            ]);
        }
        return $this->response($data,$status,$msg);

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
    public function create_update_profile(Request $request)
    {   
        try{
            $id  = $request->token_id;
            $profile = Profile::where('user_id',$id)->first();
            if($profile){
                $rules = [
                    'profile_pic' => 'image',
                    'first_name' => 'required|string',
                    'last_name' => 'required|string',
                ];
            }
            else{
                $rules = [
                    'profile_pic' => 'image'
                ];
            }

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = $validator->errors();
                $data['validation_errors'] = $errors;
                $status = 422;
                $msg = "Validation error";
            }
            else{
                $data = $request->all();
                $data['user_id'] = $id;
                
                if ($request->hasFile('profile_picture')) {
                    $image = $request->file('profile_picture');
                    $imageName = 'profile_pic_' . $id . '.' . 'jpeg';
                    $image->move(public_path('images/profile_picture'), $imageName);
                    $path = 'images/profile_picture/' . $imageName;
                    $data['profile_pic'] = $path;
                }
                elseif(!$profile){
                    $path = 'images/profile_picture/user.jpeg';
                    $data['profile_pic'] = $path;
                }


                if($profile){
                    $profile->update($data);
                    $msg = "Profile updated successfully";
                }
                else{
                    $profile = Profile::create($data);
                    $msg = "Profile created successfully";
                }
                $data = $profile;
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

    public function add_new_address(Request $request){
        try{
            $user_id  = $request->token_id;
            $rules = [
                'state'   => 'required',
                'city'    => 'required',
                'pin'     => 'required',
                'address' => 'required',
            ];

            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $data['validation_errors'] = $errors;
                $status = 422;
                $msg = "Validation error";
            }
            else{
                $data = $request->all();
                $data['user_id'] = $user_id;
                $profile_address = ProfileAddress::create($data);

                $data = $profile_address;
                $msg = "Address added successfully";
                $status = 200;
            }
        }
        catch (\Exception $e) {
            $data = [];
            $status = 500;
            $msg = 'Error occurred while createing address';

            // Log the error for debugging purposes
            \Log::error('Error occurred while createing address.', [
                'message' => $e->getMessage(),
                'function called' => 'UserController::add_new_address',
            ]);
        }
        
        return $this->response($data,$status,$msg);
    }

    public function update_address(Request $request, $id){
        try{
            $rules = [];

            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
                $errors = $validator->errors();
                $data['validation_errors'] = $errors;
                $status = 422;
                $msg = "Validation error";
            }
            else{
                $address = ProfileAddress::where('id',$id)->first();

                if($address){
                    $address->update($request->all());
                    $data = $address;
                    $msg = "Address updated sucessfully";
                    $status = 200;
                }
                else{
                    $data = [];
                    $msg = "Address doesnt exist";
                    $status = 400;
                }
            }
        }
        catch (\Exception $e) {
            $data = [];
            $status = 500;
            $msg = 'Error occurred while updateing address';

            // Log the error for debugging purposes
            \Log::error('Error occurred while updateing address.', [
                'message' => $e->getMessage(),
                'function called' => 'UserController::update_address',
            ]);
        }
        
        return $this->response($data,$status,$msg);
    }
}
