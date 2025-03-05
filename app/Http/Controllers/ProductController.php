<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::user()?->is_admin != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json([
            "message "=>'All Products Data',
            'Products'=>Product::All()

        ],200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
      //  dd(Auth::user());

        if (Auth::user()?->is_admin != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        // else{
        //     return response()->json([
        //         'Auth'=>Auth::User(),
        //     ],200);
        // }
        $validator=Validator::make($request->all(),[

            'name'=>'required|string',
            'price'=>'required|numeric|min:0',
            'stock'=>'required|integer|min:0',

        ]);

        if($validator->fails()){
            return response()->json([
                'error' =>$validator->errors(),

            ],400);
        }
        $product = Product::create($request->all());

        return response()->json([
            'message'=>'Prodct added successfully',
            'data'=>$product
        ],200);


    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        if (Auth::user()?->is_admin != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $product=Product::find($id);
        if($product){
            return response()->json([
                'Product'=>'Show Product',
                'Data'=>$product
            ],200);
        }else{

            return response()->json([
                'status'=>'Product Not Found'
            ],400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        if (Auth::user()?->is_admin != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $product=Product::find($id);
        if(!$product){
            return response()->json([
                'Product status'=>'Product Not Found !!!'
            ],400);
        }

        $validator=Validator::make($request->all(),[

            'name'=>'required|string',
            'price'=>'required|numeric|min:0',
            'stock'=>'required|integer|min:0',

        ]);

        if($validator->fails()){
            return response()->json([
                'error' =>$validator->errors(),

            ],400);
        }

        $product->update($request->all());

        return response()->json([
            'message'=>'Prodct Updated  successfully',
            'data'=>$product
        ],200);




    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (Auth::user()?->is_admin != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $product=Product::find($id);
        if(!$product){
            return response()->json([
                'Product status'=>'Product Not Found !!!'
            ],400);
        }
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);

    }
}
