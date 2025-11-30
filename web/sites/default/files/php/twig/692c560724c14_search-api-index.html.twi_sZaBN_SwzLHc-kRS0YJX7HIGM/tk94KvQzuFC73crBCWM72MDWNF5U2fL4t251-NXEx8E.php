<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\CoreExtension;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;
use Twig\TemplateWrapper;

/* modules/contrib/search_api/templates/search-api-index.html.twig */
class __TwigTemplate_138c7f92bffbf4b522e9ecd18a9b1404 extends Template
{
    private Source $source;
    /**
     * @var array<string, Template>
     */
    private array $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->extensions[SandboxExtension::class];
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = []): iterable
    {
        $macros = $this->macros;
        // line 16
        if (($context["description"] ?? null)) {
            // line 17
            yield "  <p class=\"description\">";
            yield Twig\Extension\CoreExtension::nl2br($this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["description"] ?? null), "html", null, true));
            yield "</p>
";
        }
        // line 19
        if (($context["index_progress"] ?? null)) {
            // line 20
            yield "  <h3>";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(t("Index status"));
            yield "</h3>
  <div class=\"search-api-index-status\">";
            // line 21
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["index_progress"] ?? null), "html", null, true);
            yield "</div>
";
        }
        // line 23
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["table"] ?? null), "html", null, true);
        yield "
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["description", "index_progress", "table"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "modules/contrib/search_api/templates/search-api-index.html.twig";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable(): bool
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo(): array
    {
        return array (  64 => 23,  59 => 21,  54 => 20,  52 => 19,  46 => 17,  44 => 16,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "modules/contrib/search_api/templates/search-api-index.html.twig", "/var/www/html/web/modules/contrib/search_api/templates/search-api-index.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["if" => 16];
        static $filters = ["nl2br" => 17, "escape" => 17, "t" => 20];
        static $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['if'],
                ['nl2br', 'escape', 't'],
                [],
                $this->source
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
