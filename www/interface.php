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
$electives = $api->getElectives($institution);

if (isset($_GET["exchangemode"])) {
	$exchangemode = true;
} else {
	$exchangemode = false;
}

if (isset($_GET["delete"])) {
	$api->deleteUser($id);
	header("Location: /index.php");
	die(0);
}
?>

<!DOCTYPE html>
<html>

<head>
	<title>Rate My Elective</title>
	<?php include('header.php'); ?>
	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
	<meta property="og:image" content="https://payiota.me/resources/payiota_icon.png" />
</head>

<body>
	<?php include_once './components/navbar.php'; ?>
	<?php include './components/reviewCard.php'; ?>
	<main class="content">
		<section class="flex flex-col gap-4">
			<div class="flex flex-col sm:flex-row justify-between md:items-center gap-4">
				<h1 class="text-2xl font-medium text-primary-500">
					<?php if ($exchangemode) {
						echo "Exchanges";
					} else {
						echo "Electives";
					} ?>
				</h1>
				<div class="flex flex-col sm:flex-row sm:items-center gap-4">
					<div class='relative'>
						<img src="./resources/search.svg" alt="Search" class="w-[18px] h-[18px] absolute top-2.5 left-2.5" />
						<input type="search" id="electiveSearch" placeholder="Search..." class=" py-1.5 px-2.5 pl-9 border-gray-500/30 rounded">
					</div>
					<select id="sortCriteria" class="py-1.5 px-2.5 border-gray-500/30 rounded cursor-pointer text-gray-500">
						<option value="alphabetical">Alphabetical</option>
						<option value="rating">Rating (Highest First)</option>
						<option value="rating-reverse">Rating (Lowest First)</option>
						<option value="difficulty-reverse">Difficulty (Lowest First) </option>
						<option value="difficulty">Difficulty (Highest First)</option>
					</select>
				</div>
			</div>
			<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
				<?php foreach ($electives as $key => $value) {
					$description = $value[0]["description"];
					$stars = $api->getElectiveStars($key, $institution);
					$difficulty = $api->getElectiveStarsDifficulty($key, $institution);
					$workload = $api->getElectiveStarsWorkload($key, $institution);
					$entity_type = $api->getEntityTypeByName($key, $institution);

					if ($exchangemode && $entity_type == "elective" || !$exchangemode && $entity_type == "exchange") {
						continue;
					}

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

					$string = floatval($difficulty);
					$formattedDifficulty = number_format($string, 2, '.', '');

					$string = floatval($workload);
					$formattedWorkload = number_format($string, 2, '.', '');

					if ($exchangemode) {
						$href = "./elective.php?elective=$key&exchangemode";
					} else {
						$href = "./elective.php?elective=$key";
					}

					reviewCard($key, $description, $stars, $specialstring . $formattedDifficulty, $specialstring2.$formattedWorkload, "", $href);
				}
				?>
			</div>

		</section>
	</main>

	<script>
		// For Searching
		var electiveSearchInput = document.getElementById("electiveSearch");
		var electiveCards = document.querySelectorAll(".elective-card");
		electiveSearchInput.addEventListener("input", function() {
			var searchTerm = electiveSearchInput.value.toLowerCase();
			electiveCards.forEach(function(card) {
				var cardTitle = card.querySelector(".elective-title").innerText.toLowerCase();
				if (cardTitle.includes(searchTerm)) {
					card.style.display = "block";
				} else {
					card.style.display = "none";
				}
			});
		});

		// For Sorting
		var sortCriteriaSelect = document.getElementById("sortCriteria");
		var electiveCardsContainer = document.querySelector(".grid");

		sortCriteriaSelect.addEventListener("change", function() {
			var sortCriteria = sortCriteriaSelect.value;

			var electiveCards = Array.from(document.querySelectorAll(".elective-card"));

			switch (sortCriteria) {
				case "alphabetical":
					electiveCards.sort(function(a, b) {
						var titleA = a.querySelector(".elective-title").innerText.toLowerCase();
						var titleB = b.querySelector(".elective-title").innerText.toLowerCase();
						return titleA.localeCompare(titleB);
					});
					break;
				case "difficulty-reverse":
					electiveCards.sort(function(a, b) {
						var difficultyA = a.querySelector(".subtitle").innerText.toLowerCase().split(":")[1];
						var difficultyB = b.querySelector(".subtitle").innerText.toLowerCase().split(":")[1];
						return difficultyA - difficultyB;
					});
					break;
				case "difficulty":
					electiveCards.sort(function(a, b) {
						var difficultyA = a.querySelector(".subtitle").innerText.toLowerCase().split(":")[1];
						var difficultyB = b.querySelector(".subtitle").innerText.toLowerCase().split(":")[1];
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

		var success = document.getElementsByClassName("success-alert")[0];
		var error = document.getElementsByClassName("error-alert")[0];
		var info = document.getElementsByClassName("info")[0];
		var warning = document.getElementsByClassName("warning")[0];

		if (window.history.replaceState) {
			window.history.replaceState(null, null, window.location.href);
		}

		// changeNumber();

		<?php if (isset($result)) {
			echo "" . $api->matchCodeToType($result) . ".innerHTML='" . $api->matchCodeToMessage($result) . "';";
			echo "\n";
			echo "" . $api->matchCodeToType($result) . ".style.display = 'block';";
		} ?>
	</script>

</body>

</html>
