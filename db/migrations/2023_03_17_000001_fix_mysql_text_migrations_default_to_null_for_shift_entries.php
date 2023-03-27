<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;

class FixMysqlTextMigrationsDefaultToNullForShiftEntries extends Migration
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
        $this->schema->table('shift_entries', function (Blueprint $table): void {
            $table->mediumText('user_comment')->nullable()->default(null)->change();
            $table->mediumText('freeloaded_comment')->nullable()->default(null)->change();
        });
    }

    // Down migration not needed when on same version
}
