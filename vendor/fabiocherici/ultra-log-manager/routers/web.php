<?php

/**
 * @var \Illuminate\Routing\Router $router
 */

use Fabio\UltraSecureUpload\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

 Route::middleware(['auth'])
 ->prefix('ultra-secure-upload')
 ->group(function () {
    
     Route::post('/upload-file', [UploadController::class, 'upload'])->name('ultra_secure_upload.upload');
     Route::post('/save-temp-file', [UploadController::class, 'saveTemporaryFile'])->name('ultra_secure_upload.save_temp_file');
     Route::post('/scan-file', [UploadController::class, 'startVirusScan'])->name('ultra_secure_upload.scan_file');
     Route::get('/get-presigned-url', [UploadController::class, 'getPresignedUrl'])->name('ultra_secure_upload.get_presigned_url');
     Route::post('/set-file-acl', [UploadController::class, 'setFileACL'])->name('ultra_secure_upload.set_file_acl');
     Route::delete('/delete-temp-file', [UploadController::class, 'deleteTemporaryFileLocal'])->name('ultra_secure_upload.delete_temp_file');
     Route::post('/notify-upload-complete', [UploadController::class, 'notifyUploadComplete'])->name('ultra_secure_upload.notify_upload_complete');
 });
 