<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenesItemModel extends Model
{
    use HasFactory;
    protected $table = 'expenses_items';
    protected $fillable = ['expenses_id','expenses_lines_id','currency_id','cost','date','description'];
}
