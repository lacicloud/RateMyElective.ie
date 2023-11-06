<?php
session_start();
require("../functions.php");
$api = new RateMyElective;

if (isset($_GET["logout"])) {
	session_destroy();
	header("Location: ./index.php");
	die(0);
}

//session expiration
if (isset($_SESSION['FIRST_ACTIVITY']) && (time() - $_SESSION['FIRST_ACTIVITY'] > 1800)) {
	session_destroy();
	header("Location: /index.php");
	die(0);
} elseif (!isset($_SESSION["FIRST_ACTIVITY"])) {
	$_SESSION['FIRST_ACTIVITY'] = time(); //first activity timestamp
}


$id = $_SESSION["id"];
if (!is_numeric($id)) {
	header("Location: /index.php");
	die(1);
}

$data = $api->getUserValues($id);
$institution = $data["institution"];


//elective interface specific logic
if (!isset($_GET["elective"])) {
	header("Location: /interface.php");
	die(0);
}


$elective_name_to_get = $_GET["elective"];
//filter useless queries (its protected anyways because of PDO from SQL injection but to protect against useless requests)
if ($api->verifyElectiveName($elective_name_to_get, $institution) !== "ERR_OK") {
	header("Location: /interface.php");
	die(0);
}

//update function

if (isset($_POST["review_text"]) && isset($_POST["stars"]) && isset($_POST["stars_assessment_difficulty"]) && isset($_POST["stars_workload_difficulty"]) && !isset($_POST["update"])) {
	$entity_type = $api->getEntityTypeByName($elective_name_to_get, $institution);
	$result = $api->insertNewReview($elective_name_to_get, $_POST["stars"], $_POST["stars_assessment_difficulty"], $institution, $_POST["review_text"], $id, $entity_type, $_POST["stars_workload_difficulty"]);
} elseif (isset($_POST["review_text"]) && isset($_POST["stars"]) && isset($_POST["stars_assessment_difficulty"]) && isset($_POST["stars_workload_difficulty"]) && isset($_POST["update"])) {
	$entity_type = $api->getEntityTypeByName($elective_name_to_get, $institution);
	$result = $api->updateReview($elective_name_to_get, $_POST["stars"], $_POST["stars_assessment_difficulty"], $institution, $_POST["review_text"], $id, $entity_type, $_POST["stars_workload_difficulty"]);
} else {
	if (isset($_GET["deleteUserReview"])) {
		$result = $api->deleteReview($elective_name_to_get, $id, $institution);
	}
}

$stars = $api->getElectiveStars($elective_name_to_get, $institution);
$difficulty = $api->getElectiveStarsDifficulty($elective_name_to_get, $institution);
$workload = $api->getElectiveStarsWorkload($elective_name_to_get, $institution);
$description = $api->getElectiveDescriptionByName($elective_name_to_get, $institution);
$entity_type = $api->getEntityTypeByName($elective_name_to_get, $institution);

$elective_data_to_get = $api->getElectiveReviews($elective_name_to_get, $institution, $id);
$already_submitted = $api->checkIfUserAlreadySubmittedReview($elective_name_to_get, $id, $institution);


if (isset($_GET["delete"])) {
	$api->deleteUser($id);
	header("Location: /index.php");
	die(0);
}

if (isset($_GET["exchangemode"])) {
	$exchangemode = true;
} else {
	$exchangemode = false;
}

?>
<!DOCTYPE html>
<html>

<head>
	<title>Rate My Elective | Elective</title>
	<?php include('header.php'); ?>
	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
	<meta property="og:image" content="https://ratemyelective.ie/resources/payiota_icon.png" />
</head>

<body>
	<?php include_once './components/navbar.php'; ?>
	<?php include './components/reviewCard.php'; ?>
	<?php include './components/slider.php'; ?>
	<?php include './components/reviewModal.php'; ?>

	<main class="content space-y-10">
		<header class="grid grid-cols-1 sm:grid-cols-2 items-baseline gap-5 md:gap-4">
			<h1 class="text-3xl md:text-4xl font-medium order-0">
				<?php echo $elective_name_to_get; ?>
			</h1>
			<div class="flex items-center sm:justify-end gap-3 order-2">
				<div class="font-medium text-gray-400 md:hidden">Rating:</div>
				<?php starRating($stars, "large"); ?>
			</div>
			<div class="space-y-1 md:space-y-3 order-1 sm:order-2">
				<div class="md:text-lg font-medium text-gray-400">Description</div>
				<div title="<?php echo $description; ?>" class="line-clamp-3">
					<?php echo $description; ?>
				</div>
			</div>
			<div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3 order-4">
				<div class="md:text-lg font-medium text-gray-400">
					<?php
					if ($exchangemode) {
						echo "Price: ";
					} else {
						echo "Difficulty: ";
					}
					?>
				</div>
				<?php slider($difficulty, 'elective-detail', true, true, ""); ?>

				<div class="md:text-lg font-medium text-gray-400">
					<?php
					if ($exchangemode) {
						echo "Fun: ";
					} else {
						echo "Workload: ";
					}
					?>
				</div>
				<?php slider("", 'elective-detail', true, true, $workload); ?>

			</div>
		</header>
		<section class="">
			<div class="flex justify-between items-center flex-wrap mb-4 gap-3">
				<h3 class="text-2xl font-medium text-primary-500 flex-1">Reviews</h3>
				<select id="sortCriteria" class="py-[7px] px-2.5 border-gray-500/30 rounded cursor-pointe min-w-[220px] w-full sm:w-auto text-gray-500 cursor-pointer">
					<option value="date">Posted (Newest First)</option>
					<option value="date-reverse">Posted (Oldest First)</option>
					<option value="rating">Rating (Highest First)</option>
					<option value="rating-reverse">Rating (Lowest First)</option>
					<option value="difficulty-reverse">Difficulty (Lowest First) </option>
					<option value="difficulty">Difficulty (Highest First)</option>
					<option value="workload-reverse">Workload (Lowest First) </option>
					<option value="workload">Workload (Highest First)</option>
				</select>
				<?php
				if ($already_submitted == "ERR_NOT_SUBMITTED") {
					button("Write a Review", "toggleModal('new')", "");
				} else {
					button("Edit Review", "toggleModal($id)", "");
				}
				?>
			</div>
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 reviews">
				<?php
				foreach ($elective_data_to_get as $key => $value) {
					foreach ($value as $key => $value) {
						$realID = $value["realID"];

						$title = intval($id) ===  intval($realID) ? "Your Review" : "Review";
						$description = $value["review"];
						$stars = $value["stars"];
						$date = "Posted on: " . $value["date_submitted"];
						$workload = $value["stars_workload_difficulty"];

						$difficulty = $value["stars_assessment_difficulty"];

						if ($exchangemode) {
							$specialstring = "Price: ";
						} else {
							$specialstring = "Difficulty: ";
						}


						if ($exchangemode) {
							$specialstring2 = "Fun: ";
						} else {
							$specialstring2 = "Workload: ";
						}


						$onClick = "toggleModal('$realID');";
						reviewCard($title, $description, $stars, $specialstring2. $workload, $date, $specialstring . $difficulty, "", $onClick, true);
					}
				}
				?>
			</div>
		</section>

		<?php
		reviewModal($elective_data_to_get, $exchangemode, $elective_name_to_get, $id);
		?>
	</main>

	<script>
		// For Sorting
		var sortCriteriaSelect = document.getElementById("sortCriteria");
		var electiveCardsContainer = document.querySelector(".reviews");

		sortCriteriaSelect.addEventListener("change", function() {
			var sortCriteria = sortCriteriaSelect.value;

			var electiveCards = Array.from(document.querySelectorAll(".elective-card"));

			switch (sortCriteria) {

				case "date":
					electiveCards.sort(function(a, b) {
						var difficultyA = new Date(a.querySelector(".subtitle").innerText.split(":")[1]);
						var difficultyB = new Date(b.querySelector(".subtitle").innerText.split(":")[1]);

						return difficultyB - difficultyA;
					});
					break;
				case "date-reverse":
					electiveCards.sort(function(a, b) {
						var difficultyA = new Date(a.querySelector(".subtitle").innerText.split(":")[1]);
						var difficultyB = new Date(b.querySelector(".subtitle").innerText.split(":")[1]);
						console.log(difficultyA, difficultyB);
						return difficultyA - difficultyB;
					});
					break;
				case "difficulty-reverse":
					electiveCards.sort(function(a, b) {
						var difficultyA = a.querySelector(".subtitle-two").innerText.toLowerCase().split(":")[1];
						var difficultyB = b.querySelector(".subtitle-two").innerText.toLowerCase().split(":")[1];
						return difficultyA - difficultyB;
					});
					break;
				case "difficulty":
					electiveCards.sort(function(a, b) {
						var difficultyA = a.querySelector(".subtitle-two").innerText.toLowerCase().split(":")[1];
						var difficultyB = b.querySelector(".subtitle-two").innerText.toLowerCase().split(":")[1];
						return difficultyB - difficultyA;
					});
					break;
					case "workload-reverse":
					electiveCards.sort(function(a, b) {
						var difficultyA = a.querySelector(".subtitle-three").innerText.toLowerCase().split(":")[1];
						var difficultyB = b.querySelector(".subtitle-three").innerText.toLowerCase().split(":")[1];
						return difficultyA - difficultyB;
					});
					break;
				case "workload":
					electiveCards.sort(function(a, b) {
						var difficultyA = a.querySelector(".subtitle-three").innerText.toLowerCase().split(":")[1];
						var difficultyB = b.querySelector(".subtitle-three").innerText.toLowerCase().split(":")[1];
						return difficultyB - difficultyA;
					});
					break;
				case "rating":
					electiveCards.sort(function(a, b) {
						var ratingA = parseFloat(a.querySelector("#hidden-input").value);
						var ratingB = parseFloat(b.querySelector("#hidden-input").value);
						console.log(ratingA, ratingB);
						return ratingB - ratingA;
					});
					break;
				case "rating-reverse":
					electiveCards.sort(function(a, b) {
						var ratingA = parseFloat(a.querySelector("#hidden-input").value);
						var ratingB = parseFloat(b.querySelector("#hidden-input").value);
						console.log(ratingA, ratingB);
						return ratingA - ratingB;
					});
					break;

			}

			electiveCards.forEach(function(card) {
				electiveCardsContainer.appendChild(card);
			});
		});

		function toggleModal(id) {
			const modalContainer = document.getElementById('modal-container');
			modalContainer.classList.toggle('hidden');

			var currentURL = window.location.href;
			var url = new URL(currentURL);
			var searchParams = url.searchParams;

			if (searchParams.has("reviewId")) {
				searchParams.delete("reviewId");
				var modifiedURL = url.toString();
				window.history.replaceState({}, document.title, modifiedURL);
				document.body.classList.remove('modal-open');
			} else {

				url.searchParams.set('reviewId', id);
				var modifiedURL = url.toString();
				window.history.replaceState(null, null, modifiedURL);
				location.reload();
			}
		}

		window.addEventListener('load', function() {
			var currentURL = window.location.href;
			var url = new URL(currentURL);
			var searchParams = url.searchParams;

			if (searchParams.has("reviewId")) {
				document.body.classList.add('modal-open');
			}
		});
	</script>

	<script>
		var success = document.getElementsByClassName("success")[0];
		var error = document.getElementsByClassName("error")[0];
		var info = document.getElementsByClassName("info")[0];
		var warning = document.getElementsByClassName("warning")[0];

		if (window.history.replaceState) {
			window.history.replaceState(null, null, window.location.href);
		}

		<?php
		if (isset($result)) {
			echo "" . $api->matchCodeToType($result) . ".innerHTML='" . $api->matchCodeToMessage($result) . "';";
			echo "\n";
			echo "" . $api->matchCodeToType($result) . ".style.display = 'block';";
		}
		?>
	</script>

</body>

</html>
