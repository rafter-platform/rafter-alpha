<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatabaseUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('database_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('database_instance_id');
            $table->string('name');
            $table->string('password');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('database_instance_id')
                ->references('id')->on('database_instances')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('database_users');
    }
}
