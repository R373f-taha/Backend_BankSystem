<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Services\AdminService;
use Illuminate\Http\Request;
use App\Http\Requests\Employee\CreateEmployeeRequest;
class AdminController extends Controller
{

protected AdminService $adminService;


public function __construct(AdminService $adminService)
{
    $this->adminService = $adminService;
}

public function getStatistics()
{
    $statistics = $this->adminService->getStatistics();
    return response()->json([
            'success' => true,
            'statistics' => $statistics
        ]);

}

public function getAllUsers(Request $request)
{
   $filters=$request->only(['name', 'email', 'role']);

   $users=$this->adminService->getAllUsers($filters);

    return response()->json([
            'success' => true,
            'users' => $users
        ]);
}
public function editUser(UpdateUserRequest $request,int $userId)
{

    $data=$request->validated();
   if (empty($data)) {
        return response()->json([
            'success' => false,
            'message' => 'No data provided to update',
        ], 400);
    }
   $result=$this->adminService->editUser($userId,$data);

     return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'user'    => $result['user'] ?? null,
        ]);

}

public function removeUser(int $userId){
    $result=$this->adminService->removeUser($userId);

     return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
}

public function getActiveCustomers(){
    $customers=$this->adminService->getActiveCustomer();

     return response()->json([
            'success' => true,
            'customers' => $customers
        ]);

}
public function getUnActiveCustomers(){
    $customers=$this->adminService->getUnActiveCustomer();

     return response()->json([
            'success' => true,
            'customers' => $customers
        ]);
}
public function deactivateCustomer(int $userId){
    $result=$this->adminService->deactivateCustomer($userId);

     return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
}

public function getAllEmployees(Request $request)
{
   $filters=$request->only(['name', 'email']);

   $employees=$this->adminService->getAllEmployees($filters);

    return response()->json([
            'success' => true,
            'employees' => $employees
        ]);
}

public function addEmployee(CreateEmployeeRequest $request){

    $data=$request->validated();

    $data['role']='employee';

    $result=$this->adminService->addEmployee($data);

     return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'employee' => $result['employee'] ?? null,
        ]);
}

public function updateEmployee(UpdateEmployeeRequest $request,int $empId){

    $data=$request->validated();
    $data = array_filter($data); // Remove null values
    $result=$this->adminService->updateEmployee($empId,$data);

     return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'employee' => $result['employee'] ?? null,
        ]);

}

public function removeEmployee(int $employeeId){
    $result=$this->adminService->removeUser($employeeId);

     return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
}


}
