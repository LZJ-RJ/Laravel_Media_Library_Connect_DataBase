<?php

namespace Modules\MediaLibrary\Http\Controllers;

use Illuminate\Routing\Controller;
use Jhofm\FlysystemIterator\Plugin\IteratorPlugin;
use Modules\MediaLibrary\Http\Controllers\Modules\Lock;
use Modules\MediaLibrary\Http\Controllers\Modules\Move;
use Modules\MediaLibrary\Http\Controllers\Modules\Utils;
use Modules\MediaLibrary\Http\Controllers\Modules\Delete;
use Modules\MediaLibrary\Http\Controllers\Modules\Rename;
use Modules\MediaLibrary\Http\Controllers\Modules\Upload;
use Modules\MediaLibrary\Http\Controllers\Modules\Download;
use Modules\MediaLibrary\Http\Controllers\Modules\NewFolder;
use Modules\MediaLibrary\Http\Controllers\Modules\GetContent;
use Modules\MediaLibrary\Http\Controllers\Modules\Visibility;
use Modules\MediaLibrary\Http\Controllers\Modules\GlobalSearch;

class MediaController extends Controller
{
    use Utils,
        GetContent,
        Delete,
        Download,
        Lock,
        Move,
        Rename,
        Upload,
        NewFolder,
        Visibility,
        GlobalSearch;

    protected $baseUrl;
    protected $db;
    protected $fileChars;
    protected $fileSystem;
    protected $folderChars;
    protected $ignoreFiles;
    protected $LMF;
    protected $GFI;
    protected $sanitizedText;
    protected $storageDisk;
    protected $storageDiskInfo;
    protected $unallowedMimes;

    public function __construct()
    {

        $config = app('config')->get('medialibrary');

        $this->fileSystem           = $config['storage_disk'];
        $this->ignoreFiles          = $config['ignore_files'];
        $this->fileChars            = $config['allowed_fileNames_chars'];
        $this->folderChars          = $config['allowed_folderNames_chars'];
        $this->sanitizedText        = $config['sanitized_text'];
        $this->unallowedMimes       = $config['unallowed_mimes'];
        $this->LMF                  = $config['last_modified_format'];
        $this->GFI                  = $config['get_folder_info']   ?? true;
        $this->paginationAmount     = $config['pagination_amount'] ?? 50;

        $this->storageDisk     = app('filesystem')->disk($this->fileSystem);
        $this->storageDiskInfo = app('config')->get("filesystems.disks.{$this->fileSystem}");
        $this->baseUrl         = $this->storageDisk->url('/');
        $this->db              = app('db')
                                    ->connection($config['database_connection'])
                                    ->table($config['table_locked']);

        $this->storageDisk->addPlugin(new IteratorPlugin());
    }

    /**
     * main view.
     *
     * @return [type] [description]
     */
    public function index()
    {
        return view('MediaLibrary::media');
    }
}
