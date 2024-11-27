<?php
// App/Http/Controllers/DataController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class DataController extends Controller
{
    /** List all table autorise in both database */
    public function listTables()
    {
        // Get the list of tables from the primary PostgreSQL database
        $primaryTables = DB::connection('pgsql')->select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");

        // Get the list of tables from the secondary 'users_invites' database
        $secondaryTables = DB::connection('pgsql_metheo')->select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");

        // Merge both lists of tables
        $tables = array_merge($primaryTables, $secondaryTables);

        // Specify unauthorized tables that should be filtered out
        $unauthorizedTables = ['migrations', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'model_has_permissions', 'model_has_roles', 'role_has_permissions'];

        // Filter the tables to exclude unauthorized ones
        $filteredTables = array_filter($tables, function ($table) use ($unauthorizedTables) {
            return !in_array($table->table_name, $unauthorizedTables);
        });

        return response()->json(array_column($filteredTables, 'table_name'));
    }

    /** @throws QueryException */
    /** @return \Illuminate\Http\JsonResponse */
    /** Fetch data from the specified table */
    public function fetchData($table)
    {
        // Check if the table exists in the primary database
        $primaryExists = DB::connection('pgsql')->getSchemaBuilder()->hasTable($table);

        // Check if the table exists in the secondary database
        $secondaryExists = DB::connection('pgsql_metheo')->getSchemaBuilder()->hasTable($table);

        if (!$primaryExists && !$secondaryExists) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        // Fetch data from the primary database if the table exists there
        if ($primaryExists) {
            $data = DB::connection('pgsql')->table($table)->get();
        } else {
            // Otherwise, fetch data from the secondary database
            $data = DB::connection('pgsql_metheo')->table($table)->get();
        }

        return response()->json($data);
    }

    /** @throws QueryException */
    /** @return \Illuminate\Http\JsonResponse */
    /** Create a new row in the table specified */
    public function createRow(Request $request, $table)
    {
        // Check if the table exists in the primary database
        $primaryExists = DB::connection('pgsql')->getSchemaBuilder()->hasTable($table);

        // Check if the table exists in the secondary database
        $secondaryExists = DB::connection('pgsql_metheo')->getSchemaBuilder()->hasTable($table);

        if (!$primaryExists && !$secondaryExists) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        // Define validation rules
        $rules = [];
        $primaryColumns = $primaryExists ? DB::connection('pgsql')->select("SELECT column_name, is_nullable, data_type FROM information_schema.columns WHERE table_name = ?", [$table]) : [];
        $secondaryColumns = $secondaryExists ? DB::connection('pgsql_metheo')->select("SELECT column_name, is_nullable, data_type FROM information_schema.columns WHERE table_name = ?", [$table]) : [];

        // Merge columns from both databases
        $columns = array_merge($primaryColumns, $secondaryColumns);

        foreach ($requiredColumns as $column) {
            // Skip validation for `id` or `Id` fields
            if (!in_array(strtolower($column->column_name), ['id', 'Id'])) {
                $columnType = DB::selectOne("SELECT data_type FROM information_schema.columns WHERE table_name = ? AND column_name = ?", [$table, $column->column_name])->data_type;
                $typeRule = match ($columnType) {
                    'integer' => 'integer',
                    'character varying', 'text' => 'string',
                    'boolean' => 'boolean',
                    'date' => 'date',
                    'timestamp without time zone', 'timestamp with time zone' => 'date_format:Y-m-d H:i:s',
                    default => 'string',
                };
                $rules[$column->column_name] = $typeRule;
            }
        }

        // Validate the request data
        try {
            $validatedData = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        // Insert the new row into the appropriate database
        try {
            if ($primaryExists) {
                $row = DB::connection('pgsql')->table($table)->insert($validatedData);
            } else {
                $row = DB::connection('pgsql_metheo')->table($table)->insert($validatedData);
            }
        } catch (QueryException $e) {
            // Check if the error is a unique constraint violation
            if ($e->getCode() === '23505') { // PostgreSQL unique violation error code
                return response()->json(['error' => 'Duplicate entry for a unique field'], 422);
            }
            // Handle other query exceptions
            return response()->json(['error' => 'Database error'], 500);
        }

        return response()->json($row);
    }

    /** @throws \Illuminate\Validation\ValidationException */
    /** @return \Illuminate\Http\JsonResponse */
    /** @throws \Illuminate\Database\QueryException */
    /** Update an existing row in the table specified */
    public function updateRow(Request $request, $table, $id)
    {
        // Determine the correct primary key column based on the table name
        $primaryKey = $table === 'users' || $table === 'invites' ? 'id' : 'Id';

        // Check if the table exists in the primary database
        $primaryExists = DB::connection('pgsql')->getSchemaBuilder()->hasTable($table);

        // Check if the table exists in the secondary database
        $secondaryExists = DB::connection('pgsql_metheo')->getSchemaBuilder()->hasTable($table);

        if (!$primaryExists && !$secondaryExists) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        // Check if the row exists in the primary database
        $existsInPrimary = $primaryExists ? DB::connection('pgsql')->table($table)->where($primaryKey, $id)->exists() : false;

        // Check if the row exists in the secondary database
        $existsInSecondary = $secondaryExists ? DB::connection('pgsql_metheo')->table($table)->where($primaryKey, $id)->exists() : false;

        if (!$existsInPrimary && !$existsInSecondary) {
            return response()->json(['error' => 'Row not found'], 404);
        }

        // Define validation rules
        $rules = [];
        // Fetch columns from both databases
        $primaryColumns = $primaryExists ? DB::connection('pgsql')->select("SELECT column_name, is_nullable, data_type FROM information_schema.columns WHERE table_name = ?", [$table]) : [];
        $secondaryColumns = $secondaryExists ? DB::connection('pgsql_metheo')->select("SELECT column_name, is_nullable, data_type FROM information_schema.columns WHERE table_name = ?", [$table]) : [];

        // Merge columns from both databases
        $columns = array_merge($primaryColumns, $secondaryColumns);

        foreach ($columns as $column) {
            // Skip validation for `id` or `Id` fields
            if (!in_array(strtolower($column->column_name), ['id', 'Id'])) {
            $typeRule = match ($column->data_type) {
                'integer' => 'integer',
                'character varying', 'text' => 'string',
                'double precision', 'numeric' => 'numeric',
                'boolean' => 'boolean',
                'date' => 'date',
                'timestamp without time zone', 'timestamp with time zone' => 'date_format:Y-m-d H:i:s',
                default => 'string',
            };
            $rules[$column->column_name] = ($column->is_nullable === 'NO' ? 'required|' : '') . $typeRule;
            }
        }

        // Validate the request data
        try {
            $validatedData = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        // Remove any fields that are not in the table columns
        $validatedData = array_intersect_key($validatedData, array_flip(array_column($columns, 'column_name')));

        if (empty($validatedData)) {
            return response()->json(['error' => 'No valid data to update'], 422);
        }

        // Update the row in the appropriate database
        if ($existsInPrimary) {
            $row = DB::connection('pgsql')->table($table)->where($primaryKey, $id)->update($validatedData);
        } else {
            $row = DB::connection('pgsql_metheo')->table($table)->where($primaryKey, $id)->update($validatedData);
        }

        return response()->json($row);
    }

    /** @return \Illuminate\Http\JsonResponse */
    /** Delete a row from the table specified */
    public function deleteRow($table, $id)
    {
        // Determine the correct primary key column based on the table name
        $primaryKey = $table === 'users' || $table === 'invites' ? 'id' : 'Id';

        // Check if the table exists in the primary database
        $primaryExists = DB::connection('pgsql')->getSchemaBuilder()->hasTable($table);

        // Check if the table exists in the secondary database
        $secondaryExists = DB::connection('pgsql_metheo')->getSchemaBuilder()->hasTable($table);

        if (!$primaryExists && !$secondaryExists) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        // Check if the row exists in the primary database
        $existsInPrimary = $primaryExists ? DB::connection('pgsql')->table($table)->where($primaryKey, $id)->exists() : false;

        // Check if the row exists in the secondary database
        $existsInSecondary = $secondaryExists ? DB::connection('pgsql_metheo')->table($table)->where($primaryKey, $id)->exists() : false;

        if (!$existsInPrimary && !$existsInSecondary) {
            return response()->json(['error' => 'Row not found'], 404);
        }

        // Delete the row from the appropriate database
        if ($existsInPrimary) {
            $deletedRows = DB::connection('pgsql')->table($table)->where($primaryKey, $id)->delete();
        } else {
            $deletedRows = DB::connection('pgsql_metheo')->table($table)->where($primaryKey, $id)->delete();
        }

        // Return a JSON response indicating if a row was deleted
        return response()->json(['deleted' => $deletedRows > 0]);
    }
}

/**
 * DataController handles operations related to database tables and rows.
 * It interacts with two PostgreSQL databases: the primary database and a secondary 'users_invites' database.
 *
 * Methods:
 * - listTables(): Retrieves and returns a list of tables from both databases, excluding unauthorized tables.
 * - fetchData($table): Fetches and returns data from the specified table in either the primary or secondary database.
 * - createRow(Request $request, $table): Validates and inserts a new row into the specified table in either the primary or secondary database.
 * - updateRow(Request $request, $table, $id): Validates and updates an existing row in the specified table in either the primary or secondary database.
 * - deleteRow($table, $id): Deletes a row from the specified table in either the primary or secondary database.
 *
 * Note:
 * - Unauthorized tables are filtered out in the listTables() method.
 * - Validation rules for createRow() and updateRow() methods are dynamically generated based on the table's columns.
 * - The primary key column is determined based on the table name for updateRow() and deleteRow() methods.
 * - Error handling is implemented for table not found, row not found, validation errors, and database errors.
 */
