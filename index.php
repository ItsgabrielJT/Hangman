<?php
session_start();

$LETRAS = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
$WON = false;

// temp variables for testing

$guess = "HANGMAN";
$maxLetters = strlen($guess) - 1;
$responses = ["H", "G", "A"];


// ALl the body parts
$cuerpo_partes = ["nohead", "head", "body", "hand", "hands", "leg", "legs"];


// Random words for the game and you to guess
$words = [
    "HANGMAN", "BUTTERFLY", "APPLE", "INSIDIOUSLY", "DUPLICATE",
    "CASUALTY", "GLOOMFUL"
];


function getPartImage($part)
{
    return "./images/hangman_" . $part . ".png";
}


function startGame()
{
}

// restart the game. Clear the session variables
function restartGame()
{
    session_destroy();
    session_start();
}

// Get all the hangman Parts
function getParts()
{
    global $cuerpo_partes;
    return isset($_SESSION["parts"]) ? $_SESSION["parts"] : $cuerpo_partes;
}

// add part to the Hangman
function addPart()
{
    $parts = getParts();
    array_shift($parts);
    $_SESSION["parts"] = $parts;
}

// get Current Hangman Body part
function getCurrentPart()
{
    $parts = getParts();
    return $parts[0];
}

// get the current words
function getCurrentWord()
{
    //  return "HANGMAN"; // for now testing
    global $words;
    if (!isset($_SESSION["word"]) && empty($_SESSION["word"])) {
        $key = array_rand($words);
        $_SESSION["word"] = $words[$key];
    }
    return $_SESSION["word"];
}


// user responses logic

// get user response
function getCurrentResponses()
{
    return isset($_SESSION["responses"]) ? $_SESSION["responses"] : [];
}

function addResponse($letter)
{
    $responses = getCurrentResponses();
    array_push($responses, $letter);
    $_SESSION["responses"] = $responses;
}

// check if pressed letter is correct
function isLetterCorrect($letter)
{
    $word = getCurrentWord();
    $max = strlen($word) - 1;
    for ($i = 0; $i <= $max; $i++) {
        if ($letter == $word[$i]) {
            return true;
        }
    }
    return false;
}

// is the word (guess) correct

function isWordCorrect()
{
    $guess = getCurrentWord();
    $responses = getCurrentResponses();
    $max = strlen($guess) - 1;
    for ($i = 0; $i <= $max; $i++) {
        if (!in_array($guess[$i],  $responses)) {
            return false;
        }
    }
    return true;
}

// check if the body is ready to hang

function isBodyComplete()
{
    $parts = getParts();
    // is the current parts less than or equal to one
    if (count($parts) <= 1) {
        return true;
    }
    return false;
}

// manage game session

// is game complete
function gameComplete()
{
    return isset($_SESSION["gamecomplete"]) ? $_SESSION["gamecomplete"] : false;
}


// set game as complete
function markGameAsComplete()
{
    $_SESSION["gamecomplete"] = true;
}

// start a new game
function markGameAsNew()
{
    $_SESSION["gamecomplete"] = false;
}



/* Detect when the game is to restart. From the restart button press*/
if (isset($_GET['start'])) {
    restartGame();
}


/* Detect when Key is pressed */
if (isset($_GET['kp'])) {
    $currentPressedKey = isset($_GET['kp']) ? $_GET['kp'] : null;
    // if the key press is correct
    if (
        $currentPressedKey
        && isLetterCorrect($currentPressedKey)
        && !isBodyComplete()
        && !gameComplete()
    ) {

        addResponse($currentPressedKey);
        if (isWordCorrect()) {
            $WON = true; // game complete
            markGameAsComplete();
        }
    } else {
        // start hanging the man :)
        if (!isBodyComplete()) {
            addPart();
            if (isBodyComplete()) {
                markGameAsComplete(); // lost condition
            }
        } else {
            markGameAsComplete(); // lost condition
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style.css" rel="stylesheet" type="text/css">
    <title> El Ahorcado Game </title>
</head>

<body>
    <div class="main-app">
        <div class="image">
            <img style="width:80%; display:inline-block;" src="<?php echo getPartImage(getCurrentPart());?>">
        </div>

        <div class="header">
            <h1> El Juego del Ahorcado </h1>
            <div class="controls">
                <form method="get">
                    <?php
                    $max = strlen($LETRAS) - 1;
                    for ($i = 0; $i <= $max; $i++) {
                        echo "<button type='submit' name='kp' value='" . $LETRAS[$i] . "' >"
                            . $LETRAS[$i] . " 
                        </button>";

                        if ($i % 7 == 0 && $i > 0) {
                            echo '<br>';
                        }
                    }
                    ?>
                    <br><br>
                    <!-- Restart game button -->
                    <button type="submit" name="start">Restart Game</button>
                </form>
            </div>
        </div>

        <div style="margin-top:20px; padding:15px; background: lightseagreen; color: #fcf8e3">
            <!-- Display the current guesses -->
            <?php 
                 $guess = getCurrentWord();
                 $maxLetters = strlen($guess) - 1;
                for($j=0; $j<= $maxLetters; $j++): $l = getCurrentWord()[$j]; ?>
                    <?php if(in_array($l, getCurrentResponses())):?>
                        <span style="font-size: 35px; border-bottom: 3px solid #000; margin-right: 5px;"><?php echo $l;?></span>
                    <?php else: ?>
                        <span style="font-size: 35px; border-bottom: 3px solid #000; margin-right: 5px;">&nbsp;&nbsp;&nbsp;</span>
                    <?php endif;?>
                <?php endfor;?>
        </div>
    </div>
</body>

</html>