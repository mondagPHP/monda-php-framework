<?php

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => [
            'syntax' => 'short'
        ],
        'list_syntax' => [
            'syntax' => 'short'
        ],
        'concat_space' => [
            'spacing' => 'one'
        ],
        'blank_line_before_statement' => [
            'statements' => [
                'declare',
            ],
        ],
        'ordered_imports' => [
            'imports_order' => [
                'class', 'function', 'const',
            ],
            'sort_algorithm' => 'alpha',
        ],

        'combine_consecutive_unsets' => true,
        'declare_strict_types' => false,
        'linebreak_after_opening_tag' => true,
        'lowercase_static_reference' => true,
        'no_useless_else' => true,
        'not_operator_with_successor_space' => true,
        'not_operator_with_space' => false,
        'ordered_class_elements' => true,
        'php_unit_strict' => false,
        'phpdoc_separation' => false,
        'multiline_comment_opening_closing' => true,
        'single_quote' => true,
        'no_unused_imports' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'self_accessor' => true,
        'binary_operator_spaces' => true,
        'no_empty_statement' => true,
        'no_extra_consecutive_blank_lines' => true,
        'no_blank_lines_after_class_opening' => true,
        'include' => true,
        'no_trailing_comma_in_list_call' => true,
        'no_leading_namespace_whitespace' => true,
        'standardize_not_equals' => true,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('runtime')
            ->exclude('static')
            ->exclude('herosphp')
            ->exclude('runtime')
            ->exclude('vendor')
            ->in(__DIR__)
    )
    ->setUsingCache(false);
