<?php
/**
 * Created by PhpStorm.
 * User: imanu
 * Date: 26.01.2018
 * Time: 19:02
 */

namespace Jinya\Services\Mailing;

use Jinya\Entity\Form\Form;
use Jinya\Framework\Events\Mailing\MailerEvent;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MailerService implements MailerServiceInterface
{
    /** @var Swift_Mailer */
    private $swift;

    /** @var string */
    private $mailerSender;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * MailerService constructor.
     * @param Swift_Mailer $swift
     * @param string $mailerSender
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Swift_Mailer $swift, string $mailerSender, EventDispatcherInterface $eventDispatcher)
    {
        $this->swift = $swift;
        $this->mailerSender = $mailerSender;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function sendMail(Form $form, array $data): void
    {
        $pre = $this->eventDispatcher->dispatch(MailerEvent::PRE_SEND_MAIL, new MailerEvent($form, $data));
        if (!$pre->isCancel()) {
            /** @var Swift_Message $message */
            $message = $this->swift->createMessage('message');
            $message->addTo($form->getToAddress());
            $message->setSubject('Form ' . $form->getTitle() . ' submitted');
            $message->setBody($this->formatBody($data), 'text/html');
            $message->setFrom($this->mailerSender);
            $this->swift->send($message);
            $this->eventDispatcher->dispatch(MailerEvent::POST_SEND_MAIL, new MailerEvent($form, $data));
        }
    }

    private function formatBody(array $data): string
    {
        $body = '<html><head></head><body><table>';

        foreach ($data as $key => $item) {
            $body .= "<tr>
                        <td>$key</td>
                        <td>$item</td>
                      </tr>";
        }

        return $body . '</table></body></html>';
    }
}
