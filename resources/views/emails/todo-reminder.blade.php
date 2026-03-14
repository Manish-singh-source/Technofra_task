<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo Reminder</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7fb; color: #213047; margin: 0; padding: 24px; }
        .wrap { max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(25, 44, 79, 0.08); }
        .head { background: linear-gradient(135deg, #0f6cbd, #1f8f5f); color: #fff; padding: 28px; }
        .body { padding: 28px; }
        .card { background: #f7fafc; border: 1px solid #dce6f2; border-radius: 12px; padding: 18px; margin: 18px 0; }
        .label { font-size: 12px; font-weight: 700; letter-spacing: 0.06em; color: #6a7b92; text-transform: uppercase; margin-bottom: 6px; }
        .value { font-size: 16px; color: #213047; }
        .btn { display: inline-block; background: #0f6cbd; color: #fff !important; text-decoration: none; padding: 12px 20px; border-radius: 10px; font-weight: 700; }
        .foot { padding: 0 28px 28px; color: #6a7b92; font-size: 13px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="head">
            <h2 style="margin:0 0 8px 0;">Todo Reminder</h2>
            <p style="margin:0;">Your scheduled todo is now due.</p>
        </div>
        <div class="body">
            <div class="card">
                <div class="label">Title</div>
                <div class="value">{{ $todo->title }}</div>
            </div>

            @if($todo->description)
                <div class="card">
                    <div class="label">Description</div>
                    <div class="value">{{ $todo->description }}</div>
                </div>
            @endif

            <div class="card">
                <div class="label">Occurrence Date</div>
                <div class="value">{{ $occurrenceDate->format('d M Y') }}</div>
            </div>

            <div class="card">
                <div class="label">Schedule</div>
                <div class="value">{{ $todo->display_schedule }}</div>
            </div>

            <a href="{{ route('to-do-list') }}" class="btn">Open Todo List</a>
        </div>
        <div class="foot">
        This email was sent automatically so you can view your todo on time.
        </div>
    </div>
</body>
</html>
