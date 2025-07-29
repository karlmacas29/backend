<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RaterAuthController extends Controller
{

      //create account and register rater account
    public function Raters_register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'job_batches_rsp_id' => 'required|array',
            'job_batches_rsp_id.*' => 'exists:job_batches_rsp,id',
            'position' => 'required|string|max:255',
            'office' => 'required|string|max:255',
            'password' => 'required|string|min:5',
            // 'active' => 'required|boolean',
        ]);

        // Generate username from name if not provided
        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'position' => $validated['position'],
            'office' => $validated['office'],
            'password' => Hash::make($validated['password']),
            //  'active ' => $validated['active'],
            'role_id' => 2, // Assuming 2 is for raters
            'remember_token' => Str::random(32)
        ]);

        // Attach job batches
        $user->job_batches_rsp()->attach($validated['job_batches_rsp_id']);
        return response()->json([
            'status' => true,
            'message' => 'Rater Registered Successfully',
            'data' => $user->load('job_batches_rsp')
        ], 201);
    }

    //edit rater where his assign
    public function editRater(Request $request, $id)
    {
        $validated = $request->validate([
            'job_batches_rsp_id' => 'nullable|array',
            'job_batches_rsp_id.*' => 'exists:job_batches_rsp,id',
            'office' => 'required|string|max:255',
        ]);

        // Find the user (rater) by ID
        $user = User::findOrFail($id);

        // Update office
        $user->office = $validated['office'];
        $user->save();

        // Sync job_batches_rsp only if provided
        if (isset($validated['job_batches_rsp_id'])) {
            $user->job_batches_rsp()->sync($validated['job_batches_rsp_id']);
        }

        return response()->json([
            'status' => true,
            'message' => 'Rater Updated Successfully',
            'data' => $user->load('job_batches_rsp')
        ]);
    }


    // login
    public function Raters_Login(Request $request)
    {
        // First check if username and password are provided
        if (empty($request->username) || empty($request->password)) {
            return response([
                'status' => false,
                'message' => 'Invalid Credentials',
                'errors' => [
                    'username' => empty($request->username) ? ['Please enter username'] : [],
                    'password' => empty($request->password) ? ['Please enter password'] : []
                ]
            ], 401);
        }

        $user = User::where('username', $request->username)->first();
        if (!$user) {
            return response([
                'status' => false,
                'message' => 'Invalid Credentials',
                'errors' => [
                    'username' => ['Username does not exist'],
                    'password' => ['Please enter password']
                ]
            ], 401);
        }

        // Then check if the password is correct
        if (!Hash::check($request->password, $user->password)) {
            return response([
                'status' => false,
                'message' => 'Invalid Credentials',
                'errors' => [
                    'password' => ['Wrong password']
                ]
            ], 401);
        }

        // Only allow users with role_id == 1
        if ($user->role_id != 2) {
            return response([
                'status' => false,
                'message' => 'Access Denied: You do not have permission to login.',
                'errors' => [
                    'role_id' => ['Only Rater admin can login.']
                ]
            ], 403);
        }

        // Authenticate the user
        Auth::login($user);

        $user = Auth::user();

        // Check if the user is active
        if (!$user->active) {
            return response([
                'status' => false,
                'message' => 'Your account is inactive. Please contact the administrator.',
            ], 403);
        }

        // Generate a token for the user
        $token = $user->createToken('my-secret-token')->plainTextToken;

        // Set the token in a secure cookie
        $cookie = cookie('rater_token', $token, 60 * 24, null, null, true, true, false, 'None');

        return response([
            'status' => true,
            'message' => 'Login Successfully',
            'user' => [
                'name' => $user->name,
                'position' => $user->position,
                'role_id' => (int)$user->role_id, // Always integer
                'role_name' => $user->role?->name // Optional chaining in case it's null
            ],
            'token' => $token,
        ])->withCookie($cookie);
    }


public function change_password(Request $request)
{
    // Validate request
    $validator = Validator::make($request->all(), [
        'old_password' => 'required',
        'new_password' => 'required|min:8|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    // Get authenticated rater
    $rater = $request->user();

    // Verify old password
    if (!Hash::check($request->old_password, $rater->password)) {
        return response()->json([
            'status' => false,
            'errors' => ['old_password' => ['The current password is incorrect']]
        ], 422);
    }

    // Update password
    $rater->password = Hash::make($request->new_password);
    $rater->save();

    return response()->json([
        'status' => true,
        'message' => 'Password changed successfully'
    ]);
}

    // Delete a user and associated rspControl data
    public function deleteUser($id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);

            // Prevent deleting currently authenticated user
            if (Auth::id() == $id) {
                return response()->json([
                    'status' => false,
                    'message' => 'You cannot delete your own account'
                ], 403);
            }

            // Delete associated rspControl first (if not using cascading deletes)
            if ($user->rspControl) {
                $user->rspControl->delete();
            }

            // Delete the user
            $user->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Rater and associated permissions deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Rater not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete Rater',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get all users (User Management) with rspControl data


}
