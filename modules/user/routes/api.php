<?php

use Illuminate\Support\Facades\Route;
use App\User\Http\Controllers\Api\{UserController, AuthController, UserLegalController, UserAddresscontroller, PasswordController};

Route::prefix('auth')->group(function() {

	Route::controller(AuthController::class)->group(function() {
		Route::post('logout', 'logout')->middleware('auth:api');
		Route::post('check-username', 'checkUsername');
		Route::post('check-otp-code', 'checkOtpCode');
		Route::post('check-password', 'checkPassword');
		Route::post('send-otp-code', 'sendOtpCode');
		Route::post('registration-confirmation', 'registrationConfirmation')->middleware('auth:api');
	});


	Route::middleware('auth:api')->group(function() {

		Route::controller(UserController::class)->group(function() {
			Route::post('user', 'getUser');
			Route::post('update-profile-attribute', 'updateProfileAttribute');
			Route::post('confirmation-attribute', 'confirmationAttribute');
			Route::post('resend-confirmation-code', 'resendConfirmationCode');
		});

		Route::prefix('user')->group(function() {

			Route::post('/change-password', [PasswordController::class, 'changePassword']);
			Route::post('/legal-information', [UserLegalController::class, 'saveLegalInformation']);

			Route::prefix('addresses')->group(function() {
				Route::post('/new', [UserAddresscontroller::class, 'newAddress']);
				Route::post('/update', [UserAddresscontroller::class, 'updateAddress']);
				Route::post('/delete', [UserAddresscontroller::class, 'deleteAddress']);
				Route::post('/list', [UserAddresscontroller::class, 'AddressesList']);
			});

			Route::post('/upload-profile-picture', [UserController::class, 'uploadProfilePicture']);
		});


	});


});