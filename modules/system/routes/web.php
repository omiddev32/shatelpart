<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect('/admin/login'));

// Route::get('/StorageLink', function () {
// 	$targetFolder = $_SERVER['DOCUMENT_ROOT'].'/app/storage/app/public';
// 	$linkFolder = $_SERVER['DOCUMENT_ROOT'].'/storage';
// 	symlink($targetFolder,$linkFolder);
// 	echo 'Symlink process successfully completed';
// });