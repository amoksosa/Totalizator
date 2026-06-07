<?php

use App\Http\Controllers\Admin\CommissionController as AdminCommissionController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\WithdrawRequestController as AdminWithdrawRequestController;
use App\Http\Controllers\Agent\CommissionReportController;
use App\Http\Controllers\Agent\PlayerCodeController;
use App\Http\Controllers\Agent\UserManagementController as AgentUserManagementController;
use App\Http\Controllers\Agent\WithdrawRequestController as AgentWithdrawRequestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Declare\EventController as DeclareEventController;
use App\Http\Controllers\Declare\RoundController as DeclareRoundController;
use App\Http\Controllers\DeclareController;
use App\Http\Controllers\PlayerGameController;
use App\Http\Controllers\PokemonGameController;
use App\Http\Controllers\PokemonLobbyController;
use App\Http\Controllers\WithdrawRequestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])
        ->name('register');

    Route::post('/register', [AuthController::class, 'register'])
        ->name('register.store');

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.store');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Main Dashboards
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/dashboard', [AuthController::class, 'adminDashboard'])
        ->name('admin.dashboard');

    Route::get('/agent/dashboard', [AuthController::class, 'agentDashboard'])
        ->name('agent.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Player Dashboard / Game Selection
    |--------------------------------------------------------------------------
    */

    Route::get('/player/dashboard', function () {
        if (auth()->user()->role !== 'player') {
            abort(403);
        }

        return view('player.dashboard');
    })->name('player.dashboard');

    /*
    |--------------------------------------------------------------------------
    | Player Totalizator
    |--------------------------------------------------------------------------
    */

    Route::get('/player/totalizator', [PlayerGameController::class, 'dashboard'])
        ->name('player.totalizator');

    Route::post('/player/bet', [PlayerGameController::class, 'placeBet'])
        ->name('player.bet');

    Route::get('/player/bet-history', [PlayerGameController::class, 'history'])
        ->name('player.bet-history');

    Route::get('/player/latest-declaration', [PlayerGameController::class, 'latestDeclaration'])
        ->name('player.latest-declaration');

    Route::get('/player/current-bet-totals', [PlayerGameController::class, 'currentBetTotals'])
        ->name('player.current-bet-totals');

    /*
    |--------------------------------------------------------------------------
    | Pokémon Demo Game
    |--------------------------------------------------------------------------
    */

    Route::get('/pokemon-game', [PokemonGameController::class, 'index'])
        ->name('pokemon-game.index');

    Route::post('/pokemon-game/battle', [PokemonGameController::class, 'battle'])
        ->name('pokemon-game.battle');

    /*
    |--------------------------------------------------------------------------
    | Pokémon PvP Lobby Game
    |--------------------------------------------------------------------------
    */

    Route::get('/pokemon-lobby', [PokemonLobbyController::class, 'index'])
        ->name('pokemon-lobby.index');

    Route::post('/pokemon-lobby', [PokemonLobbyController::class, 'store'])
        ->name('pokemon-lobby.store');

    Route::get('/pokemon-lobby/pokemon-options', [PokemonLobbyController::class, 'pokemonOptions'])
        ->name('pokemon-lobby.pokemon-options');

    Route::get('/pokemon-options', [PokemonLobbyController::class, 'pokemonOptions'])
        ->name('pokemon-options');

    Route::get('/pokemon-lobby/{lobby}', [PokemonLobbyController::class, 'show'])
        ->name('pokemon-lobby.show');

    Route::post('/pokemon-lobby/{lobby}/join', [PokemonLobbyController::class, 'join'])
        ->name('pokemon-lobby.join');

    Route::post('/pokemon-lobby/{lobby}/choose', [PokemonLobbyController::class, 'choose'])
        ->name('pokemon-lobby.choose');

    Route::post('/pokemon-lobby/{lobby}/ready', [PokemonLobbyController::class, 'ready'])
        ->name('pokemon-lobby.ready');

    Route::post('/pokemon-lobby/{lobby}/finalize', [PokemonLobbyController::class, 'finalize'])
        ->name('pokemon-lobby.finalize');

    Route::post('/pokemon-lobby/{lobby}/next-round', [PokemonLobbyController::class, 'nextRound'])
        ->name('pokemon-lobby.next-round');

    Route::match(['post', 'patch'], '/pokemon-lobby/{lobby}/leave', [PokemonLobbyController::class, 'leave'])
        ->name('pokemon-lobby.leave');

    Route::match(['post', 'patch', 'delete'], '/pokemon-lobby/{lobby}/cancel', [PokemonLobbyController::class, 'cancel'])
        ->name('pokemon-lobby.cancel');

    /*
    |--------------------------------------------------------------------------
    | Admin - User Management
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/users', [UserManagementController::class, 'index'])
        ->name('admin.users.index');

    Route::patch('/admin/users/{user}/approve', [UserManagementController::class, 'approve'])
        ->name('admin.users.approve');

    Route::patch('/admin/users/{user}/disapprove', [UserManagementController::class, 'disapprove'])
        ->name('admin.users.disapprove');

    Route::patch('/admin/users/{user}/role', [UserManagementController::class, 'updateRole'])
        ->name('admin.users.role');

    Route::patch('/admin/users/{user}/info', [UserManagementController::class, 'updateInfo'])
        ->name('admin.users.info');

    Route::patch('/admin/users/{user}/password', [UserManagementController::class, 'changePassword'])
        ->name('admin.users.password');

    Route::patch('/admin/users/{user}/give-credit', [UserManagementController::class, 'giveCredit'])
        ->name('admin.users.giveCredit');

    Route::patch('/admin/users/{user}/get-credit', [UserManagementController::class, 'getCredit'])
        ->name('admin.users.getCredit');

    Route::delete('/admin/users/{user}/force-logout', [UserManagementController::class, 'forceLogout'])
        ->name('admin.users.forceLogout');

    /*
    |--------------------------------------------------------------------------
    | Admin - Player Registration Link
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/player-registration-link', function () {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        return view('admin.player-registration-link');
    })->name('admin.player-registration-link');

    /*
    |--------------------------------------------------------------------------
    | Admin - Daily Commission Report
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/commissions', [AdminCommissionController::class, 'index'])
        ->name('admin.commissions.index');

    /*
    |--------------------------------------------------------------------------
    | Admin - Sales Reports
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/sales', [SalesReportController::class, 'index'])
        ->name('admin.sales.index');

    Route::get('/admin/sales/{event}', [SalesReportController::class, 'show'])
        ->name('admin.sales.show');

    Route::get('/admin/sales-reports', [SalesReportController::class, 'index'])
        ->name('admin.sales-reports.index');

    /*
    |--------------------------------------------------------------------------
    | Admin - Withdraw Requests
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/withdrawals', [AdminWithdrawRequestController::class, 'index'])
        ->name('admin.withdrawals.index');

    Route::patch('/admin/withdrawals/{withdrawRequest}/approve', [AdminWithdrawRequestController::class, 'approve'])
        ->name('admin.withdrawals.approve');

    Route::patch('/admin/withdrawals/{withdrawRequest}/reject', [AdminWithdrawRequestController::class, 'reject'])
        ->name('admin.withdrawals.reject');

    /*
    |--------------------------------------------------------------------------
    | Agent - Player Codes
    |--------------------------------------------------------------------------
    */

    Route::get('/agent/player-codes', [PlayerCodeController::class, 'index'])
        ->name('agent.player-codes.index');

    /*
    |--------------------------------------------------------------------------
    | Agent - User Management
    |--------------------------------------------------------------------------
    */

    Route::get('/agent/users', [AgentUserManagementController::class, 'index'])
        ->name('agent.users.index');

    Route::patch('/agent/users/{user}/give-credit', [AgentUserManagementController::class, 'giveCredit'])
        ->name('agent.users.giveCredit');

    Route::patch('/agent/users/{user}/get-credit', [AgentUserManagementController::class, 'getCredit'])
        ->name('agent.users.getCredit');

    /*
    |--------------------------------------------------------------------------
    | Agent - Commission Report
    |--------------------------------------------------------------------------
    | IMPORTANT:
    | This must use CommissionReportController, not CommissionController.
    | This is what makes Pokémon commissions show on the agent side.
    */

    Route::get('/agent/commissions', [CommissionReportController::class, 'index'])
        ->name('agent.commissions.index');

    Route::post('/agent/commissions/convert-to-wallet', [CommissionReportController::class, 'convertToWallet'])
        ->name('agent.commissions.convertToWallet');

    /*
    |--------------------------------------------------------------------------
    | Withdrawals
    |--------------------------------------------------------------------------
    */

    Route::get('/withdrawals', [WithdrawRequestController::class, 'index'])
        ->name('withdrawals.index');

    Route::post('/withdrawals', [WithdrawRequestController::class, 'store'])
        ->name('withdrawals.store');

    /*
    |--------------------------------------------------------------------------
    | Agent - Player Withdraw Requests
    |--------------------------------------------------------------------------
    */

    Route::get('/agent/withdrawals', [AgentWithdrawRequestController::class, 'index'])
        ->name('agent.withdrawals.index');

    Route::patch('/agent/withdrawals/{withdrawRequest}/approve', [AgentWithdrawRequestController::class, 'approve'])
        ->name('agent.withdrawals.approve');

    Route::patch('/agent/withdrawals/{withdrawRequest}/reject', [AgentWithdrawRequestController::class, 'reject'])
        ->name('agent.withdrawals.reject');

    /*
    |--------------------------------------------------------------------------
    | Declare Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/declare/dashboard', [DeclareController::class, 'index'])
        ->name('declare.dashboard');

    Route::post('/declare/winner', [DeclareController::class, 'store'])
        ->name('declare.winner.store');

    /*
    |--------------------------------------------------------------------------
    | Declare - Events
    |--------------------------------------------------------------------------
    */

    Route::get('/declare/events', [DeclareEventController::class, 'index'])
        ->name('declare.events.index');

    Route::post('/declare/events', [DeclareEventController::class, 'store'])
        ->name('declare.events.store');

    Route::get('/declare/events/{event}', [DeclareEventController::class, 'show'])
        ->name('declare.events.show');

    Route::patch('/declare/events/{event}/close', [DeclareEventController::class, 'close'])
        ->name('declare.events.close');

    /*
    |--------------------------------------------------------------------------
    | Declare - Rounds
    |--------------------------------------------------------------------------
    */

    Route::post('/declare/rounds/start', [DeclareRoundController::class, 'start'])
        ->name('declare.rounds.start');

    Route::patch('/declare/rounds/{round}/close', [DeclareRoundController::class, 'close'])
        ->name('declare.rounds.close');
});