<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceFilesModel extends Model
{
    use HasFactory;

    protected $table = 'maintenance_files';
    protected $fillable = ['maintenance_request_id', 'file_type', 'file_name', 'thumbnail_name', 'upload_by'];

    // public function getFileNameAttribute($value)
    // {
    //     $file_type  = $this->file_type;
    //    //dd(\Config::get('constants.image_base_url'));
    //     switch ($file_type) {
    //         case 1:
    //             return \Config::get('constants.image_base_url') . '/' . $value;
    //             break;
    //         case 2:
    //             return \Config::get('constants.video_base_url') . '/' . $value;
    //             break;
    //     }
    // }

  //thumbnail of videos uploding in image container
    // public function getThumbnailNameAttribute($value)
    // {
    //     $file_type  = $this->file_type;
    //    //dd(\Config::get('constants.image_base_url'));
    //     switch ($file_type) {
    //         case 1:
    //             return \Config::get('constants.image_base_url') . '/' . $value;
    //             break;
    //         case 2:
    //             return \Config::get('constants.video_thumb_url');
    //             // return \Config::get('constants.image_base_url') . '/' . $value;
    //             break;
    //     }
    // }
}
