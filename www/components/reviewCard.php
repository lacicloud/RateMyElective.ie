<?php include 'starRating.php'; ?>

<?php
function reviewCard($title, $description, $rating, $subtitle = "", $subtitle2 = "", $subtitle3, $href = "", $onClick = "", $isItalics = false)
{

  $tag = 'button';
  $attributes = '';
  $descriptionClasses = $isItalics ? "italic" : "";

  if (!empty($href)) {
    $tag = 'a';
    $attributes = "href=\"{$href}\"";
  } elseif (!empty($onClick)) {
    $attributes = "onclick=\"{$onClick}\"";
  }

  echo
  "<{$tag} class='elective-card bg-white border-2 border-gray-300 rounded-lg px-3 py-3 flex flex-col items-stretch !no-underline !text-primary-600 hover:!border-primary-500 hover:!bg-gray-200/50 transition-colors duration-200' href='{$href}' {$attributes}>
    <div class='flex items-center gap-3'>
      <div class='flex-1 text-left font-semibold text-[17px] elective-title'>{$title}</div>
      <div>";
  starRating($rating);
  echo "</div>
    </div>
    <div class='flex justify-between items-center gap-2 mb-3'>
      <div class='text-[11px] text-gray-500 mt-1 subtitle'>{$subtitle}</div>
      <div class='text-[11px] text-gray-500 mt-1 subtitle-two'>{$subtitle2}</div>
       <div class='text-[11px] text-gray-500 mt-1 subtitle-three'>{$subtitle3}</div>
    </div>
    <div class='text-[14px] text-left line-clamp-3 $descriptionClasses' title='{$description}'>{$description}</div>
  </{$tag}>";
}
?>
