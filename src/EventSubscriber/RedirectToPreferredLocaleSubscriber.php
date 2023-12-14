<?php


namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function Symfony\Component\String\u;

class RedirectToPreferredLocaleSubscriber implements EventSubscriberInterface
{
    private array $locales;
    private string $defaultLocale;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        string $locales,
        ?string $defaultLocale = null
    ) {
        // Le constructeur prend l'URLGenerator, la liste des langues supportées et la langue par défaut en tant que dépendances.
        $this->locales = explode('|', trim($locales));
        if (empty($this->locales)) {
            throw new \UnexpectedValueException('The list of supported locales must not be empty.');
        }

        $this->defaultLocale = $defaultLocale ?: $this->locales[0];

        if (!\in_array($this->defaultLocale, $this->locales, true)) {
            throw new \UnexpectedValueException(sprintf('The default locale ("%s") must be one of "%s".', $this->defaultLocale, $locales));
        }

        array_unshift($this->locales, $this->defaultLocale);
        $this->locales = array_unique($this->locales);
        // Initialise la liste des langues supportées et la langue par défaut, en ajoutant la langue par défaut au début de la liste.
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
        // Cette méthode statique déclare que cette classe est un abonné à l'événement KernelEvents::REQUEST.
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest() || '/' !== $request->getPathInfo()) {
            return;
        }
        // Vérifie si la demande est la demande principale et si le chemin n'est pas différent de '/'.

        $referrer = $request->headers->get('referer');
        if (null !== $referrer && u($referrer)->ignoreCase()->startsWith($request->getSchemeAndHttpHost())) {
            return;
        }
        // Vérifie si la demande provient d'un lien interne en comparant le référent avec l'URL actuelle.

        $preferredLanguage = $request->getPreferredLanguage($this->locales);

        if ($preferredLanguage !== $this->defaultLocale) {
            $response = new RedirectResponse($this->urlGenerator->generate('homepage', ['_locale' => $preferredLanguage]));
            $event->setResponse($response);
        }
        // Redirige vers la langue préférée si elle n'est pas la langue par défaut.
    }
}
