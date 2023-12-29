<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Auction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class AuctionController extends Controller
{   
    public function get_auction($auction_id)
    {
        try {
            $auction = Auction::find($auction_id);
            if($auction){
                $data = $auction;
                $status = 200;
                $msg = 'Auction retrieved successfully';
            }
            else{
                $data = [];
                $status = 204;
                $msg = 'Auction not found';
            }
            
        } catch (\Exception $e) {
            $data = [];
            $status = 500; // 404 Not Found
            $msg = 'Error while fetching Auction';

            // Log the error for debugging purposes
            \Log::error('Error occurred while retrieving auction.', [
                'message' => $e->getMessage(),
                'function called' => 'AuctionController::get_auction',
            ]);
        }

        return $this->response($data, $status, $msg);
    }


    public function create_auction(Request $request)
    {
        try {
            $user_id = $request->token_id;

            // Validation rules
            $rules = [
                'product_id' => 'required|exists:products,id',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time',
                'starting_price' => 'required',
                'maximum_price' => 'required',
            ];

            // Run the validator
            $validator = Validator::make($request->all(), $rules);

            // Check for validation failure
            if ($validator->fails()) {
                $errors = $validator->errors();
                $data['validation_errors'] = $errors;
                $status = 422;
                $msg = "Validation error";
            } else {
                // Validation passed, proceed to create auction


                // Create auction record
                $auctionData = $request->all();
                $auctionData['user_id'] = $user_id;

                $auction = Auction::create($auctionData);

                $data = $auction;
                $status = 200;
                $msg = 'Auction created successfully';
            }
        } catch (\Exception $e) {
            $data = [];
            $status = 500;
            $msg = 'Error occurred while creating auction';

            // Log the error for debugging purposes
            \Log::error('Error occurred while creating auction.', [
                'message' => $e->getMessage(),
                'function called' => 'AuctionController::create_auction',
            ]);
        }

        return $this->response($data, $status, $msg);
    }


    public function update_auction(Request $request, $auction_id)
    {
        try {
            $auction = Auction::find($auction_id);
            
            if(!$auction){
                $data = [];
                $status = 404;
                $msg = 'Auction not found';
                return $this->response($data, $status, $msg);
            }
            

            // Validation rules (similar to create_auction)
            $rules = [
                'product_id' => 'exists:products,id',
                'start_time' => 'date',
                'end_time' => 'date',
                'starting_price' => '',
                'maximum_price' => '',
            ];

            // Run the validator
            $validator = Validator::make($request->all(), $rules);

            // Check for validation failure
            if ($validator->fails()) {
                $errors = $validator->errors();
                $data['validation_errors'] = $errors;
                $status = 422;
                $msg = "Validation error";
            } else {
                // Validation passed, proceed to update auction
                $auction->update($request->all());

                $data = $auction;
                $status = 200;
                $msg = 'Auction updated successfully';
            }
        } 
        catch (QueryException $qe) {
            // Catch SQL exceptions
            $data = [];
            $status = 500;
            $msg = 'check data start time > end time or starting price > maximum price';

            // Log the SQL error for debugging purposes
            \Log::error('Database error occurred while updating auction.', [
                'message' => $qe->getMessage(),
                'function_called' => 'AuctionController::update_auction',
            ]);
        }
        catch (\Exception $e) {
            $data = [];
            $status = 500;
            $msg = 'Error occurred while updating auction';

            // Log the error for debugging purposes
            \Log::error('Error occurred while updating auction.', [
                'message' => $e->getMessage(),
                'function called' => 'AuctionController::update_auction',
            ]);
        }

        return $this->response($data, $status, $msg);
    }


    public function delete_auction($auction_id)
    {
        try {
            $auction = Auction::findOrFail($auction_id);
            $auction->delete();
            $data = [];
            $status = 200;
            $msg = 'Auction deleted successfully';
        } catch (\Exception $e) {
            $data = [];
            $status = 404; // 404 Not Found
            $msg = 'Auction not found';

            // Log the error for debugging purposes
            \Log::error('Error occurred while deleting auction.', [
                'message' => $e->getMessage(),
                'function called' => 'AuctionController::delete_auction',
            ]);
        }

        return $this->response($data, $status, $msg);
    }

    public function get_all_auction(Request $request){
        try {
            // You can apply additional filters based on the request parameters if needed
            $query = Auction::query();

            
            if ($request->has('user_id')) {
                $query->where('user_id', $request->token_id);
            }

            
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            
            $query->orderBy('start_time', 'asc');

            // Retrieve the auctions
            $auctions = $query->get();

            $data =  $auctions;
            $status = 200;
            $msg = 'Auctions retrieved successfully';
        } catch (\Exception $e) {
            $data = [];
            $status = 500;
            $msg = 'Error occurred while retrieving auctions';

            // Log the error for debugging purposes
            \Log::error('Error occurred while retrieving auctions.', [
                'message' => $e->getMessage(),
                'function_called' => 'AuctionController::get_all_auction',
            ]);
        }

        return $this->response($data, $status, $msg);
    }
    
    

}
