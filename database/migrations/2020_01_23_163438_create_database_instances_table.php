<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatabaseInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('database_instances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('google_project_id');
            $table->string('name');
            $table->string('type');
            $table->string('version');
            $table->string('tier');
            $table->string('size');
            $table->string('region');
            $table->string('root_password')->nullable();
            $table->string('operation_name')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->foreign('google_project_id')
                ->references('id')->on('google_projects')
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
        Schema::dropIfExists('database_instances');
    }
}
