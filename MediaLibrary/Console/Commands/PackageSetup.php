<?php

namespace Modules\MediaLibrary\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

class PackageSetup extends Command
{
    protected $file;
    protected $signature   = 'lmm:setup';
    protected $description = 'setup package routes & assets compiling';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        $this->file = app('files');

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // routes
        $route_file = base_path('routes/web.php');
        $search     = 'MediaLibrary';


        // mix
        $mix_file = base_path('webpack.mix.js');
        $search   = 'MediaLibrary';

        if ($this->checkExist($mix_file, $search)) {
            $data =
<<<EOT

// MediaLibrary
mix.sass('Modules/MediaLibrary/Resources/assets/sass/library.scss', 'public/assets/MediaLibrary/style.css')
    .copyDirectory('Modules/MediaLibrary/Resources/assets/dist', 'public/assets/MediaLibrary')
EOT;

            $this->file->append($mix_file, $data);
        }

        $this->info('All Done');
    }

    /**
     * [checkExist description].
     *
     * @param [type] $file   [description]
     * @param [type] $search [description]
     *
     * @return [type] [description]
     */
    protected function checkExist($file, $search)
    {
        return $this->file->exists($file) && !Str::contains($this->file->get($file), $search);
    }
}
