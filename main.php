<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);

	//includes hidden info to get access to the database
	include 'dbInfo.php';

	//Used to store database information
	$id = NULL;
	$name = NULL;
	$category = NULL;
	$length = NULL;
	$rented = NULL;
	$filterBy = NULL;

	//Connects to the database and gives an error if it does not connect properly
	$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
	if(!$mysqli || $mysqli->connect_errno){
		echo 'Cannot log in to MySQL: (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error;
	} 
	
	//If add movies is filled out, it error handles and adds the movies tot he database
	if(isset($_POST['addMovie'])){

			$name = $_POST['name'];
			$category = $_POST['category'];
			$length = $_POST['length'];
			$rented = 1;

			//Checks if any of the post form textboxes are blank 
			if($name == null){
				echo 'Name must be filled in...<br/>';
			}
			if($category == null){
				echo 'Category must be filled in...<br/>';
			}
			if($length == null){
				echo 'Length must be filled in...<br/>';
			}	

			//If there are no errors, proceed to adding the movies to the database
			if(!($name == null || $category == null || $length == null)){
				if(!($stmt = $mysqli->prepare("INSERT INTO movieRental(name, category, length, rented) VALUES ('$name', '$category', '$length', '$rented')"))){
					echo 'Prepare video failed...';
				}
				if(!$stmt->execute()){
					echo 'Execute video failed...';
				}
			} else{
				echo 'Video has not been added... Please fix the entries and try again...';
			}

	}

	//Updates available movie to be checked out
	if(isset($_POST['checkoutMovie'])){
		//Gets the ID number for the video
		$id = $_POST['getID'];
		if(!($stmt = $mysqli->prepare("UPDATE movieRental SET rented = 0 WHERE id = '$id'"))){
			echo 'Prepare video failed...';
		}
		if(!$stmt->execute()){
			echo 'Execute video failed...';
		}
	}

	//Updates checked out movie to be available
	if(isset($_POST['returnMovie'])){
		//Gets the ID number for the video
		$id = $_POST['getID'];
		if(!($stmt = $mysqli->prepare("UPDATE movieRental SET rented = 1 WHERE id = '$id'"))){
			echo 'Prepare video failed...';
		}
		if(!$stmt->execute()){
			echo 'Execute video failed...';
		}
	}

	//Deletes a line from the SQL database.
	if(isset($_POST['deleteMovie'])){
		//Gets the ID number for the video
		$id = $_POST['getID'];
		if(!($stmt = $mysqli->prepare("DELETE FROM movieRental WHERE id = '$id'"))){
			echo 'Prepare video failed...';
		}
		if(!$stmt->execute()){
			echo 'Execute video failed...';
		}
	}

	//Deletes all lines in the database
	if(isset($_POST['delete'])){
		//Deletes all lines
		if(!($stmt = $mysqli->prepare("DELETE FROM movieRental"))){
			echo 'Prepare video failed...';
		}
		if(!$stmt->execute()){
			echo 'Execute video failed...';
		}
	}

	//Filters by category
	if(isset($_GET['filterCategory'])){
		$filterBy = $_GET['filter'];
	  
	}


?>


<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Assignment 5 PHP Part 2</title>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<h3>Add new video here</h3>
		<form action='main.php' method='POST'>
			Name: <input type='text' name='name'><br>
			Category: <input type='text' name='category'><br>
			Length (minutes): <input type='number' name='length'><br>
			<input type='submit' name='addMovie' value="Add Movie">
		</form>
		<form action='main.php' method='POST'>
			<input type='submit' name='delete' value="Delete All Records">
		</form>
		<form action='main.php' method='GET'>
			Filter by: <select name="filter">
				<?php
					//Accesses the database to filter by category
					//Learned from the lecture videos for week 6
					$filter = array();
					array_push($filter, 'No Filter');
					if(!($stmt = $mysqli->prepare('SELECT DISTINCT category FROM movieRental'))){
						echo 'Prepare failed: (' . $mysqli->errno . ') ' . $mysqli->error;
					}
					if(!$stmt->execute()){
						echo 'Execute failed: (' . $stmt->errno . ') ' . $stmt->error;
					}
					if(!$stmt->bind_result($category)){
						echo 'Binding parameters failed: (' . $stmt->errno . ') ' . $stmt->error;
					}
					while($stmt->fetch()){
						array_push($filter, $category);
					}
					
					//Removes duplicate values in an the filter array
					$category = array_unique($filter);
					
					//Displays the options
					foreach($filter as $value){
						echo "<option value=\"$value\">$value</option>";
					}
				?>
			</select>
			<input type='submit' name='filterCategory' value='Filter'>
		</form>
		<table>
			<caption>Video Rental</caption>
			<thead>
				<tr>
	  			<th>ID</th>
	  			<th>Name</th>
	  			<th>Category</th>
	  			<th>Length</th>
	  			<th>Rented</th>
	  			<th>Check In or Check Out</th>
	  			<th>Delete</th>
				<tr>
			</thead>
			<tbody>
				<?php
					//Displays the table contents gathered from the query and filters based on filter settings
					//Learned from the lecture videos for week 6
					if($filterBy === NULL || $filterBy == "No Filter"){
						if(!($stmt = $mysqli->prepare('SELECT id, name, category, length, rented FROM movieRental'))){
							echo 'Prepare failed: (' . $mysqli->errno . ') ' . $mysqli->error;
						}
					} else{
						if(!($stmt = $mysqli->prepare("SELECT id, name, category, length, rented FROM movieRental WHERE category = '$filterBy'"))){
							echo 'Prepare failed: (' . $mysqli->errno . ') ' . $mysqli->error;
						}
					}
					if(!$stmt->execute()){
						echo 'Execute failed: (' . $stmt->errno . ') ' . $stmt->error;
					}
					if(!$stmt->bind_result($id, $name, $category, $length, $rented)){
						echo 'Binding parameters failed: (' . $stmt->errno . ') ' . $stmt->error;
					}
					while($stmt->fetch()){
						echo '<tr>';
						echo '<td>' . $id . '</td>';
						echo '<td>' . $name . '</td>';
						echo '<td>' . $category . '</td>';
						echo '<td>' . $length . '</td>';

						//Displays available if 1. Otherwise, checked out if 2.
						if($rented === 1){
							echo '<td>Available</td>';
						}
						else{
							echo '<td>Checked Out</td>';
						}

						//Displays the checkout and delete button
						if($rented ===1){
							echo "<td>
											<form action=\"main.php\" method=\"POST\">
												<input type=\"hidden\" name =\"getID\" value=\"" . $id . "\">
												<input type=\"submit\" name=\"checkoutMovie\" value=\"Checkout Movie\">
											</form>
										</td>";
						} else{
							echo "<td>
											<form action=\"main.php\" method=\"POST\">
												<input type=\"hidden\" name =\"getID\" value=\"" . $id . "\">
												<input type=\"submit\" name=\"returnMovie\" value=\"Return Movie\">
											</form>
										</td>";							
						}

						echo "<td>
										<form action=\"main.php\" method=\"POST\">
											<input type=\"hidden\" name =\"getID\" value=\"" . $id . "\">
											<input type=\"submit\" name=\"deleteMovie\" value=\"Delete Record\">
										</form>
									</td>";				

						echo '</tr>';
					 }
				?>
		</tbody>
		</table>
	</body>
</html>