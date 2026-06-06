<?php
// api.php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TransferController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TransactionController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
  Route::post('account/account-request', [AccountRequestController::class, 'create']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/user/latest-transactions', [UserController::class, 'getLatestTransactions']);
    Route::post('/user/change-password', [UserController::class, 'changePassword']);
    Route::put('/user/update-profile', [UserController::class, 'updateProfile']);


});

  Route::post('verify-account', [AccountRequestController::class, 'verify']);
Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {

    Route::get('account/requests', [AccountRequestController::class, 'index']);


    Route::post('account/{accountRequest}/accept', [AccountRequestController::class, 'acceptRequest']);

    Route::post('account/{accountRequest}/reject', [AccountRequestController::class, 'reject']);

    Route::get('/users', [UserController::class, 'index']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('notifications')->group(function () {
        Route::get('/all', [NotificationController::class, 'index']);
        Route::get('/count', [NotificationController::class, 'unreadCount']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);

       Route::middleware('check.notification.owner')->group(function () {
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);

       });
    });

});


Route::middleware('auth:sanctum')->group(function () {
    Route::post('/transfers', [TransferController::class, 'store']);
    Route::post('/withdrawals', [WithdrawalController::class, 'store']);
    Route::post('/deposit', [TransactionController::class, 'deposit']);
    Route::get('/balance', [TransactionController::class, 'showBalance']);
    Route::get('/statement', [TransactionController::class, 'accountStatement']);
});



Route::middleware(['auth:sanctum', 'is_admin'])->prefix('admin')->group(function () {

    Route::get('/statistics', [AdminController::class, 'getStatistics']);

    Route::get('/users', [AdminController::class, 'getAllUsers']);
    Route::put('/users/{userId}', [AdminController::class, 'editUser']);
    Route::delete('/users/{userId}', [AdminController::class, 'removeUser']);

    Route::get('/customers/active', [AdminController::class, 'getActiveCustomers']);
    Route::get('/customers/unActive', [AdminController::class, 'getUnActiveCustomers']);
    Route::patch('/customers/{userId}/deactivate', [AdminController::class, 'deactivateCustomer']);

    Route::get('/employees', [AdminController::class, 'getAllEmployees']);
    Route::post('/employees', [AdminController::class, 'addEmployee']);
    Route::put('/employees/{empId}', [AdminController::class, 'updateEmployee']);
    Route::delete('/employees/{employeeId}', [AdminController::class, 'removeEmployee']); 
});
