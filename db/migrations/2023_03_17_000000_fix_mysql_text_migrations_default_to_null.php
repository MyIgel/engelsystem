<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class FixMysqlTextMigrationsDefaultToNull extends Migration
{
    /**
     * Run the migration
     *
     * Text (and blob) fields can only default null
     * @see https://bugs.mysql.com/bug.php?id=21532
     * @see https://dev.mysql.com/doc/refman/8.0/en/data-type-defaults.html#data-type-defaults-explicit
     */
    public function up(): void
    {
        $this->schema->table('angel_types', function (Blueprint $table): void {
            $table->text('description')->nullable()->default(null)->change();
        });

        $this->schema->table('shifts', function (Blueprint $table): void {
            $table->text('description')->nullable()->default(null)->change();
        });

        // ToDo: shift_entries user_comment / freeloaded_comment too?
    }

    // Down migration not needed when on same version
}
