<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractFilesModel extends Model
{
    use HasFactory;
    protected $table = 'contracts_files_tables';
    protected $guarded = [];

    public function getFileNameAttribute($value)
    {
        return \Config::get('constants.file_base_url').'/'.$value ; 
    }
}
