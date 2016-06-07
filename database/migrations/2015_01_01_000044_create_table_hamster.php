<?php

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Database\Migrations\Migration;

class CreateTableKaleidoscope extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kaleidoscope', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('path', 255)->nullable();
            $table->enum('type', ['image', 'audio', 'video', 'paper', 'other']);
            $table->integer('created_at');
            $table->integer('updated_at');
            $table->integer('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('kaleidoscope');
    }
}
