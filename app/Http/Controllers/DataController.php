<?php
// App/Http/Controllers/DataController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class DataController extends Controller
{

    public function fetchData($table)
    {
        // Check if the table exists
        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        // Fetch data for the specified table
        $data = DB::table($table)->get();
        return response()->json($data);
    }

    public function createRow(Request $request, $table)
    {
        // Define validation rules
        $rules = [];
        $requiredColumns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = ? AND is_nullable = 'NO'", [$table]);
    
        foreach ($requiredColumns as $column) {
            // Skip validation for `id` or `Id` fields
            if (!in_array(strtolower($column->column_name), ['id'])) {
                $columnType = DB::selectOne("SELECT data_type FROM information_schema.columns WHERE table_name = ? AND column_name = ?", [$table, $column->column_name])->data_type;
                $typeRule = match ($columnType) {
                    'integer' => 'integer',
                    'character varying', 'text' => 'string',
                    'boolean' => 'boolean',
                    'date' => 'date',
                    'timestamp without time zone', 'timestamp with time zone' => 'date_format:Y-m-d H:i:s',
                    default => 'string',
                };
                $rules[$column->column_name] = 'required|' . $typeRule;
            }
        }
    
        // Validate the request data
        try {
            $validatedData = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    
        // Check if the table exists
        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return response()->json(['error' => 'Table not found'], 404);
        }
    
        // Insert the new row into the specified table
        try {
            $row = DB::table($table)->insert($validatedData);
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

    public function updateRow(Request $request, $table, $id)
    {
        // Determine the correct primary key column based on the table name
        $primaryKey = $table === 'users' ? 'id' : 'Id';
        
        // Check if the table exists
        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        $exists = DB::table($table)->where($primaryKey, $id)->exists();
        if (!$exists) {
            return response()->json(['error' => 'Row not found'], 404);
        }

        // Define validation rules
        $rules = [];
        $requiredColumns = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name = ? AND is_nullable = 'NO'", [$table]);
    
        foreach ($requiredColumns as $column) {
            // Skip validation for `id` or `Id` fields
            if (!in_array(strtolower($column->column_name), ['id'])) {
                $columnType = DB::selectOne("SELECT data_type FROM information_schema.columns WHERE table_name = ? AND column_name = ?", [$table, $column->column_name])->data_type;
                $typeRule = match ($columnType) {
                    'integer' => 'integer',
                    'character varying', 'text' => 'string',
                    'boolean' => 'boolean',
                    'date' => 'date',
                    'timestamp without time zone', 'timestamp with time zone' => 'date_format:Y-m-d H:i:s',
                    default => 'string',
                };
                $rules[$column->column_name] = 'required|' . $typeRule;
            }
        }
    
        // Validate the request data
        try {
            $validatedData = $request->validate($rules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
        
        // Update the row in the specified table
        $row = DB::table($table)->where($primaryKey, $id)->update($validatedData);
        return response()->json($row);
    }

    public function deleteRow($table, $id)
    {
        // Determine the correct primary key column based on the table name
        $primaryKey = $table === 'users' ? 'id' : 'Id';

        // Check if the table exists
        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return response()->json(['error' => 'Table not found'], 404);
        }

        $exists = DB::table($table)->where($primaryKey, $id)->exists();
        if (!$exists) {
            return response()->json(['error' => 'Row not found'], 404);
        }

        // Delete the row from the specified table using the determined primary key
        $deletedRows = DB::table($table)->where($primaryKey, $id)->delete();
    
        // Return a JSON response indicating if a row was deleted
        return response()->json(['deleted' => $deletedRows > 0]);
    }    
}
