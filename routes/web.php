<?php

use App\Http\Controllers\Admin\AgentCodeController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\WithdrawRequestController as AdminWithdrawRequestController;
use App\Http\Controllers\Agent\CommissionController;
use App\Http\Controllers\Agent\PlayerCodeController;
use App\Http\Controllers\Agent\UserManagementController as AgentUserManagementController;
use App\Http\Controllers\Agent\WithdrawRequestController as AgentWithdrawRequestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeclareController;
use App\Http\Controllers\PlayerGameController;
use App\Http\Controllers\WithdrawRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    
    Route::get('/admin/dashboard', [AuthController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::get('/agent/dashboard', [AuthController::class, 'agentDashboard'])->name('agent.dashboard');

    
    Route::get('/player/dashboard', [PlayerGameController::class, 'dashboard'])->name('player.dashboard');
    Route::post('/player/bet', [PlayerGameController::class, 'placeBet'])->name('player.bet');
    Route::get('/player/bet-history', [PlayerGameController::class, 'history'])->name('player.bet-history');

    Route::get('/player/latest-declaration', [PlayerGameController::class, 'latestDeclaration'])
        ->name('player.latest-declaration');

    Route::get('/player/current-bet-totals', [PlayerGameController::class, 'currentBetTotals'])
        ->name('player.current-bet-totals');

    
    Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users.index');
    Route::patch('/admin/users/{user}/approve', [UserManagementController::class, 'approve'])->name('admin.users.approve');
    Route::patch('/admin/users/{user}/disapprove', [UserManagementController::class, 'disapprove'])->name('admin.users.disapprove');
    Route::patch('/admin/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('admin.users.role');
    Route::patch('/admin/users/{user}/info', [UserManagementController::class, 'updateInfo'])->name('admin.users.info');
    Route::patch('/admin/users/{user}/password', [UserManagementController::class, 'changePassword'])->name('admin.users.password');
    Route::patch('/admin/users/{user}/give-credit', [UserManagementController::class, 'giveCredit'])->name('admin.users.giveCredit');
    Route::delete('/admin/users/{user}/force-logout', [UserManagementController::class, 'forceLogout'])->name('admin.users.forceLogout');

    
    Route::get('/admin/agent-codes', [AgentCodeController::class, 'index'])->name('admin.agent-codes.index');
    Route::post('/admin/agent-codes', [AgentCodeController::class, 'store'])->name('admin.agent-codes.store');

    
    Route::get('/agent/player-codes', [PlayerCodeController::class, 'index'])->name('agent.player-codes.index');
    Route::post('/agent/player-codes', [PlayerCodeController::class, 'store'])->name('agent.player-codes.store');

    Route::get('/agent/users', [AgentUserManagementController::class, 'index'])->name('agent.users.index');
    Route::patch('/agent/users/{user}/give-credit', [AgentUserManagementController::class, 'giveCredit'])->name('agent.users.giveCredit');

    Route::get('/agent/commissions', [CommissionController::class, 'index'])->name('agent.commissions.index');

    
    Route::get('/withdrawals', [WithdrawRequestController::class, 'index'])->name('withdrawals.index');
    Route::post('/withdrawals', [WithdrawRequestController::class, 'store'])->name('withdrawals.store');

    Route::get('/agent/withdrawals', [AgentWithdrawRequestController::class, 'index'])->name('agent.withdrawals.index');
    Route::patch('/agent/withdrawals/{withdrawRequest}/approve', [AgentWithdrawRequestController::class, 'approve'])->name('agent.withdrawals.approve');
    Route::patch('/agent/withdrawals/{withdrawRequest}/reject', [AgentWithdrawRequestController::class, 'reject'])->name('agent.withdrawals.reject');

    Route::get('/admin/withdrawals', [AdminWithdrawRequestController::class, 'index'])->name('admin.withdrawals.index');
    Route::patch('/admin/withdrawals/{withdrawRequest}/approve', [AdminWithdrawRequestController::class, 'approve'])->name('admin.withdrawals.approve');
    Route::patch('/admin/withdrawals/{withdrawRequest}/reject', [AdminWithdrawRequestController::class, 'reject'])->name('admin.withdrawals.reject');

    
    Route::get('/declare/dashboard', [DeclareController::class, 'index'])->name('declare.dashboard');
    Route::post('/declare/winner', [DeclareController::class, 'store'])->name('declare.winner.store');

    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});