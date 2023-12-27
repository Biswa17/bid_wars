<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;
use App\Models\Category;


class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // Validate the request parameters
            $rules = [
                'per_page' => 'integer|min:1',
                'page' => 'integer|min:1',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $errors = $validator->errors();
                $data['validation_errors'] = $errors;
                $status = 422;
                $msg = "Validation error";
            } else {
                // Set default values for per_page and page if not provided
                $per_page = $request->input('per_page', 10);
                $page = $request->input('page', 1);

                // Get paginated categories
                $categories = Category::paginate($per_page, ['*'], 'page', $page);

                $data = $categories->items();
                $status = 200;
                $msg = 'Retrieved categories successfully';
            }
        } catch (\Exception $e) {
            $data = [];
            $status = 500;
            $msg = 'Error occurred while retrieving categories';

            // Log the error for debugging purposes
            \Log::error('Error occurred while retrieving categories.', [
                'message' => $e->getMessage(),
                'function called' => 'CategoryController::index',
            ]);
        }

        return $this->response($data,$status,$msg);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        
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
                'category_name' => 'required|string|unique:categories,category_name',
                'cat_image' => 'required|image',
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
                if ($request->hasFile('cat_image')) {
                    $image = $request->file('cat_image');
                    $imageName = 'cat_image_' . $data['category_name'] . '.' . 'jpeg';
                    $image->move(public_path('images/category_image'), $imageName);
                    $path = 'images/category_image/' . $imageName;
                    $data['category_image'] = $path;
                }
                $category = Category::create($data);
                $data = $category;
                $status = 200;
                $msg = 'Created category successfully';
            }
        }
        catch (\Exception $e) {
            $data = [];
            $status = 500;
            $msg = 'Error occurred while createing category';

            // Log the error for debugging purposes
            \Log::error('Error occurred while createing category.', [
                'message' => $e->getMessage(),
                'function called' => 'CategoryController::store',
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
            // Find the category by ID
            $category = Category::find($id);

            if (!$category) {
                $data = [];
                $status = 404;
                $msg = 'Category not found';
            } else {
                $data = $category;
                $status = 200;
                $msg = 'Retrieved category successfully';
            }
        } catch (\Exception $e) {
            $data = [];
            $status = 500;
            $msg = 'Error occurred while retrieving category';

            // Log the error for debugging purposes
            \Log::error('Error occurred while retrieving category.', [
                'message' => $e->getMessage(),
                'function called' => 'CategoryController::show',
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Find the category by ID
            $category = Category::find($id);

            if (!$category) {
                $data = [];
                $status = 404;
                $msg = 'Category not found';
            } else {
                // Delete the category
                $category->delete();

                $data = [];
                $status = 200;
                $msg = 'Deleted category successfully';
            }
        } catch (\Exception $e) {
            $data = [];
            $status = 500;
            $msg = 'Error occurred while deleting category';

            // Log the error for debugging purposes
            \Log::error('Error occurred while deleting category.', [
                'message' => $e->getMessage(),
                'function called' => 'CategoryController::destroy',
            ]);
        }

        return $this->response($data, $status, $msg);
    }
}
