<?php

namespace Modules\MediaLibrary\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MediaLibrary.
 */
class MediaLibraryModel extends Model
{
    protected $table = 'media_library';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    public $timestamps = false;

    protected $fillable = [
        'name', 'type', 'path'
    ];
}
