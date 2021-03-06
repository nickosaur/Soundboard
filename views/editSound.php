<?php
	session_start();
	require_once '../../../config.inc';

	if ( !(isset($_SESSION['user'])) ){
	    header("Location: ./login.php");
	    die();
	}
	define('MAX_BYTE_UPLOAD', '2000000');

	$username = $_SESSION['user'];
	$sql = "SELECT * FROM users WHERE BINARY username='$username'";
	$result = mysqli_query($conn, $sql);

	$getID = mysqli_fetch_array($result );
	$userID = (int)$getID['id'];

	function readable_filesize($bytes, $decimals = 2){
		  $sz = 'BKMGTP';
		  $factor = floor((strlen($bytes) - 1) / 3);
		    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}

	  $sound_id = $_GET['sound_id'];

	  $stmt = $conn->prepare("SELECT * FROM sound WHERE sound_id = ?");
	  $stmt->bind_param('s', $sound_id);
	  $stmt->execute();
	  /*$result = mysqli_query($conn, $sql);*/
	  $result = $stmt->get_result();
	  $get_soundboard = mysqli_fetch_array($result);
	  $e_soundboard_id = (int)$get_soundboard['soundboard_id'];

	  $sql = "SELECT * FROM soundboard WHERE soundboard_id = '$e_soundboard_id'";
	  $result = mysqli_query($conn, $sql);
	  $get_owner = mysqli_fetch_array($result);
	  $owner_id = (int)$get_owner['id'];

	  if(($userID == $owner_id) || $_SESSION['isAdmin'] == true)
	  {
		$e_sound_name = $get_soundboard['sound_name'];
		$e_sound_description = $get_soundboard['sound_description'];
		$e_sound = $get_soundboard['sound'];
	  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Add/Create Sounds">
    <meta name="author" content="Mario Palma">
    <title>Edit Sound</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/jumbotron-narrow.css" rel="stylesheet">

  </head>

  <body>
    <br>
    <div class="container">
      <div class="header clearfix">
        <nav>
          <ul class="nav nav-tabs pull-right">
	    <li role="presentation"><a href="../index.php">Home</a></li>
	    <li role="presentation"><a href="dashboard.php">
		<?php echo $_SESSION['user']?>'s Dashboard</a></li> 
	  </ul>
        </nav>
      </div>

      <div class="jumbotron text-center">
        <h1>Edit Sound</h1>
	<p class="lead">
		<?php
			if(isset($_SESSION['alphanumeric']) && $_SESSION['alphanumeric']){
				echo "Only alphanumeric can be used in fields <br>";
			}

			unset($_SESSION['alphanumeric']);
		?>
		
	</p>
	<form action="" method="POST" enctype="multipart/form-data" >
		<div class="form-group">
		<input type="text" pattern="[a-zA-Z0-9]{1,29}" 
			title="Alphanumeric characters only" class="form-control"
			name="sound_name" value="<?php echo $e_sound_name?>" placeholder="SOUND NAME" required>
		</div> 
		<br> <br>

		<div class="form-group">
		<input type="text" pattern="[a-zA-Z0-9]{1,20}" 
			title="Alphanumeric characters where input is NOT longer
			than 20"
			class="form-control"
			name="sound_description" value="<?php echo $e_sound_description?>" placeholder="DESCRIPTION"> 
		</div>
		<br><br>
		
		<div class = "form-group">
		<input type="file" name="sound_file" value="<?php echo $e_sound?>" id = "sound_file" accept = "audio/*" required/>
		</div>
		<br>
		<br>

		<input type="submit" class="btn btn-lg btn-success"  value="Upload Audio" name = "save_audio"/>
	</form> 
  </div>
<!--Add sound to database-->
	<?php
		$soundboard_id = $_SESSION['curr_soundboard_id'];
		$sound_name = $_REQUEST['sound_name'];
		$sound_description = $_REQUEST['sound_description'];
		
		if(isset($_FILES['sound_file']['name'])){
			$name = $_FILES['sound_file']['name'];
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = finfo_file($finfo, $_FILES['sound_file']['tmp_name']);
			if($mime == "audio/wav" || $mime == "audio/x-wav" || $mime == "audio/mp3" || $mime == "audio/mpeg3" ){
				$sizefile = $_FILES['sound_file']['tmp_name'];
				if(filesize($_FILES['sound_file']['tmp_name']) > MAX_BYTE_UPLOAD){
					echo "<div class=\"alert alert-danger\"> <strong>ERROR:</strong> $name is too large at" . readable_filesize($sizefile) . "!(max file size is 2MB) </div>";
					die();
				}
				if(move_uploaded_file($_FILES['sound_file']['tmp_name'], "../audio/$name")){                                
					$sound = "../audio/" . $name;                       
				}
				else{
					echo "<div class=\"alert alert-warning\">
					      <strong>Warning!</strong> A file with that name already exists in our database! Try again!
					      </div>";
			                die();

				}
				//echo "<script>alert(\" $mime \")</script>";
				  if(($userID == $owner_id) || $_SESSION['isAdmin'] == true)
				  {
				    $sql = "UPDATE sound SET sound_name='$sound_name', sound='$sound', sound_description='$sound_description' WHERE sound_id = '$sound_id'";
				  }
				$result = mysqli_query($conn, $sql);
				echo "<div class=\"alert alert-success\">
				<strong>Success!</strong> $name was successfully uploaded</div>";
			}
			else{
				//echo "<script>alert(\" $mime \")</script>";
				echo "<div class=\"alert alert-danger\"> <strong>ERROR:</strong> $name is the wrong file type!(choose wav or mp3) </div>";
				die();

			}
			
		}
		else{
			echo "<div class=\"alert alert-warning\">
				  <strong>Warning!</strong> Something went wrong! We could not obtain your chosen file. Perphaps it's too large. Try again with a file no bigger than 2MB.
				  </div>";
			die();
		}
	?>

  </body>
</html>
