<?php

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class UsersStateAddToken extends Migration
{
    use Reference;

    /**
     * Run the migration
     */
    public function up()
    {
        $this->schema->table(
            'users_state',
            function (Blueprint $table) {
                $table->string('token')->nullable()->default(null)->after('got_voucher');
            }
        );
    }

    /**
     * Reverse the migration
     */
    public function down()
    {
        $this->schema->table(
            'users_state',
            function (Blueprint $table) {
                $table->dropColumn('token');
            }
        );
    }
}
