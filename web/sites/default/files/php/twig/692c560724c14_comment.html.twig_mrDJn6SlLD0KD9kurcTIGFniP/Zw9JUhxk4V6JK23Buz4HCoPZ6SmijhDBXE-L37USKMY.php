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

/* core/themes/olivero/templates/content/comment.html.twig */
class __TwigTemplate_429ed90ad0d632ddc9a0a0521f4bf0a1 extends Template
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
        // line 70
        $context["classes"] = ["comment", "js-comment", (( !        // line 73
($context["parent_comment"] ?? null)) ? ("comment--level-1") : ("")), (((        // line 74
($context["status"] ?? null) != "published")) ? (("comment--" . ($context["status"] ?? null))) : ("")), ((CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source,         // line 75
($context["comment"] ?? null), "owner", [], "any", false, false, true, 75), "anonymous", [], "any", false, false, true, 75)) ? ("by-anonymous") : ("")), (((        // line 76
($context["author_id"] ?? null) && (($context["author_id"] ?? null) == CoreExtension::getAttribute($this->env, $this->source, ($context["commented_entity"] ?? null), "getOwnerId", [], "method", false, false, true, 76)))) ? ((("by-" . CoreExtension::getAttribute($this->env, $this->source, ($context["commented_entity"] ?? null), "getEntityTypeId", [], "method", false, false, true, 76)) . "-author")) : (""))];
        // line 79
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("olivero/comments"), "html", null, true);
        yield "
<article ";
        // line 80
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, CoreExtension::getAttribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [($context["classes"] ?? null)], "method", false, false, true, 80), "setAttribute", ["role", "article"], "method", false, false, true, 80), "setAttribute", ["data-drupal-selector", "comment"], "method", false, false, true, 80), "html", null, true);
        yield ">
  ";
        // line 86
        yield "  <span class=\"hidden\" data-comment-timestamp=\"";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["new_indicator_timestamp"] ?? null), "html", null, true);
        yield "\"></span>

  ";
        // line 88
        if (($context["submitted"] ?? null)) {
            // line 89
            yield "    <div class=\"comment__picture-wrapper\">
      <div class=\"comment__picture\">
        ";
            // line 91
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["user_picture"] ?? null), "html", null, true);
            yield "
      </div>
    </div>
  ";
        }
        // line 95
        yield "  <div class=\"comment__text-wrapper\">
    ";
        // line 96
        if (($context["submitted"] ?? null)) {
            // line 97
            yield "      <footer class=\"comment__meta\">
        <p class=\"comment__author\">";
            // line 98
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["author"] ?? null), "html", null, true);
            yield "</p>
        <p class=\"comment__time\">";
            // line 99
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["created"] ?? null), "html", null, true);
            yield "</p>
        ";
            // line 105
            yield "        ";
            if (($context["parent"] ?? null)) {
                // line 106
                yield "          <p class=\"visually-hidden\">";
                yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["parent"] ?? null), "html", null, true);
                yield "</p>
        ";
            }
            // line 108
            yield "      </footer>
    ";
        }
        // line 110
        yield "    <div";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, CoreExtension::getAttribute($this->env, $this->source, ($context["content_attributes"] ?? null), "addClass", ["comment__content"], "method", false, false, true, 110), "html", null, true);
        yield ">
      ";
        // line 111
        if (($context["title"] ?? null)) {
            // line 112
            yield "        ";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title_prefix"] ?? null), "html", null, true);
            yield "
        <h3";
            // line 113
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title_attributes"] ?? null), "html", null, true);
            yield ">";
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title"] ?? null), "html", null, true);
            yield "</h3>
        ";
            // line 114
            yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["title_suffix"] ?? null), "html", null, true);
            yield "
      ";
        }
        // line 116
        yield "      ";
        yield $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, ($context["content"] ?? null), "html", null, true);
        yield "
    </div>
  </div>
</article>
";
        $this->env->getExtension('\Drupal\Core\Template\TwigExtension')
            ->checkDeprecations($context, ["parent_comment", "status", "comment", "author_id", "commented_entity", "attributes", "new_indicator_timestamp", "submitted", "user_picture", "author", "created", "parent", "content_attributes", "title", "title_prefix", "title_attributes", "title_suffix", "content"]);        yield from [];
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName(): string
    {
        return "core/themes/olivero/templates/content/comment.html.twig";
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
        return array (  129 => 116,  124 => 114,  118 => 113,  113 => 112,  111 => 111,  106 => 110,  102 => 108,  96 => 106,  93 => 105,  89 => 99,  85 => 98,  82 => 97,  80 => 96,  77 => 95,  70 => 91,  66 => 89,  64 => 88,  58 => 86,  54 => 80,  50 => 79,  48 => 76,  47 => 75,  46 => 74,  45 => 73,  44 => 70,);
    }

    public function getSourceContext(): Source
    {
        return new Source("", "core/themes/olivero/templates/content/comment.html.twig", "/var/www/html/web/core/themes/olivero/templates/content/comment.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = ["set" => 70, "if" => 88];
        static $filters = ["escape" => 79];
        static $functions = ["attach_library" => 79];

        try {
            $this->sandbox->checkSecurity(
                ['set', 'if'],
                ['escape'],
                ['attach_library'],
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
