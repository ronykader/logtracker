<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContextFieldsToLogtrackersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('logtrackers', function (Blueprint $table) {
            // Context fields to track where the action happened
            $table->text('url')->nullable()->after('user_agent');
            $table->string('route_name', 255)->nullable()->after('url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('logtrackers', function (Blueprint $table) {
            $table->dropColumn(['url', 'route_name']);
        });
    }
}
