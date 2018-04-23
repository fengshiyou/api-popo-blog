<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class BlogTag extends Model
{
    public $timestamps = false;
    //表名
    protected $table="blog_tag";
}
