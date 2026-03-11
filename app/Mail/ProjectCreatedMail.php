<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Project $project;
    public string $recipientType;

    public function __construct(Project $project, string $recipientType = 'member')
    {
        $this->project = $project;
        $this->recipientType = $recipientType;
    }

    public function envelope(): Envelope
    {
        $subject = $this->recipientType === 'admin'
            ? 'New Project Created - ' . $this->project->project_name
            : 'You Have Been Assigned to Project - ' . $this->project->project_name;

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.project-created',
            with: [
                'project' => $this->project,
                'recipientType' => $this->recipientType,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
