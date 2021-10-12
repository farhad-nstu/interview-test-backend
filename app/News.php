<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Category;

class News extends Model
{
	protected $table = "news";

    public function category()
    {
    	$this->belongsTo(Category::class);
    }
}
