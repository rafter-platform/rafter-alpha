<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeploymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deployments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('environment_id')->index();
            $table->unsignedBigInteger('initiator_id')->nullable();
            $table->string('status')->default('queued');
            $table->string('operation_name')->nullable();
            $table->string('image')->nullable();
            $table->string('commit_hash')->nullable();
            $table->string('commit_message')->nullable();
            $table->text('meta')->nullable();
            $table->timestamps();

            $table->foreign('environment_id')
                ->references('id')->on('environments')
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
        Schema::dropIfExists('deployments');
    }
}
