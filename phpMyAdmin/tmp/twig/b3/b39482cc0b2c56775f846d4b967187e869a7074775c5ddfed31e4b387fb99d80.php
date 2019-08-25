<?php

/* table/secondary_tabs.twig */
class __TwigTemplate_23a98f98d1bacd2be6187e69174c247b1ec1d91662456beb045376c1488ad1b6 extends Twig_Template
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
        if (($this->getAttribute(($context["cfg_relation"] ?? null), "relwork", [], "array") || ($context["is_foreign_key_supported"] ?? null))) {
            // line 2
            echo "    <ul id=\"topmenu2\">
        ";
            // line 3
            echo PhpMyAdmin\Util::getHtmlTab(["icon" => "b_props", "link" => "tbl_structure.php", "text" => _gettext("Table structure"), "id" => "table_strucuture_id"],             // line 8
($context["url_params"] ?? null));
            echo "
        ";
            // line 9
            echo PhpMyAdmin\Util::getHtmlTab(["icon" => "b_relations", "link" => "tbl_relation.php", "text" => _gettext("Relation view"), "id" => "table_relation_id"],             // line 14
($context["url_params"] ?? null));
            echo "
    </ul>
    <div class=\"clearfloat\"></div>
";
        }
    }

    public function getTemplateName()
    {
        return "table/secondary_tabs.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  30 => 14,  29 => 9,  25 => 8,  24 => 3,  21 => 2,  19 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "table/secondary_tabs.twig", "/var/www/hawkwynd.com/html/stream/phpMyAdmin/templates/table/secondary_tabs.twig");
    }
}
