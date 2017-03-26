<?php

namespace OverwatchLounge\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class MatchNotification extends Notification
{
    private $match;

    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($match)
    {
        $this->match = $match;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['slack'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toSlack($notifiable)
    {
        $match = $this->match;

        return (new SlackMessage)
            ->success()
            ->from(substr($match->user->tag, 0, strpos($match->user->tag, "#")))
            ->image($match->user->{$match->region . '_profile'}->avatar_url)
            ->attachment(function ($attachment) use ($match) {
                $howMuch = $match->howMuch;
                $languages = '';

                for ($i = 0; $i < $match->howMuch; $i++) {
                    $howMuch .= ' :person_frowning:';
                }

                $games = [
                    'qp' => 'Quick Play',
                    'comp' => 'Competitive',
                    'custom' => 'Custom games',
                    'brawl' => 'Brawl',
                ];

                foreach ($match->languages as $language) {
                    $languages .= ' :flag_' . $language . ':';
                }

                $fields = [
                    'Region' => ':flag_' . $match->region . ': ' . strtoupper($match->region),
                    'Type' => $games[$this->match->type],
                    'Languages' => $languages,
                    'How Many' => $howMuch,
                    'With voice' => ($match->invitationLink) ? ':thumbsup:' : ':thumbsdown:',
                ];

                if ($match->type == 'comp') {
                    $fields['Min Rank'] = $match->minRank;
                    $fields['Max Rank'] = $match->maxRank;
                } else {
                    $fields['Description'] = $match->description;
                }

                $fields['Invitation'] = $match->invitationLink;

                $attachment->title('Click for more details', route('getMatch', $match->id))->fields($fields);
            });
    }

    private function getRankIcon($rank)
    {
        if ($rank < 1499) {
            $imageId = 'bronze';
        } elseif ($rank < 1999) {
            $imageId = 'silver';
        } elseif ($rank < 2499) {
            $imageId = 'gold';
        } elseif ($rank < 2999) {
            $imageId = 'platinum';
        } elseif ($rank < 3499) {
            $imageId = 'diamond';
        } elseif ($rank < 3999) {
            $imageId = 'master';
        } elseif ($rank <= 5000) {
            $imageId = 'grandmaster';
        }

        return ':' . $imageId . ':';
    }
}
