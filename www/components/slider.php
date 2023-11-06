<?php
function slider($difficulty = 3, $id = "custom-slider", $name = "", $disabled = true, $workload)
{
  $sliderClass = $difficulty > 3 ? "" : "";
  $workloadClass = $workload > 3 ? "" : "";
  $isDisabled = $disabled ? 'disabled' : '';
if ($difficulty !== "") {
  echo "
    <div class='relative cursor-pointer'>
      <input type='range' min='1' max='5' value='{$difficulty}' class='slider {$sliderClass}' id='{$id}' name='{$name}' {$isDisabled} />
    </div>
  ";
} else {
  echo "
    <div class='relative cursor-pointer'>
      <input type='range' min='1' max='5' value='{$workload}' class='slider {$workloadClass}' id='{$id}' name='{$name}' {$isDisabled} />
    </div>
  ";
}
  

  
}
?>
<script>
  function updateSliderAppearance() {
    const sliders = document.querySelectorAll('.slider');
    sliders.forEach(slider => {
      const percentageValue = slider.value * 20 - 10 + '%';
      const color = slider.value > 3 ? '#F87171' : '#34D399';

      const gradient = 'linear-gradient(90deg, ' + color + ' ' + percentageValue + ', #DBDCE2 ' + percentageValue + ')';
      slider.style.background = gradient;

      if (slider.value > 3) {
        slider.classList.add('error');
        slider.classList.remove('success');
      } else {
        slider.classList.remove('error');
        slider.classList.add('success');
      }
    });
  }

  document.addEventListener('input', updateSliderAppearance);
  window.addEventListener('load', updateSliderAppearance);
</script>
