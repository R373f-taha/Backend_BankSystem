<?php

namespace App\Services;

use App\Models\AccountRequest;
use App\Models\Customer;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminService
{

public function getStatistics()
{
    $monthlyTransactions = DB::table('transactions')
        ->select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get();

    $recentUsers = User::latest()->take(5)->get();

    $monthlyRegistrations = User::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('YEAR(created_at) as year'),
            DB::raw('COUNT(*) as total')
        )
        ->groupBy('year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get();

    $activeCustomers = Customer::where('status', 'active')->count();
    $unactiveCustomers = Customer::where('status', 'un_active')->count();

    $totalCustomers = User::where('role', 'customer')->count();
    $totalEmployees = User::where('role', 'employee')->count();
    $totalUsers = User::count();

    $admin = User::where('role', 'admin')->first();
    Notification::create([
                'user_id' => $admin->id,
                'title' => '📊 Statistics Viewed',
                'message' => "Admin " . Auth::user()->name . "' viewed the system statistics.",
                'is_read' => false
            ]);

    return [
        'total_users' => $totalUsers,
        'total_customers' => $totalCustomers,
        'total_employees' => $totalEmployees,
        'total_transactions' => DB::table('transactions')->count(),
        'active_customers' => $activeCustomers,
        'unactive_customers' => $unactiveCustomers,
        'monthly_transactions' => $monthlyTransactions,
        'monthly_registrations' => $monthlyRegistrations,
        'recent_users' => $recentUsers,
        'system_summary' => [
            'total_registered' => $totalUsers,
            'Percentage change in registrations this month compared to last month 📊' => $this->calculateRegistrationRate()
        ]
    ];
}

private  function calculateRegistrationRate()
{
      $lastMonth = User::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();

        $thisMonth = User::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        if ($lastMonth == 0) return 100;

        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 2);
}

public function getAllUsers(array $filters)
{
    $query=User::query();

    if(isset($filters['name'])){
        $query->where('name','like',"%{$filters['name']}%");
    }
        if(isset($filters['email'])){
            $query->where('email','like',"%{$filters['email']}%");
        }

    if(isset($filters['role'])){
        $query->where('role',$filters['role']);
    }

    return $query->get();
}

public function getActiveCustomer(){

return Customer::where('status','active')->with('user')->get();

}

public function getUnActiveCustomer(){

return Customer::where('status','un_active')->with('user')->get();

}

public function deactivateCustomer(int $userId)
{
    $user = User::find($userId);

    if (!$user) {
        return [
            'success' => false,
            'message' => 'User not found',
            'code' => 404
        ];
    }

    if ($user->role !== 'customer') {
        return [
            'success' => false,
            'message' => 'User is not a customer',
            'code' => 400
        ];
    }

    $customer = Customer::where('user_id', $userId)->first();

    if (!$customer) {
        return [
            'success' => false,
            'message' => 'Customer profile not found',
            'code' => 404
        ];
    }

    $customer->status = 'un_active';
    $customer->save();

    $admin = User::where('role', 'admin')->first();
    Notification::create([
        'user_id' => $admin->id,
        'title' => '🚫 Customer Deactivated',
        'message' => "Customer '{$user->name}' has been deactivated by " . Auth::user()->name,
        'is_read' => false
    ]);

    return [
        'success' => true,
        'message' => 'User deactivated successfully',
        'code' => 200
    ];
}
public function editUser(int $userId, array $data)
{
    $user = User::findOrFail($userId);

    $updated = $user->update($data);

    if (!$updated) {
        return [
            'success' => false,
            'message' => 'Failed to update user',
            'code' => 500
        ];
    }

    $admin = User::where('role', 'admin')->first();
    Notification::create([
        'user_id' => $admin->id,
        'title' => '✏️ User Updated',
        'message' => "User '{$user->name}' has been updated by " . Auth::user()->name,
        'is_read' => false
    ]);

    return [
        'success' => true,
        'message' => 'User updated successfully',
        'user' => $user->fresh()
    ];
}

public function removeUser(int $userId)
{
    $user = User::find($userId);

      if (!$user) {
        return [
            'success' => false,
            'message' => 'User profile not found',
            'code' => 404
        ];
    }


    $deleted = false;

    switch ($user->role) {
        case 'customer':
            AccountRequest::where('email', $user->email)->delete();
            if ($user->customer()) {
                $user->customer()->delete();
            }
            $deleted = $user->delete();
            break;
        case 'employee':
            $deleted = $user->delete();
            break;
        default:
            $deleted = $user->delete();
            break;
    }

    if (!$deleted) {
        return [
            'success' => false,
            'message' => 'Failed to remove user'
        ];
    }
    $admin = User::where('role', 'admin')->first();
    Notification::create([
        'user_id' => $admin->id,
        'title' => '🗑️ User Removed',
        'message' => "User '{$user->name}' has been removed from the system by " . Auth::user()->name,
        'is_read' => false
    ]);

    return [
        'success' => true,
        'message' => 'User removed successfully'
    ];
}

public function addEmployee(array $data)
{
    $employee = User::create($data);

    if (!$employee) {
        return [
            'success' => false,
            'message' => 'Failed to add employee'
        ];
    }

    $admin = User::where('role', 'admin')->first();
    Notification::create([
        'user_id' => $admin->id,
        'title' => '📌 New Employee Added',
        'message' => "A new employee '{$employee->name}' has been added to the system by " . Auth::user()->name,
        'is_read' => false
    ]);

    return [
        'success' => true,
        'message' => 'Employee added successfully',
        'employee' => $employee
    ];
}


public function updateEmployee(int $empId, array $data)
{
    $employee = User::where('id', $empId)->where('role', 'employee')->firstOrFail();

    $updated = $employee->update($data);

    if (!$updated) {
        return [
            'success' => false,
            'message' => 'Failed to update employee'
        ];
    }

    $admin = User::where('role', 'admin')->first();
    Notification::create([
        'user_id' => $admin->id,
        'title' => '✏️ Employee Updated',
        'message' => "Employee '{$employee->name}' has been updated by " . Auth::user()->name,
        'is_read' => false
    ]);

    return [
        'success' => true,
        'message' => 'Employee updated successfully',
        'employee' => $employee->fresh()
    ];
}

    public function getAllEmployees(array $filters = []): array
    {
      //  $filters = $request->only(['name', 'email']);
        $employees = User::where('role', 'employee');

        if (isset($filters['name'])) {
            $employees->where('name', 'like', "%{$filters['name']}%");
        }

        if (isset($filters['email'])) {
            $employees->where('email', 'like', "%{$filters['email']}%");
        }

        return [
            'success' => true,
            'message' => 'Employees retrieved successfully',
            'employees' => $employees->get()
        ];
    }


}
