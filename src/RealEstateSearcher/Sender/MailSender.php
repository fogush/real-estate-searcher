<?php

namespace App\RealEstateSearcher\Sender;

use App\Entity\Collection\RealEstateCollection;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;

class MailSender implements SenderInterface
{
    private $mailer;
    private $twig;
    private $emailRecipients;

    public function __construct(\Swift_Mailer $mailer, Environment $twig, ParameterBagInterface $parameterBag)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->emailRecipients = $parameterBag->get('app.email.recipients');
    }

    public function send(RealEstateCollection $realEstateCollection): bool
    {
        if (empty($this->emailRecipients)) {
            return false;
        }

        $message = (new \Swift_Message('Найдены новые объекты недвижимости'))
            ->setFrom('realt.crawler.sender@gmail.com', 'Real Estate Notifier')
            ->setTo($this->emailRecipients)
            ->setBody(
                $this->generateEmail($realEstateCollection),
                'text/html'
            )
        ;

        return (bool) $this->mailer->send($message);
    }

    private function generateEmail(RealEstateCollection $realEstateCollection): string
    {
        return $this->twig->render(
            'email/new_real_estates.html.twig',
            ['realEstates' => $realEstateCollection]
        );
    }
}
