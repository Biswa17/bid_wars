<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            // Get all products with images, paginated
            $products = Product::with('images')
                ->paginate($perPage, ['*'], 'page', $page);
            

            $data = $products;
            $status = 200;
            $msg = 'Retrieved all products successfully';
        } catch (\Exception $e) {
            $data = [];
            $status = 500;
            $msg = 'Error occurred while retrieving products';

            // Log the error for debugging purposes
            \Log::error('Error occurred while retrieving products.', [
                'message' => $e->getMessage(),
                'function called' => 'ProductController::index',
            ]);
        }
        return $this->response($data, $status, $msg);
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
        
        try{
            $rules = [
                'name' => 'required|string|unique:products,name',
                'category_id' => 'required|exists:categories,id',
                'images' => 'required|array|min:1',
                'images.*' => 'image',
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
                $data['user_id'] = $request->token_id;
                $product = Product::create($data);
                if($product){
                    if ($request->hasFile('images')) {
                        $images = $request->file('images');
                        $order  = 0;
                        $uploadedImages = [];
                        foreach($images as $image){
                            $randomImageName = 'product_image_' . Str::random(10) . '.jpeg';
                            $image->move(public_path('images/product_image'), $randomImageName);
                            $path = 'images/product_image/' . $randomImageName;
                            $new_data['image_path'] = $path;
                            $new_data['product_id'] = $product->id;
                            $new_data['order_number'] = $order;
                            
                            $product_image = ProductImage::create($new_data);

                            $uploadedImages[] = $product_image;
                            $order++;
                        }
                    
                        $product['images'] = $uploadedImages;
                    }


                }
                
                $data = $product;
                $status = 200;
                $msg = 'Created product successfully';
            }
        }
        catch (\Exception $e) {
            $data = [];
            $status = 500;
            $msg = 'Error occurred while creating product';

            // Log the error for debugging purposes
            \Log::error('Error occurred while creating product.', [
                'message' => $e->getMessage(),
                'function called' => 'ProductController::store',
            ]);
        }
        
        return $this->response($data,$status,$msg);
        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            // Get a product by ID with images
            $product = Product::with('images')->find($id);

            if (!$product) {
                $data = [];
                $status = 404;
                $msg = 'Product not found';
            } else {
                $data = $product;
                $status = 200;
                $msg = 'Retrieved product successfully';
            }
        } catch (\Exception $e) {
            $data = [];
            $status = 500;
            $msg = 'Error occurred while retrieving product';

            // Log the error for debugging purposes
            \Log::error('Error occurred while retrieving product.', [
                'message' => $e->getMessage(),
                'function called' => 'ProductController::show',
            ]);
        }

        return $this->response($data, $status, $msg);
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
    public function update(Request $request, $id)
    {
        try {
            // Find the product by ID
            $product = Product::find($id);

            if (!$product) {
                $data = [];
                $status = 404;
                $msg = 'Product not found';
            } else {
                // Validate the request
                $rules = [
                    'name' => 'string|unique:products,name,' . $id,
                    'category_id' => 'exists:categories,id',
                    'images' => 'array',
                    'images.*' => 'image',
                ];

                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    $errors = $validator->errors();
                    $data['validation_errors'] = $errors;
                    $status = 422;
                    $msg = "Validation error";
                } else {
                    $data = $request->all();
                    $data['user_id'] = $request->token_id;

                    // Update the product data
                    $product->update($data);
                    
                    if($request->delete_image){
                        $delete_image_array = explode(',',$request->delete_image);
                        $number_deleted_image = ProductImage::where('product_id', $product->id)->whereIn('id',$delete_image_array)->delete();
                    }
                    
                    if($request->ordered_image){
                        $ordered_image_array = explode(',',$request->ordered_image);
                        // Update the order of each image
                        foreach ($ordered_image_array as $order => $image_id) {
                            ProductImage::where('id', $image_id)->update(['order_number' => $order]);
                        }
                        $product_images = ProductImage::where('product_id', $product->id)->whereNotIn('id',$ordered_image_array)->get();
                        $order = count($ordered_image_array);
                        foreach($product_images as $product_image){
                            ProductImage::where('id', $product_image->id)->update(['order_number' => $order]);
                            $order++;
                        }
                    }
                    
                    if ($request->hasFile('images')) {
                        $product_images = ProductImage::where('product_id', $product->id)->get();
                        $images = $request->file('images');
                        $order = $product_images->count();
                        

                        foreach ($images as $image) {
                            $randomImageName = 'product_image_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                            $image->move(public_path('images/product_image'), $randomImageName);

                            $path = 'images/product_image/' . $randomImageName;
                            $new_data['image_path'] = $path;
                            $new_data['product_id'] = $product->id;
                            $new_data['order_number'] = $order;

                            // Update or create the product image
                            $product_image = ProductImage::create($new_data);       
                            $order++;
                        }
                        
                    }
                    
                    $product['images'] = $product_images = ProductImage::where('product_id', $product->id)->orderBy('order_number')->get();
                    $data = $product;
                    $status = 200;
                    $msg = 'Updated product successfully';
                }
            }
        } catch (\Exception $e) {
            $data = [];
            $status = 500;
            $msg = 'Error occurred while updating product';

            // Log the error for debugging purposes
            \Log::error('Error occurred while updating product.', [
                'message' => $e->getMessage(),
                'function called' => 'ProductController::update',
            ]);
        }

        return $this->response($data, $status, $msg);
    
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
}
