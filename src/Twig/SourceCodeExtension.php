<?php


namespace App\Twig;

use function Symfony\Component\String\u;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TemplateWrapper;
use Twig\TwigFunction;


class SourceCodeExtension extends AbstractExtension
{
    private $controller;

    public function setController(?callable $controller): void
    {
        $this->controller = $controller;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('show_source_code', [$this, 'showSourceCode'], ['is_safe' => ['html'], 'needs_environment' => true]),
        ];
    }

    /**
     * @param string|TemplateWrapper|array $template
     */
    public function showSourceCode(Environment $twig, $template): string
    {
        return $twig->render('debug/source_code.html.twig', [
            'controller' => $this->getController(),
            'template' => $this->getTemplateSource($twig->resolveTemplate($template)),
        ]);
    }

    private function getController(): ?array
    {
        // cela arrive par exemple pour les exceptions (erreurs 404, etc.)
        if (null === $this->controller) {
            return null;
        }

        $method = $this->getCallableReflector($this->controller);

        if (false === $classCode = file($method->getFileName())) {
            throw new \LogicException(sprintf('There was an error while trying to read the contents of the "%s" file.', $method->getFileName()));
        }

        $startLine = $method->getStartLine() - 1;
        $endLine = $method->getEndLine();

        while ($startLine > 0) {
            $line = trim($classCode[$startLine - 1]);

            if (\in_array($line, ['{', '}', ''], true)) {
                break;
            }

            --$startLine;
        }

        $controllerCode = implode('', \array_slice($classCode, $startLine, $endLine - $startLine));

        return [
            'file_path' => $method->getFileName(),
            'starting_line' => $method->getStartLine(),
            'source_code' => $this->unindentCode($controllerCode),
        ];
    }

    /**
     * Obtient un réflecteur pour un appelable.
     */
    private function getCallableReflector(callable $callable): \ReflectionFunctionAbstract
    {
        if (\is_array($callable)) {
            return new \ReflectionMethod($callable[0], $callable[1]);
        }

        if (\is_object($callable) && !$callable instanceof \Closure) {
            $r = new \ReflectionObject($callable);

            return $r->getMethod('__invoke');
        }

        return new \ReflectionFunction($callable);
    }

    private function getTemplateSource(TemplateWrapper $template): array
    {
        $templateSource = $template->getSourceContext();

        return [
            'file_path' => $templateSource->getPath(),
            'starting_line' => 1,
            'source_code' => $templateSource->getCode(),
        ];
    }

    /**
     * Méthode utilitaire qui "annule l'indentation" du $code donné lorsque toutes ses lignes commencent
     * avec une tabulation de quatre espaces blancs.
     */
    private function unindentCode(string $code): string
    {
        $codeLines = u($code)->split("\n");

        $indentedOrBlankLines = array_filter($codeLines, static function ($lineOfCode) {
            return u($lineOfCode)->isEmpty() || u($lineOfCode)->startsWith('    ');
        });

        $codeIsIndented = \count($indentedOrBlankLines) === \count($codeLines);
        if ($codeIsIndented) {
            $unindentedLines = array_map(static function ($lineOfCode) {
                return u($lineOfCode)->after('    ');
            }, $codeLines);
            $code = u("\n")->join($unindentedLines)->toString();
        }

        return $code;
    }
}
