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
        // Get all request data
        $data = $request->all();

        // Remove 'Id' if it is not provided
        if (empty($data['Id'])) {
            unset($data['Id']);
        }

        // Insert the new row into the specified table
        $row = DB::table($table)->insert($data);
        return response()->json($row);
    }

    public function updateRow(Request $request, $table, $id)
    {
        // Validate request data

        // Update the row in the specified table
        $row = DB::table($table)->where('Id', $id)->update($request->all());
        return response()->json($row);
    }

    public function deleteRow($table, $id)
    {
        // Delete the row from the specified table
        $row = DB::table($table)->where('Id', $id)->delete();
        // log $row
        return response()->json($row);
        return response()->json(['deleted' => $row]);
    }
}
