<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
  public function up(): void
  {
    if (DB::getDriverName() === 'mysql') {
      DB::statement("ALTER TABLE locations MODIFY COLUMN type ENUM('portal', 'local', 'garage', 'storage') NOT NULL");

      return;
    }

    if (DB::getDriverName() === 'sqlite') {
      DB::statement('PRAGMA foreign_keys = OFF');

      DB::statement("CREATE TABLE locations_tmp (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR NOT NULL CHECK (type IN ('portal', 'local', 'garage', 'storage')), code VARCHAR(10) NOT NULL, name VARCHAR(50) NOT NULL, created_at DATETIME, updated_at DATETIME, deleted_at DATETIME)");
      DB::statement('CREATE UNIQUE INDEX locations_tmp_code_unique ON locations_tmp (code)');
      DB::statement('INSERT INTO locations_tmp (id, type, code, name, created_at, updated_at, deleted_at) SELECT id, type, code, name, created_at, updated_at, deleted_at FROM locations');
      DB::statement('DROP TABLE locations');
      DB::statement('ALTER TABLE locations_tmp RENAME TO locations');

      DB::statement('PRAGMA foreign_keys = ON');
    }
  }

  public function down(): void
  {
    if (DB::getDriverName() === 'mysql') {
      DB::statement("ALTER TABLE locations MODIFY COLUMN type ENUM('portal', 'garage', 'storage') NOT NULL");

      return;
    }

    if (DB::getDriverName() === 'sqlite') {
      DB::statement('PRAGMA foreign_keys = OFF');

      DB::statement("CREATE TABLE locations_tmp (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type VARCHAR NOT NULL CHECK (type IN ('portal', 'garage', 'storage')), code VARCHAR(10) NOT NULL, name VARCHAR(50) NOT NULL, created_at DATETIME, updated_at DATETIME, deleted_at DATETIME)");
      DB::statement('CREATE UNIQUE INDEX locations_tmp_code_unique ON locations_tmp (code)');
      DB::statement("INSERT INTO locations_tmp (id, type, code, name, created_at, updated_at, deleted_at) SELECT id, CASE WHEN type = 'local' THEN 'portal' ELSE type END, code, name, created_at, updated_at, deleted_at FROM locations");
      DB::statement('DROP TABLE locations');
      DB::statement('ALTER TABLE locations_tmp RENAME TO locations');

      DB::statement('PRAGMA foreign_keys = ON');
    }
  }
};
