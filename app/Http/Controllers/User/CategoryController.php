<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Category;
use Validator;
use Illuminate\Routing\UrlGenerator;
use Auth;

class CategoryController extends Controller
{
	protected $category;
	protected $base_url;

	public function __construct(UrlGenerator $urlGenerator)
    {
        $this->middleware("auth:users");
        $this->base_url = $urlGenerator->to("/");
        $this->category = new Category;
    }

    public function add_category(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            "token"=>"required",
            "title"=>"required|string",
        ]); 

        if($validator->fails())
        {
            return response()->json([
                "success"=>false,
                "message"=>$validator->messages()->toArray()
            ],400);
        }

        $this->category->title = $request->title;
        $this->category->alias = $request->alias;
        $this->category->description = $request->description;
        $this->category->save();

        return response()->json([
            "success"=>true,
            "message"=>"Category saved successfully"
        ],200);
    }

    public function get_paginate_category($token, $pagination=null)
    {
 		$user = auth("users")->authenticate($token);
       	$user_id = $user->id;  //change from user_id to id

       	if($pagination==null || $pagination=="") {
         	$categories = $this->category->orderBy("id", "DESC")->get()->toArray();
	        return response()->json([
	            "success"=>true,
	            "data"=>$categories,
	        ],200);
     	}

     	$paginated_categories = $this->category->orderBy("id", "DESC")->paginate($pagination);
     	return response()->json([
	        "success"=>true,
	        "data"=>$paginated_categories,
	    ],200);
	}

	public function delete_category($id)
	{
	 	$findData = $this->category::find($id);

	 	if(!$findData) {
		    return response()->json([
		        "success"=>true,
		        "message"=>"Category with this id doesnt exist"
		    ],500);
	 	}

	 	if($findData->delete()) {
		    return response()->json([
		        "success"=>true,
		        "message"=>"Category deleted successfully!"
		    ],200);
	 	}
	}

	public function search_category($search, $token, $pagination = null)
	{
	    $user = auth("users")->authenticate($token);
	    $user_id = $user->id;

	    $search = explode("%20", $search);
	    $search = implode(" ", $search);

	    if($pagination == null || $pagination == "") {

	        $non_paginated_search_query = $this->category::
	        	where(function($query) use ($search){
	           		$query->where("title", "LIKE", "%$search%")->orWhere("alias", "LIKE", "%$search%");
	       		})->orderBy("id", "DESC")->get()->toArray();

	        return response()->json([
	            "success"=>true,
	            "data"=>$non_paginated_search_query,
	        ],200);
	    }

	    $paginated_search_query = $this->category::
	    	where(function($query) use ($search){
	       		$query->where("title", "LIKE", "%$search%")->orWhere("alias", "LIKE", "%$search%");
	   		})->orderBy("id", "DESC")->paginate($pagination);

	    return response()->json([
	        "success"=>true,
	        "data"=>$paginated_search_query,
	    ],200);
	}

	public function get_category($id)
	{
		$findData = $this->category::find($id);

		if(!$findData) {
		    return response()->json([
		       "success"=>true,
		       "message"=>"Category with this id doesnt exist"
		   	],500);
		}

		return response()->json([
		    "success"=>true,
		    "data"=>$findData,
		],200);
	}

	public function update_category(Request $request, $id)
 	{
  		$validator = Validator::make($request->all(),
      	[
          	"title"=>"required|string",
      	]);

	  	if($validator->fails()) {
	      	return response()->json([
	          	"success"=>false,
	          	"message"=>$validator->messages()->toArray()
	      	],400);
	  	}

	  	$findData = $this->category->find($id);
	  	if(!$findData) {
		    return response()->json([
		        "success"=>false,
		        "message"=>"please this content has no valid id"
		    ],400);
		}

	    $findData->title = $request->title;
	    $findData->description = $request->description;
	    $findData->update();

		return response()->json([
		    "success"=>true,
		    "message"=>"Category updated successfully",
		],200);

	}

}
