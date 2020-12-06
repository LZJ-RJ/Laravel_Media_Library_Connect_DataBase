<?php

namespace Modules\MediaLibrary\Http\Controllers\Modules;

use Exception;
use Illuminate\Http\Request;
use Modules\MediaLibrary\Events\MediaFileOpsNotifications;

trait Rename
{
    /**
     * rename item.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function renameItem(Request $request)
    {
        $path             = $request->path;
        $file             = $request->file;
        $old_filename     = $file['name'];
        $type             = $file['type'];
        $new_filename     = $this->cleanName($request->new_filename, $type == 'folder');
        $old_path         = $file['storage_path'];
        $new_path         = dirname($old_path) . "/$new_filename";
        $message          = '';

        try {
            if (!$this->storageDisk->exists($new_path)) {
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
}
