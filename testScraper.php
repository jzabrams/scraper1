<?php
// setup simple-quiz database connection
try {
    $db = new PDO("mysql:host=172.19.0.30", "zach", "tf3n56G4Z!");
} catch (PDOException $e) {
    die();
}



$quizID = 25;
$CURRENTDURECTORY = 'scrapedFiles/q14/';


$stmt = $db->prepare("use `simple-quiz`");
$stmt->execute();

$stmt = $db->prepare("select max(num) as max from questions where quiz_id=?");
$stmt->execute([$quizID]);
$maxQNum = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['max'] ?? 0;

$curQNum = $maxQNum + 1;

$count = 0;

$questions = [];
$possible = ["A", "B", "C", "D", "E", "F"];




$files = scandir($CURRENTDURECTORY);
foreach($files as $file) {
    $handle = fopen($CURRENTDURECTORY. $file, "r");
    if ($handle) {
        while (($line = fgets($handle)) !== false) {
            // process the line read.

            if (preg_match('/.*<li id="contentListItem:.*"	class="clearfix read">.*$/', $line)) {
                //get question block
                $count++;
                $ansPosition = 0;
                $fullQA = [];


                while (((($line = fgets($handle)) !== false))) {


                    //get question

                    if (strpos($line, '<td colspan=2 valign="top" width="100%"><div class="vtbegenerated inlineVtbegenerated">') === 0) {


                        $fullQA['question'] = get_string_between($line, '<td colspan=2 valign="top" width="100%"><div class="vtbegenerated inlineVtbegenerated">', '</div>');
                    }


                    //get selected answer


                    if (strpos($line, '<div class="vtbegenerated inlineVtbegenerated">') === 0) {


                        $correctAnswerText = '';
                        $preProAns = get_string_between($line, '<div class="vtbegenerated inlineVtbegenerated">', '</div>');
                        $processedAns = get_string_between($preProAns, "\">", "</label>");
                        if (empty($processedAns)) {
                            //set correct answer text
                            $correctAnswerText = $preProAns;
                            $fullQA['correctAns']['text'] = $correctAnswerText;
                        } else {

                            $fullQA['posAns'][$possible[$ansPosition]] = $processedAns;
                            $ansPosition++;
                        }

                    }


                    if (strpos($line, '</li>') !== false) {

                        //get correct answer letter

                        foreach ($fullQA['posAns'] as $letter => $answer) {


                            if ($fullQA['correctAns']['text'] == $answer) {
                                $fullQA['correctAns']['letters'][] = $letter;
                            }

                        }

                        $questions[] = $fullQA;
                        break;
                    }
                }
            }
        }

        fclose($handle);

//        echo PHP_EOL;
//        echo $count;
//        echo PHP_EOL;
//        echo PHP_EOL;
//        echo PHP_EOL;


        foreach ($questions as $key => $question) {


            $correct = $question['correctAns']['letters'][0];

            //echo $question['question'];

            $stmt = $db->prepare("select id from questions where text=? && quiz_id=?");
            $stmt->execute([$question['question'],$quizID]);
            $QExists = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['id'] ?? 0;

            if ($QExists == 0) {

                //insert question text into db
                $stmt = $db->prepare("insert into questions (num, quiz_id, text) values(?,?,?)");
                $stmt->execute([$curQNum, $quizID, $question['question']]);


                foreach ($question['posAns'] as $letter => $text) {

                    if ($letter == $correct) $isCor = 1;
                    else $isCor = 0;

                    $stmt = $db->prepare("insert into answers (question_num, quiz_id, text, correct) values(?,?,?,?)");
                    $stmt->execute([$curQNum, $quizID, $text, $isCor]);

                }

                $curQNum++;
//        print_r($question);

            }
        }

        echo PHP_EOL;
        echo 'DONE';
        echo PHP_EOL;

    } else {
        // error opening the file.
    }
}

function get_string_between($string, $start, $end)
{
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}


?>