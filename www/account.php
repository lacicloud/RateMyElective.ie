<?php
// TODO: Make this reusable in one place
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
  <title>RateMyElective | Account</title>
  <?php include('header.php'); ?>
  <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
  <meta property="og:image" content="https://payiota.me/resources/payiota_icon.png" />
</head>

<body>
  <?php include_once './components/navbar.php'; ?>
  <main class="content">
    <section class='bg-white border-2 border-gray-300 p-4 rounded-lg space-y-6'>
      <h1 class='text-2xl font-medium text-primary-500'>My Account</h1>
      <div class='space-y-4'>
        <div class='flex items-baseline gap-4 text-lg'>
          <div class='text-gray-500'>Email:</div>
          <div class='font-semibold'>
            <?php echo $data["email"]; ?>
          </div>
        </div>
        <div class='flex items-baseline gap-4 text-lg'>
          <div class='text-gray-500'>Institution:</div>
          <div class='font-semibold uppercase'>
            <?php echo $data["institution"]; ?>
          </div>
        </div>
      </div>
      <div class='flex justify-end'>
        <?php button("Delete Account", "ValidateAccountDelete()", "", "solid", "medium", "error"); ?>
      </div>
    </section>
  </main>
</body>

</html>