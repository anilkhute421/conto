<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenesItemFilesModel extends Model
{
    use HasFactory;
    protected $table = 'expense_files';
    protected $fillable = ['expense_item_id','file_name'];

    public function getFileNameAttribute($value)
    {
        return \Config::get('constants.file_base_url').'/'.$value ;
    }
}
