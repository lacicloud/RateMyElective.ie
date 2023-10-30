<?php
session_start();
require("../functions.php");
$api = new RateMyElective;

if (isset($_SESSION["logged_in"]) and $_SESSION["logged_in"] == 1) {
  header("Location: ./interface.php");
}

if (isset($_POST["email"]) and isset($_POST["password"]) and !isset($_POST["password_retyped"])) {

  $result = $api->loginUser($_POST["email"], $_POST["password"]);
  if (is_numeric($result)) {
    $_SESSION["id"] = $result;
    header("Location: ./interface.php");
    die(0);
  } else {
    //do nothing
  }
}

if (isset($_GET["confirm"])) {
  if ($_GET["confirm"] == "ok") {
    $result = "ERR_CONFIRM_OK";
  } else {
    $result = "ERR_KEY_WRONG";
  }
}

if (isset($_GET["result"])) {
  $result = "ERR_RESET_STEP_2_OK";
}

// Registration Code
if (isset($_POST["email"]) and isset($_POST["password"]) and isset($_POST["password_retyped"]) and !isset($_POST["reset_key"])) {
  $result = $api->createUser($_POST["email"], $_POST["password"], $_POST["password_retyped"]);
}

//forgot step 1
if (isset($_POST["email"]) and !isset($_POST["password"]) and !isset($_POST["password_retyped"])) {
  $result = $api->forgotLoginStep1($_POST["email"]);
}

//forgot step 2
if (isset($_POST["reset_key"]) and isset($_POST["password"]) and isset($_POST["password_retyped"])) {

  $result = $api->forgotLoginStep2($_POST["reset_key"], $_POST["password"], $_POST["password_retyped"]);
}

?>

<!DOCTYPE html>
<html>

<head>
  <title>RateMyElective - Account</title>
  <?php include('header.php'); ?>
  <?php include('./components/button.php'); ?>

  <style>

.info, .success, .warning, .error, .validation {
    border: 1px solid rgba(0,0,0,0.3);
	border-radius:2px;
    margin: 5px 15px 20px 15px;
    padding: 5px 10px;
    width:100%;
    background-repeat: no-repeat;
    background-position: 10px center;
    display: none;
	color: rgba(25,25,25,0.85);
}

.info {
    /*color: #00529B;*/
    background-color: #BDE5F8;
}

.success {
    /*color: #4F8A10;*/
    background-color: #DFF2BF;
}

.warning {
    /*color: #9F6000;*/
    background-color: #FEEFB3;
}

.error {
    /*color: #D8000C;*/
    background-color: #FFBABA;
}

</style>

</head>

<body>
  <main class="min-h-screen flex flex-col lg:flex-row items-stretch">
    <!-- <div class="flex"> -->
    <div class="relative flex-[4] self-stretch flex flex-col gap-2 lg:gap-6 justify-center items-center text-center text-white p-8 min-h-[340px] sm:min-h-[540px] bg-center" style="background:url('resources/hero-<?php echo rand(1, 9); ?>.jpg'); background-size: cover; background-position: center">
      <div class='absolute top-0 left-0 w-full h-full bg-black !opacity-60 z-0'></div>
      <h1 class="text-3xl md:text-6xl !leading-tight font-medium max-w-xl lg:max-w-3xl z-10">Rate your electives, open modules and exchanges!</h1>
      <p class="text-lg lg:text-2xl font-light z-10">See what other students have said about their choices!</p>
    </div>
    <div class="flex-[3] flex flex-col items-center justify-center p-8">
      <img src="./resources/logo.svg" class="h-10" />

      <ul class="nav nav-pills nav-justified nav-login mt-4 mb-8 w-full max-w-lg" id="myTab" role="tablist">
        <li class="nav-item">
          <a class="nav-link 
            <?php if (!isset($_GET['register']) and !isset($_GET['forgot_1']) and !isset($_GET['forgot_2'])) {
              echo 'active';
            } ?>" id="login-tab" data-toggle="tab" href="#login" role="tab" aria-controls="login" aria-expanded="true">Login</a>
        </li>
        <li class="nav-item">
          <a class="nav-link
            <?php if (isset($_GET['register'])) {
              echo 'active';
            } ?>" id="register-tab" data-toggle="tab" href="#register" role="tab" aria-controls="register">Sign up</a>
        </li>
        <li class="nav-item hidden xl:block">
          <a class="nav-link
            <?php if (isset($_GET['forgot_1']) or isset($_GET['forgot_2'])) {
              echo 'active';
            } ?>" id="forgot-tab" data-toggle="tab" href="#forgot" role="tab" aria-controls="forgot">Forgot Login</a>
        </li>
      </ul>

        <div class="row">
          <div class="success"></div>
          <div class="warning"></div>
          <div class="error"></div>
          <div class="info"></div>
        </div>

      <div class="tab-content w-full max-w-lg !min-h-[400px] -mb-28">
        <div class="tab-pane fade 
          <?php if (!isset($_GET['register']) and !isset($_GET['forgot_1']) and !isset($_GET['forgot_2'])) {
            echo 'show active';
          } ?>" id="login" role="tabpanel" aria-labelledby="login-tab">
          <form action="./index.php" method="POST" onsubmit="return ValidateLogin(this);" class="flex flex-col gap-4">
            <div class="flex flex-col">
              <label for="email-address" class="font-semibold">Email Address</label>
              <input type="email" id="email-address" name="email" placeholder="you@yourdomain.com" required value="" class="py-2 px-2 border-gray-500/30 rounded">
            </div>
            <div class="flex flex-col">
              <label for="register_password" class="font-semibold">Password</label>
              <input type="password" id="inputPassword" name="password" class="py-2 px-2 border-gray-500/30 rounded" placeholder="Password" required value="">
            </div>
            <div class='mt-2 w-full flex flex-col items-stretch'>
              <?php button("Log In", "", "", "solid", "medium", "primary"); ?>
            </div>
          </form>
          <div class="flex justify-end mt-3">
            <?php button("Forgot password?", "", "./index.php?forgot_1", "link", "medium", "primary"); ?>
          </div>
        </div>
        <div class="tab-pane fade" id="register" role="tabpanel" aria-labelledby="register-tab">
          <form action="/index.php?register" method="POST" onsubmit="return ValidateRegister(this);" class="flex !flex-col !gap-4">
            <div class="flex flex-col">
              <label for="email-address" class="font-semibold">Email Address</label>
              <input type="email" id="email-address" name="email" placeholder="you@yourdomain.com" required class="py-2 px-2 border-gray-500/30  rounded">
            </div>
            <div class="flex flex-col md:flex-row gap-4">
              <div class="flex-1 flex flex-col">
                <label for="register_password" class="font-semibold">Password</label>
                <input type="password" id="register_password" name="password" class="py-2 px-2 border-gray-500/30  rounded" placeholder="*******" required>
              </div>
              <div class="flex-1 flex flex-col">
                <label for="register_password_retype" class="font-semibold">Retype Password</label>
                <input type="password" id="register_password_retype" name="password_retyped" class="py-2 px-2 border-gray-500/30 rounded" placeholder="*******" required>
              </div>
            </div>
            <div class="form-check tos_agreement">
              <label>
                <input required type="checkbox" name="checkbox" id="terms_and_conditions_checkbox" value="" />
                I agree to the <a href="/resources/ratemyelective_legal.pdf" target="_blank">Terms and Conditions</a>
              </label>
            </div>
            <div class='w-full flex flex-col items-stretch'>
              <?php button("Sign Up", "", "", "solid", "medium", "primary"); ?>
            </div>

          </form>
        </div>
        <?php if (isset($_GET["forgot_2"])) { ?>
          <div class="tab-pane fade  
            <?php if (isset($_GET['forgot_2'])) {
              echo 'show active';
            } ?>" id="forgot" role="tabpanel" aria-labelledby="forgot-tab">
            <form action="./index.php?forgot_2&reset_key=<?php echo $_GET['reset_key'] ?>" method="POST" onsubmit="return ValidateForgotStep2(this);" class="flex flex-col gap-4">
              <div class="flex flex-col">
                <label for="email-address" class="font-semibold">Reset Key</label>
                <input type="text" id="email-address" class="py-2 px-2 border-gray-500/30 rounded" name="reset_key" <?php echo (isset($_GET["reset_key"]) == true) ? "value='" . $_GET["reset_key"] . "'" : "" ?> required>
              </div>
              <input type="hidden" name="email" value="<?php echo $api->matchResetKeyToEmail($_GET['reset_key']) ?>">
              <div class="flex gap-4">
                <div class="flex-1 flex flex-col">
                  <label for="register_password" class="font-semibold">New Password</label>
                  <input type="password" id="forgot_password" name="password" class="py-2 px-2 border-gray-500/30 rounded" placeholder="*******" required>
                </div>
                <div class="flex-1 flex flex-col">
                  <label for="register_password_retype" class="font-semibold">Retype New Password</label>
                  <input type="password" id="forgot_password_retype" name="password_retyped" class="py-2 px-2 border-gray-500/30 rounded" placeholder="*******" required>
                </div>
              </div>
              <!-- <input type="submit" value="Reset My Account"></li> -->
              <div class='w-full flex flex-col items-stretch mt-3'>
                <?php button("Reset My Account"); ?>
              </div>
            </form>
          </div>
        <?php } else { ?>
          <div class="tab-pane fade  
          <?php if (isset($_GET['forgot_1'])) {
            echo 'show active';
          } ?>" id="forgot" role="tabpanel" aria-labelledby="forgot-tab">
            <form action="./index.php?forgot_1" method="POST" onsubmit="return ValidateForgotStep1(this);" class="flex flex-col gap-6">
              <div class="flex flex-col">
                <label for="email-address" class="font-semibold">Email Address</label>
                <input type="email" id="email-address" class="py-2 px-2 border-gray-500/30 rounded" name="email" placeholder="you@yourdomain.com" required>
              </div>
              <div class='w-full flex flex-col items-stretch'>
                <?php button("Reset My Account", "", "", "solid", "medium", "primary"); ?>
              </div>
            </form>
          </div>
        <?php } ?>

      </div>
    </div>
    </div>

    <script>
      var success = document.getElementsByClassName("success")[0];
      var error = document.getElementsByClassName("error")[0];
      var info = document.getElementsByClassName("info")[0];
      var warning = document.getElementsByClassName("warning")[0];
      <?php
      if (isset($result)) {

        echo "" . $api->matchCodeToType($result) . ".innerHTML='" . $api->matchCodeToMessage($result) . "';";
        echo "\n";
        echo "" . $api->matchCodeToType($result) . ".style.display = 'block';";
      }
      ?>
      <?php if (isset($_GET['register'])) {

        echo "
        
        $(function() {
$('#register').tab('show');});";
      }

      if (isset($result) and $result == "ERR_RESET_STEP_2_OK" and !isset($_GET["redirect"])) {
        echo "window.location = './index.php?result=ERR_RESET_STEP_2_OK&redirect'";
      }

      ?>
    </script>
</body>

</html>