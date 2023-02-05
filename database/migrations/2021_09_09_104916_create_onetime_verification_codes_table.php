<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOnetimeVerificationCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('onetime_verification_codes', function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->text("code");
            $table->integer("status")->default(1);
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("onetime_verification_type_id");
            $table->foreign("user_id")->references("id")->on("users");
            $table->foreign("onetime_verification_type_id")->references("id")->on("onetime_verification_types");
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('onetime_verification_codes');
    }
}
