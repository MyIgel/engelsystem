<?php

declare(strict_types=1);

namespace Engelsystem\Migrations;

use Carbon\Carbon;
use Engelsystem\Database\Migration\Migration;
use Illuminate\Database\Schema\Blueprint;
use stdClass;

class CreateUsersTables extends Migration
{
    use ChangesReferences;
    use Reference;

    /**
     * Run the migration
     */
    public function up(): void
    {
        $this->schema->create('users', function (Blueprint $table): void {
            $table->increments('id');

            $table->string('name', 24)->unique();
            $table->string('email', 254)->unique();
            $table->string('password', 255);
            $table->string('api_key', 32);

            $table->dateTime('last_login_at')->nullable();
            $table->timestamps();
        });

        $this->schema->create('users_personal_data', function (Blueprint $table): void {
            $this->referencesUser($table, true);

            $table->string('first_name', 64)->nullable();
            $table->string('last_name', 64)->nullable();
            $table->string('shirt_size', 4)->nullable();

            $table->date('planned_arrival_date')->nullable();
            $table->date('planned_departure_date')->nullable();
        });

        $this->schema->create('users_contact', function (Blueprint $table): void {
            $this->referencesUser($table, true);

            $table->string('dect', 5)->nullable();
            $table->string('mobile', 40)->nullable();
            $table->string('email', 254)->nullable();
        });

        $this->schema->create('users_settings', function (Blueprint $table): void {
            $this->referencesUser($table, true);

            $table->string('language', 64);
            $table->tinyInteger('theme');
            $table->boolean('email_human')->default(false);
            $table->boolean('email_shiftinfo')->default(false);
        });

        $this->schema->create('users_state', function (Blueprint $table): void {
            $this->referencesUser($table, true);

            $table->boolean('arrived')->default(false);
            $table->dateTime('arrival_date')->nullable();
            $table->boolean('active')->default(false);
            $table->boolean('force_active')->default(false);
            $table->boolean('got_shirt')->default(false);
            $table->integer('got_voucher')->default(0);
        });

        $this->schema->create('password_resets', function (Blueprint $table): void {
            $this->referencesUser($table, true);

            $table->text('token');

            $table->timestamp('created_at')->nullable();
        });

        if ($this->schema->hasTable('User')) {
            $connection = $this->schema->getConnection();
            $emptyDates = ['0000-00-00 00:00:00', '0001-01-01 00:00:00', '1000-01-01 00:00:00'];
            /** @var stdClass[] $users */
            $users = $connection->table('User')->get();

            foreach ($users as $data) {
                $user = [
                    'id'            => $data->UID,
                    'name'          => $data->Nick,
                    'password'      => $data->Passwort,
                    'email'         => $data->email,
                    'api_key'       => $data->api_key,
                    'last_login_at' => $data->lastLogIn
                        ? Carbon::createFromTimestamp($data->lastLogIn, Carbon::now()->timezone)
                        : null,
                ];
                if (!in_array($data->CreateDate, $emptyDates)) {
                    $user['created_at'] = new Carbon($data->CreateDate);
                }
                $connection->table('users')->insert($user);

                $connection->table('users_contact')->insert([
                    'user_id' => $data->UID,
                    'dect'    => $data->DECT ?: null,
                    'mobile'  => $data->Handy ?: ($data->Telefon ?: null),
                ]);

                $connection->table('users_personal_data')->insert([
                    'user_id'                => $data->UID,
                    'first_name'             => $data->Vorname ?: null,
                    'last_name'              => $data->Name ?: null,
                    'shirt_size'             => $data->Size ?: null,
                    'planned_arrival_date'   => $data->planned_arrival_date
                        ? Carbon::createFromTimestamp($data->planned_arrival_date, Carbon::now()->timezone)
                        : null,
                    'planned_departure_date' => $data->planned_departure_date
                        ? Carbon::createFromTimestamp($data->planned_departure_date, Carbon::now()->timezone)
                        : null,
                ]);

                $connection->table('users_settings')->insert([
                    'user_id'         => $data->UID,
                    'language'        => $data->Sprache,
                    'theme'           => $data->color,
                    'email_human'     => $data->email_by_human_allowed,
                    'email_shiftinfo' => $data->email_shiftinfo,
                ]);

                $connection->table('users_state')->insert([
                    'user_id'      => $data->UID,
                    'arrived'      => $data->Gekommen,
                    'arrival_date' => $data->arrival_date
                        ? Carbon::createFromTimestamp($data->arrival_date, Carbon::now()->timezone)
                        : null,
                    'active'       => $data->Aktiv,
                    'force_active' => $data->force_active,
                    'got_shirt'    => $data->Tshirt,
                    'got_voucher'  => $data->got_voucher,
                ]);

                if ($data->password_recovery_token) {
                    $connection->table('password_resets')->insert([
                        'user_id' => $data->UID,
                        'token'   => $data->password_recovery_token,
                    ]);
                }
            }

            $this->changeReferences(
                'User',
                'UID',
                'users',
                'id'
            );
        }

        $this->schema->dropIfExists('User');
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        $connection = $this->schema->getConnection();

        $this->schema->create('User', function (Blueprint $table): void {
            $table->integer('UID', true);

            $table->string('Nick', 23)->unique()->default('');
            $table->string('Name', 23)->nullable();
            $table->string('Vorname', 23)->nullable();
            $table->tinyInteger('Alter')->nullable();
            $table->string('Telefon', 40)->nullable();
            $table->string('DECT', 5)->nullable();
            $table->string('Handy', 40)->nullable();
            $table->string('email', 123)->nullable();
            $table->boolean('email_shiftinfo')->default(false);
            $table->string('jabber', 200)->nullable();
            $table->string('Size', 4)->nullable();
            $table->string('Passwort', 128)->nullable();
            $table->string('password_recovery_token', 32)->nullable();
            $table->tinyInteger('Gekommen')->default(false);
            $table->tinyInteger('Aktiv')->default(false);
            $table->boolean('force_active');
            $table->tinyInteger('Tshirt')->default(false)->nullable();
            $table->tinyInteger('color')->default(10)->nullable();
            $table->char('Sprache', 64)->nullable();
            $table->char('Menu', 1)->default('L');
            $table->integer('lastLogIn')->nullable();
            $table->dateTime('CreateDate')->default('0001-01-01 00:00:00');
            $table->char('Art', 30)->nullable();
            $table->text('kommentar')->nullable();
            $table->string('Hometown')->default('');
            $table->string('api_key', 32);
            $table->integer('got_voucher');
            $table->integer('arrival_date')->nullable();
            $table->integer('planned_arrival_date');
            $table->integer('planned_departure_date')->nullable();
            $table->boolean('email_by_human_allowed');

            $table->index('api_key', 'api_key');
            $table->index('password_recovery_token', 'password_recovery_token');
            $table->index('force_active', 'force_active');
            $table->index(['arrival_date', 'planned_arrival_date'], 'arrival_date');
            $table->index('planned_departure_date', 'planned_departure_date');
        });

        foreach ($connection->table('users')->get() as $user) {
            /** @var stdClass $user */
            /** @var stdClass $contact */
            $contact = $connection->table('users_contact')->where('user_id', $user->id)->first();
            /** @var stdClass $personal */
            $personal = $connection->table('users_personal_data')->where('user_id', $user->id)->first();
            /** @var stdClass $settings */
            $settings = $connection->table('users_settings')->where('user_id', $user->id)->first();
            /** @var stdClass $state */
            $state = $connection->table('users_state')->where('user_id', $user->id)->first();

            $this->schema
                ->getConnection()
                ->table('User')
                ->insert([
                    'UID'                    => $user->id,
                    'Nick'                   => $user->name,
                    'Name'                   => $personal->last_name,
                    'Vorname'                => $personal->first_name,
                    'DECT'                   => $contact->dect,
                    'Handy'                  => $contact->mobile,
                    'email'                  => $user->email,
                    'email_shiftinfo'        => $settings->email_shiftinfo,
                    'Size'                   => $personal->shirt_size,
                    'Passwort'               => $user->password,
                    'Gekommen'               => $state->arrived,
                    'Aktiv'                  => $state->active,
                    'force_active'           => $state->force_active,
                    'Tshirt'                 => $state->got_shirt,
                    'color'                  => $settings->theme,
                    'Sprache'                => $settings->language,
                    'lastLogIn'              => $user->last_login_at
                        ? Carbon::make($user->last_login_at)->getTimestamp()
                        : null,
                    'CreateDate'             => $user->created_at
                        ? Carbon::make($user->created_at)->toDateTimeString()
                        : '0001-01-01 00:00:00',
                    'api_key'                => $user->api_key,
                    'got_voucher'            => $state->got_voucher,
                    'arrival_date'           => $state->arrival_date
                        ? Carbon::make($state->arrival_date)->getTimestamp()
                        : null,
                    'planned_arrival_date'   => $personal->planned_arrival_date
                        ? Carbon::make($personal->planned_arrival_date)->getTimestamp()
                        : 0,
                    'planned_departure_date' => $personal->planned_departure_date
                        ? Carbon::make($personal->planned_departure_date)->getTimestamp()
                        : null,
                    'email_by_human_allowed' => $settings->email_human,
                ]);
        }

        foreach ($connection->table('password_resets')->get() as $passwordReset) {
            $this->schema->getConnection()
                ->table('User')
                ->where('UID', '=', $passwordReset->user_id)
                ->update(['password_recovery_token' => $passwordReset->token]);
        }

        $this->schema->drop('users_personal_data');
        $this->schema->drop('users_contact');
        $this->schema->drop('users_settings');
        $this->schema->drop('users_state');
        $this->schema->drop('password_resets');

        $this->changeReferences(
            'users',
            'id',
            'User',
            'UID',
            'integer'
        );

        $this->schema->drop('users');
    }
}
