create controller file if not already exists in Controllers/Api/ folder.
Check existing functionality for similar in web first if available then use similar logic for api too.
    // Service CRUD routes refer this
    Route::resource('services', ServiceController::class)->middleware('permission:view_services');
    Route::post('/services/delete-selected', [ServiceController::class, 'deleteSelected'])->name('delete.selected.service')->middleware('permission:delete_services');

always return valid errors formats and proper http codes.
check authentication and authorization properly.

after making all apis create a readme file in docs/ folder where all these apis curl will be written
