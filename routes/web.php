<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChangeLogController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ForcePasswordUpdateController;
use App\Http\Controllers\GolonganController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [RegistrationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/password/force-update', [ForcePasswordUpdateController::class, 'show'])->name('password.force.show');
    Route::post('/password/force-update', [ForcePasswordUpdateController::class, 'update'])->name('password.force.update');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/menu/area', [AreaController::class, 'index'])->name('menu.area');
    Route::get('/menu/area/create', [AreaController::class, 'create'])->name('menu.area.create');
    Route::post('/menu/area', [AreaController::class, 'store'])->name('menu.area.store');
    Route::get('/menu/area/{area}', [AreaController::class, 'show'])->name('menu.area.show');
    Route::get('/menu/area/{area}/edit', [AreaController::class, 'edit'])->name('menu.area.edit');
    Route::put('/menu/area/{area}', [AreaController::class, 'update'])->name('menu.area.update');
    Route::delete('/menu/area/{area}', [AreaController::class, 'destroy'])->name('menu.area.destroy');
    Route::post('/menu/area/{area}/petugas', [AreaController::class, 'assignPetugas'])->name('menu.area.petugas.assign');
    Route::delete('/menu/area/{area}/petugas/{petugas}', [AreaController::class, 'removePetugas'])->name('menu.area.petugas.remove');
    Route::get('/menu/golongan', [GolonganController::class, 'index'])->name('menu.golongan.index');
    Route::get('/menu/golongan/create', [GolonganController::class, 'create'])->name('menu.golongan.create');
    Route::post('/menu/golongan', [GolonganController::class, 'store'])->name('menu.golongan.store');
    Route::get('/menu/golongan/{golongan}', [GolonganController::class, 'show'])->name('menu.golongan.show');
    Route::get('/menu/golongan/{golongan}/edit', [GolonganController::class, 'edit'])->name('menu.golongan.edit');
    Route::put('/menu/golongan/{golongan}', [GolonganController::class, 'update'])->name('menu.golongan.update');
    Route::delete('/menu/golongan/{golongan}', [GolonganController::class, 'destroy'])->name('menu.golongan.destroy');
    Route::post('/menu/golongan/{golongan}/tariffs', [GolonganController::class, 'storeTariff'])->name('menu.golongan.tariffs.store');
    Route::delete('/menu/golongan/{golongan}/tariffs/{tariff}', [GolonganController::class, 'destroyTariff'])->name('menu.golongan.tariffs.destroy');
    Route::post('/menu/golongan/{golongan}/fees', [GolonganController::class, 'storeNonAirFee'])->name('menu.golongan.fees.store');
    Route::delete('/menu/golongan/{golongan}/fees/{fee}', [GolonganController::class, 'destroyNonAirFee'])->name('menu.golongan.fees.destroy');
    Route::get('/menu/data-pelanggan', [CustomerController::class, 'index'])->name('menu.customers.index');
    Route::get('/menu/data-pelanggan/create', [CustomerController::class, 'create'])->name('menu.customers.create');
    Route::post('/menu/data-pelanggan', [CustomerController::class, 'store'])->name('menu.customers.store');
    Route::get('/menu/data-pelanggan/{customer}', [CustomerController::class, 'show'])->name('menu.customers.show');
    Route::get('/menu/data-pelanggan/{customer}/edit', [CustomerController::class, 'edit'])->name('menu.customers.edit');
    Route::put('/menu/data-pelanggan/{customer}', [CustomerController::class, 'update'])->name('menu.customers.update');
    Route::post('/menu/data-pelanggan/{customer}/account', [CustomerController::class, 'createAccount'])->name('menu.customers.account.create');
    Route::delete('/menu/data-pelanggan/{customer}', [CustomerController::class, 'destroy'])->name('menu.customers.destroy');
    Route::get('/menu/user', [UserManagementController::class, 'index'])->name('menu.user');
    Route::get('/menu/user/create', [UserManagementController::class, 'create'])->name('menu.user.create');
    Route::get('/menu/user/{managedUser}', [UserManagementController::class, 'show'])->name('menu.user.show');
    Route::post('/menu/user', [UserManagementController::class, 'store'])->name('menu.user.store');
    Route::put('/menu/user/{managedUser}', [UserManagementController::class, 'update'])->name('menu.user.update');
    Route::patch('/menu/user/{managedUser}/password', [UserManagementController::class, 'updatePassword'])->name('menu.user.password');
    Route::patch('/menu/user/{managedUser}/approve', [UserManagementController::class, 'approve'])->name('menu.user.approve');
    Route::delete('/menu/user/{managedUser}', [UserManagementController::class, 'destroy'])->name('menu.user.destroy');
    Route::patch('/menu/user/{managedUser}/role', [UserManagementController::class, 'updateRole'])->name('menu.user.role');
    Route::get('/menu/logs-perubahan', [ChangeLogController::class, 'index'])->name('menu.logs');
    Route::post('/menu/logs-perubahan/{changeLog}/undo', [ChangeLogController::class, 'undo'])->name('menu.logs.undo');
    Route::get('catat-meter', [\App\Http\Controllers\MeterPeriodController::class, 'index'])->name('catat-meter.index');
    Route::post('catat-meter', [\App\Http\Controllers\MeterPeriodController::class, 'store'])->name('catat-meter.store');
    Route::delete('catat-meter/{meterPeriod}', [\App\Http\Controllers\MeterPeriodController::class, 'destroy'])->name('catat-meter.destroy');
    Route::get('catat-meter/{meterPeriod}', [\App\Http\Controllers\MeterPeriodController::class, 'show'])->name('catat-meter.show');
    Route::get('catat-meter/{meterPeriod}/pending', [\App\Http\Controllers\MeterPeriodController::class, 'pending'])->name('catat-meter.pending');
    Route::get('catat-meter/{meterPeriod}/input/{meterReading}', [\App\Http\Controllers\MeterPeriodController::class, 'inputReading'])->name('catat-meter.input');
    Route::get('catat-meter/{meterPeriod}/export/pdf', [\App\Http\Controllers\MeterPeriodController::class, 'exportPdf'])->name('catat-meter.export.pdf');
    Route::get('catat-meter/{meterPeriod}/export/excel', [\App\Http\Controllers\MeterPeriodController::class, 'exportExcel'])->name('catat-meter.export.excel');
    Route::patch('catat-meter/{meterPeriod}/readings/{meterReading}', [\App\Http\Controllers\MeterReadingController::class, 'update'])->name('catat-meter.readings.update');
    
    // Data Meter (Admin menu to manage all meter data)
    Route::get('menu/data-meter', [\App\Http\Controllers\DataMeterController::class, 'index'])->name('menu.data-meter');
    Route::get('menu/data-meter/{meterPeriod}', [\App\Http\Controllers\DataMeterController::class, 'show'])->name('menu.data-meter.show');
    
    // Billing routes
    Route::post('billing/{meterReading}/publish', [\App\Http\Controllers\BillingController::class, 'publish'])->name('billing.publish');
    Route::post('billing/{meterReading}/unpublish', [\App\Http\Controllers\BillingController::class, 'unpublish'])->name('billing.unpublish');
    Route::get('billing/customer/{customer}', [\App\Http\Controllers\BillingController::class, 'customerBills'])->name('billing.customer');
    Route::get('billing/customer/{customer}/bills', [\App\Http\Controllers\BillingController::class, 'getCustomerBillsJson'])->name('billing.customer.bills');
    Route::post('billing/{bill}/pay', [\App\Http\Controllers\BillingController::class, 'pay'])->name('billing.pay');
    
    Route::get('/menu/{slug}', [MenuController::class, 'show'])->name('menu.show');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
