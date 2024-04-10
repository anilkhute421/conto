<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenesLinesModel extends Model
{
    use HasFactory;
    protected $table = 'expenseslines';
    protected $guarded = ['expenseslines_name'];
}
