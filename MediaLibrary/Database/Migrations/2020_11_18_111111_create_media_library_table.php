<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaLibraryTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('media_library', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->comment('檔案名稱');
            $table->text('type')->comment('檔案類型');
            $table->string('path')->comment('檔案路徑')->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('media_library');
    }
}
