<?php

/* privileges/resource_limits.twig */
class __TwigTemplate_26037b2be7e13808cc752a35531956def598862f77f7c2a668ac3c0d9159e8a8 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<fieldset>
    <legend>";
        // line 2
        echo _gettext("Resource limits");
        echo "</legend>
    <p>
        <small>
            <em>";
        // line 5
        echo _gettext("Note: Setting these options to 0 (zero) removes the limit.");
        echo "</em>
        </small>
    </p>
    ";
        // line 8
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["limits"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["limit"]) {
            // line 9
            echo "        ";
            $this->loadTemplate("privileges/resource_limit_item.twig", "privileges/resource_limits.twig", 9)->display(["limit" =>             // line 10
$context["limit"]]);
            // line 12
            echo "    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['limit'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 13
        echo "</fieldset>
";
    }

    public function getTemplateName()
    {
        return "privileges/resource_limits.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  48 => 13,  42 => 12,  40 => 10,  38 => 9,  34 => 8,  28 => 5,  22 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "privileges/resource_limits.twig", "/var/www/hawkwynd.com/html/stream/phpMyAdmin/templates/privileges/resource_limits.twig");
    }
}
