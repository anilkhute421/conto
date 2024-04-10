<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenesModel extends Model
{
    use HasFactory;
    protected $table = 'expenses';
    protected $fillable = ['pm_company_id','request_id','tenant_id','building_id',
                         'unit_id','expense_item_id','currency_id','cost','date','media'];
}
