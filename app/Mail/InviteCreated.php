<?php
namespace App\Mail;

use App\Models\Invite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $invite;

    /**
     * Create a new message instance.
     *
     * @param  Invite  $invite
     * @return void
     */
    public function __construct(Invite $invite)
    {
        // Store the invite data (for use in the email view)
        $this->invite = $invite;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('You have been invited to register!')
            ->view('emails.invite')  // Specify the email view to use
            ->with([
                'token' => $this->invite->token,  // Pass the invite token to the view
                'expires_at' => $this->invite->expires_at,  // Optional: pass expiration date if needed
            ]);
    }
}
