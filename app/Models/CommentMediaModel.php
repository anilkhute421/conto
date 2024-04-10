<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentMediaModel extends Model
{
    use HasFactory;

    protected $table = 'comment_media';
    protected $fillable = ['maintenance_request_id', 'media_type', 'media_name', 'thumbnail_name', 'upload_by'];

    public function getMediaNameAttribute($value)
    {
        $media_type  = $this->media_type;
        switch ($media_type) {
            case 1:
                return \Config::get('constants.image_base_url') . '/' . $value;
                break;
            case 2:
                return \Config::get('constants.video_base_url') . '/' . $value;
                break;
        }
    }

    //thumbnail of videos uploding in image container
    public function getThumbnailNameAttribute($value)
    {
        $file_type  = $this->media_type;
       //dd(\Config::get('constants.image_base_url'));
        switch ($file_type) {
            case 1:
                return \Config::get('constants.image_base_url') . '/' . $value;
                break;
            case 2:
                return \Config::get('constants.video_thumb_url');
                // return \Config::get('constants.image_base_url') . '/' . $value;
                break;
        }
    }
}
