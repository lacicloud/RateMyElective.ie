<?php
require_once 'vendor/autoload.php';

define('ROOT', dirname(__FILE__));
define('SALT', "");
define("MYSQL_HOST", "");
define("MYSQL_DBNAME", "");
define("MYSQL_USERNAME", "");
define("MYSQL_PASSWORD", "");
define("EMAIL_HOST", "");
define("EMAIL_USERNAME", "");
define("EMAIL_PASSWORD", "");


class RateMyElective
{

	public $allowed_emails = array("tcd.ie" => "tcd");

	public function getDB()
	{
		$db = new PDO('mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DBNAME, MYSQL_USERNAME, MYSQL_PASSWORD);
		return $db;
	}

	public function matchInstitution($email)
	{
		return $this->allowed_emails[array_pop(explode('@', $email))];
	}

	public function validateInfo($email, $password, $password_retyped)
	{

		if (empty($email) or empty($password) or empty($password_retyped)) {
			return "ERR_EMPTY_VALUES";
		}

		if ($password !== $password_retyped) {
			return "ERR_PASSWORDS_DO_NOT_MATCH";
		}

		if (strlen($password) < 8 or !preg_match("#[0-9]+#", $password) or !preg_match("#[a-zA-Z]+#", $password)) {
			return "ERR_PASS_WEAK";
		}

		if (preg_match('/\s/', $email) or strlen($email) < 5 or strlen($email) > 320 or !strpos($email, "@") or !strpos($email, ".")) {
			return "ERR_EMAIL_INVALID";
		}

		if (!array_key_exists(array_pop(explode('@', $email)), $this->allowed_emails)) {
			return "ERR_INSTITUTION_NOT_FOUND";
		}

		return "ERR_OK";
	}

	public function hashPassword($password)
	{
		return sha1(SALT . $password);
	}

	public function getNewVerificationString()
	{
		return bin2hex(openssl_random_pseudo_bytes(64));
	}

	public function getNewResetVerificationString()
	{
		return bin2hex(openssl_random_pseudo_bytes(32));
	}

	public function matchResetKeyToEmail($reset_key)
	{
		$db = $this->getDB();
		$sql = "SELECT email FROM users WHERE reset_key = :reset_key";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":reset_key", $reset_key);
		$stmt->execute();

		$email = key(array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC)));

		return $email;
	}

	public function forgotLoginStep1($email)
	{
		$id = $this->matchEmailtoID($email);

		if (!isset($id)) {
			return "ERR_EMAIL_INVALID";
		}

		$reset_key = $this->getNewResetVerificationString();

		$db = $this->getDB();
		$sql = "UPDATE users SET reset_key = :reset_key WHERE id = :id";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":reset_key", $reset_key);
		$stmt->bindParam(":id", $id);
		$stmt->execute();

		$this->sendEmail($email, "RateMyElective - Reset Account",  "<html><body><p>Hi there!</p><p>To reset your account, please click <a href='https://ratemyelective.ie/index.php?forgot_2&reset_key=" . $reset_key . "'>here</a> or go to <a href='https://ratemyelective.ie/index.php?forgot_2'>https://ratemyelective.ie/index.php?forgot_2</a> and enter your reset key: " . $reset_key . ".</p><p>Best regards,<br>RateMyElective</p></body></html>");

		return "ERR_RESET_STEP_1_OK";
	}

	public function forgotLoginStep2($reset_key, $password, $password_retyped)
	{
		$email = $this->matchResetKeyToEmail($reset_key);

		if (!isset($email) or is_null($email) or $reset_key == '1' or $reset_key == 1) {
			return "ERR_RESET_KEY_INVALID";
		}

		//verify data
		if ($this->validateInfo($email, $password, $password_retyped) !== "ERR_OK") {
			return "ERR_INVALID_INFO";
		}

		$password = $this->hashPassword($password);

		$db = $this->getDB();
		$sql = "UPDATE users SET password = :password WHERE email = :email";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":password", $password);
		$stmt->bindParam(":email", $email);
		$stmt->execute();

		$db = $this->getDB();
		$sql = "UPDATE users SET reset_key = '1' WHERE email = :email";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":email", $email);
		$stmt->execute();

		$this->sendEmail($email, "RateMyElective - Account Reset",  "<html><body><p>Hi there!</p><p>Your account's password was reset. If this was not you, please contact support at support@ratemyelective.ie as soon as possible!</p><p>Best regards,<br>RateMyElective</p></body></html>");

		return "ERR_RESET_STEP_2_OK";
	}


	public function getNumberOfUsers()
	{
		$db = $this->getDB();
		$sql = "SELECT count(*) FROM users";
		$stmt = $db->prepare($sql);
		$stmt->execute();

		return key(array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC)));
	}

	public function matchCodeToMessage($code)
	{
		$array = array("ERR_EXISTS" => "Sorry, email already exists in database!", "ERR_LOGIN_INCORRECT" => "Sorry, email or password incorrect!", "ERR_INVALID_INFO" => "Sorry, email or password could not be validated!", "ERR_INSTITUTION_NOT_FOUND" => "Sorry, your institution is not yet supported or you are not signing up with your institutional email!", "ERR_CAPTCHA" => "Sorry, captcha entered is incorrect!", "ERR_KEY_WRONG" => "Sorry, confirm key is incorrect!", "ERR_CONFIRM_OK" => "Successfully confirmed account!", "ERR_REGISTER_OK" => "Successfully created account! Please confirm it via your email address, check spam too!", "ERR_UNCONFIRMED" => "Account not confirmed! Please confirm it first!", "ERR_RESET_STEP_1_OK" => "Email sent, please check your email account!", "ERR_RESET_STEP_2_OK" => "Account\"s password reset!", "ERR_TOS_UNCHECKED" => "Please check the TOS box before proceeding!", "ERR_OK" => "Action Successfully completed!", "ERR_DELETE_OKAY" => "Successfully deleted comment!", "ERR_OK_SUBMITTED" => "Successfully submitted new comment!", "ERR_SUBMITTED" => "You already have a comment submitted!", "ERR_NOT_ALLOWED" => "Use of profaniy is not allowed!");
		return $array[$code];
	}

	public function matchCodeToType($code)
	{
		$array = array("ERR_EXISTS" => "error", "ERR_LOGIN_INCORRECT" => "error", "ERR_INVALID_INFO" => "error", "ERR_CAPTCHA" => "error", "ERR_KEY_WRONG" => "error", "ERR_CONFIRM_OK" => "success", "ERR_INSTITUTION_NOT_FOUND" => "error", "ERR_TOS_UNCHECKED" => "error", "ERR_REGISTER_OK" => "success", "ERR_UNCONFIRMED" => "warning", "ERR_RESET_STEP_1_OK" => "success", "ERR_RESET_STEP_2_OK" => "success", "ERR_OK" => "success", "ERR_DELETE_OKAY" => "success", "ERR_OK_SUBMITTED" => "success", "ERR_SUBMITTED" => "error", "ERR_NOT_ALLOWED" => "error");
		return $array[$code];
	}

	public function logEvent($event, $event_message)
	{
		chdir(ROOT);
		$message = date('l jS \of F Y h:i:s A') . " : " . " Event: " . $event . " Message: " . $event_message . "\n";
		error_log($message, 3, "logs/RateMyElective.log");
	}


	public function deleteUser($id)
	{
		$db = $this->getDB();

		$sql = "DELETE FROM users WHERE id = :id";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":id", $id);
		$stmt->execute();


		$sql = "DELETE FROM elective_reviews WHERE realID = :id";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":id", $id);
		$stmt->execute();

		return "ERR_OK";
	}

	public function regenerateSession()
	{
		session_regenerate_id(true);
	}

	public function matchEmailtoID($email)
	{
		$db = $this->getDB();
		$sql = "SELECT id FROM users WHERE email = :email";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":email", $email);
		$stmt->execute();

		$id = key(array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC)));

		return $id;
	}

	public function checkIfAlreadyExists($email)
	{
		$db = $this->getDB();
		$sql = "SELECT email FROM users WHERE email = :email";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":email", $email);
		$stmt->execute();

		$email = key(array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC)));

		if (!empty($email)) {
			return "ERR_EXISTS";
		} else {
			return "ERR_OK";
		}
	}

	public function createUser($email, $password, $password_retyped)
	{
		$email =  trim($email);

		//verify data
		if ($this->validateInfo($email, $password, $password_retyped) !== "ERR_OK") {
			return "ERR_INVALID_INFO";
		}

		//verify whether email already exists in DB
		if ($this->checkIfAlreadyExists($email) !== "ERR_OK") {
			return "ERR_EXISTS";
		}

		//check terms & conditions checkbox
		if (@count($_POST["checkbox"]) == 0) {
			//return "ERR_TOS_UNCHECKED";
		}

		$institution = $this->matchInstitution($email);
		$password = $this->hashPassword($password);
		$verification = $this->getNewVerificationString();

		$this->sendEmail($email, "RateMyElective - Confirm Account",  "<html><body><p>Hi there!</p><p>To confirm your account, please click <a href='https://ratemyelective.ie/confirm.php?key=" . $verification . "'>here</a>.</p><p>Best regards,<br>RateMyElective</p></body></html>");


		$reset_key = '1';

		$db = $this->getDB();
		$sql = "INSERT INTO users (email, password, confirmed, reset_key, institution) VALUES (:email, :password, :confirmed, :reset_key, :institution)";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(':email', $email);
		$stmt->bindParam(':password', $password);
		$stmt->bindParam(':confirmed', $verification);
		$stmt->bindParam(':reset_key', $reset_key);
		$stmt->bindParam(':institution', $institution);
		$stmt->execute();

		return "ERR_REGISTER_OK";
	}

	public function sendEmail($to, $subject, $body)
	{

		try {
			//sends email to user about signup/payment
			$title = "RateMyElective_Email";
			$transport = (new Swift_SmtpTransport(EMAIL_HOST, 465, "ssl"))
				->setUsername(EMAIL_USERNAME)
				->setPassword(EMAIL_PASSWORD)
				->setSourceIp("0.0.0.0");
			$mailer = new Swift_Mailer($transport);
			$logger = new \Swift_Plugins_Loggers_ArrayLogger();
			$mailer->registerPlugin(new \Swift_Plugins_LoggerPlugin($logger));
			$message = new Swift_Message("$title");
			$message
				->setSubject($subject)
				->setFrom(array("bot@ratemyelective.ie" => "RateMyElective"))
				->setTo(array("$to"))
				->setCharset('utf-8')
				->setBody($body, 'text/html');
			$mailer->send($message, $errors);
			$result = "ERR_OK";
		} catch (\Swift_TransportException $e) {
			$response = $e->getMessage();
			$result = "ERR_EMAIL_ERROR";
			$this->logEvent("ERR_EMAIL_ERROR", "Error while sending email to " . $to . " with subject " . $subject . " and body " . $body . " : " . $response);
		} catch (Exception $e) {
			$response = $e->getMessage();
			$result = "ERR_EMAIL_ERROR";
			$this->logEvent("ERR_EMAIL_ERROR", "Exception while sending email to " . $to . " with subject " . $subject . " and body " . $body . " : " . $response);
		}

		return $result;
	}

	public function checkConfirmed($id)
	{
		$db = $this->getDB();
		$sql = "SELECT confirmed FROM users WHERE id = :id";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":id", $id);
		$stmt->execute();

		$confirmed = key(array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC)));
		if ($confirmed == 1) {
			return "ERR_OK";
		} else {
			return "ERR_UNCONFIRMED";
		}
	}

	public function confirmUser($key)
	{
		$db = $this->getDB();
		$sql = "SELECT confirmed FROM users WHERE confirmed = :confirmed";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(':confirmed', $key);
		$stmt->execute();

		$key = key(array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC)));
		if (empty($key)) {
			return "ERR_KEY_WRONG";
		} else {
			$sql = "UPDATE users SET confirmed = 1 WHERE confirmed = :confirmed";
			$stmt = $db->prepare($sql);
			$stmt->bindParam(':confirmed', $key);
			$stmt->execute();

			return "ERR_CONFIRM_OK";
		}
	}

	public function loginUser($email, $password)
	{
		$email =  trim($email);

		//verify data
		if ($this->validateInfo($email, $password, $password) !== "ERR_OK") {
			return "ERR_INVALID_INFO";
		}

		$db = $this->getDB();
		$sql = "SELECT email, password, id FROM users WHERE email = :email";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":email", $email);
		$stmt->execute();

		$data = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
		$email_db = key($data);
		$password_db = @$data[$email][0]["password"];
		$id = @$data[$email][0]["id"];

		$password = $this->hashPassword($password);

		if ($password_db == $password and $email_db == $email) {
			if ($this->checkConfirmed($id) == "ERR_UNCONFIRMED") {
				return "ERR_UNCONFIRMED";
			} else {
				return $id;
			}
		} else {
			return "ERR_LOGIN_INCORRECT";
		}
	}

	public function getUserValues($id)
	{
		$db = $this->getDB();
		$sql = "SELECT * FROM users WHERE id = :id";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":id", $id);
		$stmt->execute();

		//flatten user array
		return call_user_func_array('array_merge', array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC)));
	}

	public function getElectives($institution)
	{
		$db = $this->getDB();
		$sql = "SELECT * FROM electives WHERE institution = :institution";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":institution", $institution);
		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
	}

	public function getElectiveStars($elective, $institution)
	{
		$db = $this->getDB();
		$sql = "SELECT AVG(stars) FROM elective_reviews WHERE name = :elective AND institution = :institution";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":elective", $elective);
		$stmt->bindParam(":institution", $institution);
		$stmt->execute();

		$average = key(array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC)));
		if ($average == "") {
			return "ERR_EMPTY_REVIEWS";
		} else {
			return $average;
		}
	}


	public function getElectiveStarsDifficulty($elective, $institution)
	{
		$db = $this->getDB();
		$sql = "SELECT AVG(stars_assessment_difficulty) FROM elective_reviews WHERE name = :elective AND institution = :institution";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":elective", $elective);
		$stmt->bindParam(":institution", $institution);
		$stmt->execute();

		$average = key(array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC)));
		if ($average == "") {
			return "ERR_EMPTY_REVIEWS";
		} else {
			return $average;
		}
	}

	public function verifyElectiveName($elective, $institution)
	{
		$db = $this->getDB();
		$sql = "SELECT name FROM electives WHERE name = :elective AND institution = :institution";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":elective", $elective);
		$stmt->bindParam(":institution", $institution);
		$stmt->execute();

		$name = key(array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC)));

		if ($name == $elective) {
			return "ERR_OK";
		} else {
			return "ERR_ELECTIVE_NOT_VERIFIED";
		}
	}

	public function getElectiveDescriptionByName($elective, $institution)
	{
		$db = $this->getDB();
		$sql = "SELECT description FROM electives WHERE name = :elective AND institution = :institution";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":elective", $elective);
		$stmt->bindParam(":institution", $institution);
		$stmt->execute();

		return key(array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC)));
	}

	public function getEntityTypeByName($elective, $institution)
	{
		$db = $this->getDB();
		$sql = "SELECT type_entity FROM electives WHERE name = :elective AND institution = :institution";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":elective", $elective);
		$stmt->bindParam(":institution", $institution);
		$stmt->execute();

		return key(array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC)));
	}

	public function getElectiveReviews($elective, $institution, $user_id)
	{
		$db = $this->getDB();
		$sql = "SELECT * FROM elective_reviews WHERE name = :elective AND institution = :institution ORDER BY realID = :realID DESC, date_submitted ASC";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":elective", $elective);
		$stmt->bindParam(":institution", $institution);
		$stmt->bindParam(":realID", $user_id);
		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
	}


	public function checkIfUserAlreadySubmittedReview($elective, $user_id, $institution)
	{
		$db = $this->getDB();

		$sql = "SELECT * FROM elective_reviews WHERE name = :elective AND institution = :institution AND realID = :user_id";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":elective", $elective);
		$stmt->bindParam(":institution", $institution);
		$stmt->bindParam(":user_id", $user_id);
		$stmt->execute();

		$name = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);

		if (!empty($name)) {
			return "ERR_SUBMITTED";
		} else {
			return "ERR_NOT_SUBMITTED";
		}
	}

	public function deleteReview($elective, $user_id, $institution)
	{
		$db = $this->getDB();
		$sql = "DELETE FROM elective_reviews WHERE name = :elective AND institution = :institution AND realID = :user_id";
		$stmt = $db->prepare($sql);
		$stmt->bindParam(":elective", $elective);
		$stmt->bindParam(":institution", $institution);
		$stmt->bindParam(":user_id", $user_id);
		$stmt->execute();

		return "ERR_DELETE_OKAY";
	}

	public function updateReview($elective, $stars, $stars_assessment_difficulty, $institution, $review_text, $user_id, $type_entity) {
		$db =  $this->getDB();

		$this->deleteReview($elective, $user_id, $institution);

		return $this->insertNewReview($elective, $stars, $stars_assessment_difficulty, $institution, $review_text, $user_id, $type_entity);

	}

	public function insertNewReview($elective, $stars, $stars_assessment_difficulty, $institution, $review_text, $user_id, $type_entity)
	{
		$db =  $this->getDB();

		$date_submitted = date('Y-m-d');

		if ($this->verifyReviewText($review_text) !== "ERR_OK") {
			return "ERR_NOT_ALLOWED";
		}

		if ($this->checkIfUserAlreadySubmittedReview($elective, $user_id, $institution) == "ERR_SUBMITTED") {
			return "ERR_SUBMITTED";
		}



		if (is_numeric($stars) && $stars > 0 && $stars <= 5 && is_numeric($stars_assessment_difficulty) && $stars_assessment_difficulty > 0 && $stars_assessment_difficulty <= 5) {
			$sql = "INSERT INTO elective_reviews (name, stars, stars_assessment_difficulty, review, date_submitted, realID, institution, type_entity) VALUES (:name, :stars, :stars_assessment_difficulty, :review, :date_submitted, :realID, :institution, :type_entity)";
			$stmt = $db->prepare($sql);
			$stmt->bindParam(':name', $elective);
			$stmt->bindParam(':stars', $stars);
			$stmt->bindParam(':stars_assessment_difficulty', $stars_assessment_difficulty);
			$stmt->bindParam(':review', $review_text);
			$stmt->bindParam(':date_submitted', $date_submitted);
			$stmt->bindParam(':realID', $user_id);
			$stmt->bindParam(':institution', $institution);
			$stmt->bindParam(':type_entity', $type_entity);
			$stmt->execute();

			return "ERR_OK_SUBMITTED";
		} else {
			return "ERR_NOT_ALLOWED";
		}
	}

	public function verifyReviewText($review_text)
	{

		if (\ConsoleTVs\Profanity\Builder::blocker($review_text)->clean() !== true) {
			return "ERR_NOT_ALLOWED";
		} else {
			return "ERR_OK";
		}
	}

	public function setupDB()
	{
		$db =  $this->getDB();

		$sql = "CREATE TABLE users (id INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT, email TEXT, password TEXT, confirmed TEXT, reset_key TEXT, institution TEXT)";
		$statement = $db->prepare($sql);
		$statement->execute();

		$sql = 'CREATE TABLE electives (name TEXT, description TEXT, institution TEXT, type_entity TEXT)';
		$statement = $db->prepare($sql);
		$statement->execute();

		$sql = 'CREATE TABLE elective_reviews (name TEXT, stars DECIMAL, stars_assessment_difficulty DECIMAL, review TEXT, date_submitted DATE, realID text, institution TEXT, type_entity TEXT)';
		$statement = $db->prepare($sql);
		$statement->execute();
	}

	public function createData()
	{

		$csv = array_map('str_getcsv', file('data/electives.csv'));
		$institution = "tcd";
		$type_entity = "elective";
		foreach ($csv as $key => $value) {
			var_dump($value);
			$db = $this->getDB();
			$sql = "INSERT INTO electives (name, description, institution, type_entity) VALUES (:name, :description, :institution, :type_entity)";
			$stmt = $db->prepare($sql);
			$stmt->bindParam(':name', $value[0]);
			$stmt->bindParam(':description', $value[1]);
			$stmt->bindParam(':institution', $institution);
			$stmt->bindParam(':type_entity', $type_entity);
			$stmt->execute();

		}


		   $csv = array_map('str_getcsv', file('data/exchanges.csv'));
                $institution = "tcd";
                $type_entity = "exchange";
		$description = "";
                foreach ($csv as $key => $value) {
                        var_dump($value);
                        $db = $this->getDB();
                        $sql = "INSERT INTO electives (name, description, institution, type_entity) VALUES (:name, :description, :institution, :type_entity)";
                        $stmt = $db->prepare($sql);
                        $stmt->bindParam(':name', $value[0]);
                        $stmt->bindParam(':description', $description);
                        $stmt->bindParam(':institution', $institution);
                        $stmt->bindParam(':type_entity', $type_entity);
                        $stmt->execute();

                }

	}
}


