<?php

namespace Modules\MediaLibrary\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\MediaLibrary\Model\MediaLibraryModel;
use Exception;
use Modules\MediaLibrary\Events\MediaFileOpsNotifications;

class MediaLibraryController extends MediaController
{

    public function getComponentMediaLibrary()
    {
        return view('medialibrary::admin.components.mediaLibrary');
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('medialibrary::media');
    }

    public function getMediaData(Request $request){
        $media_id = 0;
        $media_name = '';
        $data = array();
        if($request->get('id')){
            $mediaLibrary = MediaLibraryModel::where('id', $request->get('id'))->get();
            if(count($mediaLibrary)){
                $media_id = $mediaLibrary->toArray()[0]['id'];
                $media_name = $mediaLibrary->toArray()[0]['name'];
            }
        }elseif($request->get('path')){
            $mediaLibrary = MediaLibraryModel::where('path', $request->get('path'))->orWhere('path', substr($request->get('path'), 1))->get();
            if(count($mediaLibrary)){
                $media_id = $mediaLibrary->toArray()[0]['id'];
                $media_name = $mediaLibrary->toArray()[0]['name'];
            }
        }

        $data['id'] = $media_id;
        $data['name'] = $media_name;
        return $data;
    }

    public function upload(Request $request){
        $upload_path = $request->upload_path;
        $random_name = filter_var($request->random_names, FILTER_VALIDATE_BOOLEAN);
        $result      = [];
        $broadcast   = false;
        $custom_attr = collect(json_decode($request->custom_attrs));

        foreach ($request->file as $one) {
            if ($this->allowUpload($one)) {
                $one        = $this->optimizeUpload($one);
                $orig_name  = $one->getClientOriginalName();

                $name_only  = pathinfo($orig_name, PATHINFO_FILENAME);
                $ext_only   = pathinfo($orig_name, PATHINFO_EXTENSION);
                $final_name = $random_name
                    ? $this->getRandomString() . ".$ext_only"
                    : $this->cleanName($name_only) . ".$ext_only";

                $file_options = optional($custom_attr->firstWhere('name', $orig_name))->options;
                $file_type    = $one->getMimeType();
                $destination  = !$upload_path ? $final_name : $this->clearDblSlash("$upload_path/$final_name");

                try {
                    // check for mime type
                    if (Str::contains($file_type, $this->unallowedMimes)) {
                        throw new Exception(
                            trans('medialibrary::messages.not_allowed_file_ext', ['attr' => $file_type])
                        );
                    }

                    // check existence
                    if ($this->storageDisk->exists($destination)) {
                        throw new Exception(
                            trans('medialibrary::messages.error.already_exists')
                        );
                    }

                    // save file
                    $full_path = $this->storeFile($one, $upload_path, $final_name);


                    // fire event
                    event('MMFileUploaded', [
                        'file_path'  => $full_path,
                        'mime_type'  => $file_type,
                        'options'    => $file_options,
                    ]);

                    $broadcast = true;
                    $result[]  = [
                        'success'   => true,
                        'file_name' => $final_name,
                    ];

                    //儲存檔案的資料到資料庫
                    MediaLibraryModel::create([
                        'name' => $final_name,
                        'type' => $file_type,
                        'path' => $full_path,
                    ]);

                } catch (Exception $e) {
                    $result[] = [
                        'success' => false,
                        'message' => "\"$final_name\" " . $e->getMessage(),
                    ];
                }
            } else {
                $result[] = [
                    'success' => false,
                    'message' => trans('medialibrary::messages.error.cant_upload'),
                ];
            }
        }

        // broadcast
        if ($broadcast) {
            broadcast(new MediaFileOpsNotifications([
                'op'   => 'upload',
                'path' => $upload_path,
            ]))->toOthers();
        }

        return response()->json($result);
    }

    public function deleteItem(Request $request)
    {
        $path        = $request->path;
        $result      = [];
        $toBroadCast = [];

        foreach ($request->deleted_files as $one) {
            $name      = $one['name'];
            $type      = $one['type'];
            $item_path = $one['storage_path'];
            $defaults  = [
                'name' => $name,
                'path' => $item_path,
            ];

            $del = $type == 'folder'
                ? $this->storageDisk->deleteDirectory($item_path)
                : $this->storageDisk->delete($item_path);

            if ($del) {
                $result[]      = array_merge($defaults, ['success' => true]);
                $toBroadCast[] = $defaults;

                // fire event
                event('MMFileDeleted', [
                    'file_path' => $item_path,
                    'is_folder' => $type == 'folder',
                ]);

                // 刪除檔案時也到資料庫刪除，這裡刪除的path會因為根目錄的"/"而影響到，所以多做個orWhere
                if($type == 'folder'){
                    $mediaLibrary = MediaLibraryModel::where('path', 'LIKE', $item_path.'/%')->orWhere('path', 'LIKE', '/'.$item_path.'/%')->orWhere('path', 'LIKE', './'.$item_path.'/%');
                }else{
                    $mediaLibrary = MediaLibraryModel::where('name', $name)->where('type', $type)->where('path', $item_path)->orWhere('path', '/'.$item_path)->orWhere('path', './'.$item_path);
                }

                // 刪除媒體前先清除有使用到此媒體的資料表 TODO，需根據各個有使用到媒體庫的欄位的資料庫做修改
//                foreach($mediaLibrary->get() as $singleMedia){
//                    $post = Post::where('img_id', $singleMedia->id);
//                    $post->update(['img_id' => NULL]);
//                }
                $mediaLibrary->delete();

            } else {
                $result[] = array_merge($defaults, [
                    'success' => false,
                    'message' => trans('medialibrary::messages.error.deleting_file'),
                ]);
            }
        }

        // broadcast
        broadcast(new MediaFileOpsNotifications([
            'op'    => 'delete',
            'items' => $toBroadCast,
            'path'  => $path,
        ]))->toOthers();

        return response()->json($result);
    }

    public function moveItem(Request $request)
    {
        $copy        = $request->use_copy;
        $destination = $request->destination;
        $result      = [];
        $toBroadCast = [];

        foreach ($request->moved_files as $one) {
            $file_name = $one['name'];
            $file_type = $one['type'];
            $old_path  = $one['storage_path'];
            $defaults  = [
                'name'     => $file_name,
                'old_path' => $old_path,
            ];
            $new_path = "$destination/$file_name";


            try {
                if ($file_type == 'folder' && Str::startsWith($destination, "/$old_path")) {
                    throw new Exception(
                        trans('medialibrary::messages.error.move_into_self')
                    );
                }

                if (!file_exists($new_path)) {
                    // copy
                    if ($copy) {
                        // folders
                        if ($file_type == 'folder') {
                            //    複製資料夾的處理
                            if (app('files')->copyDirectory($this->storageDiskInfo['root'].'/'.$old_path, $this->storageDiskInfo['root'].$new_path)) {
                                $allMediaInFolder = MediaLibraryModel::where('path', 'LIKE', $old_path.'/%')->orWhere('path', 'LIKE', '/'.$old_path.'/%')->orWhere('path', 'LIKE', './'.$old_path.'/%');
                                foreach($allMediaInFolder->get() as $media)
                                {
                                    MediaLibraryModel::create(['name' => $media->name, 'type' => $media->type, 'path' => $new_path.$media->name]);
                                }

                                $result[] = array_merge($defaults, ['success' => true]);
                            } else {
                                throw new Exception(
                                    isset($this->storageDiskInfo['root'])
                                        ? trans('medialibrary::messages.error.moving')
                                        : trans('medialibrary::messages.error.moving_cloud')
                                );
                            }
                        }

                        // files
                        else {

                            if ($this->storageDisk->copy($old_path, $new_path)) {

                                $result[] = array_merge($defaults, ['success' => true]);

                                //  更改媒體庫檔案的路徑時，也變更資料庫內容
                                MediaLibraryModel::create(['name' => $file_name, 'type' => $file_type, 'path' => $new_path]);

                            } else {
                                throw new Exception(
                                    trans('medialibrary::messages.error.moving')
                                );
                            }
                        }
                    }

                    // move
                    else {
                        if ($this->storageDisk->move($old_path, $new_path)) {

                            $result[]      = array_merge($defaults, ['success' => true]);
                            $toBroadCast[] = $defaults;

                            // fire event
                            event('MMFileMoved', [
                                'old_path' => $old_path,
                                'new_path' => $new_path,
                            ]);

                            // 更改媒體庫檔案的路徑時，也變更資料庫內容
                            if($file_type == 'folder'){
                                $allMediaInFolder = MediaLibraryModel::where('path', 'LIKE', $old_path.'/%')->orWhere('path', 'LIKE', '/'.$old_path.'/%')->orWhere('path', 'LIKE', './'.$old_path.'/%');
                                foreach($allMediaInFolder->get() as $media){
                                    MediaLibraryModel::find($media->id)->update(['path' => $new_path.'/'.$media->name]);
                                }
                            }else{
                                $media = MediaLibraryModel::where('name', $file_name)->where('type', $file_type)->where('path', $old_path)->orWhere('path', '/'.$old_path)->orWhere('path', './'.$old_path);
                                $media->update(['name' => $file_name, 'path' => $new_path]);
                            }


                        } else {
                            $exc = trans('medialibrary::messages.error.moving');

                            if ($file_type == 'folder' && !isset($this->storageDiskInfo['root'])) {
                                $exc = trans('medialibrary::messages.error.moving_cloud');
                            }

                            throw new Exception($exc);
                        }
                    }
                } else {
                    throw new Exception(
                        trans('medialibrary::messages.error.already_exists')
                    );
                }
            } catch (Exception $e) {
                $result[]  = [
                    'success' => false,
                    'message' => "\"$old_path\" " . $e->getMessage(),
                ];
            }
        }

        // broadcast
        broadcast(new MediaFileOpsNotifications([
            'op'    => 'move',
            'path'  => $destination,
        ]))->toOthers();

        return response()->json($result);
    }

    public function renameItem(Request $request)
    {
        $path             = $request->path;
        $file             = $request->file;
        $old_filename     = $file['name'];
        $type             = $file['type'];
        $new_filename = $this->cleanName($request->new_filename, $type == 'folder');
        $old_path         = $file['storage_path'];
        $new_path         = dirname($old_path) . "/$new_filename";
        $message          = '';
        $compareDifferent_before = array();
        $compareDifferent_after = array();
        try {
            if (!$this->storageDisk->exists($new_path)) {
                if($type == 'folder') {
                    $compareDifferent_before = $this->getDirContents($this->storageDiskInfo['root']);
                }
                if ($this->storageDisk->move($old_path, $new_path)) {
                    // broadcast
                    broadcast(new MediaFileOpsNotifications([
                        'op'   => 'rename',
                        'path' => $path,
                        'item' => [
                            'type'    => $type,
                            'oldName' => $old_filename,
                            'newName' => $new_filename,
                        ],
                    ]))->toOthers();

                    // fire event
                    event('MMFileRenamed', [
                        'old_path' => $old_path,
                        'new_path' => $new_path,
                    ]);

                    // 更換媒體庫檔案名稱時，也更換資料庫的資料
                    if($type == 'folder'){

                        $allMediaInFolder = MediaLibraryModel::where('path', 'LIKE', $old_path.'/%')->orWhere('path', 'LIKE', '/'.$old_path.'/%')->orWhere('path', 'LIKE', './'.$old_path.'/%');
                        $compareDifferent_after = $this->getDirContents($this->storageDiskInfo['root']);
                        $compareDifferent_result_before = array_diff($compareDifferent_before, $compareDifferent_after); //輸出的內容為第一個陣列中，不同於第二個陣列的值。
                        $compareDifferent_result_after = array_diff($compareDifferent_after, $compareDifferent_before); //輸出的內容為第一個陣列中，不同於第二個陣列的值。

                        if(
                            count($allMediaInFolder->get()) &&
                            !empty($compareDifferent_result_before) &&
                            !empty($compareDifferent_result_after) &&
                            sizeof($compareDifferent_result_before) == sizeof($compareDifferent_result_after)
                        ){
                            for($i=0;$i<sizeof($compareDifferent_before);$i++)
                            {
                                MediaLibraryModel::where('path', $compareDifferent_before[$i])->orWhere('path', substr($compareDifferent_before[$i], 1))->update(['path' => $compareDifferent_after[$i]]);
                            }
                        }

                    }else{

                        /**
                         *  $type = image/png
                         *  $path = /123
                         *  $old_path = 123/oldImg.png
                         *  $old_filename = oldImg.png
                         *  $new_path = 123/newImg.png
                         *  $new_filename = newImg.png
                         */

                        $media = MediaLibraryModel::where('name', $old_filename)->where('type', $type)->where('path', $old_path)->orWhere('path', '/'.$old_path)->orWhere('path', './'.$old_path);
                        $media->update(['name' => $new_filename, 'path' => $new_path]);
                    }
                } else {
                    throw new Exception(
                        trans('medialibrary::messages.error.moving')
                    );
                }
            } else {
                throw new Exception(
                    trans('medialibrary::messages.error.already_exists')
                );
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        return compact('message', 'new_filename');
    }

    private function getDirContents($dir, &$results = array()) {
//         取得根目錄下的所有「檔案」的路徑
        $files = scandir($dir);
        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = str_replace($this->storageDiskInfo['root'], '', $path);
            } else if ($value != "." && $value != "..") {
                $this->getDirContents($path, $results);
            }
        }
        return $results;
    }

    public function createNewFolder(Request $request)
    {
        $path            = $request->path;
        $new_folder_name = $this->cleanName($request->new_folder_name, true);
        $full_path       = !$path ? $new_folder_name : $this->clearDblSlash("$path/$new_folder_name");
        $message         = '';

        if ($this->storageDisk->exists($full_path)) {
            $message = trans('medialibrary::messages.error.already_exists');
        } elseif (!$this->storageDisk->makeDirectory($full_path)) {
            $message = trans('medialibrary::messages.error.creating_dir');
        }

        // broadcast
        broadcast(new MediaFileOpsNotifications([
            'op'   => 'new_folder',
            'path' => $path,
        ]))->toOthers();

        return compact('message', 'new_folder_name');
    }

    public function cleanName($text, $folder = false)
    {
//         除了原本過濾的字元外，另外做了判斷是否中文，若是中文就保留文字。
        $pattern = $this->filePattern($folder ? $this->folderChars : $this->fileChars);
        if(preg_replace($pattern, '', $text) == '' &&
            !preg_match("/\p{Han}+/u", $text) ){
            $text = '';
        }
        return $text ?: $this->getRandomString();
    }


//   =========== 以下是原本的 ===========


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('medialibrary::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request){}

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('medialibrary::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('medialibrary::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id){}

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id){}
}
