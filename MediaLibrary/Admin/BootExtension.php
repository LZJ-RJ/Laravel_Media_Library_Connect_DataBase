<?php

namespace Modules\MediaLibrary\Admin;

use Encore\Admin\Admin;

trait BootExtension
{
    public static function boot()
    {
        Admin::extend('media-library', __CLASS__);
    }

    public static function import()
    {
        parent::createMenu('Media library', 'media', 'fa-file');

        parent::createPermission('Media library', 'module.media.library', 'media*');
    }
}
