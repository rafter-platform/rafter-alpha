<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnvironmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('environments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('project_id')->index();
            $table->unsignedBigInteger('database_id')->nullable();
            $table->unsignedBigInteger('database_user_id')->nullable();
            $table->string('name')->index();
            $table->unsignedBigInteger('active_deployment_id')->nullable()->index();
            $table->string('branch')->default('master')->index();
            $table->string('url')->nullable();
            $table->string('worker_url')->nullable();
            $table->string('web_service_name')->nullable();
            $table->string('worker_service_name')->nullable();
            $table->mediumText('environmental_variables')->nullable();
            $table->text('options')->nullable();
            $table->timestamps();

            $table->foreign('project_id')
                ->references('id')->on('projects')
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
        Schema::dropIfExists('environments');
    }
}
