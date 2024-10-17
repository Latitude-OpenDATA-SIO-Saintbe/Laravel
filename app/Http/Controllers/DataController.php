<?php
// App/Http/Controllers/DataController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataController extends Controller
{
    public function listTables()
    {
        // Get the list of tables from the PostgreSQL database
        $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");

        // Specify unauthorized tables that should be filtered out
        $unauthorizedTables = ['migrations', 'password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'];

        // Filter the tables to exclude unauthorized ones
        $filteredTables = array_filter($tables, function ($table) use ($unauthorizedTables) {
            return !in_array($table->table_name, $unauthorizedTables);
        });

        return response()->json(array_column($filteredTables, 'table_name'));
    }

    public function fetchData($table)
    {
        // Fetch data for the specified table
        $data = DB::table($table)->get();
        return response()->json($data);
    }

    public function createRow(Request $request, $table)
    {
        // Validate request data
        $validatedData = $request->validate([
            // Add validation
        ]);

        // Insert the new row into the specified table
        $row = DB::table($table)->insert($validatedData);
        return response()->json($row);
    }

    public function updateRow(Request $request, $table, $id)
    {
        // Validate request data
        $validatedData = $request->validate([
            // Add validation
        ]);

        // Update the row in the specified table
        $row = DB::table($table)->where('id', $id)->update($validatedData);
        return response()->json($row);
    }

    public function deleteRow($table, $id)
    {
        // Delete the row from the specified table
        $row = DB::table($table)->where('id', $id)->delete();
        return response()->json(['deleted' => $row]);
    }
}
