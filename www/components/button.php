<?php
function button($text, $onClick = '', $href = '', $variant = 'solid', $size = 'medium',  $theme = 'primary', $disabled = false)
{
  // Define an array of valid button sizes and variants
  $validSizes = ['small', 'medium', 'large'];
  $validVariants = ['solid', 'outlined', 'link'];

  $isDisabled = $disabled ? 'disabled' : '';
  $disabledClasses = $disabled ? 'pointer-events-none opacity-40' : '';

  // Check if the provided size and variant are valid; if not, use defaults
  if (!in_array($size, $validSizes)) {
    $size = 'medium';
  }

  if (!in_array($variant, $validVariants)) {
    $variant = 'solid';
  }

  $sizeClasses = [
    'medium' => [
      'solid' => 'px-4 py-2 text-sm font-medium !no-underline text-center',
      'outlined' => 'px-4 py-2 text-sm font-medium !no-underline',
      'link' => '!no-underline'
    ],
  ];

  $themeClasses = [
    'primary' => [
      'solid' => 'shadow-md text-white border-2 border-primary-500 bg-primary-500 hover:bg-primary-600',
      'outlined' => 'text-primary-500 border-2 border-primary-500 hover:bg-primary-500 hover:text-white',
      'link' => 'font-semibold text-gray-500 hover:text-primary-500'
    ],
    'success' => [
      'solid' => 'shadow-md text-white border-2 border-success-500 bg-success-500 hover:bg-success-600 hover:border-success-600',
      'outlined' => '',
      'link' => ''
    ],
    'error' => [
      'solid' => 'shadow-md text-white border-2 border-error-500 bg-error-500 hover:bg-error-600 hover:border-error-600',
      'outlined' => '',
      'link' => ''
    ]
  ];

  $buttonClasses = "rounded-lg focus:outline-none transition-colors duration-200 {$sizeClasses[$size][$variant]} {$themeClasses[$theme][$variant]}";

  $tag = 'button';
  $attributes = '';

  if (!empty($href)) {
    $tag = 'a';
    $attributes = "href=\"{$href}\"";
  } elseif (!empty($onClick)) {
    $attributes = "onclick=\"{$onClick}\"";
  }

  // Output the button HTML
  echo "<{$tag} class=\"{$buttonClasses} {$disabledClasses}\" {$attributes} {$isDisabled}>{$text}</{$tag}>";
}
