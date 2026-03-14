<?php

namespace Tests\Unit;

use App\Services\DatabaseCopyService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseCopyServiceTest extends TestCase
{
    use RefreshDatabase;

    private array $temporarySqliteFiles = [];

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->temporarySqliteFiles as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    public function test_it_copies_sqlite_data_into_target_connection(): void
    {
        [$sourceConnection, $targetConnection] = $this->temporaryConnections();

        $this->createTestSchema($sourceConnection);
        $this->createTestSchema($targetConnection);

        DB::connection($sourceConnection)->table('users')->insert([
            ['id' => 1, 'name' => 'Alice'],
            ['id' => 2, 'name' => 'Bob'],
        ]);
        DB::connection($sourceConnection)->table('notes')->insert([
            ['id' => 10, 'user_id' => 1, 'body' => 'Pirma piezime'],
            ['id' => 11, 'user_id' => 2, 'body' => 'Otra piezime'],
        ]);

        DB::connection($targetConnection)->table('users')->insert([
            ['id' => 99, 'name' => 'Old user'],
        ]);

        $result = app(DatabaseCopyService::class)->copy($sourceConnection, $targetConnection, true, 1);

        $this->assertSame(2, $result['tables']);
        $this->assertSame(4, $result['rows']);
        $this->assertSame(2, $result['per_table']['users']);
        $this->assertSame(2, $result['per_table']['notes']);

        $this->assertSame(2, DB::connection($targetConnection)->table('users')->count());
        $this->assertSame(2, DB::connection($targetConnection)->table('notes')->count());
        $this->assertDatabaseHas('users', ['id' => 1, 'name' => 'Alice'], $targetConnection);
        $this->assertDatabaseHas('notes', ['id' => 11, 'body' => 'Otra piezime'], $targetConnection);
        $this->assertDatabaseMissing('users', ['id' => 99, 'name' => 'Old user'], $targetConnection);
    }

    /**
     * @return array{0:string,1:string}
     */
    private function temporaryConnections(): array
    {
        $sourceFile = tempnam(sys_get_temp_dir(), 'noma-source-');
        $targetFile = tempnam(sys_get_temp_dir(), 'noma-target-');

        $this->temporarySqliteFiles = [$sourceFile, $targetFile];

        config()->set('database.connections.sqlite_copy_source', [
            'driver' => 'sqlite',
            'database' => $sourceFile,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        config()->set('database.connections.sqlite_copy_target', [
            'driver' => 'sqlite',
            'database' => $targetFile,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        DB::purge('sqlite_copy_source');
        DB::purge('sqlite_copy_target');

        return ['sqlite_copy_source', 'sqlite_copy_target'];
    }

    private function createTestSchema(string $connection): void
    {
        Schema::connection($connection)->create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });

        Schema::connection($connection)->create('notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('body');
        });
    }
}
