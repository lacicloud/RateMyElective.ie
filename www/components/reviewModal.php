<?php
function reviewModal($elective_data_to_get, $exchangemode, $elective_name_to_get, $userId)
{

  $foundItem = null;
  $id = $_GET['reviewId'];

  foreach ($elective_data_to_get as $key => $value) {
    foreach ($value as $key => $item) {
      if ($item["realID"] == $id) {
        $foundItem = $item;
        break;
      }
    }
  }

  $classes =  $foundItem || $id === "new" ? "" : " hidden";

  $isNew = $id === "new";
  $isDisabled = $id !== "new" ? 'disabled' : '';
  $isUsersReview = intval($userId) === intval($id);
  $buttonClasses =  $isUsersReview || $id === "new" ? "" : " hidden";
  
  if ($isUsersReview == true) { 

    $isDisabled = false;

 }

  echo '
    <div id="modal-container" class="fixed top-0 left-0 w-full h-[100svh] flex items-center justify-center overflow-hidden !mt-0' . $classes .  ' "">
        <div class="modal-background fixed top-0 left-0 w-full h-[100vh] bg-black opacity-40" onclick="toggleModal()"></div>
        <div class="modal-content bg-white p-4 rounded-xl shadow-md w-[90%] max-w-2xl">
            <div class="flex justify-between items-center mb-2 md:mb-4">
              <h2 class="text-2xl font-bold">Review</h2>
              <div onclick="toggleModal()" >
                <img src="./resources/close.svg" alt="Close" class="w-6 h-6 cursor-pointer" />
              </div>
            </div>

            <form action="./elective.php?elective=' . $elective_name_to_get . '" method="POST">';
               if ($isNew == false) {
    echo '<input type="hidden" id="update" name="update" value="true">';
   }

            echo '<div class="flex flex-col gap-4 md:1 md:flex-row justify-between md:items-center">
                    <div class="flex flex-col gap-2">
                        <div class="font-semibold">
                            Rating
                        </div>';


  starRating($foundItem["stars"], "large", 'modal-stars', 'stars', $isDisabled);

  echo '
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="font-semibold md:text-right">
                            ' . ($exchangemode ? "Price" : "Difficulty") . '
                        </div>';

  slider($foundItem["stars_assessment_difficulty"], "modal-slider", 'stars_assessment_difficulty', $isDisabled, "");
  echo '
                    </div>
                    <div class="flex flex-col gap-2">
                        <div class="font-semibold md:text-right">
                                ' . ($exchangemode ? "Fun" : "Workload") . '
                        </div>';
  slider("", "modal-slider", 'stars_workload_difficulty', $isDisabled, $foundItem["stars_workload_difficulty"]);
  echo '
                    </div>
                </div>


                <div class="mt-3 md:mt-4 flex flex-col gap-1">
                    <label for="review_text" class="font-semibold">Review</label>
                    <textarea name="review_text" id="review_text" rows="6" cols="50" required="true"' . $isDisabled . ' class="p-3 border-gray-500/50 rounded-lg disabled:bg-gray-100 disabled:text-black/90 disabled:opacity-100" placeholder="Write a review...">' . $foundItem["review"] . '</textarea>
                </div>



                
                <div class="mt-4 flex flex-col-reverse md:flex-row justify-center md:items-center gap-4' . $buttonClasses  . '">';



  button("Delete Review", "", "./elective.php?elective=$elective_name_to_get&deleteUserReview", "solid", "medium", "error", $isNew);
  button("Edit Review", "", "", "solid", "medium", "success", $isNew);
  button("Submit Review", "", "", "solid", "medium", "success", $isUsersReview);
  echo '</div>
            </form>
        </div>
    </div>';
}
