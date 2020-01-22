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

    public function sendNew(RealEstateCollection $realEstateCollection): bool
    {
        if (empty($this->emailRecipients)) {
            return false;
        }

        $message = (new \Swift_Message('Найдены новые объекты недвижимости'))
            ->setFrom('realt.crawler.sender@gmail.com', 'Real Estate Searcher')
            ->setTo($this->emailRecipients)
            ->setBody(
                $this->generateEmailNew($realEstateCollection),
                'text/html'
            )
        ;

        return (bool) $this->mailer->send($message);
    }

    private function generateEmailNew(RealEstateCollection $realEstateCollection): string
    {
        return $this->twig->render(
            'email/new_real_estates.html.twig',
            ['realEstates' => $realEstateCollection]
        );
    }

    public function sendDeleted(RealEstateCollection $realEstateCollection): bool
    {
        if (empty($this->emailRecipients)) {
            return false;
        }

        $message = (new \Swift_Message('Объекты недвижимости пропали из поиска'))
            ->setFrom('realt.crawler.sender@gmail.com', 'Real Estate Searcher')
            ->setTo($this->emailRecipients)
            ->setBody(
                $this->generateEmailDeleted($realEstateCollection),
                'text/html'
            )
        ;

        return (bool) $this->mailer->send($message);
    }

    private function generateEmailDeleted(RealEstateCollection $realEstateCollection): string
    {
        return $this->twig->render(
            'email/deleted_real_estates.html.twig',
            ['realEstates' => $realEstateCollection]
        );
    }
}
