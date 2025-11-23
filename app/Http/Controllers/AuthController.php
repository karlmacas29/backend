<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAdminRegisterRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\Employee;
use App\Models\Submission;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller

{
    public function get_role()
    {
        $data = Role::all();
        return response()->json([
            'status' => true,
            'message' => 'Roles retrieved successfully',
            'data' => $data
        ]);
    }

    // create account
    public function userRegister(UserAdminRegisterRequest $request)
    {

        try {
            DB::beginTransaction();

            $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password), // Don't forget to hash!
            'position' => $request->position,
            'active' => $request->active,
            'role_id' => 1, // Set appropriate role
            'remember_token' => Str::random(32)
           ]);

            if ($request->has('permissions')) {
                $user->rspControl()->create([
                    'viewDashboardstat' => $request->input('permissions.viewDashboardstat', false),
                    'viewPlantillaAccess' => $request->input('permissions.viewPlantillaAccess', false),
                    'modifyPlantillaAccess' => $request->input('permissions.modifyPlantillaAccess', false),
                    'viewJobpostAccess' => $request->input('permissions.viewJobpostAccess', false),
                    'modifyJobpostAccess' => $request->input('permissions.modifyJobpostAccess', false),
                    'viewActivityLogs' => $request->input('permissions.viewActivityLogs', false),
                    'userManagement' => $request->input('permissions.userManagement', false),
                    'viewRater' => $request->input('permissions.viewRater', false),
                    'modifyRater' => $request->input('permissions.modifyRater', false),

                    'viewCriteria' => $request->input('permissions.viewCriteria', false),
                    'modifyCriteria' => $request->input('permissions.modifyCriteria', false),

                    'viewReport' => $request->input('permissions.viewReport', false),
                ]);
            }


            // ✅ Activity Log
            activity($user->name)
                ->causedBy($user)  // The currently logged-in admin creating this new admin
                ->performedOn($user)
                ->withProperties([
                    'created_by' => Auth::user()?->name,
                    'new_admin_name' => $user->name,
                    'username' => $user->username,
                    'position' => $user->position,
                    'active' => $user->active,
                    'role' =>  $user->role_id,
                    'ip' => request()->ip(),
                    'user_agent' => request()->header('User-Agent'),
                ])
                ->log("'{$user->name}' was registered successfully by '" . Auth::user()?->name . "'.");

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'User Registered Successfully',
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Registration Failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }



    // Login
    public function Token_Login(Request $request)
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
        if ($user->role_id != 1) {
            return response([
                'status' => false,
                'message' => 'Access Denied: You do not have permission to login.',
                'errors' => [
                    'role_id' => ['Only users admin can login.']
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
        // $token = $user->createToken('my-secret-token')->plainTextToken;
        // $token = $user->createToken('admin_token')->plainTextToken;
        // Force logout any previous sessions
        $user->tokens()->delete();

        // Generate fresh token
        $token = $user->createToken('admin_token')->plainTextToken;

        // Set the token in a secure cookie
        $cookie = cookie('admin_token', $token, 60 * 24, null, null, true, true, false, 'None');

        if ($user instanceof \App\Models\User) {
            $user->load('role'); // make sure the role is loaded
            activity($user->name)
                ->causedBy($user)
                ->performedOn($user)
                ->withProperties([
                    'username' => $user->username,
                    'role' => $user->role?->name,
                    'office' => $user->office,
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                ])
                ->log("'{$user->name}' logged in successfully.");
        }


        return response([
            'status' => true,
            'message' => 'Login Successfully',
            'user' => [
                'name' => $user->name,
                'position' => $user->position,
                'role_id' => (int)$user->role_id, // Always integer

            ],
            'token' => $token,
        ])->withCookie($cookie);
    }


    // logout
    public function userLogout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $user->tokens()->delete();
        }

        $cookie = cookie()->forget('admin_token');

        if ($user instanceof \App\Models\User) {
            activity($user->name)
                ->causedBy($user)
                ->performedOn($user)
                ->withProperties([
                    'username' => $user->username,
                    'role' => $user->role?->role_name,
                    'office' => $user->office,
                    'ip' => $request->ip(),
                    'user_agent' => $request->header('User-Agent'),
                ])
                ->log("'{$user->name}' logout successfully.");
        }

        return response([
            'status' => true,
            'message' => 'Logout Successfully',
        ])->withCookie($cookie);
    }

    // Get all users (User Management) with rspControl data
    public function getAllUsers()
    {
        try {
            $users = User::where('role_id', 1)->with('rspControl')
                ->select('id', 'name', 'username', 'position', 'active', 'created_at', 'updated_at')
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Users retrieved successfully',
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Get a specific user by ID with rspControl data
    public function getUserById($id)
    {
        try {
            $user = User::with('rspControl')
                ->select('id', 'name', 'username', 'position', 'active')
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'User retrieved successfully',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    // Update user details including permissions
    public function updateUser(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);

            // Validation rules
            $rules = [
                'name' => 'required|string|max:255',
                'position' => 'required|string|max:255',
                'active' => 'required|boolean',
                'permissions.viewDashboardstat' => 'boolean',
                'permissions.viewPlantillaAccess' => 'boolean',
                'permissions.modifyPlantillaAccess' => 'boolean',
                'permissions.viewJobpostAccess' => 'boolean',
                'permissions.modifyJobpostAccess' => 'boolean',
                'permissions.viewActivityLogs' => 'boolean',
                'permissions.userManagement' => 'boolean',
                'permissions.viewRaterManagement' => 'boolean',
                'permissions.modifyRaterManagement' => 'boolean',
                'permissions.viewCriteria' => 'boolean',
                'permissions.modifyCriteria' => 'boolean',
                'permissions.viewReport' => 'boolean',



            ];

            // Add username validation, but exclude the current user's username from the unique check
            $rules['username'] = 'required|string|max:255|unique:users,username,' . $id;

            // If password is provided, add validation rule
            if ($request->filled('password')) {
                $rules['password'] = 'string|min:3';
            }

            $validatedData = $request->validate($rules);

            // Update user data
            $user->name = $validatedData['name'];
            $user->username = $validatedData['username'];
            $user->position = $validatedData['position'];
            $user->active = $validatedData['active'];

            // Only update password if provided
            if ($request->filled('password')) {
                $user->password = Hash::make($validatedData['password']);
            }

            $user->save();

            // Update or create permissions
            if ($request->has('permissions')) {
                if ($user->rspControl) {
                    $user->rspControl->update([
                        'viewDashboardstat' => $request->input('permissions.viewDashboardstat', false),
                        'viewPlantillaAccess' => $request->input('permissions.viewPlantillaAccess', false),
                        'modifyPlantillaAccess' => $request->input('permissions.modifyPlantillaAccess', false),
                        'viewJobpostAccess' => $request->input('permissions.viewJobpostAccess', false),
                        'modifyJobpostAccess' => $request->input('permissions.modifyJobpostAccess', false),
                        'viewActivityLogs' => $request->input('permissions.viewActivityLogs', false),
                        'userManagement' => $request->input('permissions.userManagement', false),
                        'viewRater' => $request->input('permissions.viewRater', false),
                        'modifyRater' => $request->input('permissions.modifyRater', false),

                        'viewCriteria' => $request->input('permissions.viewCriteria', false),
                        'modifyCriteria' => $request->input('permissions.modifyCriteria', false),

                        'viewReport' => $request->input('permissions.viewReport', false),


                    ]);
                } else {
                    $user->rspControl()->create([
                        'viewDashboardstat' => $request->input('permissions.viewDashboardstat', false),
                        'viewPlantillaAccess' => $request->input('permissions.viewPlantillaAccess', false),
                        'modifyPlantillaAccess' => $request->input('permissions.modifyPlantillaAccess', false),
                        'viewJobpostAccess' => $request->input('permissions.viewJobpostAccess', false),
                        'modifyJobpostAccess' => $request->input('permissions.modifyJobpostAccess', false),
                        'viewActivityLogs' => $request->input('permissions.viewActivityLogs', false),
                        'userManagement' => $request->input('permissions.userManagement', false),
                        'viewRater' => $request->input('permissions.viewRater', false),
                        'modifyRater' => $request->input('permissions.modifyRater', false),

                        'viewCriteria' => $request->input('permissions.viewCriteria', false),
                        'modifyCriteria' => $request->input('permissions.modifyCriteria', false),

                        'viewReport' => $request->input('permissions.viewReport', false),

                    ]);
                }
            }

            DB::commit();

            // Fetch updated user with permissions
            $updatedUser = User::with('rspControl')
                ->select('id', 'name', 'username', 'position', 'active')
                ->findOrFail($id);

            return response()->json([
                'status' => true,
                'message' => 'User updated successfully',
                'data' => $updatedUser
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
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
                'message' => 'User and associated permissions deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
