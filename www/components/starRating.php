<?php
function starRating($rating, $size = "medium", $id = "custom-stars", $name = "", $disabled = true)
{
  $sizeClasses = [
    'medium' => "w-3 h-3",
    'large' => "w-6 h-6",
  ];

  $isDisabled = $disabled ? 'pointer-events-none' : '';

  echo "
    <div class='relative cursor-pointer {$isDisabled}' id='{$id}'>
      <input id='hidden-input' class='absolute opacity-0 pointer-events-none' name='{$name}' value='{$rating}' required='true'>
      <div class='flex items-center gap-0.5'>
  ";

  for ($i = 1; $i <= 5; $i++) {
    $starClass = ($i <= $rating) ? 'text-yellow-400 ' : 'text-gray-600 hover:text-yellow-400';
    echo "<svg class='star {$sizeClasses[$size]} {$starClass}' data-value='{$i}' aria-hidden='true' xmlns='http://www.w3.org/2000/svg' fill='currentColor' viewBox='0 0 22 20'><path d='M20.924 7.625a1.523 1.523 0 0 0-1.238-1.044l-5.051-.734-2.259-4.577a1.534 1.534 0 0 0-2.752 0L7.365 5.847l-5.051.734A1.535 1.535 0 0 0 1.463 9.2l3.656 3.563-.863 5.031a1.532 1.532 0 0 0 2.226 1.616L11 17.033l4.518 2.375a1.534 1.534 0 0 0 2.226-1.617l-.863-5.03L20.537 9.2a1.523 1.523 0 0 0 .387-1.575Z'/></svg>";
  }

  echo "
      </div>
    </div>
    <script>
      function updateStarRatingAppearance() {
        const starContainers = document.querySelectorAll('#{$id}');
        starContainers.forEach(container => {
          const stars = container.querySelectorAll('.star');
          const hiddenInput = container.querySelector('#hidden-input');
          
          stars.forEach(star => {
            star.addEventListener('click', function() {
              const value = parseInt(star.getAttribute('data-value'));
              hiddenInput.value = value;
              
              stars.forEach((s, index) => {
                if (index < value) {
                  s.classList.add('text-yellow-400');
                  s.classList.remove('text-gray-600');
                } else {
                  s.classList.remove('text-yellow-400');
                  s.classList.add('text-gray-600');
                }
              });
            });
          });
        });
      }
      
      document.addEventListener('DOMContentLoaded', updateStarRatingAppearance);
    </script>
  ";
}
