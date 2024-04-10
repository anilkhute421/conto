<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsModel extends Model
{
    use HasFactory;
    protected $fillable = ['title','description','email','page_language', 'page_for'];
    protected $table = 'cms_pages';
    
}
