create controller file if not already exists in Controllers/Api/ folder.
Check existing functionality for similar in web first if available then use similar logic for api too.

    // Settings routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings')->middleware('permission:view_general_settings|view_company_information|view_email_settings');
        Route::put('/settings/general', [SettingController::class, 'updateGeneral'])->name('settings.update.general')->middleware('permission:view_general_settings');
        Route::put('/settings/company', [SettingController::class, 'updateCompany'])->name('settings.update.company')->middleware('permission:view_company_information');
        Route::put('/settings/email', [SettingController::class, 'updateEmail'])->name('settings.update.email')->middleware('permission:view_email_settings');
        Route::put('/settings/renewal', [SettingController::class, 'updateRenewal'])->name('settings.update.renewal')->middleware('permission:view_email_settings');
        Route::put('/settings/teams', [SettingController::class, 'updateTeams'])->name('settings.update.teams')->middleware('permission:view_general_settings');
        Route::put('/settings/departments', [SettingController::class, 'updateDepartments'])->name('settings.update.departments')->middleware('permission:view_general_settings');
        Route::post('/settings/test-email', [SettingController::class, 'sendTestEmail'])->name('settings.test.email')->middleware('permission:view_email_settings');
        Route::get('/settings/search-tags', [SettingController::class, 'searchTags'])->name('settings.search.tags');
    });


always return valid errors formats and proper http codes.
check authentication and authorization properly.

after making all apis create a readme file in docs/ folder where all these apis curl will be written
