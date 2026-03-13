document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    if (!calendarEl || typeof FullCalendar === 'undefined') {
        return;
    }

    var routes = window.calendarRoutes || {};
    var addChecklistItems = [];
    var editChecklistItems = [];

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        themeSystem: 'bootstrap5',
        editable: false,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        events: routes.events,
        dateClick: function(info) {
            $('#event_date').val(info.dateStr);
            $('#addEventModal').modal('show');
        },
        eventClick: function(info) {
            loadEventDetails(info.event.id);
        },
        eventDidMount: function(info) {
            var meta = [];
            if (info.event.extendedProps.appointment_type) {
                meta.push(info.event.extendedProps.appointment_type.replace(/_/g, ' '));
            }
            if (info.event.extendedProps.priority) {
                meta.push('Priority: ' + info.event.extendedProps.priority);
            }
            if (info.event.extendedProps.reminder_label) {
                meta.push('Reminder: ' + info.event.extendedProps.reminder_label);
            }

            $(info.el).tooltip({
                title: [info.event.title].concat(meta).join(' | '),
                placement: 'top',
                trigger: 'hover',
                container: 'body'
            });
        }
    });

    calendar.render();

    $('#event_attachment').on('change', function() {
        readImagePreview(this, '#add_attachment_preview_wrap', '#add_attachment_preview');
    });

    $('#edit_event_attachment').on('change', function() {
        readImagePreview(this, '#edit_attachment_preview_wrap', '#edit_attachment_preview');
    });

    $('#addChecklistItemBtn').on('click', function() {
        addChecklistItem('add');
    });

    $('#editAddChecklistItemBtn').on('click', function() {
        addChecklistItem('edit');
    });

    $('#event_checklist_input, #edit_event_checklist_input').on('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addChecklistItem(this.id.indexOf('edit') === 0 ? 'edit' : 'add');
        }
    });

    $('#event_checklist_preview').on('click', '[data-remove-index]', function() {
        addChecklistItems.splice(Number($(this).data('remove-index')), 1);
        syncChecklistState('add');
    });

    $('#edit_event_checklist_preview').on('click', '[data-remove-index]', function() {
        editChecklistItems.splice(Number($(this).data('remove-index')), 1);
        syncChecklistState('edit');
    });

    $('#saveEventBtn').click(function() {
        clearValidation('#addEventForm');

        if (!hasAnyRecipient($('#event_email_recipients').val(), $('#event_whatsapp_recipients').val())) {
            showAlert('error', 'Please add at least one email or WhatsApp recipient.');
            return;
        }

        if (hasCalendarConflict($("#event_date").val(), $("#event_time").val())) {
            showAlert('error', 'Another meeting is already scheduled within 30 minutes of this slot. Please select another time.');
            return;
        }


        syncChecklistState('add');

        var formData = new FormData(document.getElementById('addEventForm'));
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        $('#saveEventBtn').prop('disabled', true).text('Saving...');

        $.ajax({
            url: routes.store,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#addEventModal').modal('hide');
                    resetAddEventForm();
                    calendar.refetchEvents();
                    showAlert('success', response.message || 'Event created successfully!');
                } else {
                    showAlert('error', response.message || 'Failed to create event');
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr, '#addEventForm');
            },
            complete: function() {
                $('#saveEventBtn').prop('disabled', false).text('Save Appointment');
            }
        });
    });

    $('#updateEventBtn').click(function() {
        clearValidation('#editEventForm');

        if (!hasAnyRecipient($('#edit_event_email_recipients').val(), $('#edit_event_whatsapp_recipients').val())) {
            showAlert('error', 'Please add at least one email or WhatsApp recipient.');
            return;
        }

        var eventId = $('#edit_event_id').val();


        if (hasCalendarConflict($("#edit_event_date").val(), $("#edit_event_time").val(), eventId)) {
            showAlert('error', 'Another meeting is already scheduled within 30 minutes of this slot. Please select another time.');
            return;
        }
        syncChecklistState('edit');

        var formData = new FormData(document.getElementById('editEventForm'));
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
        formData.append('_method', 'PUT');

        $('#updateEventBtn').prop('disabled', true).text('Updating...');

        $.ajax({
            url: routes.base + '/' + eventId,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#editEventModal').modal('hide');
                    calendar.refetchEvents();
                    showAlert('success', response.message || 'Event updated successfully!');
                } else {
                    showAlert('error', response.message || 'Failed to update event');
                }
            },
            error: function(xhr) {
                handleAjaxError(xhr, '#editEventForm');
            },
            complete: function() {
                $('#updateEventBtn').prop('disabled', false).text('Update Event');
            }
        });
    });

    $('#deleteEventBtn').click(function() {
        if (!confirm('Are you sure you want to delete this event?')) {
            return;
        }

        var eventId = $('#edit_event_id').val();
        $('#deleteEventBtn').prop('disabled', true).text('Deleting...');

        $.ajax({
            url: routes.base + '/' + eventId,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                _method: 'DELETE'
            },
            success: function(response) {
                if (response.success) {
                    $('#editEventModal').modal('hide');
                    calendar.refetchEvents();
                    showAlert('success', response.message || 'Event deleted successfully!');
                }
            },
            error: function(xhr) {
                showAlert('error', xhr.responseJSON?.message || 'Error deleting event');
            },
            complete: function() {
                $('#deleteEventBtn').prop('disabled', false).text('Delete');
            }
        });
    });

    $('#addEventModal').on('hidden.bs.modal', function() {
        resetAddEventForm();
    });

    $('#editEventModal').on('hidden.bs.modal', function() {
        clearValidation('#editEventForm');
    });

    function addChecklistItem(mode) {
        var isEdit = mode === 'edit';
        var input = $(isEdit ? '#edit_event_checklist_input' : '#event_checklist_input');
        var color = $(isEdit ? '#edit_event_checklist_color' : '#event_checklist_color');
        var items = isEdit ? editChecklistItems : addChecklistItems;
        var label = (input.val() || '').trim();

        if (!label) {
            return;
        }

        items.push({
            label: label.substring(0, 30),
            color: color.val() || '#0D6EFD'
        });

        input.val('');
        syncChecklistState(mode);
    }

    function syncChecklistState(mode) {
        var isEdit = mode === 'edit';
        var items = isEdit ? editChecklistItems : addChecklistItems;
        var hidden = $(isEdit ? '#edit_event_checklist_items' : '#event_checklist_items');
        var preview = $(isEdit ? '#edit_event_checklist_preview' : '#event_checklist_preview');

        hidden.val(JSON.stringify(items));
        preview.html(items.map(function(item, index) {
            return '<span class="checklist-chip" style="background:' + escapeHtml(item.color) + '">' +
                '<span>' + escapeHtml(item.label) + '</span>' +
                '<button type="button" data-remove-index="' + index + '">&times;</button>' +
            '</span>';
        }).join(''));
    }

    function loadEventDetails(eventId) {
        $.ajax({
            url: routes.base + '/' + eventId,
            method: 'GET',
            success: function(response) {
                if (!response.success) {
                    return;
                }

                var event = response.event;
                $('#edit_event_id').val(event.id);
                $('#edit_event_title').val(event.title);
                $('#edit_event_appointment_type').val(event.appointment_type || 'meeting');
                $('#edit_event_priority').val(event.priority || 'medium');
                $('#edit_event_description').val(event.description || '');
                $('#edit_event_date').val(event.event_date);
                $('#edit_event_time').val(event.event_time);
                $('#edit_event_reminder_minutes').val(String(event.reminder_minutes || 10));
                $('#edit_event_location').val(event.location || '');
                $('#edit_event_meeting_link').val(event.meeting_link || '');
                $('#edit_event_reminder_note').val(event.reminder_note || '');
                $('#edit_event_email_recipients').val(event.email_recipients || '');
                $('#edit_event_whatsapp_recipients').val(event.whatsapp_recipients || '');
                $('#reminder_status').html(event.reminder_10min_sent ? '<span class="badge bg-success">Sent</span>' : '<span class="badge bg-warning text-dark">Pending</span>');
                $('#event_time_status').html(event.event_time_notification_sent ? '<span class="badge bg-success">Sent</span>' : '<span class="badge bg-warning text-dark">Pending</span>');
                $('#event_reminder_summary').text(event.reminder_label || '10 minutes before');
                $('#created_by').text(event.created_by);

                editChecklistItems = Array.isArray(event.checklist_items) ? event.checklist_items : [];
                syncChecklistState('edit');

                if (event.attachment_url) {
                    $('#current_attachment_link').attr('href', event.attachment_url);
                    $('#current_attachment_link_wrap').removeClass('d-none');
                    $('#edit_attachment_preview').attr('src', event.attachment_url);
                    $('#edit_attachment_preview_wrap').removeClass('d-none');
                } else {
                    $('#current_attachment_link').attr('href', '#');
                    $('#current_attachment_link_wrap').addClass('d-none');
                    $('#edit_attachment_preview').attr('src', '');
                    $('#edit_attachment_preview_wrap').addClass('d-none');
                }

                $('#edit_event_attachment').val('');
                $('#editEventModal').modal('show');
            },
            error: function() {
                showAlert('error', 'Error loading event details');
            }
        });
    }

    function showAlert(type, message) {
        $('.page-content > .alert').remove();

        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var iconClass = type === 'success' ? 'bx bx-check-circle' : 'bx bx-error-circle';

        var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert" style="position: relative; z-index: 9999;">' +
            '<i class="' + iconClass + ' me-2"></i>' +
            '<strong>' + message + '</strong>' +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
            '</div>';

        $('.page-content').prepend(alertHtml);
        $('html, body').animate({ scrollTop: 0 }, 300);

        setTimeout(function() {
            $('.page-content > .alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }

    function clearValidation(formSelector) {
        $(formSelector).find('.is-invalid').removeClass('is-invalid');
        $(formSelector).find('.invalid-feedback').text('');
    }

    function handleAjaxError(xhr, formSelector) {
        if (xhr.status === 422) {
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                applyValidationErrors(formSelector, xhr.responseJSON.errors);
                showAlert('error', 'Please fix the validation errors');
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                showAlert('error', xhr.responseJSON.message);
            } else {
                showAlert('error', 'Validation error');
            }
            return;
        }

        showAlert('error', xhr.responseJSON?.message || 'Request failed');
    }

    function applyValidationErrors(formSelector, errors) {
        $.each(errors, function(key, value) {
            var inputField = $(formSelector + ' [name="' + key + '"]');
            inputField.addClass('is-invalid');
            inputField.siblings('.invalid-feedback').text(value[0]);
        });
    }

    function hasAnyRecipient(emailRecipients, whatsappRecipients) {
        return (emailRecipients && emailRecipients.trim().length > 0) ||
            (whatsappRecipients && whatsappRecipients.trim().length > 0);
    }

    function hasCalendarConflict(dateValue, timeValue, excludeEventId) {
        if (!dateValue || !timeValue) {
            return false;
        }

        var selectedDateTime = new Date(dateValue + 'T' + timeValue + ':00');
        if (isNaN(selectedDateTime.getTime())) {
            return false;
        }

        var bufferMs = 30 * 60 * 1000;
        var events = calendar.getEvents();

        return events.some(function(event) {
            if (!event.start) {
                return false;
            }

            if (excludeEventId !== undefined && excludeEventId !== null && String(event.id) === String(excludeEventId)) {
                return false;
            }

            var diff = Math.abs(event.start.getTime() - selectedDateTime.getTime());
            return diff < bufferMs;
        });
    }

    function readImagePreview(input, wrapSelector, imageSelector) {
        if (!input.files || !input.files[0]) {
            $(imageSelector).attr('src', '');
            $(wrapSelector).addClass('d-none');
            return;
        }

        var reader = new FileReader();
        reader.onload = function(e) {
            $(imageSelector).attr('src', e.target.result);
            $(wrapSelector).removeClass('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }

    function resetAddEventForm() {
        $('#addEventForm')[0].reset();
        $('#event_appointment_type').val('meeting');
        $('#event_priority').val('medium');
        $('#event_reminder_minutes').val('10');
        $('#event_checklist_color').val('#dc3545');
        addChecklistItems = [];
        syncChecklistState('add');
        $('#add_attachment_preview').attr('src', '');
        $('#add_attachment_preview_wrap').addClass('d-none');
        clearValidation('#addEventForm');
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
});

