<?php

namespace Coyote\Notifications\Job;

use Coyote\Job;
use Coyote\Services\Notification\DatabaseChannel;
use Coyote\Services\Notification\NotificationInterface;
use Coyote\Services\UrlBuilder\UrlBuilder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;

class ApplicationSentNotification extends Notification implements ShouldQueue, NotificationInterface
{
    use Queueable;

    const ID = \Coyote\Notification::JOB_APPLICATION;

    /**
     * @var Job\Application
     */
    private $application;

    /**
     * @param Job\Application $application
     */
    public function __construct(Job\Application $application)
    {
        $this->application = $application;
    }

    /**
     * @param Job $job
     * @return array
     */
    public function via(Job $job)
    {
        $channels = ['mail', DatabaseChannel::class];

        if (!empty($job->phone)) {
            $channels[] = TwilioChannel::class;
        }

        return $channels;
    }

    /**
     * @param Job $job
     * @return TwilioSmsMessage
     */
    public function toTwilio(Job $job)
    {
        return (new TwilioSmsMessage())
            ->content(
                sprintf(
                    '%s wysłał swoją aplikację w odpowiedzi na ogłoszenie "%s". Pozdrawiamy, %s',
                    $this->application->name,
                    $job->title,
                    config('app.name')
                )
            );
    }

    /**
     * @param Job $job
     * @return MailMessage
     */
    public function toMail(Job $job)
    {
        $message = (new MailMessage())
            ->subject(sprintf('[%s] %s', $this->application->name, $job->title))
            ->replyTo($this->application->email, $this->application->name)
            ->view('emails.job.application', [
                'application' => $this->application->toArray(),
                'job' => $job
            ]);

        if ($this->application->cv) {
            $path = realpath(storage_path('app/cv/' . $this->application->cv));

            $message->attach($path, ['as' => $this->application->realFilename()]);
        }

        return $message;
    }


    /**
     * @param Job $job
     * @return array
     */
    public function toDatabase($job)
    {
        return [
            'object_id'     => $this->objectId(),
            'user_id'       => $job->user_id,
            'type_id'       => static::ID,
            'subject'       => $this->application->job->title,
            'excerpt'       => null,
            'url'           => UrlBuilder::job($this->application->job),
            'guid'          => $this->id
        ];
    }

    /**
     * @return string
     */
    public function objectId()
    {
        return substr(md5(uniqid()), 16); // uniq ID for each notification. notification won't be grouped
    }

    /**
     * @return array
     */
    public function sender()
    {
        return [
            'name' => $this->application->name,
            'user_id' => null
        ];
    }

    /**
     * @return string
     */
    protected function notificationUrl()
    {
        return route('user.notifications.url', [$this->id]);
    }
}
