<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SubdomainController extends Controller
{
    /**
     * Check if a subdomain is available
     */
    public function check(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subdomain' => ['required', 'string', 'max:63', 'regex:/^[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?$/'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'available' => false,
                'message' => 'Invalid subdomain format. Use only lowercase letters, numbers, and hyphens. Must start and end with a letter or number.'
            ]);
        }

        $subdomain = $request->input('subdomain');

        // Check if the subdomain exists in the domains table
        $exists = Domain::where('domain', 'like', $subdomain . '.%')->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'This subdomain is already taken.' : 'This subdomain is available.'
        ]);
    }

    /**
     * Update a subdomain
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_subdomain' => ['required', 'string', 'max:63'],
            'new_subdomain' => ['required', 'string', 'max:63', 'regex:/^[a-z0-9](?:[a-z0-9\-]{0,61}[a-z0-9])?$/'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid subdomain format. Use only lowercase letters, numbers, and hyphens. Must start and end with a letter or number.'
            ], 422);
        }

        $currentSubdomain = $request->input('current_subdomain');
        $newSubdomain = $request->input('new_subdomain');

        // Check if the new subdomain is already taken
        $exists = Domain::where('domain', 'like', $newSubdomain . '.%')
            ->where('domain', 'not like', $currentSubdomain . '.%')
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'This subdomain is already taken.'
            ], 422);
        }

        // Find the current domain
        $domain = Domain::where('domain', 'like', $currentSubdomain . '.%')->first();

        if (!$domain) {
            return response()->json([
                'success' => false,
                'message' => 'Current domain not found.'
            ], 404);
        }

        // Update the domain name
        $oldDomain = $domain->domain;
        $baseDomain = substr($oldDomain, strpos($oldDomain, '.') + 1);
        $domain->domain = $newSubdomain . '.' . $baseDomain;
        $domain->save();

        // If tenant ID is based on subdomain, update it
        $tenant = Tenant::find($domain->tenant_id);
        if ($tenant && $tenant->id === $currentSubdomain) {
            // Rename the database
            $oldDatabaseName = config('tenancy.database.prefix') . $tenant->id . config('tenancy.database.suffix');
            $newDatabaseName = config('tenancy.database.prefix') . $newSubdomain . config('tenancy.database.suffix');

            try {
                // Create a new database with the new name
                DB::statement("CREATE DATABASE IF NOT EXISTS `{$newDatabaseName}`");

                // Copy data from old database to new one
                // This is a simplified approach - in a real application, you might want to use a more robust method
                $tables = DB::select("SHOW TABLES FROM `{$oldDatabaseName}`");
                $tableKey = 'Tables_in_' . $oldDatabaseName;

                foreach ($tables as $table) {
                    $tableName = $table->$tableKey;
                    DB::statement("CREATE TABLE `{$newDatabaseName}`.`{$tableName}` LIKE `{$oldDatabaseName}`.`{$tableName}`");
                    DB::statement("INSERT INTO `{$newDatabaseName}`.`{$tableName}` SELECT * FROM `{$oldDatabaseName}`.`{$tableName}`");
                }

                // Update tenant ID
                $tenant->id = $newSubdomain;
                $tenant->save();

                // Drop the old database
                DB::statement("DROP DATABASE `{$oldDatabaseName}`");
            } catch (\Exception $e) {
                // If something goes wrong, revert domain change
                $domain->domain = $oldDomain;
                $domain->save();

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update database: ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Subdomain updated successfully.',
            'new_domain' => $domain->domain
        ]);
    }

    /**
     * Get the current domain for the authenticated user
     */
    public function current(Request $request)
    {
        // Get the current domain from the authenticated user
        $user = $request->user();
        
        if (!$user) {
            // If not authenticated, get from the request host
            $domain = $request->getHost();
        } else {
            // Get the domain associated with the user's tenant
            $domain = Domain::where('tenant_id', $user->tenant_id)->first();
            
            if (!$domain) {
                // Fallback to request host if no domain found
                $domain = $request->getHost();
            } else {
                $domain = $domain->domain;
            }
        }
        
        return response()->json([
            'success' => true,
            'domain' => $domain
        ]);
    }
}