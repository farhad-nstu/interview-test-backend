<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\News;
use App\Category;
use Validator;
use Illuminate\Routing\UrlGenerator;
use File;
use Auth;

class NewsController extends Controller
{
    protected $news;
    protected $base_url;

    public function __construct(UrlGenerator $urlGenerator)
    {
        $this->middleware("auth:users");
        $this->base_url = $urlGenerator->to("/");
        $this->news = new News;
    }

    public function add_news(Request $request)
    {
        $validator = Validator::make($request->all(),
        [
            "token"=>"required",
            "title"=>"required|string",
            "category_id"=>"required"
        ]); 

        if($validator->fails())
        {
            return response()->json([
                "success"=>false,
                "message"=>$validator->messages()->toArray()
            ],400);
        }

        $profile_picture = $request->profile_picture;
        $file_name = "";

        if($profile_picture == null || $profile_picture == "") {
            $file_name = "default-avatar.png";
        } else {
            $generate_name = uniqid()."_".time().date("Ymd")."_IMG";
            $base64Image =  $profile_picture;
            $fileBin = file_get_contents($base64Image);

            $mimetype = mime_content_type($base64Image);  //make sure to pass the base64 image here

            if("image/png"==$mimetype) {
                $file_name = $generate_name.".png";
            } else if("image/jpeg"==$mimetype) {
                $file_name = $generate_name.".jpeg";
            } else if("image/jpg"==$mimetype) {
                $file_name = $generate_name."jpg";
            } else{
                return response()->json([
                    "success"=>false,
                    "message"=>"only png ,jpg and jpeg files are accepted for setting profile pictures"
                ],400);
            }
        }

        $user_token = $request->token;
        $user = auth("users")->authenticate($user_token);
        $user_id = $user->id;

        $this->news->user_id = $user_id;
        $this->news->title = $request->title;
        $this->news->category_id = $request->category_id;
        $this->news->description = $request->description;

        $this->news->image_file = $file_name; //changed from $request->profile_image to new file_name generated
        $this->news->save();
        if($profile_picture == null) {

        } else {
            file_put_contents("./profile_images/".$file_name,$fileBin);
        }

        return response()->json([
            "success"=>true,
            "message"=>"news saved successfully"
        ], 200);
    }

	public function get_paginated_data($token, $pagination = null)
	{
	 	$file_directory = $this->base_url."/profile_images";
	 	$user = auth("users")->authenticate($token);
        $user_id = $user->id; 

        if($pagination == null || $pagination == "") {
	        $news = $this->news->where("user_id", $user_id)->orderBy("id","DESC")->get()->toArray();

	        return response()->json([
	            "success"=>true,
	            "data"=>$news,
	            "file_directory"=>$file_directory
         	], 200);
	    }

	    $news_paginated = $this->news->where("user_id", $user_id)->orderBy("id","DESC")->paginate($pagination);

	    return response()->json([
	        "success"=>true,
	        "data"=>$news_paginated,
	        "file_directory"=>$file_directory
	    ], 200);
	 }

 	public function edit_data(Request $request, $id)
 	{
  		$validator = Validator::make($request->all(),
      	[
          "title"=>"required|string",
          "category_id"=>"required"
      	]);

		if($validator->fails()) {
		  	return response()->json([
		      "success"=>false,
		      "message"=>$validator->messages()->toArray()
		  	], 400);
		}

  		$findData = $this->contacts->find($id);

  		if(!$findData) {
		    return response()->json([
		        "success"=>false,
		        "message"=>"please this content has no valid id"
		    ],400);
		}

		$getFile = $findData->image_file;
		$getFile == "default-avatar.png"? :File::delete('profile_images/'.$getFile);
		$profile_picture = "";

		$file_name = "";

		if($profile_picture == null || $profile_picture == "") {
		  	$file_name = "default-avatar.png";
		} else {
		  	$generate_name = uniqid()."_".time().date("Ymd")."_IMG";
		  	$base64Image =  $profile_picture;
		  	$fileBin = file_get_contents($base64Image);
		  	$mimetype = mime_content_type($base64Image);

		    if("image/png"==$mimetype)
		    {
		     	$file_name = $generate_name.".png";
		    }
		 	else if("image/jpeg"==$mimetype)
		 	{
		     	$file_name = $generate_name.".jpeg";
		 	}
		 	else if("image/jpg"==$mimetype)
		 	{
		     	$file_name = $generate_name."jpg";
		 	}
		 	else {
			  	return response()->json([
			      	"success"=>false,
			      	"message"=>"only png ,jpg and jpeg files are accepted for setting profile pictures"
			  	], 400);
			}
		}

	    $findData->title = $request->title;
	    $findData->category_id = $request->category_id;
	    $findData->image_file = $file_name;
	    $findData->description = $request->description;
	    $findData->update();

	    if($profile_picture == null || $profile_picture=="") {

	    } else {
	     	file_put_contents("./profile_images/".$file_name, $fileBin);
	 	}

		return response()->json([
		    "success"=>true,
		    "message"=>"news updated successfully",
		], 200);
	}

	public function delete_news($id)
	{
 		$findData = $this->news::find($id);

 		if(!$findData) {
		    return response()->json([
		        "success"=>true,
		        "message"=>"news with this id doesnt exist"
		    ], 500);
 		}

 		$getFile = $findData->image_file;

 		if($findData->delete()) {
     		$getFile == "default-avatar.png"? :File::delete("profile_images/".$getFile);

		    return response()->json([
		        "success"=>true,
		        "message"=>"news deleted successfully"
		    ],200);
 		}
	}

	public function get_single_data($id)
	{
		$file_directory = $this->base_url."/profile_images";
		$findData = $this->news::find($id);

		if(!$findData) {
		    return response()->json([
		       "success"=>true,
		       "message"=>"news with this id doesnt exist"
		    ], 500);
		}
		return response()->json([
		    "success"=>true,
		    "data"=>$findData,
		    "file_directory"=>$file_directory
		], 200);
	}

   	//this function is to search for data as well as paginating our data searched
	public function search_data($search, $token, $pagination = null)
	{
	    $file_directory = $this->base_url."/profile_images";
	    $user = auth("users")->authenticate($token);
	    $user_id = $user->id;

	    $search = explode("%20",$search);
	    $search = implode(" ",$search);

	    if($pagination == null || $pagination == "") {

	        $non_paginated_search_query = $this->news::where("user_id", $user_id)->
	        	where(function($query) use ($search) {
	           		$query->where("title", "LIKE", "%$search%");
	       		})->orderBy("id","DESC")->get()->toArray();

	        return response()->json([
	            "success"=>true,
	            "data"=>$non_paginated_search_query,
	            "file_directory"=>$file_directory
	        ], 200);
	    }

	    $paginated_search_query = $this->news::where("user_id", $user_id)->
	    	where(function($query) use ($search) {
	       	$query->where("title","LIKE","%$search%");
   		})->orderBy("id","DESC")->paginate($pagination);

	    return response()->json([
	        "success"=>true,
	        "data"=>$paginated_search_query,
	        "file_directory"=>$file_directory
	    ], 200);
	}
}
